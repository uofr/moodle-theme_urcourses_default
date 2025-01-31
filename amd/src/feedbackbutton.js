import $ from 'jquery';
import {get_string as getString} from 'core/str';
import Templates from 'core/templates';
import Modal from 'core/modal';

const SELECTORS = {
    PROBLEM_BUTTON: '#problem_button',
    SUGGESTION_BUTTON: '#suggestion_button',
    BACK_BUTTON: '#back_button',
    CANCEL_BUTTON: '#cancel_button'
};

const TEMPLATES = {
    FEEDBACK_MODAL_BODY: 'theme_urcourses_default/feedback_modal_body',
    FEEDBACK_MODAL_PROBLEM_FORM: 'theme_urcourses_default/feedback_modal_problem_form',
    FEEDBACK_MODAL_SUGGESTION_FORM: 'theme_urcourses_default/feedback_modal_suggestion_form'
};

const init = (root) => {
    registerEventListeners($(root));
};

const registerEventListeners = (root) => {
    root.on('click', async (e) => {
        e.preventDefault();
        const modal = await Modal.create({
            title: getString('feedback_modal_header', 'theme_urcourses_default'),
            body: Templates.render(TEMPLATES.FEEDBACK_MODAL_BODY, {}),
            removeOnClose: true,
            show: true,
            large: true
        });
        modal.getBody().on('click', SELECTORS.PROBLEM_BUTTON, () => {
            modal.setBody(Templates.render(TEMPLATES.FEEDBACK_MODAL_PROBLEM_FORM, {}));
        });
        modal.getBody().on('click', SELECTORS.SUGGESTION_BUTTON, () => {
            modal.setBody(Templates.render(TEMPLATES.FEEDBACK_MODAL_SUGGESTION_FORM, {}));
        });
        modal.getBody().on('click', SELECTORS.BACK_BUTTON, () => {
            modal.setBody(Templates.render(TEMPLATES.FEEDBACK_MODAL_BODY, {}));
        });
        modal.getBody().on('click', SELECTORS.CANCEL_BUTTON, () => {
            modal.setBody(Templates.render(TEMPLATES.FEEDBACK_MODAL_BODY, {}));
        });
    });
};

export default {
    init: init,
};