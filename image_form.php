<?php
defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir.'/formslib.php');

class theme_urcourses_default_image_form extends moodleform {

    public function definition() {
        global $COURSE;

        $mform = $this->_form;
        $maxbytes = $COURSE->maxbytes;

        $mform->addElement(
            'filemanager',
            'userfile',
            get_string('filepicker', 'theme_urcourses_default'),
            null,
            ['subdirs' => 0, 'maxbytes' => $maxbytes]
        );
    }

    public function validation($data, $files) {
        return array();
    }
}