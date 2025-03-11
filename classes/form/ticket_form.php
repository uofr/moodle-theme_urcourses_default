<?php

namespace theme_urcourses_default\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class ticket_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;

        $label = get_string('feedback_modal_problem_label', 'theme_urcourses_default');
        $mform->addElement('textarea', 'ticket', $label);
        $mform->setType('ticket', PARAM_TEXT);
        $mform->addRule('ticket', get_string('required'), 'required', null, 'client');

        $this->add_action_buttons(submitlabel: get_string('submit'));
    }

    function validation($data, $files) {
        return [];
    }
}