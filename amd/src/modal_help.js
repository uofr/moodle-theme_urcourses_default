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
    SEARCH_RESULT: '.modal-help-search-result',
    BREADCRUMBS: '#breadcrumbs',
    BREADCRUMB_SEARCH: '#breadcrumb_search',
    BREADCRUMB_HOME: '#breadcrumb_home',
    BREADCRUMB_PAGE: '#breadcrumb_page'
};

const TEMPLATES = {
    MODAL_HELP_GUIDE_PAGE: 'theme_urcourses_default/modal_help_guide_page',
    MODAL_HELP_SEARCH_RESULTS: 'theme_urcourses_default/modal_help_search_results',
    MODAL_HELP_BREADCRUMBS: 'theme_urcourses_default/modal_help_breadcrumbs'
};

/**
 * Help modal for UR Courses.
 *
 * @class ModalHelp
 * @property {Number} contextId - Current page context id. Null until it is set in init().
 * @property {Array} topicList - List of topcis for which there is help. Null until it is set in initContent().
 * @property {FuzzySet} topicIndex - FuzzySet to enable autocomplete.
 * @property {Number} minSearchScore - How similar a search term has to be to attempt autocomplete.
 * @property {JQuery} searchBox - Search field jQuery element.
 * @property {JQuery} searchButton - Search button jQuery object.
 * @property {JQuery} searchClear - Clear search jQuery object.
 * @property {JQuery} suggestionBox - Suggestion box jQuery object.
 * @property {Number} suggestionItemIndex - Index of the suggestion being highlighted.
 * @property {Object} breadcrumbData - Used to render breadcrumb template.
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
        this.breadcrumbData = {search: false, page: false, home: false};
    }

    /**
     * Sets context id and registers events.
     *
     * @method init
     * @param {Number} contextId Id of the current page's context.
     */
    init(contextId) {
        this.contextId = contextId;

        this.getRoot().on(ModalEvents.shown, () => {
            if (!this.topicList) {
                this.initTopics();
            }
            this.showLandingPage();
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
        });

        this.searchClear.on('click', () => {
            this.clearSearch();
        });

        this.searchButton.on('click', () => {
            const query = this.searchBox.val();
            this.showSearchResults(query);
            this.hideSuggestionBox();
        });

        this.searchBox.on('keydown', (e) => {
            if (e.keyCode === KeyCodes.enter) {
                const query = this.searchBox.val();
                this.showSearchResults(query);
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
            this.showSuggestionPage(url, topicTitle);
        });

        this.getModal().on(CustomEvents.events.activate, SELECTORS.SEARCH_RESULT, (e) => {
            const url = $(e.target).attr('data-url');
            const title = $(e.target).attr('data-title');
            this.showSearchResult(url, title);
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

        this.getRoot().on(CustomEvents.events.activate, SELECTORS.BREADCRUMB_HOME, () => {
            this.showLandingPage();
        });

        this.getRoot().on(CustomEvents.events.activate, SELECTORS.BREADCRUMB_SEARCH, (e) => {
            const query = $(e.target).attr('data-query');
            this.showSearchResults(query);
        });

    }

    /**
     * Update the modal body using given template and data.
     *
     * @method render
     * @param {String} template - The name of the template to render.
     * @param {Object} data - Data for template.
     * @param {Object} breadcrumbData - Data required for rending breadcrumbs.
     */
    async render(template, data, breadcrumbData) {
        const renderData = {
            data: data,
            breadcrumbs: breadcrumbData
        };
        const renderPromise = Templates.render(template, renderData);
        this.setBody(renderPromise);
    }

    /*
     * Sets topicIndex and topicList.
     *
     * @method initTopics
     */
    async initTopics() {
        try {
            const topicList = await ModalHelpAjax.getTopicList(this.contextId);
            this.topicList = topicList;
            for (const topic of topicList) {
                this.topicIndex.add(topic.title);
            }
        }
        catch (error) {
            Notification.exception(error);
        }
    }

    /**
     * Outputs modal home page to modal.
     *
     * @method showLandingPage
     */
    async showLandingPage() {
        try {
            var landingPage = await ModalHelpAjax.getLandingPage(this.contextId);
            this.breadcrumbData.home = true;
            this.breadcrumbData.search = false;
            this.breadcrumbData.page = false;
            var stringified = landingPage.html;
            var base = landingPage.base;
            var reg1 = /\.\/|\.\.\//g;
            var reg2 = /href="\b(?!https\b)/g;
            var reg3 = /src="\b(?!https\b)/g;
            
            stringified = stringified.replace(reg1, base);
            stringified = stringified.replace(reg2, 'href="'+base);
            landingPage.html = stringified.replace(reg3, 'src="'+base);
            this.render(TEMPLATES.MODAL_HELP_GUIDE_PAGE, landingPage, this.breadcrumbData);
        }
        catch (error) {
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
     * Updates the suggestion list based on whatever is in the search box.
     *
     * @method updateSuggestions
     */
    updateSuggestions() {
        const query = this.searchBox.val();
        const defaultSuggestions = this.topicIndex.values();
        const minScore = this.minSearchScore;
        const suggestions = this.getSuggestions(query, defaultSuggestions, minScore);
        this.suggestionBox.html('');
        this.suggestionBox.append(suggestions);
        this.showSuggestionBox();
    }

    /**
     * Get a list of suggestions based on the query.
     *
     * @method getSuggestions
     * @param {String} query - What to fuzzy search for in the topicList.
     * @param {Array} defaultSuggestions - Default suggestions to show if there is no match.
     * @param {Number} minScore - How similar the query has to be to a topic.
     * @returns {Array} An array of html tags.
     */
    getSuggestions(query, defaultSuggestions, minScore) {
        let suggestions = this.topicIndex.get(query, defaultSuggestions, minScore);
        if (Array.isArray(suggestions[0])) {
            suggestions = suggestions.map(suggestion => suggestion[1]);
        }
        return suggestions.map(suggestion => `<a href="#" class="modal-help-suggestion-item"
                                              data-value="${suggestion}">${suggestion}</a>`);
    }

    /**
     * Searches the guides and displays results.
     *
     * @method showSearchResults
     * @param {String} query
     */
    async showSearchResults(query) {
        if (!query) {
            return;
        }
        try {
            const searchResults = await ModalHelpAjax.searchGuides(this.contextId, query);
            this.breadcrumbData.home = false;
            this.breadcrumbData.search = {
                query: query,
                active: true
            };
            this.breadcrumbData.page = false;
            this.render(TEMPLATES.MODAL_HELP_SEARCH_RESULTS, searchResults, this.breadcrumbData);
        }
        catch (error) {
            Notification.exception(error);
        }
    }

    /**
     * Render the page for the search result the user clicked on.
     *
     * @method showPage
     * @param {String} url  - The url without the domain (ie /guides/instructor/...)
     * @param {String} title - Page title.
     */
    async showSearchResult(url, title) {
        try {
            const guidePage = await ModalHelpAjax.getGuidePage(url);
            this.breadcrumbData.home = false;
            this.breadcrumbData.search.active = false;
            this.breadcrumbData.page = {
                url: url,
                title: title
            };
            this.render(TEMPLATES.MODAL_HELP_GUIDE_PAGE, guidePage, this.breadcrumbData);
        }
        catch (error) {
            Notification.exception(error);
        }
    }

    /**
     * Render the page for the suggestion the user clicked on.
     *
     * @method showSuggestionPage
     * @param {String} url - The url without the domain (ie: /guides/instructor/...)
     * @param {String} title - Page title.
     */
    async showSuggestionPage(url, title) {
        try {
            const guidePage = await ModalHelpAjax.getGuidePage(url);
            this.breadcrumbData.home = false;
            this.breadcrumbData.search = false;
            this.breadcrumbData.page = {
                url: url,
                title: title
            };
            this.render(TEMPLATES.MODAL_HELP_GUIDE_PAGE, guidePage, this.breadcrumbData);
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
     * Removes d-none class from suggestion box and scrolls to the top of the list.
     *
     * @method showSuggestionBox
     */
    showSuggestionBox() {
        this.suggestionBox.removeClass('d-none');
        this.suggestionBox[0].scrollTop = 0;
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
     * @returns {String}
     */
    static getType() {
        return 'theme_urcourses_default-help';
    }

    /**
     * Returns an array of suggestions in the suggestion box.
     *
     * @method getSuggestionItems
     * @returns {JQuery}
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
