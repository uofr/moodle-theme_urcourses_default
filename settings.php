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

       // Create slider tab.
        $tab = new admin_settingpage('theme_boost_union_slider',
                get_string('slidertab', 'theme_boost_union', null, true));

        // Create slider general heading.
        $name = 'theme_boost_union/slidergeneralheading';
        $title = get_string('slidergeneralheading', 'theme_boost_union', null, true);
        $setting = new admin_setting_heading($name, $title, null);
        $tab->add($setting);

        // Setting: Position of the slider on the frontpage.
        $sliderfrontpagepositionoptions = [
                THEME_BOOST_UNION_SETTING_SLIDER_FRONTPAGEPOSITION_BEFOREBEFORE =>
                        get_string('sliderfrontpagepositionsetting_beforebefore', 'theme_boost_union'),
                THEME_BOOST_UNION_SETTING_SLIDER_FRONTPAGEPOSITION_BEFOREAFTER =>
                        get_string('sliderfrontpagepositionsetting_beforeafter', 'theme_boost_union'),
                THEME_BOOST_UNION_SETTING_SLIDER_FRONTPAGEPOSITION_AFTERBEFORE =>
                        get_string('sliderfrontpagepositionsetting_afterbefore', 'theme_boost_union'),
                THEME_BOOST_UNION_SETTING_SLIDER_FRONTPAGEPOSITION_AFTERAFTER =>
                        get_string('sliderfrontpagepositionsetting_afterafter', 'theme_boost_union'),
                THEME_BOOST_UNION_SETTING_SLIDER_FRONTPAGEPOSITION_DASHBOARD =>
                        get_string('sliderfrontpagepositionsetting_dashboard', 'theme_urcourses_default'),
        ];
        $name = 'theme_boost_union/sliderfrontpageposition';
        $title = get_string('sliderfrontpagepositionsetting', 'theme_boost_union', null, true);
        $url = new core\url('/admin/settings.php', ['section' => 'frontpagesettings']);
        $description = get_string('sliderfrontpagepositionsetting_desc', 'theme_boost_union', ['url' => $url], true);
        $setting = new admin_setting_configselect($name, $title, $description,
                THEME_BOOST_UNION_SETTING_SLIDER_FRONTPAGEPOSITION_BEFOREBEFORE, $sliderfrontpagepositionoptions);
        $tab->add($setting);

        // Setting: Enable arrow navigation.
        $name = 'theme_boost_union/sliderarrownav';
        $title = get_string('sliderarrownavsetting', 'theme_boost_union', null, true);
        $description = get_string('sliderarrownavsetting_desc', 'theme_boost_union', null, true);
        $setting = new admin_setting_configselect($name, $title, $description, THEME_BOOST_UNION_SETTING_SELECT_NO,
                $yesnooption);
        $tab->add($setting);

        // Setting: Enable slider indicator navigation.
        $name = 'theme_boost_union/sliderindicatornav';
        $title = get_string('sliderindicatornavsetting', 'theme_boost_union', null, true);
        $description = get_string('sliderindicatornavsetting_desc', 'theme_boost_union', null, true);
        $setting = new admin_setting_configselect($name, $title, $description, THEME_BOOST_UNION_SETTING_SELECT_NO,
                $yesnooption);
        $tab->add($setting);

        // Setting: Slider animation type.
        $slideranimationoptions = [
                // THEME_BOOST_UNION_SETTING_SLIDER_ANIMATIONTYPE_NONE =>
                //         get_string('slideranimationsetting_none', 'theme_boost_union'),
                THEME_BOOST_UNION_SETTING_SLIDER_ANIMATIONTYPE_FADE =>
                        get_string('slideranimationsetting_fade', 'theme_boost_union'),
                THEME_BOOST_UNION_SETTING_SLIDER_ANIMATIONTYPE_SLIDE =>
                        get_string('slideranimationsetting_slide', 'theme_boost_union'),
        ];
        $name = 'theme_boost_union/slideranimation';
        $title = get_string('slideranimationsetting', 'theme_boost_union', null, true);
        $description = get_string('slideranimationsetting_desc', 'theme_boost_union', null, true);
        $setting = new admin_setting_configselect($name, $title, $description,
                THEME_BOOST_UNION_SETTING_SLIDER_ANIMATIONTYPE_SLIDE, $slideranimationoptions);
        $tab->add($setting);

        // Setting: Slider interval speed.
        $name = 'theme_boost_union/sliderinterval';
        $title = get_string('sliderintervalsetting', 'theme_boost_union', null, true);
        $description = get_string('sliderintervalsetting_desc', 'theme_boost_union', null, true);
        $setting = new admin_setting_configtext($name, $title, $description, 5000, PARAM_INT, 6);
        $tab->add($setting);

        // Setting: Allow slider keyboard interaction.
        $name = 'theme_boost_union/sliderkeyboard';
        $title = get_string('sliderkeyboardsetting', 'theme_boost_union', null, true);
        $description = get_string('sliderkeyboardsetting_desc', 'theme_boost_union', null, true);
        $setting = new admin_setting_configselect($name, $title, $description, THEME_BOOST_UNION_SETTING_SELECT_YES,
                $yesnooption);
        $tab->add($setting);

        // Setting: Pause slider on mouseover.
        $name = 'theme_boost_union/sliderpause';
        $title = get_string('sliderpausesetting', 'theme_boost_union', null, true);
        $description = get_string('sliderpausesetting_desc', 'theme_boost_union', null, true);
        $setting = new admin_setting_configselect($name, $title, $description, THEME_BOOST_UNION_SETTING_SELECT_YES,
                $yesnooption);
        $tab->add($setting);

        // Setting: Cycle through slides.
        $sliderrideoptions = [
                THEME_BOOST_UNION_SETTING_SLIDER_RIDE_ONPAGELOAD =>
                        get_string('sliderridesetting_onpageload', 'theme_boost_union'),
                THEME_BOOST_UNION_SETTING_SLIDER_RIDE_AFTERINTERACTION =>
                        get_string('sliderridesetting_afterinteraction', 'theme_boost_union'),
                THEME_BOOST_UNION_SETTING_SLIDER_RIDE_NEVER =>
                        get_string('sliderridesetting_never', 'theme_boost_union'),
        ];
        $name = 'theme_boost_union/sliderride';
        $title = get_string('sliderridesetting', 'theme_boost_union', null, true);
        $description = get_string('sliderridesetting_desc', 'theme_boost_union', null, true);
        $setting = new admin_setting_configselect($name, $title, $description,
                THEME_BOOST_UNION_SETTING_SLIDER_RIDE_ONPAGELOAD, $sliderrideoptions);
        $tab->add($setting);

        // Setting: Continuously cycle through slides.
        $name = 'theme_boost_union/sliderwrap';
        $title = get_string('sliderwrapsetting', 'theme_boost_union', null, true);
        $description = get_string('sliderwrapsetting_desc', 'theme_boost_union', null, true);
        $setting = new admin_setting_configselect($name, $title, $description, THEME_BOOST_UNION_SETTING_SELECT_YES,
                $yesnooption);
        $tab->add($setting);

        // Prepare options for the order settings.
        $slidesorders = [];
        for ($i = 1; $i <= THEME_BOOST_UNION_SETTING_SLIDES_COUNT; $i++) {
            $slidesorders[$i] = $i;
        }

        // Create a hardcoded amount of slides without code duplication.
        for ($i = 1; $i <= THEME_BOOST_UNION_SETTING_SLIDES_COUNT; $i++) {

            // Create slide heading.
            $name = 'theme_boost_union/slide'.$i.'heading';
            $title = get_string('slideheading', 'theme_boost_union', ['no' => $i], true);
            $setting = new admin_setting_heading($name, $title, null);
            $tab->add($setting);

            // Setting: Slide enabled.
            $name = 'theme_boost_union/slide'.$i.'enabled';
            $title = get_string('slideenabledsetting', 'theme_boost_union', ['no' => $i], true);
            $description = get_string('slideenabledsetting_desc', 'theme_boost_union', ['no' => $i], true);
            $setting = new admin_setting_configselect($name, $title, $description, THEME_BOOST_UNION_SETTING_SELECT_NO,
                    $yesnooption);
            $tab->add($setting);

            // Setting: Slide background image.
            $name = 'theme_boost_union/slide'.$i.'backgroundimage';
            $title = get_string('slidebackgroundimagesetting', 'theme_boost_union', ['no' => $i], true);
            $description = get_string('slidebackgroundimagesetting_desc', 'theme_boost_union', ['no' => $i], true);
            $setting = new admin_setting_configstoredfile($name, $title, $description, 'slidebackgroundimage'.$i, 0,
                ['maxfiles' => 1, 'accepted_types' => 'web_image']);
            $setting->set_updatedcallback('theme_reset_all_caches');
            $tab->add($setting);
            $page->hide_if('theme_boost_union/slide'.$i.'backgroundimage', 'theme_boost_union/slide'.$i.'enabled',
                'neq', THEME_BOOST_UNION_SETTING_SELECT_YES);

            // Setting: Slide background image alt attribute.
            $name = 'theme_boost_union/slide'.$i.'backgroundimagealt';
            $title = get_string('slidebackgroundimagealtsetting', 'theme_boost_union', ['no' => $i], true);
            $description = get_string('slidebackgroundimagealtsetting_desc', 'theme_boost_union', ['no' => $i], true);
            $setting = new admin_setting_configtext($name, $title, $description, '');
            $tab->add($setting);
            $page->hide_if('theme_boost_union/slide'.$i.'backgroundimagealt', 'theme_boost_union/slide'.$i.'enabled', 'neq',
                THEME_BOOST_UNION_SETTING_SELECT_YES);

            // Setting: Slide caption.
            $name = 'theme_boost_union/slide'.$i.'caption';
            $title = get_string('slidecaptionsetting', 'theme_boost_union', ['no' => $i], true);
            $description = get_string('slidecaptionsetting_desc', 'theme_boost_union', ['no' => $i], true);
            $setting = new admin_setting_configtext($name, $title, $description, '');
            $tab->add($setting);
            $page->hide_if('theme_boost_union/slide'.$i.'caption', 'theme_boost_union/slide'.$i.'enabled', 'neq',
                THEME_BOOST_UNION_SETTING_SELECT_YES);

            // Setting: Slide content.
            $name = 'theme_boost_union/slide'.$i.'content';
            $title = get_string('slidecontentsetting', 'theme_boost_union', ['no' => $i], true);
            $description = get_string('slidecontentsetting_desc', 'theme_boost_union', ['no' => $i], true);
            $setting = new admin_setting_confightmleditor($name, $title, $description, '');
            $tab->add($setting);
            $page->hide_if('theme_boost_union/slide'.$i.'content', 'theme_boost_union/slide'.$i.'enabled', 'neq',
                THEME_BOOST_UNION_SETTING_SELECT_YES);

            // Setting: Slide content style.
            $name = 'theme_boost_union/slide'.$i.'contentstyle';
            $title = get_string('slidecontentstylesetting', 'theme_boost_union', ['no' => $i], true);
            $description = get_string('slidecontentstylesetting_desc', 'theme_boost_union', ['no' => $i], true);
            $slidecontentstyleoptions = [
                    THEME_BOOST_UNION_SETTING_CONTENTSTYLE_LIGHT =>
                            get_string('slidecontentstylesetting_light', 'theme_boost_union'),
                    THEME_BOOST_UNION_SETTING_CONTENTSTYLE_LIGHTSHADOW =>
                            get_string('slidecontentstylesetting_lightshadow', 'theme_boost_union'),
                    THEME_BOOST_UNION_SETTING_CONTENTSTYLE_DARK =>
                            get_string('slidecontentstylesetting_dark', 'theme_boost_union'),
                    THEME_BOOST_UNION_SETTING_CONTENTSTYLE_DARKSHADOW =>
                            get_string('slidecontentstylesetting_darkshadow', 'theme_boost_union'),
            ];
            $setting = new admin_setting_configselect($name, $title, $description,
                THEME_BOOST_UNION_SETTING_CONTENTSTYLE_LIGHT, $slidecontentstyleoptions);
            $tab->add($setting);
            $page->hide_if('theme_boost_union/slide'.$i.'contentstyle', 'theme_boost_union/slide'.$i.'enabled', 'neq',
                THEME_BOOST_UNION_SETTING_SELECT_YES);

            // Setting: Slide link URL.
            $name = 'theme_boost_union/slide'.$i.'link';
            $title = get_string('slidelinksetting', 'theme_boost_union', ['no' => $i], true);
            $description = get_string('slidelinksetting_desc', 'theme_boost_union', ['no' => $i], true);
            $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
            $tab->add($setting);
            $page->hide_if('theme_boost_union/slide'.$i.'link', 'theme_boost_union/slide'.$i.'enabled', 'neq',
                    THEME_BOOST_UNION_SETTING_SELECT_YES);

            // Setting: Slide link title.
            $name = 'theme_boost_union/slide'.$i.'linktitle';
            $title = get_string('slidelinktitlesetting', 'theme_boost_union', ['no' => $i], true);
            $description = get_string('slidelinktitlesetting_desc', 'theme_boost_union', ['no' => $i], true);
            $setting = new admin_setting_configtext($name, $title, $description, '');
            $tab->add($setting);
            $page->hide_if('theme_boost_union/slide'.$i.'linktitle', 'theme_boost_union/slide'.$i.'enabled', 'neq',
                    THEME_BOOST_UNION_SETTING_SELECT_YES);

            // Setting: Slide link source.
            $name = 'theme_boost_union/slide'.$i.'linksource';
            $title = get_string('slidelinksourcesetting', 'theme_boost_union', ['no' => $i], true);
            $description = get_string('slidelinksourcesetting_desc', 'theme_boost_union', ['no' => $i], true);
            $slidelinksourceoptions = [
                    THEME_BOOST_UNION_SETTING_SLIDER_LINKSOURCE_BOTH =>
                            get_string('slidelinksourcesetting_both', 'theme_boost_union'),
                    THEME_BOOST_UNION_SETTING_SLIDER_LINKSOURCE_IMAGE =>
                            get_string('slidelinksourcesetting_image', 'theme_boost_union'),
                    THEME_BOOST_UNION_SETTING_SLIDER_LINKSOURCE_TEXT =>
                            get_string('slidelinksourcesetting_text', 'theme_boost_union'),
            ];
            $setting = new admin_setting_configselect($name, $title, $description,
                    THEME_BOOST_UNION_SETTING_SLIDER_LINKSOURCE_BOTH, $slidelinksourceoptions);
            $tab->add($setting);
            $page->hide_if('theme_boost_union/slide'.$i.'linksource', 'theme_boost_union/slide'.$i.'enabled', 'neq',
                    THEME_BOOST_UNION_SETTING_SELECT_YES);

            // Setting: Slide link target.
            $name = 'theme_boost_union/slide'.$i.'linktarget';
            $title = get_string('slidelinktargetsetting', 'theme_boost_union', ['no' => $i], true);
            $description = get_string('slidelinktargetsetting_desc', 'theme_boost_union', ['no' => $i], true);
            $slidelinktargetnoptions = [
                    THEME_BOOST_UNION_SETTING_LINKTARGET_SAMEWINDOW =>
                            get_string('slidelinktargetsetting_samewindow', 'theme_boost_union'),
                    THEME_BOOST_UNION_SETTING_LINKTARGET_NEWTAB =>
                            get_string('slidelinktargetsetting_newtab', 'theme_boost_union'), ];
            $setting = new admin_setting_configselect($name, $title, $description, THEME_BOOST_UNION_SETTING_LINKTARGET_SAMEWINDOW,
                    $slidelinktargetnoptions);
            $tab->add($setting);
            $page->hide_if('theme_boost_union/slide'.$i.'linktarget', 'theme_boost_union/slide'.$i.'enabled', 'neq',
                    THEME_BOOST_UNION_SETTING_SELECT_YES);

            // Setting: Slide order position.
            $name = 'theme_boost_union/slide'.$i.'order';
            $title = get_string('slideordersetting', 'theme_boost_union', ['no' => $i], true);
            $description = get_string('slideordersetting_desc', 'theme_boost_union', ['no' => $i], true);
            $setting = new admin_setting_configselect($name, $title, $description, $i, $slidesorders);
            $tab->add($setting);
            $page->hide_if('theme_boost_union/slide'.$i.'order', 'theme_boost_union/slide'.$i.'enabled', 'neq',
                    THEME_BOOST_UNION_SETTING_SELECT_YES);
        }

        // Add tab to settings page.
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
