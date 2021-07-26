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
 * Theme Boost Campus - Settings file
 *
 * @package    theme_urcourses_default
 * @copyright  2017 Kathrin Osswald, Ulm University <kathrin.osswald@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    // Create settings page with tabs.
    $settings = new theme_boost_admin_settingspage_tabs('themesettingurcourses_default',
        get_string('configtitle', 'theme_urcourses_default', null, true));


    // Create general tab.
    $page = new admin_settingpage('theme_urcourses_default_general', get_string('generalsettings', 'theme_boost', null, true));

    // Settings title to group preset related settings together with a common heading. We don't want a description here.
    $name = 'theme_urcourses_default/presetheading';
    $title = get_string('presetheadingsetting', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);

    // Replicate the preset setting from theme_boost.
    $name = 'theme_urcourses_default/preset';
    $title = get_string('preset', 'theme_boost', null, true);
    $description = get_string('preset_desc', 'theme_boost', null, true);
    $default = 'default.scss';

    // We list files in our own file area to add to the drop down. We will provide our own function to
    // load all the presets from the correct paths.
    $context = context_system::instance();
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'theme_urcourses_default', 'preset', 0, 'itemid, filepath, filename', false);

    $choices = [];
    foreach ($files as $file) {
        $choices[$file->get_filename()] = $file->get_filename();
    }
    // These are the built in presets from Boost.
    $choices['default.scss'] = 'default.scss';
    $choices['plain.scss'] = 'plain.scss';

    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);


    // Preset files setting.
    $name = 'theme_urcourses_default/presetfiles';
    $title = get_string('presetfiles', 'theme_boost', null, true);
    $description = get_string('presetfiles_desc', 'theme_boost', null, true);

    $setting = new admin_setting_configstoredfile($name, $title, $description, 'preset', 0,
        array('maxfiles' => 20, 'accepted_types' => array('.scss')));
    $page->add($setting);

    // Settings title to group core background image related settings together with a common heading.
    // We don't want a description here.
    $name = 'theme_urcourses_default/backgroundimageheading';
    $title = get_string('backgroundimage', 'theme_boost', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);

    // Background image setting.
    $name = 'theme_urcourses_default/backgroundimage';
    $title = get_string('backgroundimage', 'theme_boost', null, true);
    $description = get_string('backgroundimage_desc', 'theme_boost', null, true);
    $description .= get_string('backgroundimage_desc_note', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'backgroundimage');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Settings title to group brand color related settings together with a common heading. We don't want a description here.
    $name = 'theme_urcourses_default/brandcolorheading';
    $title = get_string('brandcolorheadingsetting', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);

    // Variable $brand-color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_urcourses_default/brandcolor';
    $title = get_string('brandcolor', 'theme_boost', null, true);
    $description = get_string('brandcolor_desc', 'theme_boost', null, true);
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '#848889');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Variable $brand-succes-color.
    $name = 'theme_urcourses_default/brandsuccesscolor';
    $title = get_string('brandsuccesscolorsetting', 'theme_urcourses_default', null, true);
    $description = get_string('brandsuccesscolorsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Variable $brand-info-color.
    $name = 'theme_urcourses_default/brandinfocolor';
    $title = get_string('brandinfocolorsetting', 'theme_urcourses_default', null, true);
    $description = get_string('brandinfocolorsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Variable $brand-warning-color.
    $name = 'theme_urcourses_default/brandwarningcolor';
    $title = get_string('brandwarningcolorsetting', 'theme_urcourses_default', null, true);
    $description = get_string('brandwarningcolorsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Variable $brand-warning-color.
    $name = 'theme_urcourses_default/branddangercolor';
    $title = get_string('branddangercolorsetting', 'theme_urcourses_default', null, true);
    $description = get_string('branddangercolorsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Settings title to group favicon related settings together with a common heading. We don't want a description here.
    $name = 'theme_urcourses_default/faviconheading';
    $title = get_string('faviconheadingsetting', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);

    // Favicon upload.
    $name = 'theme_urcourses_default/favicon';
    $title = get_string('faviconsetting', 'theme_urcourses_default', null, true);
    $description = get_string('faviconsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'favicon', 0,
        array('maxfiles' => 1, 'accepted_types' => array('.ico', '.png')));
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Add tab to settings page.
    $settings->add($page);


    // Create advanced settings tab.
    $page = new admin_settingpage('theme_urcourses_default_advanced', get_string('advancedsettings', 'theme_boost', null, true));

    // Raw SCSS to include before the content.
    $name = 'theme_urcourses_default/scsspre';
    $title = get_string('rawscsspre', 'theme_boost', null, true);
    $description = get_string('rawscsspre_desc', 'theme_boost', null, true);
    $setting = new admin_setting_configtextarea($name, $title, $description, '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Raw SCSS to include after the content.
    $name = 'theme_urcourses_default/scss';
    $title = get_string('rawscss', 'theme_boost', null, true);
    $description = get_string('rawscss_desc', 'theme_boost', null, true);
    $setting = new admin_setting_configtextarea($name, $title, $description, '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Settings title for the catching keybaord commands.
    $name = 'theme_urcourses_default/catchkeyboardcommandsheading';
    $title = get_string('catchkeyboardcommandsheadingsetting', 'theme_urcourses_default', null, true);
    $description = get_string('catchkeyboardcommandsheadingsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, $description);
    $page->add($setting);

    // Setting for catching the end key.
    $name = 'theme_urcourses_default/catchendkey';
    $title = get_string('catchendkeysetting', 'theme_urcourses_default', null, true);
    $description = get_string('catchendkeysetting_desc', 'theme_urcourses_default', null, true) . ' ' .
        get_string('catchkeys_desc_addition', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Setting for catching the cmd + arrow down keys.
    $name = 'theme_urcourses_default/catchcmdarrowdown';
    $title = get_string('catchcmdarrowdownsetting', 'theme_urcourses_default', null, true);
    $description = get_string('catchcmdarrowdownsetting_desc', 'theme_urcourses_default', null, true) . ' ' .
        get_string('catchkeys_desc_addition', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Setting for catching the strg + arrow down keys.
    $name = 'theme_urcourses_default/catchctrlarrowdown';
    $title = get_string('catchctrlarrowdownsetting', 'theme_urcourses_default', null, true);
    $description = get_string('catchctrlarrowdownsetting_desc', 'theme_urcourses_default', null, true) . ' ' .
        get_string('catchkeys_desc_addition', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Add tab to settings page.
    //$settings->add($page);
    
    // Settings title for the Add a block widget. We don't need a description here.
    $name = 'theme_urcourses_default/addablockwidgetheading';
    $title = get_string('addablockwidgetheadingsetting', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);
    // Setting to manage where the Add a block widget should be displayed.
    $name = 'theme_urcourses_default/addablockposition';
    $title = get_string('addablockpositionsetting', 'theme_urcourses_default', null, true);
    $description = get_string('addablockpositionsetting_desc', 'theme_urcourses_default', null, true);
    $addablockpositionsetting = [
        // Don't use string lazy loading (= false) because the string will be directly used and would produce a
        // PHP warning otherwise.
        'positionblockregion' => get_string('settingsaddablockpositionbottomblockregion', 'theme_urcourses_default', null, false),
        'positionnavdrawer' => get_string('settingsaddablockpositionbottomnavdrawer', 'theme_urcourses_default', null, true),
    ];
    $setting = new admin_setting_configselect($name, $title, $description, $addablockpositionsetting['positionblockregion'],
        $addablockpositionsetting);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Settings title for "Back to top" button. We don't need a description here.
    $name = 'theme_urcourses_default/bcbttbuttonheading';
    $title = get_string('bcbttbuttonheadingsetting', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);

    // Setting enabling the Boost Campus version of the "Back to top" button.
    $name = 'theme_urcourses_default/bcbttbutton';
    $title = get_string('bcbttbuttonsetting', 'theme_urcourses_default', null, true);
    $description = get_string('bcbttbuttonsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $page->add($setting);

    // Add tab to settings page.
    $settings->add($page);


    // Create course layout settings tab.
    $name = 'theme_urcourses_default_courselayout';
    $title = get_string('courselayoutsettings', 'theme_urcourses_default', null, true);
    $page = new admin_settingpage($name, $title);

    // Setting for displaying section-0 title in courses.
    $name = 'theme_urcourses_default/section0title';
    $title = get_string('section0titlesetting', 'theme_urcourses_default', null, true);
    $description = get_string('section0titlesetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 'no', 'yes', 'no'); // Overriding default values
        // yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss() (lib.php). Default 0 value would
        // not write the variable to scss that could cause the scss to crash if used in that file. See MDL-58376.
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Setting for displaying edit on / off button addionally in course header.
    $name = 'theme_urcourses_default/courseeditbutton';
    $title = get_string('courseeditbuttonsetting', 'theme_urcourses_default', null, true);
    $description = get_string('courseeditbuttonsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Settings title for grouping course settings related aspects together. We don't need a description here.
    $name = 'theme_urcourses_default/coursehintsheading';
    $title = get_string('coursehintsheadingsetting', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);

    // Setting to display information of a switched role in the course header.
    $name = 'theme_urcourses_default/showswitchedroleincourse';
    $title = get_string('showswitchedroleincoursesetting', 'theme_urcourses_default', null, true);
    $description = get_string('showswitchedroleincoursesetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 'no', 'yes', 'no'); // Overriding default values
        // yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss() (lib.php).
        // Default 0 value would not write the variable to scss that could cause the scss to crash if used in that file.
        // See MDL-58376.
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Setting to display a hint to the hidden visibility of a course.
    $name = 'theme_urcourses_default/showhintcoursehidden';
    $title = get_string('showhintcoursehiddensetting', 'theme_urcourses_default', null, true);
    $description = get_string('showhintcoursehiddensetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 'no', 'yes', 'no'); // Overriding default values
    // yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss() (lib.php).
    // Default 0 value would not write the variable to scss that could cause the scss to crash if used in that file.
    // See MDL-58376.
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Setting to display a hint to the guest accessing of a course.
    $name = 'theme_urcourses_default/showhintcourseguestaccess';
    $title = get_string('showhintcoursguestaccesssetting', 'theme_urcourses_default', null, true);
    $description = get_string('showhintcourseguestaccesssetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 'no', 'yes', 'no'); // Overriding default values
    // yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss() (lib.php).
    // Default 0 value would not write the variable to scss that could cause the scss to crash if used in that file.
    // See MDL-58376.
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Setting to display a hint that the active course has a unrestricted self enrolment.
    $name = 'theme_urcourses_default/showhintcourseselfenrol';
    $title = get_string('showhintcourseselfenrolsetting', 'theme_urcourses_default', null, true);
    $description = get_string('showhintcourseselfenrolsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 'no', 'yes', 'no'); // Overriding default values
    // yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss() (lib.php).
    // Default 0 value would not write the variable to scss that could cause the scss to crash if used in that file.
    // See MDL-58376.
    $page->add($setting);

    // Settings title for grouping course settings related aspects together. We don't need a description here.
    $name = 'theme_urcourses_default/coursesettingsheading';
    $title = get_string('coursesettingsheadingsetting', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);

    // Setting to display the course settings page as a panel within the course.
    $name = 'theme_urcourses_default/showsettingsincourse';
    $title = get_string('showsettingsincoursesetting', 'theme_urcourses_default', null, true);
    $description = get_string('showsettingsincoursesetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 'no', 'yes', 'no'); // Overriding default values
    // yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss() (lib.php).
    // Default 0 value would not write the variable to scss that could cause the scss to crash if used in that file.
    // See MDL-58376.
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Setting to display the switch role to link as a separate tab within the in-course settings panel.
    $name = 'theme_urcourses_default/incoursesettingsswitchtoroleposition';
    $title = get_string('incoursesettingsswitchtorolepositionsetting', 'theme_urcourses_default', null, true);
    $description = get_string('incoursesettingsswitchtorolepositionsetting_desc', 'theme_urcourses_default', null, true);
    $incoursesettingsswitchtorolesetting = [
     // Don't use string lazy loading (= false) because the string will be directly used and would produce a PHP warning otherwise.
    'no' => get_string('incoursesettingsswitchtorolesettingjustmenu', 'theme_urcourses_default', null, false),
    'yes' => get_string('incoursesettingsswitchtorolesettingjustcourse', 'theme_urcourses_default', null, true),
    'both' => get_string('incoursesettingsswitchtorolesettingboth', 'theme_urcourses_default', null, true)
    ];
    $setting = new admin_setting_configselect($name, $title, $description, $incoursesettingsswitchtorolesetting['no'],
        $incoursesettingsswitchtorolesetting);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    $settings->hide_if('theme_urcourses_default/incoursesettingsswitchtoroleposition',
            'theme_urcourses_default/showsettingsincourse', 'notchecked');

    // Add tab to settings page.
    $settings->add($page);


    // Create footer layout settings tab.
    $name = 'theme_urcourses_default_footerlayout';
    $title = get_string('footerlayoutsettings', 'theme_urcourses_default', null, true);
    $page = new admin_settingpage($name, $title);

    // Settings title for the footer blocks. We don't need a description here.
    $name = 'theme_urcourses_default/footerblocksheading';
    $title = get_string('footerblocksheadingsetting', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);

    // Setting for enabling blocks with different layouts in the footer.
    $name = 'theme_urcourses_default/footerblocks';
    $title = get_string('footerblockssetting', 'theme_urcourses_default', null, true);
    $description = get_string('footerblockssetting_desc', 'theme_urcourses_default', null, true);
    $footerlayoutoptions = [
     // Don't use string lazy loading (= false) because the string will be directly used and would produce a PHP warning otherwise.
    '0columns' => get_string('footerblocks0columnssetting', 'theme_urcourses_default', null, false),
    '1columns' => get_string('footerblocks1columnssetting', 'theme_urcourses_default', null, true),
    '2columns' => get_string('footerblocks2columnssetting', 'theme_urcourses_default', null, true),
    '3columns' => get_string('footerblocks3columnssetting', 'theme_urcourses_default', null, true)
    ];
    $setting = new admin_setting_configselect($name, $title, $description, $footerlayoutoptions['0columns'], $footerlayoutoptions);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Settings title to group the settings footerhelplink, footerlogininfo and footerhomelink together with a common description.
    $name = 'theme_urcourses_default/footerlinksheading';
    $title = get_string('footerlinksheadingsetting', 'theme_urcourses_default', null, true);
    $description = get_string('footerlinksheadingsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, $description);
    $page->add($setting);

    // Helplink.
    $name = 'theme_urcourses_default/footerhidehelplink';
    $title = get_string('footerhidehelplinksetting', 'theme_urcourses_default', null, true);
    $description = get_string('footerlinks_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 'no', 'yes', 'no' ); // Overriding default values
        // yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss() (lib.php). Default 0 value would
        // not write the variable to scss that could cause the scss to crash if used in that file. See MDL-58376.
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Logininfo.
    $name = 'theme_urcourses_default/footerhidelogininfo';
    $title = get_string('footerhidelogininfosetting', 'theme_urcourses_default', null, true);
    $description = get_string('footerlinks_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 'no', 'yes', 'no' ); // Overriding default values
        // yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss() (lib.php). Default 0 value would
        // not write the variable to scss that could cause the scss to crash if used in that file. See MDL-58376.
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Homelink.
    $name = 'theme_urcourses_default/footerhidehomelink';
    $title = get_string('footerhidehomelinksetting', 'theme_urcourses_default', null, true);
    $description = get_string('footerlinks_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 'no', 'yes', 'no' ); // Overriding default values
        // yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss() (lib.php). Default 0 value would
        // not write the variable to scss that could cause the scss to crash if used in that file. See MDL-58376.
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // User tours.
    $name = 'theme_urcourses_default/footerhideusertourslink';
    $title = get_string('footerhideusertourslinksetting', 'theme_urcourses_default', null, true);
    $description = get_string('footerlinks_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 'no', 'yes', 'no' ); // Overriding default values
    // yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss() (lib.php). Default 0 value would
    // not write the variable to scss that could cause the scss to crash if used in that file. See MDL-58376.
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Settings title for hiding the footer. We don't need a description here.
    $name = 'theme_urcourses_default/hidefooterheading';
    $title = get_string('hidefooterheadingsetting', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);

    // Hide the footer on the login page.
    $name = 'theme_urcourses_default/hidefooteronloginpage';
    $title = get_string('hidefooteronloginpagesetting', 'theme_urcourses_default', null, true);
    $description = get_string('hidefooteronloginpagesetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 'no', 'yes', 'no' ); // Overriding default values
    // yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss() (lib.php). Default 0 value would
    // not write the variable to scss that could cause the scss to crash if used in that file. See MDL-58376.
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Add tab to settings page.
    $settings->add($page);


    // Create additional layout settings tab.
    $name = 'theme_urcourses_default_additionallayout';
    $title = get_string('additionallayoutsettings', 'theme_urcourses_default', null, true);
    $page = new admin_settingpage($name, $title);

    // Settings title to group image area settings together with a common heading and description.
    $name = 'theme_urcourses_default/imageareaheading';
    $title = get_string('imageareaheadingsetting', 'theme_urcourses_default', null, true);
    $description = get_string('imageareaheadingsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, $description);
    $page->add($setting);

    // Image area setting.
    $name = 'theme_urcourses_default/imageareaitems';
    $title = get_string('imageareaitemssetting', 'theme_urcourses_default', null, true);
    $description = get_string('imageareaitemssetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'imageareaitems', 0, array('maxfiles' => 100,
        'accepted_types' => array('web_image'), 'subdirs' => 0));
    $setting->set_updatedcallback('theme_urcourses_default_reset_app_cache');
    $page->add($setting);

    $name = 'theme_urcourses_default/imageareaitemslink';
    $title = get_string('imageareaitemslinksetting', 'theme_urcourses_default', null, true);
    $description = get_string('imageareaitemslinksetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configtextarea($name, $title, $description, null, PARAM_TEXT);
    $setting->set_updatedcallback('theme_urcourses_default_reset_app_cache');
    $page->add($setting);

    $name = 'theme_urcourses_default/imageareaitemsmaxheight';
    $title = get_string('imageareaitemsmaxheightsetting', 'theme_urcourses_default', null, true);
    $description = get_string('imageareaitemsmaxheightsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configtext_with_maxlength($name, $title, $description, 100, PARAM_INT, null, 3);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Settings title to group footnote settings together with a common heading and description.
    $name = 'theme_urcourses_default/footnoteheading';
    $title = get_string('footnoteheadingsetting', 'theme_urcourses_default', null, true);
    $description = get_string('footnoteheadingsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, $description);
    $page->add($setting);

    // Footnote setting.
    $name = 'theme_urcourses_default/footnote';
    $title = get_string('footnotesetting', 'theme_urcourses_default', null, true);
    $description = get_string('footnotesetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_confightmleditor($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Settings title to group navdrawer related settings together with a common heading. We don't want a description here.
    $setting = new admin_setting_heading('theme_urcourses_default/navdrawerheading',
        get_string('navdrawerheadingsetting', 'theme_urcourses_default', null, true), null);
    $page->add($setting);

    // Create default homepage on top control widget
    // (switch label and description depending on what will really happens on the site).
    if (get_config('core', 'defaulthomepage') == HOMEPAGE_SITE) {
        $page->add(new admin_setting_configcheckbox('theme_urcourses_default/defaulthomepageontop',
            get_string('sitehomeontopsetting', 'theme_urcourses_default', null, true),
            get_string('sitehomeontopsetting_desc', 'theme_urcourses_default', null, true), 'no', 'yes', 'no'));
            // Overriding default values yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss()
            // (lib.php). Default 0 value would not write the variable to scss that could cause the scss to crash if used in
            // that file. See MDL-58376.
    } else if (get_config('core', 'defaulthomepage') == HOMEPAGE_MY) {
        $page->add(new admin_setting_configcheckbox('theme_urcourses_default/defaulthomepageontop',
            get_string('dashboardontopsetting', 'theme_urcourses_default', null, true),
            get_string('dashboardontopsetting_desc', 'theme_urcourses_default', null, true), 'no', 'yes', 'no'));
            // Overriding default values yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss()
            // (lib.php). Default 0 value would not write the variable to scss that could cause the scss to crash if used in
            // that file. See MDL-58376.
    } else if (get_config('core', 'defaulthomepage') == HOMEPAGE_USER) {
        $page->add(new admin_setting_configcheckbox('theme_urcourses_default/defaulthomepageontop',
            get_string('userdefinedontopsetting', 'theme_urcourses_default', null, true),
            get_string('userdefinedontopsetting_desc', 'theme_urcourses_default', null, true), 'no', 'yes', 'no'));
            // Overriding default values yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss()
            // (lib.php). Default 0 value would not write the variable to scss that could cause the scss to crash if used in
            // that file. See MDL-58376.
    } else { // This should not happen.
        $page->add(new admin_setting_configcheckbox('theme_urcourses_default/defaulthomepageontop',
            get_string('defaulthomepageontopsetting', 'theme_urcourses_default', null, true),
            get_string('defaulthomepageontopsetting_desc', 'theme_urcourses_default', null, true), 'no', 'yes', 'no'));
            // Overriding default values yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss()
            // (lib.php). Default 0 value would not write the variable to scss that could cause the scss to crash if used in
            // that file. See MDL-58376.
    }
    $page->add($setting);

    // Set navdrawer to full width on small screens when opened.
    $name = 'theme_urcourses_default/navdrawerfullwidth';
    $title = get_string('navdrawerfullwidthsetting', 'theme_urcourses_default', null, true);
    $description = get_string('navdrawerfullwidthsettings_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 'no', 'yes', 'no' ); // Overriding default
    // values yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss() (lib.php). Default 0 value
    // would not write the variable to scss that could cause the scss to crash if used in that file. See MDL-58376.
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Add tab to settings page.
    $settings->add($page);


     // Create design settings tab.
    $page = new admin_settingpage('theme_urcourses_default_design', get_string('designsettings', 'theme_urcourses_default', null, true));

    // Settings title to group login page related settings together with a common heading. We don't want a description here.
    $name = 'theme_urcourses_default/loginpagedesignheading';
    $title = get_string('loginpagedesignheadingsetting', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);

    // Login page background setting.
    $name = 'theme_urcourses_default/loginbackgroundimage';
    $title = get_string('loginbackgroundimagesetting', 'theme_urcourses_default', null, true);
    $description = get_string('loginbackgroundimagesetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'loginbackgroundimage', 0,
        array('maxfiles' => 10, 'accepted_types' => 'web_image'));
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_urcourses_default/loginbackgroundimagetext';
    $title = get_string('loginbackgroundimagetextsetting', 'theme_urcourses_default', null, true);
    $description = get_string('loginbackgroundimagetextsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configtextarea($name, $title, $description, null, PARAM_TEXT);
    $page->add($setting);

    // Setting to change the position and design of the login form.
    $name = 'theme_urcourses_default/loginform';
    $title = get_string('loginform', 'theme_urcourses_default', null, true);
    $description = get_string('loginform_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 'no', 'yes', 'no'); // Overriding default values
        // yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss() (lib.php). Default 0 value would
        // not write the variable to scss that could cause the scss to crash if used in that file. See MDL-58376.
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Settings title to group font related settings together with a common heading. We don't want a description here.
    $name = 'theme_urcourses_default/fontdesignheading';
    $title = get_string('fontdesignheadingsetting', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);

    // Font files upload.
    $name = 'theme_urcourses_default/fontfiles';
    $title = get_string('fontfilessetting', 'theme_urcourses_default', null, true);
    $description = get_string('fontfilessetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'fontfiles', 0,
            array('maxfiles' => 100, 'accepted_types' => array('.ttf', '.eot', '.woff', '.woff2')));
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Settings title to group block related settings together with a common heading. We don't want a description here.
    $name = 'theme_urcourses_default/blockdesignheading';
    $title = get_string('blockdesignheadingsetting', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);

    // Setting for displaying a standard Font Awesome icon in front of the block title.
    $name = 'theme_urcourses_default/blockicon';
    $title = get_string('blockiconsetting', 'theme_urcourses_default', null, true);
    $description = get_string('blockiconsetting_desc', 'theme_urcourses_default', null, true) .
        get_string('blockiconsetting_desc_code', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 'no', 'yes', 'no'); // Overriding default values
        // yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss() (lib.php). Default 0 value would
        // not write the variable to scss that could cause the scss to crash if used in that file. See MDL-58376.
        $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Setting for the width of the block column on the Dashboard.
    $name = 'theme_urcourses_default/blockcolumnwidthdashboard';
    $title = get_string('blockcolumnwidthdashboardsetting', 'theme_urcourses_default', null, true);
    $description = get_string('blockcolumnwidthdashboardsetting_desc', 'theme_urcourses_default', null, true).' '.
            get_string('blockcolumnwidthdefault', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configtext_with_maxlength($name, $title, $description, 360, PARAM_INT, null, 3);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Setting for the width of the block column on all other pages.
    $name = 'theme_urcourses_default/blockcolumnwidth';
    $title = get_string('blockcolumnwidthsetting', 'theme_urcourses_default', null, true);
    $description = get_string('blockcolumnwidthsetting_desc', 'theme_urcourses_default', null, true).' '.
            get_string('blockcolumnwidthdefault', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configtext_with_maxlength($name, $title, $description, 360, PARAM_INT, null, 3);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Settings title to group navbar related settings together with a common heading. We don't want a description here.
    $name = 'theme_urcourses_default/navbardesignheading';
    $title = get_string('navbardesignheadingsetting', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);

    $name = 'theme_urcourses_default/darknavbar';
    $title = get_string('darknavbarsetting', 'theme_urcourses_default', null, true);
    $description = get_string('darknavbarsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 'no', 'yes', 'no' ); // Overriding default values
    // yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss() (lib.php). Default 0 value would
    // not write the variable to scss that could cause the scss to crash if used in that file. See MDL-58376.
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Settings title to group navbar related settings together with a common heading. We don't want a description here.
    $name = 'theme_urcourses_default/helptextheading';
    $title = get_string('helptextheadingsetting', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);

    $name = 'theme_urcourses_default/helptextmodal';
    $title = get_string('helptextmodalsetting', 'theme_urcourses_default', null, true);
    $description = get_string('helptextmodalsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 'no', 'yes', 'no' ); // Overriding default values
    // yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss() (lib.php). Default 0 value would
    // not write the variable to scss that could cause the scss to crash if used in that file. See MDL-58376.
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Settings title to group breakpoint related settings together with a common heading. We don't want a description here.
    $name = 'theme_urcourses_default/breakpointheading';
    $title = get_string('breakpointheadingsetting', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);

    $name = 'theme_urcourses_default/breakpoint';
    $title = get_string('breakpointsetting', 'theme_urcourses_default', null, true);
    $description = get_string('breakpointsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 'no', 'yes', 'no' ); // Overriding default values
    // yes = 1 and no = 0 because of the use of empty() in theme_urcourses_default_get_pre_scss() (lib.php). Default 0 value would
    // not write the variable to scss that could cause the scss to crash if used in that file. See MDL-58376.
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Settings title to group additional resources settings together with a common heading. We don't want a description here.
    $name = 'theme_urcourses_default/additionalresourcesheading';
    $title = get_string('additionalresourcesheadingsetting', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);

    // Background image setting.
    $name = 'theme_urcourses_default/additionalresources';
    $title = get_string('additionalresourcessetting', 'theme_urcourses_default', null, true);
    $description = get_string('additionalresourcessetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'additionalresources', 0,
        array('maxfiles' => -1));
    $page->add($setting);

    // Add tab to settings page.
    $settings->add($page);

    // Create info banner settings tab.
    $page = new admin_settingpage('theme_urcourses_default_infobanner', get_string('infobannersettings',
            'theme_urcourses_default', null, true));

    // Settings title to group perpetual information banner settings together with a common heading and description.
    $name = 'theme_urcourses_default/perpetualinfobannerheading';
    $title = get_string('perpetualinfobannerheadingsetting', 'theme_urcourses_default', null, true);
    $description = get_string('perpetualinfobannerheadingsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, $description);
    $page->add($setting);

    // Activate perpetual information banner.
    $name = 'theme_urcourses_default/perpibenable';
    $title = get_string('perpibenablesetting', 'theme_urcourses_default', null, true);
    $description = get_string('perpibenablesetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $page->add($setting);

    // Perpetual information banner content.
    $name = 'theme_urcourses_default/perpibcontent';
    $title = get_string('perpibcontent', 'theme_urcourses_default', null, true);
    $description = get_string('perpibcontent_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_confightmleditor($name, $title, $description, '');
    $page->add($setting);
    $settings->hide_if('theme_urcourses_default/perpibcontent',
            'theme_urcourses_default/perpibenable', 'notchecked');

    // Select pages on which the perpetual information banner should be shown.
    $name = 'theme_urcourses_default/perpibshowonpages';
    $title = get_string('perpibshowonpagessetting', 'theme_urcourses_default', null, true);
    $description = get_string('perpibshowonpagessetting_desc', 'theme_urcourses_default', null, true);
    $perpibshowonpageoptions = [
            // Don't use string lazy loading (= false) because the string will be directly used and would produce a
            // PHP warning otherwise.
            'mydashboard' => get_string('myhome', 'core', null, false),
            'course' => get_string('course', 'core', null, false),
            'login' => get_string('login_page', 'theme_urcourses_default', null, false)
    ];
    $setting = new admin_setting_configmultiselect($name, $title, $description,
            array($perpibshowonpageoptions['mydashboard']), $perpibshowonpageoptions);
    $page->add($setting);
    $settings->hide_if('theme_urcourses_default/perpibshowonpages',
            'theme_urcourses_default/perpibenable', 'notchecked');

    // Select the bootstrap class that should be used for the perpetual info banner.
    $name = 'theme_urcourses_default/perpibcss';
    $title = get_string('perpibcsssetting', 'theme_urcourses_default', null, true);
    $description = get_string('perpibcsssetting_desc', 'theme_urcourses_default', null, true).'<br />'.
            get_string('ibcsssetting_nobootstrap', 'theme_urcourses_default',
                   array('bootstrapnone' => get_string('bootstrapnone', 'theme_urcourses_default')));
    $perpibcssoptions = [
            // Don't use string lazy loading (= false) because the string will be directly used and would produce a
            // PHP warning otherwise.
            'primary' => get_string('bootstrapprimarycolor', 'theme_urcourses_default', null, false),
            'secondary' => get_string('bootstrapsecondarycolor', 'theme_urcourses_default', null, false),
            'success' => get_string('bootstrapsuccesscolor', 'theme_urcourses_default', null, false),
            'danger' => get_string('bootstrapdangercolor', 'theme_urcourses_default', null, false),
            'warning' => get_string('bootstrapwarningcolor', 'theme_urcourses_default', null, false),
            'info' => get_string('bootstrapinfocolor', 'theme_urcourses_default', null, false),
            'light' => get_string('bootstraplightcolor', 'theme_urcourses_default', null, false),
            'dark' => get_string('bootstrapdarkcolor', 'theme_urcourses_default', null, false),
            'none' => get_string('bootstrapnone', 'theme_urcourses_default', null, false)
    ];
    $setting = new admin_setting_configselect($name, $title, $description, $perpibcssoptions['primary'],
            $perpibcssoptions);
    $page->add($setting);
    $settings->hide_if('theme_urcourses_default/perpibcss',
            'theme_urcourses_default/perpibenable', 'notchecked');

    // Perpetual information banner dismissible.
    $name = 'theme_urcourses_default/perpibdismiss';
    $title = get_string('perpibdismisssetting', 'theme_urcourses_default', null, true);
    $description = get_string('perpibdismisssetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $page->add($setting);
    $settings->hide_if('theme_urcourses_default/perpibdismiss',
            'theme_urcourses_default/perpibenable', 'notchecked');

    // Perpetual information banner show confirmation dialogue when dismissing.
    $name = 'theme_urcourses_default/perpibconfirm';
    $title = get_string('perpibconfirmsetting', 'theme_urcourses_default', null, true);
    $description = get_string('perpibconfirmsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $page->add($setting);
    $settings->hide_if('theme_urcourses_default/perpibconfirm',
            'theme_urcourses_default/perpibenable', 'notchecked');
    $settings->hide_if('theme_urcourses_default/perpibconfirm',
            'theme_urcourses_default/perpibdismiss', 'notchecked');

    // Reset the user preference for all users.
    $name = 'theme_urcourses_default/perpibresetvisibility';
    $title = get_string('perpetualinfobannerresetvisiblitysetting', 'theme_urcourses_default', null, true);
    $description = get_string('perpetualinfobannerresetvisiblitysetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_urcourses_default_infobanner_reset_visibility');
    $page->add($setting);
    $settings->hide_if('theme_urcourses_default/perpibresetvisibility',
            'theme_urcourses_default/perpibenable', 'notchecked');
    $settings->hide_if('theme_urcourses_default/perpibresetvisibility',
            'theme_urcourses_default/perpibdismiss', 'notchecked');

    // Settings title to group time controlled information banner settings together with a common heading and description.
    $name = 'theme_urcourses_default/timedinfobannerheading';
    $title = get_string('timedinfobannerheadingsetting', 'theme_urcourses_default', null, true);
    $description = get_string('timedinfobannerheadingsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_heading($name, $title, $description);
    $page->add($setting);

    // Activate time controlled information banner.
    $name = 'theme_urcourses_default/timedibenable';
    $title = get_string('timedibenablesetting', 'theme_urcourses_default', null, true);
    $description = get_string('timedibenablesetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $page->add($setting);

    // Time controlled information banner content.
    $name = 'theme_urcourses_default/timedibcontent';
    $title = get_string('timedibcontent', 'theme_urcourses_default', null, true);
    $description = get_string('timedibcontent_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_confightmleditor($name, $title, $description, '');
    $page->add($setting);
    $settings->hide_if('theme_urcourses_default/timedibcontent',
            'theme_urcourses_default/timedibenable', 'notchecked');

    // Select pages on which the time controlled information banner should be shown.
    $name = 'theme_urcourses_default/timedibshowonpages';
    $title = get_string('timedibshowonpagessetting', 'theme_urcourses_default', null, true);
    $description = get_string('timedibshowonpagessetting_desc', 'theme_urcourses_default', null, true);
    $timedibpageoptions = [
        // Don't use string lazy loading (= false) because the string will be directly used and would produce a
        // PHP warning otherwise.
            'mydashboard' => get_string('myhome', 'core', null, false),
            'course' => get_string('course', 'core', null, false),
            'login' => get_string('login_page', 'theme_urcourses_default', null, false)
    ];
    $setting = new admin_setting_configmultiselect($name, $title, $description,
            array($timedibpageoptions['mydashboard']), $timedibpageoptions);
    $page->add($setting);
    $settings->hide_if('theme_urcourses_default/timedibshowonpages',
            'theme_urcourses_default/timedibenable', 'notchecked');

    // Select the bootstrap class that should be used for the perpetual info banner.
    $name = 'theme_urcourses_default/timedibcss';
    $title = get_string('timedibcsssetting', 'theme_urcourses_default', null, true);
    $description = get_string('timedibcsssetting_desc', 'theme_urcourses_default', null, true).'<br />'.
            get_string('ibcsssetting_nobootstrap', 'theme_urcourses_default',
                    array('bootstrapnone' => get_string('bootstrapnone', 'theme_urcourses_default')));
    $timedibcssoptions = [
        // Don't use string lazy loading (= false) because the string will be directly used and would produce a
        // PHP warning otherwise.
            'primary' => get_string('bootstrapprimarycolor', 'theme_urcourses_default', null, false),
            'secondary' => get_string('bootstrapsecondarycolor', 'theme_urcourses_default', null, false),
            'success' => get_string('bootstrapsuccesscolor', 'theme_urcourses_default', null, false),
            'danger' => get_string('bootstrapdangercolor', 'theme_urcourses_default', null, false),
            'warning' => get_string('bootstrapwarningcolor', 'theme_urcourses_default', null, false),
            'info' => get_string('bootstrapinfocolor', 'theme_urcourses_default', null, false),
            'light' => get_string('bootstraplightcolor', 'theme_urcourses_default', null, false),
            'dark' => get_string('bootstrapdarkcolor', 'theme_urcourses_default', null, false),
            'none' => get_string('bootstrapnone', 'theme_urcourses_default', null, false)
    ];
    $setting = new admin_setting_configselect($name, $title, $description, $timedibcssoptions['primary'],
            $timedibcssoptions);
    $page->add($setting);
    $settings->hide_if('theme_urcourses_default/timedibcss',
            'theme_urcourses_default/timedibenable', 'notchecked');

    // This will check for the desired date time format YYYY-MM-DD HH:MM:SS.
    $timeregex = '/(20[0-9]{2}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s([0-1][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9])|^$/';

    // Start time for controlled information banner.
    $name = 'theme_urcourses_default/timedibstart';
    $title = get_string('timedibstartsetting', 'theme_urcourses_default', null, true);
    $description = get_string('timedibstartsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configtext($name, $title, $description, '', $timeregex);
    $page->add($setting);
    $settings->hide_if('theme_urcourses_default/timedibstart',
            'theme_urcourses_default/timedibenable', 'notchecked');

    // End time for controlled information banner.
    $name = 'theme_urcourses_default/timedibend';
    $title = get_string('timedibendsetting', 'theme_urcourses_default', null, true);
    $description = get_string('timedibendsetting_desc', 'theme_urcourses_default', null, true);
    $setting = new admin_setting_configtext($name, $title, $description, '', $timeregex);
    $page->add($setting);
    $settings->hide_if('theme_urcourses_default/timedibend',
            'theme_urcourses_default/timedibenable', 'notchecked');

    // Add tab to settings page.
    $settings->add($page);
}
