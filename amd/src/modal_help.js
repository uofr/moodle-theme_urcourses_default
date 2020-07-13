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
 * Theme Boost Campus - ModalHelp class
 *
 * @package    theme_urcourses_default
 * @author     John Lane
 *
 */

import $ from 'jquery';
import Notification from 'core/notification';
import Templates from 'core/templates';
import CustomEvents from 'core/custom_interaction_events';
import KeyCodes from 'core/key_codes';
import Modal from 'core/modal';
import ModalEvents from 'core/modal_events';
import ModalRegistry from 'core/modal_registry';
import ModalHelpAjax from 'theme_urcourses_default/modal_help_ajax';
import {FuzzySet} from 'theme_urcourses_default/fuzzyset';

const SELECTORS = {
    SEARCH: '#modal_help_search',
    SEARCH_BUTTON: '#modal_help_search_btn',
    SEARCH_CLEAR: '#modal_help_search_clear',
    SUGGESTIONS: '#modal_help_search_suggestions',
    SUGGESTION_ITEM: '.modal-help-suggestion-item',
    SEARCH_RESULT: '.modal-help-search-result'
};

const TEMPLATES = {
    MODAL_HELP_CONTENT: 'theme_urcourses_default/modal_help_content',
    MODAL_HELP_SEARCH_RESULTS: 'theme_urcourses_default/modal_help_search_results'
};

/**
 * Help modal for UR Courses.
 *
 * @class ModalHelp
 * @property {Number} contextId - ID of the current page's context. Null until it is set in init.
 * @property {Array} topicList - List of topcis for which there is help. Null until it is set in initContent.
 * @property {FuzzySet} topicIndex - FuzzySet to enable autocomplete.
 * @property {Number} minSearchScore - How similar a search term has to be to attempt autocomplete.
 * @property {JQuery} searchBox - Search field jQuery element.
 * @property {JQuery} searchButton - Search button jQuery object.
 * @property {JQuery} searchClear - Clear search jQuery object.
 * @property {JQuery} suggestionBox - Suggestion box jQuery object.
 * @property {Number} suggestionItemIndex - Index of the autocomplete suggestion being selected.
 */
export default class ModalHelp extends Modal {

    constructor(root) {
        super(root);

        this.contextId = null;
        this.topicList = null;
        this.topicIndex = FuzzySet();
        this.minSearchScore = 0;
        this.searchBox = this.modal.find(SELECTORS.SEARCH);
        this.searchButton = this.modal.find(SELECTORS.SEARCH_BUTTON);
        this.searchClear = this.modal.find(SELECTORS.SEARCH_CLEAR);
        this.suggestionBox = this.modal.find(SELECTORS.SUGGESTIONS);
        this.suggestionItemIndex = 0;
    }

    /**
     * Sets contextid and registers events.
     *
     * @method init
     * @param {Number} contextId Id of the current page's context.
     */
    init(contextId) {
        this.contextId = contextId;

        this.getRoot().on(ModalEvents.shown, () => {
            this.initContent();
        });

        this.getRoot().on(ModalEvents.hidden, () => {
            this.resetModal();
        });

        this.getRoot().on('click', (e) => {
            if (e.target !== this.searchBox[0] && e.target !== this.suggestionBox[0]) {
                this.hideSuggestionBox();
            }
        });

        this.searchBox.on('input', () => {
            if (this.searchBox.val()) {
                this.showSearchClear();
            }
            else {
                this.hideSearchClear();
            }

            this.updateSuggestions();
        });

        this.searchBox.on('focus', () => {
            this.updateSuggestions();
            this.suggestionBox[0].scrollTop = 0;
        });

        this.searchClear.on('click', () => {
            this.clearSearch();
        });

        this.searchButton.on('click', () => {
            const query = this.searchBox.val();
            this.search(query);
            this.hideSuggestionBox();
        });

        this.searchBox.on('keydown', (e) => {
            if (e.keyCode === KeyCodes.enter) {
                const query = this.searchBox.val();
                this.search(query);
                this.getModal().focus();
                this.hideSuggestionBox();
            }
        });

        this.suggestionBox.on(CustomEvents.events.activate, SELECTORS.SUGGESTION_ITEM, (e) => {
            const topicTitle = $(e.target).attr('data-value');
            const topic = this.topicList.find(topic => topic.title.toLowerCase() === topicTitle.toLowerCase());
            const url = topic.url;
            this.searchBox.val(topicTitle);
            this.showSearchClear();
            this.getPage(url);
        });

        this.getModal().on(CustomEvents.events.activate, SELECTORS.SEARCH_RESULT, (e) => {
            const url = $(e.target).attr('data-url');
            this.getPage(url);
        });

        this.searchBox.on('keydown', (e) => {
            if (e.keyCode === KeyCodes.arrowUp || e.keyCode === KeyCodes.arrowLeft) {
                e.preventDefault();
                this.suggestionItemIndex = this.getSuggestionItems().length - 1;
                this.getSuggestionItems().eq(this.suggestionItemIndex).focus();
            }
            else if (e.keyCode === KeyCodes.arrowDown || e.keyCode === KeyCodes.arrowRight) {
                e.preventDefault();
                this.suggestionItemIndex = 0;
                this.getSuggestionItems().eq(this.suggestionItemIndex).focus();
            }
        });

        this.suggestionBox.on('keydown', SELECTORS.SUGGESTION_ITEM, (e) => {
            if (e.keyCode === KeyCodes.arrowUp || e.keyCode === KeyCodes.arrowLeft) {
                e.preventDefault();
                this.suggestionItemIndex--;
                if (this.suggestionItemIndex < 0) {
                    this.suggestionItemIndex = this.getSuggestionItems().length - 1;
                }
                this.getSuggestionItems().eq(this.suggestionItemIndex).focus();
            }
            else if (e.keyCode === KeyCodes.arrowDown || e.keyCode === KeyCodes.arrowRight) {
                e.preventDefault();
                this.suggestionItemIndex++;
                if (this.suggestionItemIndex >= this.getSuggestionItems().length) {
                    this.suggestionItemIndex = 0;
                }
                this.getSuggestionItems().eq(this.suggestionItemIndex).focus();
            }
        });

    }

    /**
     * Sets up topic list and outputs landing page.
     *
     * @method initContent
     */
    async initContent() {
        try {
            const topicList = await ModalHelpAjax.getTopicList(this.contextId);
            this.topicList = topicList;
            for (const topic of topicList) {
                this.topicIndex.add(topic.title);
            }

            const landingPage = await ModalHelpAjax.getLandingPage(this.contextId);
            const renderPromise = Templates.render(TEMPLATES.MODAL_HELP_CONTENT, {html: landingPage.html});
            this.setBody(renderPromise);
        }
        catch(error) {
            Notification.exception(error);
        }
    }

    /**
     * Resets modal content.
     *
     * @method resetModal
     */
    resetModal() {
        this.setBody('');
        this.searchBox.val('');
        this.suggestionBox.html('');
    }

    /**
     * Updates the suggestion list based on whatever's in the search box.
     *
     * @method updateSuggestions
     */
    updateSuggestions() {
        const searchValue = this.searchBox.val();
        const topicIndex = this.topicIndex;
        const suggestionBox = this.suggestionBox;

        let suggestions = topicIndex.get(searchValue, topicIndex.values(), this.minSearchScore);
        if (Array.isArray(suggestions[0])) {
            suggestions = suggestions.map(suggestion => suggestion[1]);
        }

        this.suggestionBox.html('');
        for (const suggestion of suggestions) {
            suggestionBox.append(`<a href="#" class="modal-help-suggestion-item"
                                    data-value="${suggestion}">${suggestion}</a>`);
        }

        this.showSuggestionBox();
    }

    /**
     * Searches the guides and displays results.
     *
     * @method search
     * @param {String} query
     */
    async search(query) {
        if (!query) {
            return;
        }
        try {
            const searchResults = await ModalHelpAjax.searchGuides(this.contextId, query);
            const renderPromise = Templates.render(TEMPLATES.MODAL_HELP_SEARCH_RESULTS, {
                searchResults: searchResults.results,
                query: searchResults.query
            });
            this.setBody(renderPromise);
        }
        catch (error) {
            Notification.exception(error);
        }
    }

    /**
     * Fetches and outputs the guide page specified by url.
     *
     * @method getPage
     * @param {String} url The url without the domain (ie /guides/instructor/...)
     */
    async getPage(url) {
        try {
            const guidePage = await ModalHelpAjax.getGuidePage(url);
            const renderPromise = Templates.render(TEMPLATES.MODAL_HELP_CONTENT, {html: guidePage.html, title: guidePage.title});
            this.setBody(renderPromise);
        }
        catch (error) {
            Notification.exception(error);
        }
    }

    /**
     * Clears whatever is in the search box.
     *
     * @method clearSearch
     */
    clearSearch() {
        this.searchBox.val('');
        this.hideSearchClear();
    }

    /**
     * Removes d-none class from suggestion box.
     *
     * @method showSuggestionBox
     */
    showSuggestionBox() {
        this.suggestionBox.removeClass('d-none');
    }

    /**
     * Adds d-none class to suggestion box.
     *
     * @method hideSuggestionBox
     */
    hideSuggestionBox() {
        this.suggestionBox.addClass('d-none');
    }

    /**
     * Removes d-none class from clear search button.
     *
     * @method showSearchClear
     */
    showSearchClear() {
        this.searchClear.removeClass('d-none');
    }

    /**
     * Adds d-none class to clear search button.
     *
     * @method showSearchClear
     */
    hideSearchClear() {
        this.searchClear.addClass('d-none');
    }

    /**
     * Returns modal type. Used when creating the modal using ModalFactory.
     *
     * @method getType
     */
    static getType() {
        return 'theme_urcourses_default-help';
    }

    /**
     * Returns an array of suggestions in the suggestion box.
     *
     * @method getSuggestionItems
     */
    getSuggestionItems() {
        return this.suggestionBox.find(SELECTORS.SUGGESTION_ITEM);
    }

}

// Setup code for adding ModalHelp to the modal factory.
let registered = false;
if (!registered) {
    ModalRegistry.register(ModalHelp.getType(), ModalHelp, 'theme_urcourses_default/modal_help');
    registered = true;
}
