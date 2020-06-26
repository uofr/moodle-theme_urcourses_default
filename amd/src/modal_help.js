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

const SELECTORS = {
    SEARCH: '#modal_help_search',
    SEARCH_BUTTON: '#modal_help_search_btn',
    SUGGESTIONS: '#modal_help_search_suggestions',
    SUGGESTION_ITEM: '.modal-help-suggestion-item',
    SUGGESTION_ITEM_SEL: '.modal-help-suggestion-item.selected'
};

const TEMPLATES = {
    LOADING: 'core/loading',
    MODAL_HELP_CONTENT: 'theme_urcourses_default/modal_help_content',
    MODAL_HELP_TOPICS: 'theme_urcourses_default/modal_help_topics'
};

export default class ModalHelp extends Modal {

    constructor(root) {
        super(root);
        this.courseId = null;
        this.currentUrl = null;
        this.topicList = null;
        this.searchBox = this.modal.find(SELECTORS.SEARCH);
        this.searchButton = this.modal.find(SELECTORS.SEARCH_BUTTON);
        this.suggestionBox = this.modal.find(SELECTORS.SUGGESTIONS);
    }

    init(courseId, currentUrl) {
        this.setCourseId(courseId);
        this.setCurrentUrl(currentUrl);

        this.getRoot().on(ModalEvents.shown, () => {
            this.initContent();
        });
        this.getSearchBox().on('input', () => {
            const searchValue = this.getSearchBox().val();
            this.updateSuggestionBox(this.topicList, searchValue);
        });
        this.getSearchBox().on('focus', () => {
            const searchValue = this.getSearchBox().val();
            this.updateSuggestionBox(this.topicList, searchValue);
            this.showSuggestionBox();
        });
        this.getRoot().on('click', (e) => {
            if (e.target !== this.getSearchBox()[0] && e.target !== this.getSuggestionBox()[0]) {
                this.hideSuggestionBox();
            }
        });
        this.getSearchButton().on('click', () => {
            this.doSearch();
        });
        this.getSearchBox().on('keydown', (e) => {
            if (e.keyCode === KeyCodes.enter) {
                this.getModal().focus();
                this.hideSuggestionBox();
                this.doSearch();
            }
        });
        this.getSuggestionBox().on(CustomEvents.events.activate, SELECTORS.SUGGESTION_ITEM, (e) => {
            const suggestionValue = $(e.target).attr('data-value');
            this.getSearchBox().val(suggestionValue);
            this.doSearch();
        });
    }

    async initContent() {
        const courseid = this.getCourseId();
        const currenturl = this.getCurrentUrl();
        try {
            const topicList = await ModalHelpAjax.getTopicList(courseid);
            const landingPage = await ModalHelpAjax.getLandingPage(courseid, currenturl);
            const renderPromise = Templates.render(TEMPLATES.MODAL_HELP_CONTENT, {html: landingPage.html});
            const searchBoxValue = this.getSearchBox().val();

            this.setTopicList(topicList);
            this.updateSuggestionBox(topicList, searchBoxValue);
            this.setBody(renderPromise);
        } catch (error) {
            console.error('error', error);
            Notification.exception(error);
        }
    }

    async doSearch() {
        const searchValue = this.getSearchBox().val();
        const topic = this.topicList.find(topic => topic.title.toLowerCase() === searchValue.toLowerCase());
        const url = topic.url.substr(topic.url.indexOf('/', 1));

        if (!topic) {
            return;
        }
        try {
            const guidePage = await ModalHelpAjax.getGuidePage(url);
            const renderPromise = Templates.render(TEMPLATES.MODAL_HELP_CONTENT, {html: guidePage.html});
            this.setBody(renderPromise);
        } catch (error) {
            Notification.exception(error);
        }
    }

    static getType() {
        return 'theme_urcourses_default-help';
    }

    setCourseId(courseId) {
        this.courseId = courseId;
    }

    getCourseId() {
        return this.courseId;
    }

    setCurrentUrl(currentUrl) {
        this.currentUrl = currentUrl;
    }

    getCurrentUrl() {
        return this.currentUrl;
    }

    setTopicList(topicList) {
        this.topicList = topicList;
    }

    getTopicList() {
        return this.topicList;
    }

    getSearchBox() {
        return this.searchBox;
    }

    getSearchButton() {
        return this.searchButton;
    }

    getSuggestionBox() {
        return this.suggestionBox;
    }

    setSuggestionBox(suggestions) {
        const suggestionBox = this.getSuggestionBox();
        suggestionBox.html('');
        for (const suggestion of suggestions) {
            const suggestionMarkup = `<a href="#"
                                        class="modal-help-suggestion-item"
                                        data-url="${suggestion.url}"
                                        data-value="${suggestion.title}">
                                            ${suggestion.title}
                                      </a>`;
            suggestionBox.append(suggestionMarkup);
        }
    }

    updateSuggestionBox(topicList, filter) {
        const suggestions = this.generateSuggestions(topicList, filter);
        this.setSuggestionBox(suggestions);
    }

    generateSuggestions(topics, filter) {
        const regex = RegExp(filter.split('').join('.*'), 'i');
        const suggestions = [];
        for (const topic of topics) {
            const match = regex.exec(topic.title);
            if (match) {
                suggestions.push({
                    title: topic.title,
                    url: topic.url,
                    length: match[0].length,
                    start: match.index
                });
            }
        }
        suggestions.sort((a, b) => {
            if (a.start < b.start) {
                return -1;
            }
            if (a.start > b.start) {
                return 1;
            }
            if (a.length < b.length) {
                return -1;
            }
            if (a.length > b.length) {
                return 1;
            }
        });
        return suggestions;
    }

    showSuggestionBox() {
        this.getSuggestionBox().removeClass('d-none');
    }

    hideSuggestionBox() {
        this.getSuggestionBox().addClass('d-none');
    }

}

// Setup code for adding ModalHelp to the modal factory.
let registered = false;
if (!registered) {
    ModalRegistry.register(ModalHelp.getType(), ModalHelp, 'theme_urcourses_default/modal_help');
    registered = true;
}