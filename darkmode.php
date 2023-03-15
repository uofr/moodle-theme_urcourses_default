<?php
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
 * This page lets users to manage rules for a given course.
 *
 * @package    theme_boost_union
 * @copyright  2022 Moodle an Hochschulen e.V. <kontakt@moodle-an-hochschulen.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/theme/urcourses_default/lib.php');

global $DB, $USER;

$courseid = optional_param('courseid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$cmid = optional_param('cmid', 0, PARAM_INT);
$ruleid = optional_param('ruleid', 0, PARAM_INT);
$subscriptionid = optional_param('subscriptionid', 0, PARAM_INT);
$confirm = optional_param('confirm', false, PARAM_BOOL);

require_login(null, false);

/*
if (!get_config('tool_monitor', 'enablemonitor')) {
    // This should never happen as the this page does not appear in navigation when the tool is disabled.
    throw new coding_exception('Event monitoring is disabled');
}
*/

// Use the user context here so that the header shows user information.
$PAGE->set_context(context_user::instance($USER->id));

// Set up the page.
$indexurl = new moodle_url('/theme/urcourses_default/darkmode.php', array('courseid' => $courseid));
$PAGE->set_url($indexurl);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('darkmodepref', 'theme_urcourses_default'));
$PAGE->set_heading(fullname($USER));
$settingsnode = $PAGE->settingsnav->find('darkmode', null);

if ($settingsnode) {
    $settingsnode->make_active();
}

// Create/delete subscription if needed.
if (!empty($action)) {
    require_sesskey();
    switch ($action) {
        case 'auto' :
			error_log('darkmode: auto');
			
			echo $OUTPUT->header();
            echo $OUTPUT->notification(get_string('darkmodeautosuccess', 'theme_urcourses_default'), 'notifysuccess');
            break;
        case 'enable' :
			error_log('darkmode: enable');
			
            echo $OUTPUT->header();
            echo $OUTPUT->notification(get_string('darkmodeenablesuccess', 'theme_urcourses_default'), 'notifysuccess');
            break;
        case 'disable' :
			error_log('darkmode: disable');
			
            // If the subscription does not exist, then redirect back as the subscription must have already been deleted.
            //if (!$subscription = $DB->record_exists('tool_monitor_subscriptions', array('id' => $subscriptionid))) {
            //    redirect(new moodle_url('/admin/tool/monitor/index.php', array('courseid' => $courseid)));
            //}


				echo $OUTPUT->header();
                echo $OUTPUT->notification(get_string('darkmodedisablesuccess', 'theme_urcourses_default'), 'notifysuccess');

            break;
        default:
    }
} else {
    echo $OUTPUT->header();
}

$renderer = $PAGE->get_renderer('theme_urcourses_default', 'darkmodeprefs');

// Render the dark mode selector.
// There must be user courses otherwise we wouldn't make it this far.
//echo $renderer->render($usercourses);

$darkmodecheck = ($DB->get_record('theme_urcourses_darkmode', array('userid'=>$USER->id, 'darkmode'=>1))) ? 'yep' : 'nope';

echo 'Darkmode enabled: |'.$darkmodecheck.'|';

// Render the current darkmode state.

echo $OUTPUT->footer();
