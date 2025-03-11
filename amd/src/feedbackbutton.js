import $ from 'jquery';
import {get_string as getString} from 'core/str';
import Templates from 'core/templates';
import Modal from 'core/modal';
import ModalCancel from 'core/modal_cancel';
import CustomEvents from 'core/custom_interaction_events';
import * as Repository from 'theme_urcourses_default/feedbackbutton_repository';
import Notification from 'core/notification';
import Fragment from 'core/fragment';

const SELECTORS = {
    PROBLEM_BUTTON: '#problem_button',
    TICKET_FORM: '#ticket_form',
    TICKET_BOX: '#id_ticket',
    SUGGESTION_BUTTON: '#suggestion_button',
    SUGGESTION_FORM: '#suggestion_form',
    SUGGESTION_BOX: '#id_suggestion',
    CANCEL_BUTTON: '#id_cancel'
};

const TEMPLATES = {
    FEEDBACK_MODAL_BODY: 'theme_urcourses_default/feedback_modal_body'
};

const init = (root, contextid) => {
    registerEventListeners($(root), contextid);
};

const registerEventListeners = (root, contextid) => {
    root.on(CustomEvents.events.activate, async (e) => {
        e.preventDefault();

        const modal = await Modal.create({
            title: getString('feedback_modal_header', 'theme_urcourses_default'),
            body: Templates.render(TEMPLATES.FEEDBACK_MODAL_BODY, {}),
            removeOnClose: true,
            show: true,
            large: true
        });

        modal.getBody().on(CustomEvents.events.activate, SELECTORS.PROBLEM_BUTTON, () => {
            modal.setBody(Fragment.loadFragment('theme_urcourses_default', 'ticket_form', contextid, {}));
        });

        modal.getBody().on(CustomEvents.events.activate, SELECTORS.SUGGESTION_BUTTON, () => {
            modal.setBody(Fragment.loadFragment('theme_urcourses_default', 'suggestion_form', contextid, {}));
        });

        modal.getBody().on(CustomEvents.events.activate, SELECTORS.CANCEL_BUTTON, () => {
            modal.setBody(Templates.render(TEMPLATES.FEEDBACK_MODAL_BODY, {}));
        });

        modal.getBody().on('submit', SELECTORS.SUGGESTION_FORM, (e) => {
            e.preventDefault();
            const suggestion = $(SELECTORS.SUGGESTION_BOX).val();
            if (suggestion.length > 0) {
                submitSuggestion(contextid, suggestion, modal);
            }
        });

        modal.getBody().on('submit', SELECTORS.TICKET_FORM, (e) => {
            e.preventDefault();
            const problem = $(SELECTORS.TICKET_BOX).val();
            if (problem.length > 0) {
                submitTicket(contextid, problem, modal);
            }
        });
    });
};

const submitSuggestion = async (contextid, suggestion, modal) => {
    try {
        const response = await Repository.submitSuggestion({
            contextid: contextid,
            suggestion: suggestion
        });
        if (response) {
            modal.destroy();
            showConfirmModal(
                getString('feedback_modal_suggestion_confirm_title', 'theme_urcourses_default'),
                getString('feedback_modal_suggestion_confirm', 'theme_urcourses_default'),
                getString('ok')
            );
        }
    } catch (error) {
        modal.destroy();
        Notification.exception(error);
    }
};

const submitTicket = async (contextid, problem, modal) => {
    try {
        const response = await Repository.submitTicket({
            contextid: contextid,
            problem: problem
        });
        if (response) {
            modal.destroy();
            showConfirmModal(
                getString('feedback_modal_problem_confirm_title', 'theme_urcourses_default'),
                getString('feedback_modal_problem_confirm', 'theme_urcourses_default'),
                getString('ok')
            );
        }
    } catch (error) {
        modal.destroy();
        Notification.exception(error);
    }
};

const showConfirmModal = (title, body, cancelButtonText) => {
    ModalCancel.create({
        title: title,
        body: body,
        removeOnClose: true,
        show: true,
        buttons: {
            cancel: cancelButtonText
        }
    });
};

export default {
    init: init,
};