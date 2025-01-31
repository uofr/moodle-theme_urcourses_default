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
 * External functions repository for theme_urcourses_default.
 *
 * @module  theme_urcourses_default
 * @author  2024 John Lane <john.lane@uregina.ca>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import * as Repository from 'theme_urcourses_default/repository';
import {get_string as getString} from 'core/str';
import Templates from 'core/templates';
import ModalEvents from 'core/modal_events';
import ModalSaveCancel from 'core/modal_save_cancel';
import ModalCancel from 'core/modal_cancel';
import Notification from 'core/notification';

const SELECTORS = {
    CREATE_TEST_STUDENT: '#user-action-menu a[data-action="createteststudent"]',
    RESET_TEST_STUDENT: '#user-action-menu a[data-action="resetteststudent"]'
};

const TEMPLATES = {
    CREATE_TEST_STUDENT_MODAL: 'theme_urcourses_default/create_test_student_modal',
    RESET_TEST_STUDENT_MODAL: 'theme_urcourses_default/reset_test_student_modal'
};

const init = (root) => {
    registerEventListeners($(root));
};

const registerEventListeners = (root) => {
    root.on('click', SELECTORS.CREATE_TEST_STUDENT, async (e) => {
        e.preventDefault();
        const testStudent = await Repository.testAccountInfo();
        testStudentModal(
            getString('createmodal_title', 'theme_urcourses_default'),
            Templates.render(TEMPLATES.CREATE_TEST_STUDENT_MODAL, testStudent),
            getString('createmodal_button', 'theme_urcourses_default'),
            createTestStudent
        );
    });

    root.on('click', SELECTORS.RESET_TEST_STUDENT, async (e) => {
        e.preventDefault();
        const testStudent = await Repository.testAccountInfo();
        testStudentModal(
            getString('resetmodal_title', 'theme_urcourses_default'),
            Templates.render(TEMPLATES.RESET_TEST_STUDENT_MODAL, testStudent),
            getString('resetmodal_button', 'theme_urcourses_default'),
            () => {
                resetTestStudentConfirm(testStudent);
            }
        );
    });
};

const testStudentModal = async (title, body, saveButton, saveAction) => {
    try {
        const modal = await ModalSaveCancel.create({
            title: title,
            body: body,
            removeOnClose: true,
            buttons: {
                save: saveButton
            },
            show: true
        });
        modal.getRoot().on(ModalEvents.save, () => {
            modal.destroy();
            saveAction();
        });
    } catch (error) {
        Notification.exception(error);
    }
};

const confirmModal = async (title, body, saveButton, saveAction) => {
    try {
        const modal = await ModalSaveCancel.create({
            title: title,
            body: body,
            removeOnClose: true,
            buttons: {
                save: saveButton
            },
            show: true
        });
        modal.getRoot().on(ModalEvents.save, () => {
            modal.destroy();
            saveAction();
        });
    } catch (error) {
        Notification.exception(error);
    }
};

const statusModal = async (title, body) => {
    try {
        await ModalCancel.create({
            title: title,
            body: body,
            removeOnClose: true,
            show: true,
            buttons: {
                cancel: getString('ok')
            }
        });
    } catch (error) {
        Notification.exception(error);
    }
};

const createTestStudent = async () => {
    try {
        const response = await Repository.createTestStudent();
        statusModal(
            getString('createsuccess_title', 'theme_urcourses_default'),
            getString('createsuccess_body', 'theme_urcourses_default', response.email)
        );
    } catch (error) {
        Notification.exception(error);
    }
};

const resetTestStudentConfirm = (testStudent) => {
    confirmModal(
        getString('resetmodal_title', 'theme_urcourses_default'),
        getString('resetmodal_confirm', 'theme_urcourses_default', testStudent.username),
        getString('resetmodal_button', 'theme_urcourses_default'),
        resetTestStudentPassword
    );
};

const resetTestStudentPassword = async () => {
    try {
        const response = await Repository.resetTestStudent();
        if (response) {
            statusModal(
                getString('resetsuccess_title', 'theme_urcourses_default'),
                getString('resetsuccess_body', 'theme_urcourses_default', response.email)
            );
        }
    } catch (error) {
        Notification.exception(error);
    }
};

export default {
    init: init,
};