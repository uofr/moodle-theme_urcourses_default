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
 * Theme Boost Campus - Library
 *
 * @package    theme_urcourses_default
 * @copyright  2017 Kathrin Osswald, Ulm University <kathrin.osswald@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

//ADDED FOR urcourserequest for banner enrollment
if (is_file($CFG->dirroot.'/blocks/urcourserequest/lib.php')){
    require_once($CFG->dirroot.'/blocks/urcourserequest/lib.php');
    define('URCOURSEREQUEST', TRUE);  
}else{
    define('URCOURSEREQUEST', FALSE);  
}

require_once($CFG->dirroot.'/theme/urcourses_default/locallib.php');

/**
 * Get compiled css.
 *
 * @return string compiled css
 */
/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_urcourses_default_get_main_scss_content($theme) {
    global $CFG;

    $scss = '';
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
    $fs = get_file_storage();

    $context = context_system::instance();
    if ($filename == 'default.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    } else if ($filename == 'plain.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/plain.scss');

    } else if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_urcourses_default', 'preset', 0, '/', $filename))) {
        // This preset file was fetched from the file area for theme_urcourses_default and not theme_boost (see the line above).
        $scss .= $presetfile->get_content();
    } else {
        // Safety fallback - maybe new installs etc.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    }

    // Pre CSS - this is loaded AFTER any prescss from the setting but before the main scss.
    $pre = file_get_contents($CFG->dirroot . '/theme/urcourses_default/scss/pre.scss');
    // Post CSS - this is loaded AFTER the main scss but before the extra scss from the setting.
    $post = file_get_contents($CFG->dirroot . '/theme/urcourses_default/scss/post.scss');
    // legacy css for older styles
    $legacy = file_get_contents($CFG->dirroot . '/theme/urcourses_default/style/legacy.css');
    //callout css
    $callout = file_get_contents($CFG->dirroot . '/theme/urcourses_default/style/callout.css');
    //alert override css
    $alerts = file_get_contents($CFG->dirroot . '/theme/urcourses_default/style/alert.css');
    //pullquote css
    $pquote = file_get_contents($CFG->dirroot . '/theme/urcourses_default/style/pullquote.css');
    // Combine them together.
    return $pre . "\n" . $scss . "\n" . $post . "\n" . $callout . "\n" . $alerts . "\n" . $pquote . "\n" . $legacy;
}

/**
 * Override to add CSS values from settings to pre scss file.
 *
 * Get SCSS to prepend.
 *
 * @param theme_config $theme The theme config object.
 * @return array
 */
function theme_urcourses_default_get_pre_scss($theme) {
    global $CFG;
    // MODIFICATION START.
    require_once($CFG->dirroot . '/theme/urcourses_default/locallib.php');
    // MODIFICATION END.

    $scss = '';
    $configurable = [
        // Config key => [variableName, ...].
        'brandcolor' => ['primary'],
        // MODIFICATION START: Add own variables.
        'section0title' => ['section0title'],
        'showswitchedroleincourse' => ['showswitchedroleincourse'],
        'loginform' => ['loginform'],
        'footerhidehelplink' => ['footerhidehelplink'],
        'footerhidelogininfo' => ['footerhidelogininfo'],
        'footerhidehomelink' => ['footerhidehomelink'],
        'blockicon' => ['blockicon'],
        'brandsuccesscolor' => ['success'],
        'brandinfocolor' => ['info'],
        'brandwarningcolor' => ['warning'],
        'branddangercolor' => ['danger'],
        'darknavbar' => ['darknavbar'],
        'footerblocks' => ['footerblocks'],
        'imageareaitemsmaxheight' => ['imageareaitemsmaxheight'],
        'showsettingsincourse' => ['showsettingsincourse'],
        'incoursesettingsswitchtoroleposition' => ['incoursesettingsswitchtoroleposition'],
        'hidefooteronloginpage' => ['hidefooteronloginpage'],
        'footerhideusertourslink' => ['footerhideusertourslink'],
        'navdrawerfullwidth' => ['navdrawerfullwidth'],
        'helptextmodal' => ['helptextmodal'],
        'breakpoint' => ['breakpoint'],
        'blockcolumnwidth' => ['blockcolumnwidth'],
        'blockcolumnwidthdashboard' => ['blockcolumnwidthdashboard'],
        'addablockposition' => ['addablockposition']
        // MODIFICATION END.
    ];

    // Prepend variables first.
    foreach ($configurable as $configkey => $targets) {
        $value = isset($theme->settings->{$configkey}) ? $theme->settings->{$configkey} : null;
        if (empty($value)) {
            continue;
        }
        array_map(function($target) use (&$scss, $value) {
            $scss .= '$' . $target . ': ' . $value . ";\n";
        }, (array) $targets);
    }

    // MODIFICATION START: Overwrite Boost core SCSS variables which need units and thus couldn't be added to $configurable above.
    // Set variables which are processed in the context of the blockcolumnwidth setting.
    if (isset($theme->settings->blockcolumnwidth)) {
        $scss .= '$blocks-column-width: ' . $theme->settings->blockcolumnwidth . "px;\n";
        $scss .= '$grid-gutter-width: ' . "30px;\n";
    }
    // MODIFICATION END.

    // MODIFICATION START: Set own SCSS variables which need units or calculations and thus couldn't be
    // added to $configurable above.
    // Set variables which are processed in the context of the blockcolumnwidth setting.
    if (isset($theme->settings->blockcolumnwidthdashboard)) {
        $scss .= '$blocks-column-width-dashboard: ' . $theme->settings->blockcolumnwidthdashboard . "px;\n";
        $scss .= '$blocks-plus-gutter-dashboard: $blocks-column-width-dashboard + ( $grid-gutter-width / 2 )' . ";\n";
    }
    // MODIFICATION END.

    // MODIFICATION START: Add login background images that are uploaded to the setting 'loginbackgroundimage' to CSS.
    $scss .= theme_urcourses_default_get_loginbackgroundimage_scss();
    // MODIFICATION END.

    // Prepend pre-scss.
    if (!empty($theme->settings->scsspre)) {
        $scss .= $theme->settings->scsspre;
    }

    return $scss;
}

/**
 * Implement pluginfile function to deliver files which are uploaded in theme settings
 *
 * @param stdClass $course course object
 * @param stdClass $cm course module
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool
 */
function theme_urcourses_default_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM) {
        $theme = theme_config::load('urcourses_default');
        // By default, theme files must be cache-able by both browsers and proxies.
        // TODO: For new file areas: Check if the cacheability needs to be restricted.
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        if ($filearea === 'favicon') {
            return $theme->setting_file_serve('favicon', $args, $forcedownload, $options);
        } else if (s($filearea === 'logo' || $filearea === 'backgroundimage')) {
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } else if ($filearea === 'loginbackgroundimage') {
            return $theme->setting_file_serve('loginbackgroundimage', $args, $forcedownload, $options);
        } else if ($filearea === 'fontfiles') {
            return $theme->setting_file_serve('fontfiles', $args, $forcedownload, $options);
        } else if ($filearea === 'imageareaitems') {
            return $theme->setting_file_serve('imageareaitems', $args, $forcedownload, $options);
        } else if ($filearea === 'additionalresources') {
            return $theme->setting_file_serve('additionalresources', $args, $forcedownload, $options);
        } else {
            send_file_not_found();
        }
    } else {
        send_file_not_found();
    }
}

/**
 * If setting is updated, use this callback to clear the theme_urcourses_default' own application cache.
 */
function theme_urcourses_default_reset_app_cache() {
    // Get the cache from area.
    $themeboostcampuscache = cache::make('theme_urcourses_default', 'imagearea');
    // Delete the cache for the imagearea.
    $themeboostcampuscache->delete('imageareadata');
}

/**
 * If setting is updated, use this callback to reset the theme_urcourses_default_infobanner_dismissed user preferences.
 */
function theme_urcourses_default_infobanner_reset_visibility() {
    global $DB;

    if (get_config('theme_urcourses_default', 'perpibresetvisibility') == 1) {
        // Get all users that have dismissed the info banner once and therefore the user preference.
        $whereclause = 'name = :name AND value = :value';
        $params = ['name' => 'theme_urcourses_default_infobanner_dismissed', 'value' => '1'];
        $users = $DB->get_records_select('user_preferences', $whereclause, $params, '', 'userid');

        // Initialize variable for feedback messages.
        $somethingwentwrong = false;
        // Store coding exception.
        $codingexception[] = array();

        foreach ($users as $user) {
            try {
                unset_user_preference('theme_urcourses_default_infobanner_dismissed', $user->userid);
            } catch (coding_exception $e) {
                $somethingwentwrong = true;
                $codingexception['message'] = $e->getMessage();
                $codingexception['stacktrace'] = $e->getTraceAsString();
            }
        }

        if (!$somethingwentwrong) {
            \core\notification::success(get_string('resetperpetualinfobannersuccess', 'theme_urcourses_default'));
        } else {
            \core\notification::error(get_string('resetperpetualinfobannervisibilityerror',
                    'theme_urcourses_default', $codingexception));
        }

        // Reset the checkbox.
        set_config('perpibresetvisibility', 0, 'theme_urcourses_default');
    }
}


/**
 * Inplace editable elements for boost campus theme.
 * @param string $itemtype
 * @param int $itemid
 * @param string $newvalue
 * @return \core\output\inplace_editable
 */
function theme_urcourses_default_inplace_editable($itemtype, $itemid, $newvalue) {
    // coursename: allows instructors to change course name inline if editing is on
    if ($itemtype === 'coursename') {
        global $CFG;

        $course = get_course($itemid);
        $context = context_course::instance($course->id);
        $newvalue = clean_param($newvalue, PARAM_TEXT);

        \external_api::validate_context($context);
        require_capability('moodle/course:changefullname', $context);

        $course->fullname = $newvalue;
        update_course($course);

        $can_edit_coursename = has_capability('moodle/course:changefullname', $context);
        $course_link = '<h1 class="d-inline"><a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">'.$course->fullname.'</a></h1>';
        return new \core\output\inplace_editable('theme_urcourses_default', 'coursename', $course->id, $can_edit_coursename, $course_link, format_string($course->fullname));
    }
}

function theme_urcourses_default_get_fontawesome_icon_map() {
    return [
        'theme_urcourses_default:i/times' => 'fa-times'
    ];
}
