// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Theme Boost Campus - Partial string matching algorithm.
 *
 * Stores a dictionary of words.
 * Predicts partial strings based on words in dictionary. Returns the words most similar to the partial string.
 *
 * @module    theme_urcourses_default/fuzzysearch
 * @author John Lane
*/

/** @const _GRAM_SIZE_LOWER Gram size lower limit. */
const _GRAM_SIZE_LOWER = 2;

/** @const _GRAM_SIZE_UPPER Gram size upper limit. */
const _GRAM_SIZE_UPPER = 3;

/** @const _MIN_SCORE Minimum viable score to be considered a potential match. */
const _MIN_SCORE = 0;

/** @var _dictionary Stores words and their vector normals for each gram size. */
/**
 *
 * _dictionary = {
 *      gramSize: [ [normal, word], [normal, word], ... ]
 * };
 *
 */
let _dictionary = {};

/** @var _gramMap Stores grams from dictionary words. Grams stored along with where they appear and how often.*/
/**
 *
 * _gramMap = {
 *      gram: [indexToWord, count]
 * };
 *
 */
let _gramMap = {};

/** @var _originals Maps lowercase words to their original version. */
/**
 *
 * _originals = {
 *      word: original,
 *      word: original,
 *      ...
 * };
 *
 */
let _originals = {};

/**
 * Set dictionary to be used in @see search().
 * @param {Array} dictionary A list of words.
*/
const setDictionary = (dictionary) => {
    if (_dictionary) {
        _dictionary = {};
        _gramMap = {};
        _originals = {};
    }
    for (const word of dictionary) {
        for (let gramSize = _GRAM_SIZE_LOWER; gramSize < _GRAM_SIZE_UPPER + 1; gramSize++) {
            addWord(word, gramSize);
        }
    }
};

/**
 * Stores the specified word.
 * @param {String} word Word to add to dictionaries.
 * @param {Number} gramSize Gram size to use.
 */
const addWord = (word, gramSize = 3) => {
    const wordLower = word.toLowerCase();
    const grams = getGrams(word, gramSize);
    const wordNormal = getVectorNormal(grams);

    // save the original word
    _originals[wordLower] = word;

    // save word along with magnitude for the given gram size
    if (_dictionary[gramSize]) {
        _dictionary[gramSize].push([wordNormal, wordLower]);
    } else {
        _dictionary[gramSize] = [[wordNormal, wordLower]];
    }

    // store grams along with where their word appears in the dictionary
    const wordIndex = _dictionary[gramSize].length - 1;
    for (const gram in grams) {
        const gramCount = grams[gram];
        if (gram in _gramMap) {
            _gramMap[gram].push([wordIndex, gramCount]);
        } else {
            _gramMap[gram] = [[wordIndex, gramCount]];
        }
    }
};

/**
 * Search dictionary for potential matches of @param word.
 * @param {String} word
 * @return {Array} Array of potential matches ordered from most to least likely. Original words are returned.
 */
const search = (word) => {
    for (let gramSize = _GRAM_SIZE_UPPER; gramSize >= _GRAM_SIZE_LOWER; gramSize--) {
        const results = lookup(word, gramSize);
        if (results) return results.map(result => _originals[result[1]]);
    }
    return null;
};

/**
 * Lookup the word at the given gram size.
 * @param {String} word
 * @param {Number} gramSize
 * @return {Array} Array of words and their score [[score, word], [score, word], ...]
 */
const lookup = (word, gramSize) => {
    const grams = getGrams(word, gramSize);
    const wordNormal = getVectorNormal(grams);
    const matches = {}; // matches stores the index of each potential match and the associated dot product
    const dict = _dictionary[gramSize]; // dictionary of words for the given gram size
    const results = [];

    for (const gram in grams) {
        const gramCount = grams[gram];
        if (gram in _gramMap) { // if gram is in _gramMap, there are potential matching words
            for (const [index, count] of _gramMap[gram]) { // for each gram in the map, get the index of the match and count
                // get the dot product of the two vectors
                if (index in matches) {
                    matches[index] += gramCount * count;
                } else {
                    matches[index] = gramCount * count;
                }
            }
        }
    }

    if (!matches) return null; // if there are no potential matches, return null

    // for each potential match, calculate the cosine similarity score (divide dot product by the product of each normal)
    for (const matchIndex in matches) {
        const dotProduct = matches[matchIndex];
        const [matchNormal, matchingWord] = dict[matchIndex];
        const crossProduct = wordNormal * matchNormal;
        const cosineSimilarity = dotProduct / crossProduct;
        results.push([cosineSimilarity, matchingWord]);
    }

    return results.sort((a, b) => b[0] - a[0]).filter((match) => match[0] >= _MIN_SCORE);
};

/**
 * Calculate a word's vector normal from its grams.
 * @param {String} grams  Grams of word who's vector normal will be calculated.
 * @return {Number} Vector normal of word.
 */
const getVectorNormal = (grams) => {
    let sumOfSquareOfGramCounts = 0;
    for (const gram in grams) {
        const gramCount = grams[gram];
        sumOfSquareOfGramCounts += Math.pow(gramCount, 2);
    }
    return Math.sqrt(sumOfSquareOfGramCounts);
};

/**
 * Converts a string into a map of grams and their associated counts.
 *
 * @param {String} string String to be split.
 * @param {Number} gramSize Size of the substrings (grams).
 * @returns {Object} A map of grams along with how many times they appear in the string (ie: {gram: count...)
 */
const getGrams = (string, gramSize = 3) => {
    const gramMap = {};
    const stringConverted = convertString(string, gramSize);

    for (let i = 0; i < stringConverted.length - gramSize + 1; i++) {
        const gram = stringConverted.slice(i, i + gramSize);
        if (gram in gramMap) {
            gramMap[gram] += 1;
        } else {
            gramMap[gram] = 1;
        }
    }

    return gramMap;
};

/**
 * Converts string into a format which can be used by @see getGrams
 * Adds '-' to the start and end of a string, removes non-word characters.
 * If the string is smaller than the gramSize, '-'s are added to the end until the lengths match.
 *
 * @param {String} string - The string to be converted.
 * @param {Number} gramSize - Length of grams.
 * @returns {String} - String converted into a format that can be used by getGrams.
 */
const convertString = (string, gramSize = 3) => {
    const nonWordRegex = /[^a-zA-Z0-9\u00C0-\u00FF, ]+/g;
    const newString = '-' + string.toLowerCase().replace(nonWordRegex, '') + '-';
    if (newString.length < gramSize) {
        const lengthDifference = gramSize - string.length;
        return newString.padEnd((newString.length + lengthDifference), '-');
    } else {
        return newString;
    }
};

export default {
    setDictionary: setDictionary,
    search: search
};