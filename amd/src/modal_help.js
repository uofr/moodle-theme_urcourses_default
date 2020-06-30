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
        this.topicIndex = FuzzySet();
        this.searchBox = this.modal.find(SELECTORS.SEARCH);
        this.searchButton = this.modal.find(SELECTORS.SEARCH_BUTTON);
        this.suggestionBox = this.modal.find(SELECTORS.SUGGESTIONS);
    }

    init(courseId, currentUrl) {
        this.courseId = courseId;
        this.currentUrl = currentUrl;

        this.getRoot().on(ModalEvents.shown, () => {
            this.initContent();
        });
        this.searchBox.on('input', () => {
            const searchValue = this.searchBox.val();
            this.updateSuggestionBox(searchValue);
        });
        this.searchBox.on('focus', () => {
            const searchValue = this.searchBox.val();
            this.updateSuggestionBox(searchValue);
            this.showSuggestionBox();
        });
        this.getRoot().on('click', (e) => {
            if (e.target !== this.searchBox[0] && e.target !== this.suggestionBox[0]) {
                this.hideSuggestionBox();
            }
        });
        this.searchButton.on('click', () => {
            this.doSearch();
        });
        this.searchBox.on('keydown', (e) => {
            if (e.keyCode === KeyCodes.enter) {
                this.getModal().focus();
                this.hideSuggestionBox();
                this.doSearch();
            }
        });
        this.suggestionBox.on(CustomEvents.events.activate, SELECTORS.SUGGESTION_ITEM, (e) => {
            const suggestionValue = $(e.target).attr('data-value');
            this.searchBox.val(suggestionValue);
            this.doSearch();
        });
    }

    async initContent() {
        const courseid = this.courseId;
        const currenturl = this.currentUrl;
        try {
            const topicList = await ModalHelpAjax.getTopicList(courseid);
            const landingPage = await ModalHelpAjax.getLandingPage(courseid, currenturl);
            const renderPromise = Templates.render(TEMPLATES.MODAL_HELP_CONTENT, {html: landingPage.html});

            this.topicList = topicList;
            for (const topic of topicList) {
                this.topicIndex.add(topic.title);
            }
            this.setBody(renderPromise);
        } catch (error) {
            console.error('error', error);
            Notification.exception(error);
        }
    }

    updateSuggestionBox(filter) {
        const topicIndex = this.topicIndex;
        const suggestionBox = this.suggestionBox;
        let suggestions = topicIndex.get(filter, topicIndex.values(), 0);

        if (Array.isArray(suggestions[0])) {
            suggestions = suggestions.map(suggestion => suggestion[1]);
        }

        suggestionBox.html('');
        for (const suggestion of suggestions) {
            const suggestionMarkup = `<a href="#"
                                        class="modal-help-suggestion-item"
                                        data-value="${suggestion}">
                                            ${suggestion}
                                      </a>`;
            suggestionBox.append(suggestionMarkup);
        }
    }

    async doSearch() {
        const searchValue = this.searchBox.val();
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

    showSuggestionBox() {
        this.suggestionBox.removeClass('d-none');
    }

    hideSuggestionBox() {
        this.suggestionBox.addClass('d-none');
    }

}

// Setup code for adding ModalHelp to the modal factory.
let registered = false;
if (!registered) {
    ModalRegistry.register(ModalHelp.getType(), ModalHelp, 'theme_urcourses_default/modal_help');
    registered = true;
}
