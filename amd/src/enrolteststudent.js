import * as Repository from 'theme_urcourses_default/repository';
import $ from 'jquery';
import Notification from 'core/notification';
import Modal from 'core/modal';
import ModalSaveCancel from 'core/modal_save_cancel';
import ModalEvents from 'core/modal_events';
import {get_string as getString} from 'core/str';


const SELECTORS = {
    ENROL: '[data-key="enrol_test_student"] a',
    ENROL_LIST: '[data-key="enrol_test_student"',
    UNENROL: '[data-key="unenrol_test_student"] a',
    UNENROL_LIST: '[data-key="unenrol_test_student"]',
    MOREMENU: '[data-region="moredropdown"]'
};

export const init = async (courseid) => {
    registerEventListeners(courseid);
};

const registerEventListeners = (courseid) => {
    $(SELECTORS.MOREMENU).on('click', SELECTORS.ENROL, e => {
        e.preventDefault();
        confirmEnrolTestStudent(courseid);
    });

    $(SELECTORS.MOREMENU).on('click', SELECTORS.UNENROL, e => {
        e.preventDefault();
        confirmUnenrolTestStudent(courseid);
    });
};

const confirmEnrolTestStudent = async (courseid) => {
    const modal = await ModalSaveCancel.create({
        title: getString('teststudentenrol_title', 'theme_urcourses_default'),
        body: getString('teststudentenrol_body', 'theme_urcourses_default'),
        removeOnClose: true,
        show: true,
        buttons: {
            save: getString('teststudentenrol_button', 'theme_urcourses_default')
        }
    });
    modal.getRoot().on(ModalEvents.save, () => {
        enrolTestStudent(courseid);
    });
};

const enrolTestStudent = async (courseid) => {
    try {
        const response = await Repository.enrolTestStudent(courseid);
        if (response) {
            Modal.create({
                title: getString('teststudentenrolled_title', 'theme_urcourses_default'),
                body: getString('teststudentenrolled_body', 'theme_urcourses_default'),
                removeOnClose: true,
                show: true
            });
            $(SELECTORS.ENROL).text(await getString('unenrolurstudent', 'theme_urcourses_default'));
            $(SELECTORS.ENROL_LIST).attr('data-key', 'unenrol_test_student');
        }
    } catch (error) {
        Notification.exception(error);
    }
};

const confirmUnenrolTestStudent = async (courseid) => {
    const modal = await ModalSaveCancel.create({
        title: getString('teststudentunenrol_title', 'theme_urcourses_default'),
        body: getString('teststudentunenrol_body', 'theme_urcourses_default'),
        removeOnClose: true,
        show: true,
        buttons: {
            save: getString('teststudentunenrol_button', 'theme_urcourses_default')
        }
    });
    modal.getRoot().on(ModalEvents.save, () => {
        unenrolTestStudent(courseid);
    });
};

const unenrolTestStudent = async (courseid) => {
    try {
        const response = await Repository.unenrolTestStudent(courseid);
        if (response) {
            Modal.create({
                title: getString('teststudentunenrolled_title', 'theme_urcourses_default'),
                body: getString('teststudentunenrolled_body', 'theme_urcourses_default'),
                removeOnClose: true,
                show: true
            });
            $(SELECTORS.UNENROL).text(await getString('enrolurstudent', 'theme_urcourses_default'));
            $(SELECTORS.UNENROL_LIST).attr('data-key', 'enrol_test_student');
        }
    } catch (error) {
        Notification.exception(error);
    }
};