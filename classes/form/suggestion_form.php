<?php

namespace theme_urcourses_default\form;

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/mod/questionnaire/locallib.php');

class suggestion_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;

        // Add hidden elements needed to submit form to questionnaire.

        // Name of question in survey.
        $mform->addElement('hidden', 'questionname', $this->_customdata['questionname']);
        $mform->setType('questionname', PARAM_TEXT);

        // Questionnaire ID
        $mform->addElement('hidden', 'questionnaireid', $this->_customdata['questionnaireid']);
        $mform->setType('questionnaireid', PARAM_INT);

        // Add input box for suggestions.
        $label = get_string('feedback_modal_suggestion_label', 'theme_urcourses_default');
        $mform->addElement('textarea', 'suggestion', $label);
        $mform->setType('suggestion', PARAM_TEXT);
        $mform->addRule('suggestion', get_string('required'), 'required', null, 'client');

        $this->add_action_buttons(submitlabel: get_string('submit'));
    }

    function validation($data, $files) {
        return [];
    }
}