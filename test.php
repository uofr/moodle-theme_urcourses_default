<?php
require_once('../../config.php');
require('image_form.php');

$mform = new theme_urcourses_default_image_form(null, []);

if ($mform->is_cancelled()) {
	redirect(new moodle_url('/mod/mail/view.php', ['id' => $id]));
}
else if ($data = $mform->get_data()) {
    echo '<pre>', $data, '</pre>';
}
else {
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}