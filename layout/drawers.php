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
 * Theme Boost Union - Drawers page layout.
 *
 * This layoutfile is based on theme/boost/layout/drawers.php
 *
 * Modifications compared to this layout file:
 * * Include footnote
 * * Render theme_boost_union/drawers instead of theme_boost/drawers template
 * * Include course related hints
 * * Include back to top button
 * * Include activity navigation
 *
 * @package   theme_boost_union
 * @copyright 2022 Luca Bösch, BFH Bern University of Applied Sciences luca.boesch@bfh.ch
 * @copyright based on code from theme_boost by Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG,$PAGE,$DB,$COURSE;

require_once($CFG->libdir . '/behat/lib.php');
require_once($CFG->dirroot . '/course/lib.php');

// Require own locallib.php.
//require_once($CFG->dirroot . '/theme/boost_union/locallib.php');
require_once($CFG->dirroot . '/theme/urcourses_default/locallib.php');

// Add activity navigation if the feature is enabled.
$activitynavigation = get_config('theme_boost_union', 'activitynavigation');
if ($activitynavigation == THEME_BOOST_UNION_SETTING_SELECT_YES) {
    $PAGE->theme->usescourseindex = false;
}

// Add block button in editing mode.
$addblockbutton = $OUTPUT->addblockbutton();

user_preference_allow_ajax_update('drawer-open-index', PARAM_BOOL);
user_preference_allow_ajax_update('drawer-open-block', PARAM_BOOL);

if (isloggedin()) {
    $courseindexopen = (get_user_preferences('drawer-open-index', true) == true);
    $blockdraweropen = (get_user_preferences('drawer-open-block') == true);
} else {
    $courseindexopen = false;
    $blockdraweropen = false;
}

if (defined('BEHAT_SITE_RUNNING')) {
    $blockdraweropen = true;
}

$extraclasses = ['uses-drawers'];
if ($courseindexopen) {
    $extraclasses[] = 'drawer-open-index';
}

$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = (strpos($blockshtml, 'data-block=') !== false || !empty($addblockbutton));
if (!$hasblocks) {
    $blockdraweropen = false;
}
$courseindex = core_course_drawer();
if (!$courseindex) {
    $courseindexopen = false;
}

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$forceblockdraweropen = $OUTPUT->firstview_fakeblocks();

$secondarynavigation = false;
$overflow = '';
if ($PAGE->has_secondary_navigation()) {
    $tablistnav = $PAGE->has_tablist_secondary_navigation();
    $moremenu = new \core\navigation\output\more_menu($PAGE->secondarynav, 'nav-tabs', true, $tablistnav);
    $secondarynavigation = $moremenu->export_for_template($OUTPUT);
    $overflowdata = $PAGE->secondarynav->get_overflow_menu_data();
    if (!is_null($overflowdata)) {
        $overflow = $overflowdata->export_for_template($OUTPUT);
    }
}

$primary = new core\navigation\output\primary($PAGE);
$renderer = $PAGE->get_renderer('core');
$primarymenu = $primary->export_for_template($renderer);
$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions() && !$PAGE->has_secondary_navigation();
// If the settings menu will be included in the header then don't add it here.
$regionmainsettingsmenu = $buildregionmainsettings ? $OUTPUT->region_main_settings_menu() : false;

//including Dark Mode css if darkmode==1 if query string is set
//darkmode toggle code
$setdarkmode = optional_param('darkmode', -1, PARAM_INT);

if ($setdarkmode > -1) {
    $userid = $USER->id;
    $table = 'theme_urcourses_darkmode';

    $newrecord = new stdClass();
    $newrecord->userid = $userid;

    //database check if user has a record, insert if not
    if ($record = $DB->get_record($table, array('userid'=>$userid))) {
     //if has a record, update record to $setdarkmode
     
     $newrecord->darkmode = $setdarkmode;
     $newrecord->id = $record->id;
     $DB->update_record($table, $newrecord);
    }
    else {
        //create a record
        $newrecord->darkmode = $setdarkmode;
        $DB->insert_record($table, $newrecord);
    }  
    
 }

 $darkmodecheck = $DB->get_record('theme_urcourses_darkmode', array('userid'=>$USER->id, 'darkmode'=>1));
 error_log('darkmode:'.print_r($darkmodecheck,1));
//check if user has darkmode on in database and include if so
if($darkmodecheck){
   $PAGE->requires->css('/theme/urcourses_default/style/darkmode.css');
}


$PAGE->requires->css('/theme/urcourses_default/style/callout.css');

$header = $PAGE->activityheader;
$headercontent = $header->export_for_template($renderer);

$course_visibility_toggle = array();
if (theme_urcourses_default_is_show_visibility_toggle()) {
    $courseid = $COURSE->id;
    $startdate = $COURSE->startdate;
    $enddate = $COURSE->enddate;
    $is_visible = $COURSE->visible;
    $enrollment = theme_urcourses_default_get_course_enrollment($COURSE->id);
    
    $course_visibility_toggle_renderable = new \theme_urcourses_default\renderable\course_visibility_toggle(
        $courseid, $startdate, $enddate, $is_visible, $enrollment);
    
    $course_visibility_toggle = $course_visibility_toggle_renderable->export_for_template($renderer);
}

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'courseindexopen' => $courseindexopen,
    'blockdraweropen' => $blockdraweropen,
    'courseindex' => $courseindex,
    'primarymoremenu' => $primarymenu['moremenu'],
    'secondarymoremenu' => $secondarynavigation ?: false,
    'mobileprimarynav' => $primarymenu['mobileprimarynav'],
    'usermenu' => $primarymenu['user'],
    'langmenu' => $primarymenu['lang'],
    'forceblockdraweropen' => $forceblockdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'overflow' => $overflow,
    'headercontent' => $headercontent,
    'addblockbutton' => $addblockbutton,
    'visibilitytoggle' => $course_visibility_toggle
];

// Get and use the course related hints HTML code, if any hints are configured.
$courserelatedhintshtml = theme_boost_union_get_course_related_hints();
if ($courserelatedhintshtml) {
    $templatecontext['courserelatedhints'] = $courserelatedhintshtml;
}

// Include the template content for the course related hints.
require_once(__DIR__ . '/includes/courserelatedhints.php');

// Include the content for the back to top button.
require_once(__DIR__ . '/includes/backtotopbutton.php');

// Include the content for the scrollspy.
require_once(__DIR__ . '/includes/scrollspy.php');

// Include the template content for the footnote.
require_once(__DIR__ . '/includes/footnote.php');

// Include the template content for the static pages.
require_once(__DIR__ . '/includes/staticpages.php');

// Include the template content for the JavaScript disabled hint.
require_once(__DIR__ . '/includes/javascriptdisabledhint.php');

// Include the template content for the info banners.
require_once(__DIR__ . '/includes/infobanners.php');

// Render drawers.mustache from boost_union.
echo $OUTPUT->render_from_template('theme_urcourses_default/drawers', $templatecontext);
