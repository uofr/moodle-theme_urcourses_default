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
 * Javascript controller for the "Make it available"/"Make it unavailable" button.
 * Allows user to toggle course visibility.
 *
 * @module  theme_urcourses_default/toggle_course_visibility
 * @author  2023 John Lane
 */

import $ from 'jquery';
import CustomEvents from 'core/custom_interaction_events';
import * as Repository from 'theme_urcourses_default/repository';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Notification from 'core/notification';

const SELECTORS = {
    TOGGLE_VISIBILITY_BUTTON: '[data-action="toggle_course_visibility"]',
    TOGGLE_LOADING: '[data-region="vis-toggle-loading"]'
};

let _root = null;
let _confirmModal = null;
let _courseid = 0;
let _isCourseVisible = false;
let _strings = {};

export const init = (rootSelector, courseid, visible, strings) => {
    _root = $(rootSelector);
    _courseid = courseid;
    _isCourseVisible = visible;
    _strings = strings;

    ModalFactory.create({type: ModalFactory.types.SAVE_CANCEL})
    .then(modal => {
        _confirmModal = modal;
        _confirmModal.setSaveButtonText(_strings.confirmbutton);
        _confirmModal.getRoot().on(ModalEvents.save, toggleCourseVisibility);
    });

    registerEventListeners();
};

const registerEventListeners = () => {
    CustomEvents.define(_root, [
        CustomEvents.events.activate
    ]);

    _root.on(CustomEvents.events.activate, SELECTORS.TOGGLE_VISIBILITY_BUTTON, (e, data) => {
        showConfirmModal();
        data.originalEvent.preventDefault();
    });
};

const showConfirmModal = () => {
    const confirmTitle = _isCourseVisible ? _strings.hidetitle : _strings.showtitle;
    const confirmBody = _isCourseVisible ? _strings.hidebody : _strings.showbody;

    _confirmModal.setTitle(confirmTitle);
    _confirmModal.setBody(confirmBody);
    _confirmModal.show();
};

const toggleCourseVisibility = async() => {
    const loadingOverlay = _root.find(SELECTORS.TOGGLE_LOADING);
    loadingOverlay.removeClass('d-none').addClass('d-flex');
    try {
        await Repository.toggleCourseVisibility(_courseid);
        location.reload();
    } catch (error) {
        Notification.exception(error);
        loadingOverlay.removeClass('d-flex').addClass('d-none');
    }
};