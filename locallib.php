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
 * Theme UR Courses - Local library
 *
 * @package    theme_urcourses_default
 * @copyright  2023 Alexander Bias <bias@alexanderbias.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/***************************************************************
 * EXTENSION POINT:
 * Add whatever UR Courses local functions you need here.
 **************************************************************/

function theme_urcourses_default_enable_darkmode() {
    return set_user_preference('theme_urcourses_default_darkmode', true);
}

function theme_urcourses_default_disable_darkmode() {
    return unset_user_preference('theme_urcourses_default_darkmode');
}

function theme_urcourses_default_darkmode_enabled() {
    return get_user_preferences('theme_urcourses_default_darkmode', false);
}

function theme_urcourses_default_can_create_test_student($userid) {
    global $DB;

    $teacherroleid = $DB->get_field('role', 'id', ['shortname' => 'editingteacher']);
    $managerroleid = $DB->get_field('role', 'id', ['shortname' => 'manager']);
    $designerroleid = $DB->get_field('role', 'id', ['shortname' => 'instdesigner']);
    $isteacher = $DB->record_exists('role_assignments', ['userid' => $userid, 'roleid' => $teacherroleid]);
    $ismanager = $DB->record_exists('role_assignments', ['userid' => $userid, 'roleid' => $managerroleid]);
    $isdesigner = $DB->record_exists('role_assignments', ['userid' => $userid, 'roleid' => $designerroleid]);

    return ($isteacher|| $ismanager || $isdesigner || is_siteadmin());
}

function theme_urcourses_default_has_test_student_account($username) {
    global $DB;

    $email = "$username+urstudent@uregina.ca";
    return $DB->record_exists('user', ['email' => $email]);
}

function theme_urcourses_default_create_darkmode_link() {
    global $PAGE;

    $darkmodeenabled = theme_urcourses_default_darkmode_enabled();

    $darkmodelink = new stdClass();
    $darkmodelink->divider = false;
    $darkmodelink->itemtype = 'link';
    $darkmodelink->link = true;
    $darkmodelink->pixicon = $darkmodeenabled ? 'lightmode' : 'darkmode';
    $darkmodelink->pixplugin = 'theme_urcourses_default';
    $darkmodelink->title = theme_urcourses_default_darkmode_enabled()
        ? get_string('disabledarkmode', 'theme_urcourses_default')
        : get_string('enabledarkmode', 'theme_urcourses_default');
    $darkmodelink->titleidentifier = 'darkmode,theme_urcourses_default';
    $darkmodelink->url = new moodle_url($PAGE->url, ['darkmode' => !$darkmodeenabled]);

    return $darkmodelink;
}

function theme_urcourses_default_create_teststudent_link($hasteststudentaccount) {
    global $USER;

    $studentaccountlink = new \stdClass();
    $studentaccountlink->attributes = [
        [
            'name' => 'data-action',
            'value' => $hasteststudentaccount ? 'resetteststudent' : 'createteststudent' 
        ]
    ];
    $studentaccountlink->divider = false;
    $studentaccountlink->itemtype = 'link';
    $studentaccountlink->link = true;
    $studentaccountlink->pixicon = 'i/user';
    $studentaccountlink->title = $hasteststudentaccount
        ? get_string('modifyteststudent', 'theme_urcourses_default')
        : get_string('createteststudent', 'theme_urcourses_default');
    $studentaccountlink->titleidentifier = 'studentaccount,theme_urcourses_default';
    $studentaccountlink->url = '#';

    return $studentaccountlink;
}

function theme_urcourses_default_add_custom_user_menu_items($usermenuitems, $customitems) {
    $itemcount = count($usermenuitems);
    $preferenceskey = 0;

    foreach($usermenuitems as $key => $item) {
        if (isset($item->title) && $item->title == 'Preferences') {
            $item->divider = false;
            $preferenceskey = $key;
            break;
        }
    }
    $insertpoint = $preferenceskey + 1;

    return array_merge(
        array_slice($usermenuitems, 0, $insertpoint),
        $customitems,
        array_slice($usermenuitems, $insertpoint, $itemcount)
    );
}

function theme_urcourses_default_get_enrol_hint() {
    global $COURSE, $DB, $USER, $OUTPUT, $PAGE;

    if ($PAGE->context->contextlevel != CONTEXT_COURSE) {
        return '';
    }

    if (!$PAGE->url->compare(new core\url('/course/view.php'), URL_MATCH_BASE)) {
        return '';
    }

    $course = get_course($COURSE->id);
    if (empty($course->idnumber)) {
        return '';
    }

    $context = \context_course::instance($course->id);
    if (!has_capability('theme/urcourses_default:viewenrolhint', $context)) {
        return '';
    }

    $enrolments = $DB->get_records_sql("SELECT * FROM ur_crn_map WHERE courseid = '$course->idnumber' ORDER BY semester DESC");
    $hasenrolments = !empty($enrolments);
    $currentsemester = theme_urcourses_default_get_current_semester();
    $latestenrolmentsemester = '';

    if ($hasenrolments) {
        $latestenrolment = $enrolments[array_key_first($enrolments)];
        $latestenrolmentsemester = $latestenrolment->semester;
    }

    // Only show enrol banner for current enrolmens if course is in edit mode.
    // If course has past enrolment, we might want to show outside edit mode too.
    if ($hasenrolments && $latestenrolmentsemester >= $currentsemester && !$USER->editing) {
        return '';
    }

    $coursehint_enrol = new \theme_urcourses_default\output\coursehint_enrol(
        $hasenrolments,
        $currentsemester,
        $latestenrolmentsemester,
        $context->id,
        $course->id
    );

    return $OUTPUT->render($coursehint_enrol);
}

function theme_urcourses_default_get_date_hint() {
    global $COURSE, $DB, $USER, $OUTPUT, $PAGE;

    if ($PAGE->context->contextlevel != CONTEXT_COURSE) {
        return '';
    }

    if (!$PAGE->url->compare(new core\url('/course/view.php'), URL_MATCH_BASE)) {
        return '';
    }

    $course = get_course($COURSE->id);

    $context = \context_course::instance($course->id);
    if (!has_capability('theme/urcourses_default:viewdatehint', $context)) {
        return '';
    }

    $now = \core\di::get(\core\clock::class)->now();
    $nowtimestamp = $now->getTimestamp();

    if ($course->enddate == 0 || $course->enddate > $nowtimestamp) {
        return '';
    }

    $coursehint_date = new \theme_urcourses_default\output\coursehint_date(
        $course->id, 
        $course->enddate
    );

    return $OUTPUT->render($coursehint_date);
}

function theme_urcourses_default_show_status_hint() {
    global $COURSE, $DB, $USER, $PAGE;

    $isoncourseviewpage = $PAGE->url->compare(new core\url('/course/view.php'), URL_MATCH_BASE);
    $userisediting = $USER->editing;
    $context = \context_course::instance($COURSE->id, IGNORE_MISSING);
    $canviewhidden = has_capability('moodle/course:viewhiddencourses', $context);
    $coursehidden = $COURSE->visible == 0;

    return ($canviewhidden && $isoncourseviewpage && ($userisediting || $coursehidden));
}

function theme_urcourses_default_get_status_hint() {
    global $COURSE, $OUTPUT;

    $timestatus = theme_urcourses_default_get_course_time_status($COURSE->startdate, $COURSE->enddate);
    $timestatus_msg = '';
    if ($timestatus === 'ongoing') {
        $timestatus_msg = get_string('timestatus_current', 'theme_urcourses_default');
    }
    else if ($timestatus === 'current') {
        if ($COURSE->enddate == 0) {
            $timestatus_msg = get_string('timestatus_current_noenddate', 'theme_urcourses_default');
        }
        else {
            $timestatus_msg = get_string('timestatus_current', 'theme_urcourses_default');
        }
    }
    else if ($timestatus === 'past' && $COURSE->enddate != 0) {
        $str_enddate = date('F j, Y', $COURSE->enddate);
        $timestatus_msg = get_string('timestatus_past', 'theme_urcourses_default', $str_enddate);
    }
    else if ($timestatus === 'future') {
        $str_startdate = date('F j, Y', $COURSE->startdate);
        $timestatus_msg = get_string('timestatus_future', 'theme_urcourses_default', $str_startdate);
    }

    $enrollment = theme_urcourses_default_get_course_enrollment($COURSE->id);
    $enrollment_msg = '';
    if (empty($enrollment)) {
        $enrollment_msg = get_string('noenrollment', 'theme_urcourses_default');
    }
    else {
        $enrollment_msg = get_string('hasenrollment', 'theme_urcourses_default', $enrollment['name']);
    }

    $availability_button_msg = '';
    $settingslink = \html_writer::link(new \moodle_url('/course/edit.php', ['id' => $COURSE->id]), 'course settings');
    if ($COURSE->visible) {
        $availability_button_msg = get_string('hidecourse', 'theme_urcourses_default', $settingslink);
    }
    else {
        $availability_button_msg = get_string('showcourse', 'theme_urcourses_default', $settingslink);
    }


    $availability_msg = '';
    if ($COURSE->visible) {
        if (empty($enrollment)) {
            $availability_msg = get_string('visible_noenrollment', 'theme_urcourses_default');
        }
        else {
            $availability_msg = get_string('visible', 'theme_urcourses_default');
        }
    }
    else {
        if (empty($enrollment)) {
            $availability_msg = get_string('notvisible_noenrollment', 'theme_urcourses_default');
        }
        else {
            $availability_msg = get_string('notvisible', 'theme_urcourses_default');
        }
    }

    $data = new \stdClass();
    $data->timestatus_msg = $timestatus_msg;
    $data->enrollment_msg = $enrollment_msg;
    $data->availability_msg = $availability_msg;
    $data->availability_button_msg = $availability_button_msg;
    $data->courseid = $COURSE->id;
    $data->visible = $COURSE->visible;

    return $OUTPUT->render_from_template('theme_urcourses_default/course-hint-status', $data);
}

function theme_urcourses_default_get_course_time_status($startdate, $enddate) {
    $currenttime = time();
    $ongoingdate = 946706400; // Jan 01, 2000, 06:00 (date for ongoing courses)

    // Check if the start date is set to the 'ongoing courses' date.
    if ($startdate == $ongoingdate) {
        return 'ongoing';
    }
    // If startdate is greater than the currenttime, the course is in the future.
    if ($startdate > $currenttime) {
        return 'future';
    }
    // If the enddate is set, and the currenttime is after the enddate, the course is in the past.
    if ((isset($enddate) && $enddate != 0) && $enddate < $currenttime) {
        return 'past';
    }

    return 'current';
}

/**
 * Gets enrollment information for the course specified by $courseid.
 * 
 * @return array
 */
function theme_urcourses_default_get_course_enrollment(int $courseid) {
    global $CFG, $DB;
    
    $is_urcourserequest_exist = is_file($CFG->dirroot.'/admin/tool/urcourserequest/lib.php');
    $course_exists = $DB->record_exists('course', array('id' => $courseid));

    if ($is_urcourserequest_exist && $course_exists) {
        require_once($CFG->dirroot.'/admin/tool/urcourserequest/lib.php');

        $course = get_course($courseid);
        $enrollment = tool_urcourserequest_get_course_state($course->idnumber);

        return $enrollment === false ? array() : $enrollment;
    }
    else {
        return array();
    }
}

// 01 - 04: 10 (Winter)
// 05 - 08: 20 (Spring/Summer)
// 09 - 12: 30 (Fall)
function theme_urcourses_default_get_current_semester() {
    $now = \core\di::get(\core\clock::class)->now();
    $month = $now->format('m');
    $year = $now->format('Y');

    if ($month >= 1 && $month <= 4) {
        $semester = 10;
    } else if ($month >= 5 && $month <= 8) {
        $semester = 20;
    } else if ($month >= 9 && $month <= 12) {
        $semester = 30;
    }

    return "$year$semester";
}

function theme_urcourses_default_get_semester_string($semestercode) {
    $year = substr($semestercode, 0, 4);
    $semester = substr($semestercode, -2);
    switch ($semester) {
        case '10':
            return $year . ' ' . get_string('winter', 'theme_urcourses_default');
        case '20':
            return $year . ' ' . get_string('springsummer', 'theme_urcourses_default');
        case '30':
            return $year . ' ' . get_string('fall', 'theme_urcourses_default');
        default:
            return '';
    }
}

/**
 * Helper function which returns the course header image url, picking the current course from the course settings
 * or the fallback image from the theme.
 * If no course header image can should be shown for the current course, the function returns null.
 *
 * @return null | string
 */
function theme_urcourses_default_get_course_header_image_url() {
    global $PAGE;

    // If the current course is the frontpage course (which means that we are not within any real course),
    // directly return null.
    if (isset($PAGE->course->id) && $PAGE->course->id == SITEID) {
        return null;
    }

    // Get the course image.
    $courseimage = \core_course\external\course_summary_exporter::get_course_image($PAGE->course);

    // If the course has a course image.
    if ($courseimage) {
        // Then return it directly.
        return $courseimage;

        // Otherwise, if a fallback image is configured.
    } else if (get_config('theme_boost_union', 'courseheaderimagefallback')) {
        // Get the system context.
        $systemcontext = \context_system::instance();

        // Get filearea.
        $fs = get_file_storage();

        // Get all files from filearea.
        $files = $fs->get_area_files($systemcontext->id, 'theme_boost_union', 'courseheaderimagefallback',
            false, 'itemid', false);

        // Just pick the first file - we are sure that there is just one file.
        $file = reset($files);

        // Build and return the image URL.
        return core\url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
            $file->get_itemid(), $file->get_filepath(), $file->get_filename());
    }

    if (isset($PAGE->course->id)) {
        $renderer = $PAGE->get_renderer('core');
        return $renderer->get_generated_image_for_id($PAGE->course->id);
    } else {
        return null;
    }
}
