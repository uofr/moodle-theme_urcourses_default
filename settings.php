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
 * Theme UR Courses Default - Settings file
 *
 * @package    theme_urcourses_default
 * @copyright  2025 John Lane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig || has_capability('theme/boost_union:configure', context_system::instance())) {

    // How this file works:
    // Boost Union's settings are divided into multiple settings pages which resides in its own settings category.
    // You will understand it as soon as you look at /theme/boost_union/settings.php.
    // This settings file here is built in a way that it adds another settings page to this existing settings
    // category. You can add all child-theme-specific settings to this settings page here.

    // However, there is still the $settings variable which is expected by Moodle core to be filled with the theme
    // settings and which is automatically linked from the theme selector page.
    // To avoid that there appears a broken "UR Courses" settings page, we redirect the user to a settings
    // overview page if he opens this page.
    $mainsettingspageurl = new moodle_url('/admin/settings.php', ['section' => 'themesettingurcoursesdefault']);
    if ($ADMIN->fulltree && $PAGE->has_set_url() && $PAGE->url->compare($mainsettingspageurl)) {
        redirect(new moodle_url('/admin/settings.php', ['section' => 'theme_urcourses_default']));
    }

    // Create empty settings page structure to make the site administration work on non-admin pages.
    if (!$ADMIN->fulltree) {
        // Create UR Courses settings page
        // (and allow users with the theme/boost_union:configure capability to access it).
        $tab = new admin_settingpage('theme_urcourses_default',
                get_string('configtitle', 'theme_urcourses_default', null, true),
                'theme/boost_union:configure');
        $ADMIN->add('theme_boost_union', $tab);
    }

    // Create full settings page structure.
    // phpcs:disable moodle.ControlStructures.ControlSignature.Found
    else if ($ADMIN->fulltree) {

        // Require the necessary libraries.
        require_once($CFG->dirroot . '/theme/boost_union/lib.php');
        require_once($CFG->dirroot . '/theme/boost_union/locallib.php');
        require_once($CFG->dirroot . '/theme/urcourses_default/lib.php');
        require_once($CFG->dirroot . '/theme/urcourses_default/locallib.php');

        // Prepare options array for select settings.
        // Due to MDL-58376, we will use binary select settings instead of checkbox settings throughout this theme.
        $yesnooption = [THEME_BOOST_UNION_SETTING_SELECT_YES => get_string('yes'),
                THEME_BOOST_UNION_SETTING_SELECT_NO => get_string('no'), ];


        // Create UR Courses settings page with tabs
        // (and allow users with the theme/boost_union:configure capability to access it).
        $page = new theme_boost_admin_settingspage_tabs('theme_urcourses_default',
                get_string('configtitle', 'theme_urcourses_default', null, true),
                'theme/boost_union:configure');


        // Create general settings tab.
        $tab = new admin_settingpage('theme_urcourses_default_general',
                get_string('generalsettings', 'theme_boost', null, true));

        // Create inheritance heading.
        $name = 'theme_urcourses_default/inheritanceheading';
        $title = get_string('inheritanceheading', 'theme_urcourses_default', null, true);
        $setting = new admin_setting_heading($name, $title, null);
        $tab->add($setting);

        // Prepare inheritance options.
        $inheritanceoptions = [
                THEME_URCOURSES_DEFAULT_SETTING_INHERITANCE_INHERIT =>
                        get_string('inheritanceinherit', 'theme_urcourses_default'),
                THEME_URCOURSES_DEFAULT_SETTING_INHERITANCE_DUPLICATE =>
                        get_string('inheritanceduplicate', 'theme_urcourses_default'),
        ];

        // Setting: Pre SCSS inheritance setting.
        $name = 'theme_urcourses_default/prescssinheritance';
        $title = get_string('prescssinheritancesetting', 'theme_urcourses_default', null, true);
        $description = get_string('prescssinheritancesetting_desc', 'theme_urcourses_default', null, true).'<br />'.
                get_string('inheritanceoptionsexplanation', 'theme_urcourses_default', null, true);
        $setting = new admin_setting_configselect($name, $title, $description,
                THEME_URCOURSES_DEFAULT_SETTING_INHERITANCE_INHERIT, $inheritanceoptions);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $tab->add($setting);

        // Setting: Extra SCSS inheritance setting.
        $name = 'theme_urcourses_default/extrascssinheritance';
        $title = get_string('extrascssinheritancesetting', 'theme_urcourses_default', null, true);
        $description = get_string('extrascssinheritancesetting_desc', 'theme_urcourses_default', null, true).'<br />'.
                get_string('inheritanceoptionsexplanation', 'theme_urcourses_default', null, true);
        $setting = new admin_setting_configselect($name, $title, $description,
                THEME_URCOURSES_DEFAULT_SETTING_INHERITANCE_INHERIT, $inheritanceoptions);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $tab->add($setting);

        // Add tab to settings page.
        $page->add($tab);

        /**********************************************************
         * EXTENSION POINT:
         * Add your UR Courses settings here.
         *********************************************************/
        $tab = new admin_settingpage('theme_urcourses_default_colour',
        get_string('colourtab', 'theme_urcourses_default', null, true));

        $name = 'theme_urcourses_default/colour';
        $title = get_string('colourheading', 'theme_urcourses_default', null, true);
        $setting = new admin_setting_heading($name, $title, null);
        $tab->add($setting);

        $name = 'theme_urcourses_default/brandcolour';
        $title = get_string('brandcoloursetting', 'theme_urcourses_default', null, true);
        $description = get_string('brandcoloursetting_desc', 'theme_urcourses_default', null, true);
        $default = '';
        $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $tab->add($setting);

        $page->add($tab);

        // $tab = new admin_settingpage('theme_urcourses_default_feedback',
        // get_string('feedbacktab', 'theme_urcourses_default', null, true));

        // $name = 'theme_urcourses_default/feedback';
        // $title = get_string('feedbackheading', 'theme_urcourses_default', null, true);
        // $setting = new admin_setting_heading($name, $title, null);
        // $tab->add($setting);

        // $name = 'theme_urcourses_default/questionnaireid';
        // $title = get_string('questionnaireid', 'theme_urcourses_default', null, true);
        // $description = get_string('questionnaireid_desc', 'theme_urcourses_default', null, true);
        // $default = 0;
        // $sitelevelquestionnaires = $DB->get_records_sql(
        //     "SELECT cm.id, q.name
        //     FROM {course_modules} cm
        //     LEFT JOIN {questionnaire} q ON q.id = cm.instance
        //     WHERE cm.course = 1
        //     AND cm.deletioninprogress = 0
        //     AND cm.module = (SELECT id FROM {modules} m WHERE m.name = 'questionnaire')"
        // );
        // $options = [
        //         '0' => 'None'
        // ];
        // foreach ($sitelevelquestionnaires as $q) {
        //     $options[$q->id] = $q->name;
        // }
        // $setting = new admin_setting_configselect($name, $title, $description, $default, $options);
        // $tab->add($setting);

        // $name = 'theme_urcourses_default/questionname';
        // $title = get_string('questionname', 'theme_urcourses_default', null, true);
        // $description = get_string('questionname_desc', 'theme_urcourses_default', null, true);
        // $default = 0;
        // $options = [
        //         '0' => 'None'
        // ];
        // $questionnaireid = get_config('theme_urcourses_default', 'questionnaireid');
        // if ($questionnaireid != 0) {
        //     require_once($CFG->dirroot . '/mod/questionnaire/locallib.php');
        //     list($cm, $course, $questionnaire) = questionnaire_get_standard_page_items($questionnaireid);
        //     $questions = $DB->get_records_sql(
        //         "SELECT qq.id, qq.name FROM {questionnaire_question} qq
        //         WHERE qq.surveyid = ?",
        //         [$questionnaire->id]
        //     );
        //     foreach ($questions as $question) {
        //         $options["q$question->id"] = $question->name;
        //     }
        // }
        // $setting = new admin_setting_configselect($name, $title, $description, $default, $options);
        // $tab->add($setting);

        // $page->add($tab);

        // Add settings page to the admin settings category.
        $ADMIN->add('theme_boost_union', $page);
    }
}
