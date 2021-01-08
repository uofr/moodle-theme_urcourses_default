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
import FuzzySearch from 'theme_urcourses_default/fuzzysearch';

const SELECTORS = {
    SEARCH: '#modal_help_search',
    SEARCH_BUTTON: '#modal_help_search_btn',
    SEARCH_CLEAR: '#modal_help_search_clear',
    SUGGESTIONS: '#modal_help_search_suggestions',
    SUGGESTION_ITEM: '.modal-help-suggestion-item',
    SEARCH_RESULT: '.modal-help-search-result',
    BREADCRUMBS: '#breadcrumbs',
    BREADCRUMB: '.breadcrumb-item a',
    BREADCRUMB_SEARCH: '#breadcrumb_search',
    BREADCRUMB_HOME: '#breadcrumb_home',
    BREADCRUMB_PAGE: '#breadcrumb_page',
    GUIDE_PAGE_LINK: '[data-region="guide-page-content"] a',
    OVERLAY_LOADING: '.overlay-icon-container',
    BACK: '[data-action="go-back"]',
    BACK_TO_TOP: '#modal-help-back-to-top',
    MAIN: '[data-region="modal-help-main"]'
};

const TEMPLATES = {
    MODAL_HELP_GUIDE_PAGE: 'theme_urcourses_default/modal_help_guide_page',
    MODAL_HELP_SEARCH_RESULTS: 'theme_urcourses_default/modal_help_search_results',
    OVERLAY_LOADING: 'core/overlay_loading'
};

/**
 * UR Courses help modal.
 * 
 * @class ModalHelp
 * @property {Number} contextId Context ID number.
 * @property {Array} topicList Array of help topics.
 * @property {Array} topicTitles Titles of help topics.
 * @property {Object} searchBox Search input.
 * @property {Object} searchButton Search submit.
 * @property {Object} searchClear Clears search value.
 * @property {Object} suggestionBox Predictive search suggestion list.
 * @property {Object} suggestionItemIndex suggestionBox item index.
 */
export default class ModalHelp extends Modal {

    constructor(root) {
        super(root);
        this.contextId = null;
        this.topicList = null;
        this.topicTitles = null;
        this.searchBox = this.modal.find(SELECTORS.SEARCH);
        this.searchButton = this.modal.find(SELECTORS.SEARCH_BUTTON);
        this.searchClear = this.modal.find(SELECTORS.SEARCH_CLEAR);
        this.suggestionBox = this.modal.find(SELECTORS.SUGGESTIONS);
        this.content = this.modal.find(SELECTORS.MAIN);
        this.suggestionItemIndex = 0;
        this.currentPath = '';
        this.history = [];
        this.landingPageUrl = '';
        this.userIsInstructor = null;
    }

    /**
     * Setup for topic list, home page, and events.
     *
     * @param {Number} contextId Context ID number.
     * @param {String} localUrl The local path relative to the site root.
     */
    async init(contextId, localUrl) {
        try {
            this.contextId = contextId;
            [this.topicList, this.landingPageUrl, this.userIsInstructor] = await Promise.all([
                this.getTopicList(),
                ModalHelpAjax.getLandingPageUrl(this.contextId, localUrl),
                ModalHelpAjax.userIsInstructor()
            ]);
            this.topicTitles = this.topicList.map(topic => topic.title);
            FuzzySearch.setDictionary(this.topicTitles);
            this.initEvents();
        } catch (error) {
            Notification.exception(error);
        }
    }

    /**
     * Registers event listeners.
     */
    initEvents() {

        // PREDICTIVE SEARCH EVENTS //

        /** Update suggestion list based on input. */
        this.searchBox.on('input', () => {
            if (this.searchBox.val()) {
                this.showSearchClear();
            } else {
                this.hideSearchClear();
            }
            this.updateSuggestions();
        });

        /** Show suggestion list when search box is in focus. */
        this.searchBox.on('focus', () => {
            this.updateSuggestions();
        });

        /** Clear search value if search clear button is clicked. */
        this.searchClear.on('click', () => {
            this.clearSearch();
        });

        /** Hide suggestions if user clicks away from search box. */
        this.getRoot().on('click', (e) => {
            if (e.target !== this.searchBox[0] && e.target !== this.suggestionBox[0]) {
                this.hideSuggestionBox();
            }
        });

        /** If user presses up or down while focused on search, focus on suggestions. */
        this.searchBox.on('keydown', (e) => {
            if (e.keyCode === KeyCodes.arrowUp || e.keyCode === KeyCodes.arrowLeft) {
                e.preventDefault();
                this.suggestionItemIndex = this.getSuggestionItems().length - 0;
                this.getSuggestionItems().eq(this.suggestionItemIndex).focus();
            }
            else if (e.keyCode === KeyCodes.arrowDown || e.keyCode === KeyCodes.arrowRight) {
                e.preventDefault();
                this.suggestionItemIndex = -1;
                this.getSuggestionItems().eq(this.suggestionItemIndex).focus();
            }
        });

        /** If user is focused on suggestions, cycle through list. */
        this.suggestionBox.on('keydown', SELECTORS.SUGGESTION_ITEM, (e) => {
            if (e.keyCode === KeyCodes.arrowUp || e.keyCode === KeyCodes.arrowLeft) {
                e.preventDefault();
                this.suggestionItemIndex--;
                if (this.suggestionItemIndex < -1) {
                    this.suggestionItemIndex = this.getSuggestionItems().length - 0;
                }
                this.getSuggestionItems().eq(this.suggestionItemIndex).focus();
            }
            else if (e.keyCode === KeyCodes.arrowDown || e.keyCode === KeyCodes.arrowRight) {
                e.preventDefault();
                this.suggestionItemIndex++;
                if (this.suggestionItemIndex >= this.getSuggestionItems().length) {
                    this.suggestionItemIndex = -1;
                }
                this.getSuggestionItems().eq(this.suggestionItemIndex).focus();
            }
        });

        // GUIDE PAGE EVENTS //

        /** Show home page when modal is open. */
        this.getRoot().on(ModalEvents.shown, () => {
            this.renderGuidePage(this.landingPageUrl.url, this.landingPageUrl.target);
        });

        /** Reset modal to default when closed. */
        this.getRoot().on(ModalEvents.hidden, () => {
            this.resetModal();
        });

        /** Open suggestion page if user clicks or presses enter. */
        this.suggestionBox.on(CustomEvents.events.activate, SELECTORS.SUGGESTION_ITEM, (e) => {
            const suggestion = $(e.target);
            const topicTitle = suggestion.attr('data-value');
            const topicTitleLower = topicTitle.toLowerCase().replace(/&/g, '&amp;');
            const topic = this.topicList.find(topic => topic.title.toLowerCase() === topicTitleLower);
            const url = `${topic.url}.json`;
            this.searchBox.val(topicTitle);
            this.showSearchClear();
            this.renderGuidePage(url);
        });

        /** Search on click. */
        this.searchButton.on('click', () => {
            const query = this.searchBox.val();
            this.searchGuides(query);
            this.hideSuggestionBox();
        });

        /** Search on enter. */
        this.searchBox.on('keydown', (e) => {
            if (e.keyCode === KeyCodes.enter) {
                const query = this.searchBox.val();
                this.searchGuides(query);
                this.getModal().focus();
                this.hideSuggestionBox();
            }
        });

        /** Open search result. */
        this.getModal().on(CustomEvents.events.activate, SELECTORS.SEARCH_RESULT, (e) => {
            const url = $(e.target).attr('data-url');
            this.renderGuidePage(`${url}.json`);
        });

        /** If the user clicks a link inside the guide page, open the page. */
        this.getRoot().on('click', SELECTORS.GUIDE_PAGE_LINK, (e) => {
            let href = $(e.currentTarget).attr('href');
            if (href.startsWith('#') ) return;
            if (href.endsWith('/')) href = href.substring(0, href.lastIndexOf('/'));
            e.preventDefault();
            if (href.startsWith('http') || href.startsWith('https') || href.startsWith('mailto')) {
                window.open(href, '_blank');
            } else {
                const [path, target] = href.split('#');
                const hrefPath = path.split('/');
                const actualPath = this.currentPath.split('/');
                for (const node of hrefPath) {
                    if (node === '..') actualPath.pop();
                    else if (node !== '.') actualPath.push(node);
                }
                const url = actualPath.join('/');
                this.renderGuidePage(`${url}.json`, target);
            }
        });

        this.getModal().on('click', SELECTORS.BREADCRUMB, (e) => {
            e.preventDefault();
            const breadcrumb = $(e.currentTarget);
            const url = breadcrumb.attr('href');
            const target = breadcrumb.attr('data-target');
            this.renderGuidePage(url, target);
        });

        this.getModal().on('click', SELECTORS.BACK, (e) => {
            e.preventDefault();
            if (this.history.length > 1) {
                this.history.pop();
                const [url, target] = this.history.pop();
                this.renderGuidePage(url, target);
            }
        });

        $(this.getModal()).find(".modal-body").scroll(function () {
            if ($(this).scrollTop() > 50) {
                $(SELECTORS.BACK_TO_TOP).fadeIn();
            } else {
                $(SELECTORS.BACK_TO_TOP).fadeOut();
            }
        });

        this.getModal().on('click', SELECTORS.BACK_TO_TOP, (e) => {
             $(this.getModal()).find(".modal-body").animate({
                        scrollTop: 0
                }, 400);
                    return false;
        });
    }
       
    /**
     * Get list of help topics.
     * @return {Array} Array of topics.
     */
    async getTopicList() {
        const {instructor, url} = await ModalHelpAjax.getTopicListUrl(this.contextId);
        const topicListJson = await this.getJsonData(url);
        const topics = topicListJson.jsondata.page_data[0].all_pages;
        const topicsSorted = topics.sort((a, b) => a.title > b.title);
        const prefix = instructor ? 'Instructor' : 'Students';
        return topicsSorted.filter(topic => topic.prefix === prefix);
    }

    /**
     * Updates the suggestion list based on whatever is in the search box.
     */
    updateSuggestions() {
        const query = this.searchBox.val();
        let suggestions = FuzzySearch.search(query);
        if (!suggestions.length) {
            suggestions = this.topicTitles;
        }
        const suggestionList = this.getSuggestions(suggestions);
        this.suggestionBox.html('');
        this.suggestionBox.append(suggestionList);
        this.showSuggestionBox();
    }

    /**
     * Returns html markup based on list of suggested words.
     * @param {Array} suggestions - List of suggested words.
     * @returns {Array} An array of html tags.
     */
    getSuggestions(suggestions) {
        return suggestions.map(suggestion => `<a href="#" class="modal-help-suggestion-item"
                                              data-value="${suggestion}">${suggestion}</a>`);
    }

    /**
     * Searches the guides and displays results.
     * @param {String} query
     */
    async searchGuides(query) {
        if (!query) return;
        try {
            await this.setLoading(true);
            const searchUrl = await ModalHelpAjax.getSearchUrl(this.contextId, query);
            const basePath = searchUrl.split('/');
            basePath.pop();
            basePath.pop();
            const baseUrl = basePath.join('/');
            const searchResults = await this.getJsonData(searchUrl);
            const breadcrumbs = [
                {
                    active: false,
                    name: this.userIsInstructor ? 'Instructor Guide' : 'Student Guides',
                    url: this.userIsInstructor ? `${baseUrl}/instructor.json` : `${baseUrl}/student.json`,
                    target: ''
                },
                {
                    active: true,
                    name: `Search Results: ${query}`,
                    url: '',
                    target: '',
                }
            ];
            await this.renderReplace(TEMPLATES.MODAL_HELP_SEARCH_RESULTS, {results: searchResults.jsondata, query: query, breadcrumbs: breadcrumbs}, this.content);
            this.getBody()[0].scrollTop = 0;
            
            this.history.push([searchUrl]);
        } catch (error) {
            Notification.exception(error);
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Load content from the url into the modal. Scroll to target if one is provided.
     * @param {String} url Url to fetch from.
     * @param {String} target Anchor to scroll to when url is loaded.
     */
    async renderGuidePage(url, target = '') {
        try {
            await this.setLoading(true);
            const page = await this.getJsonData(url);
            const breadcrumbs = [];
            const {content, frontmatter, jsondata} = page;
            const {title} = frontmatter ? frontmatter : jsondata.page_data[0];
            const path = url.split('/');

            breadcrumbs.unshift({
                active: true,
                name: title,
                target: target,
                url: url,
            });
            path.pop();
            while (path[path.length - 1] !== 'guides') {
                const breadcrumbUrl = `${path.join('/')}.json`;
                const breadcrumbPage = await this.getJsonData(breadcrumbUrl);
                const {frontmatter, jsondata} = breadcrumbPage;
                const {title} = frontmatter ? frontmatter : jsondata.page_data[0];
                breadcrumbs.unshift({
                    active: false,
                    name: title,
                    url: breadcrumbUrl
                });
                path.pop();
            }
            
            const guidesBase = path.join('/');

            if (this.userIsInstructor && breadcrumbs[0].name !== 'Instructor Guide') {
                breadcrumbs.unshift({
                    active: false,
                    name: 'Instructor Guide',
                    url: `${guidesBase}/instructor.json`
                });
            }
            
            if (!this.userIsInstructor && breadcrumbs[0].name !== 'Student Guides') {
                breadcrumbs.unshift({
                    active: false,
                    name: 'Student Guides',
                    url: `${guidesBase}/student.json`
                });
            }


            let html = content ? content : jsondata.page_data[0].content;
            html = html.replaceAll('src="assets', `src="${guidesBase}/assets`);
            html = html.replaceAll('src="../assets', `src="${guidesBase}/assets`);
            html = html.replaceAll('src="./images', `src="${guidesBase}/images`);
            var back =false;
            if (this.history.length > 0) {
                back =true;
            }
            await this.renderReplace(TEMPLATES.MODAL_HELP_GUIDE_PAGE, {html: html, breadcrumbs: breadcrumbs,back:back}, this.content);
            this.currentPath = url.substring(0, url.lastIndexOf('/'));
            if (target) {
                const anchor = target.startsWith('#') ? $(target) : $(`#${target}`);
                if (anchor.length) anchor[0].scrollIntoView();
            }
            else this.getBody()[0].scrollTop = 0;
        
            this.history.push([url, target]);

        } catch (error) {
            Notification.exception(error);
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Fetches from url and returns json.
     * @param {String} url Url to fetch from.
     */
    async getJsonData(url) {
        const response = await fetch(url);
        return response.json();
    }

    /**
     * Appends a loading spinner to modal if loading is true, removes spinner if false.
     * @param {Boolean} loading True for loading, contentfalse for not-loading.
     */
    setLoading(loading) {
        if (loading) {
            return this.renderAppend(TEMPLATES.OVERLAY_LOADING, {visible: true}, this.content);
        } else {
            this.getBody().find(SELECTORS.OVERLAY_LOADING).remove();
        }
    }

    /**
     * Render template using data and replace area.
     * @param {String} template Template to render.
     * @param {Object} data Template data.
     * @param {Object|String} area Selector or jquery object.
     */
    renderReplace(template, data, area) {
        return Templates.render(template, data)
            .then((html, js) => Templates.replaceNodeContents(area, html, js));
    }

    /**
     * Render template using data and append to area.
     * @param {String} template Template to render.
     * @param {Object} data Template data.
     * @param {Object|String} area Selector or jquery object.
     */
    renderAppend(template, data, area) {
        return Templates.render(template, data)
            .then((html, js) => Templates.appendNodeContents(area, html, js));
    }

    /**
     * Resets modal content and search.
     */
    resetModal() {
        this.content.html('');
        this.searchBox.val('');
        this.suggestionBox.html('');
    }

    /**
     * Clears whatever is in the search box.
     */
    clearSearch() {
        this.searchBox.val('');
        this.hideSearchClear();
    }

    /**
     * Removes d-none class from suggestion box and scrolls to the top of the list.
     */
    showSuggestionBox() {
        this.suggestionBox.removeClass('d-none');
        this.suggestionBox[0].scrollTop = 0;
    }

    /**
     * Adds d-none class to suggestion box.
     */
    hideSuggestionBox() {
        this.suggestionBox.addClass('d-none');
    }

    /**
     * Removes d-none class from clear search button.
     */
    showSearchClear() {
        this.searchClear.removeClass('d-none');
    }

    /**
     * Adds d-none class to clear search button.
     */
    hideSearchClear() {
        this.searchClear.addClass('d-none');
    }

    /**
     * Returns an array of suggestions in the suggestion box.
     * @returns {JQuery}
     */
    getSuggestionItems() {
        return this.suggestionBox.find(SELECTORS.SUGGESTION_ITEM);
    }

    /**
     * Returns modal type. Used when creating the modal using ModalFactory.
     * @returns {String}
     */
    static getType() {
        return 'theme_urcourses_default-help';
    }
}

// Setup code for adding ModalHelp to the modal factory.
let registered = false;
if (!registered) {
    ModalRegistry.register(ModalHelp.getType(), ModalHelp, 'theme_urcourses_default/modal_help');
    registered = true;
}

