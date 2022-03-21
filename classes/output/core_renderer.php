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
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package   theme_urcourses_default
 * @copyright 2017 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 *            copyright based on code from theme_boost by Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_urcourses_default\output;

use coding_exception;
use core\plugininfo\enrol;
use html_writer;
use tabobject;
use tabtree;
use context_system;
use custom_menu_item;
use custom_menu;
use block_contents;
use navigation_node;
use action_link;
use stdClass;
use moodle_url;
use preferences_groups;
use action_menu;
use help_icon;
use single_button;
use single_select;
use paging_bar;
use url_select;
use context_course;
use pix_icon;
use user_picture;
use action_menu_filler;
use action_menu_link_secondary;
use core_text;



use \core_course\external\course_summary_exporter;

defined('MOODLE_INTERNAL') || die;


/**
 * Extending the core_renderer interface.
 *
 * @copyright 2017 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package theme_urcourses_default
 * @category output
 */
class core_renderer extends \theme_boost\output\core_renderer {

    /**
     * Get the compact logo URL.
     *
     * @return string
     */
    public function get_compact_logosmall_url($maxwidth = 100, $maxheight = 100) {
        global $OUTPUT;
        return $OUTPUT->image_url('logosmall', 'theme_urcourses_default');
    }

    public function get_compact_logo_url($maxwidth = 100, $maxheight = 100){
        global $OUTPUT;
        return $OUTPUT->image_url('logo', 'theme_urcourses_default');
    }

    /**
     * get login bg img url 
     * */ 
    public function get_login_bg_url() {
        global $CFG;
        $directory =  $CFG->dirroot . '/theme/urcourses_default/pix/login_backgrounds/';
        $bgfile = '';
        $files = [];
        $files = glob($directory . "*");
        $bgfiles = count($files);
        if ($bgfiles != 0){

            if ($bgfiles > 2 ){
                //assumes you have paired a low res file with a high res file
                $bgfiles = $bgfiles/2;
                $filenum = mt_rand(1,$bgfiles);
            }
            else {
              $filenum = 1;
            }
            
            $bgfile = 'login_backgrounds/loginlow' . $filenum;
        }

        global $OUTPUT;
        return $OUTPUT->image_url($bgfile, 'theme_urcourses_default');
    }


    /**
     * Override to display an edit button again by calling the parent function
     * in core/core_renderer because theme_boost's function returns an empty
     * string and therefore displays nothing.
     * @param moodle_url $url The current course url.
     * @return \core_renderer::edit_button Moodle edit button.
     */
    public function edit_button(moodle_url $url) {
        // MODIFICATION START.
        // If setting editbuttonincourseheader ist checked give out the edit on / off button in course header.
        if (get_config('theme_urcourses_default', 'courseeditbutton') == '1') {
            return \core_renderer::edit_button($url);
        }
        // MODIFICATION END.
        /* ORIGINAL START.
        return '';
        ORIGINAL END. */
    }

    /**
     * Override to add additional class for the random login image to the body.
     *
     * Returns HTML attributes to use within the body tag. This includes an ID and classes.
     *
     * KIZ MODIFICATION: This renderer function is copied and modified from /lib/outputrenderers.php
     *
     * @since Moodle 2.5.1 2.6
     * @param string|array $additionalclasses Any additional classes to give the body tag,
     * @return string
     */
    public function body_attributes($additionalclasses = array()) {
        global $CFG;
        require_once($CFG->dirroot . '/theme/urcourses_default/locallib.php');

        if (!is_array($additionalclasses)) {
            $additionalclasses = explode(' ', $additionalclasses);
        }

        // MODIFICATION START.
        // Only add classes for the login page.
        if ($this->page->bodyid == 'page-login-index') {
            $additionalclasses[] = 'loginbackgroundimage';
            // Generating a random class for displaying a random image for the login page.
            $additionalclasses[] = theme_urcourses_default_get_random_loginbackgroundimage_class();
        }
        // MODIFICATION END.

        return ' id="'. $this->body_id().'" class="'.$this->body_css_classes($additionalclasses).'"';
    }

    /**
     * Override to be able to use uploaded images from admin_setting as well.
     *
     * Returns the moodle_url for the favicon.
     *
     * KIZ MODIFICATION: This renderer function is copied and modified from /lib/outputrenderers.php
     *
     * @since Moodle 2.5.1 2.6
     * @return moodle_url The moodle_url for the favicon
     */
    public function favicon() {
        // MODIFICATION START.
        if (!empty($this->page->theme->settings->favicon)) {
            return $this->page->theme->setting_file_url('favicon', 'favicon');
        } else {
            return $this->image_url('favicon', 'theme');
        }
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START.
        return $this->image_url('favicon', 'theme');
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd
    }

    /**
     * Override to display switched role information beneath the course header instead of the user menu.
     * We change this because the switch role function is course related and therefore it should be placed in the course context.
     *
     * MODIFICATION: This renderer function is copied and modified from /lib/outputrenderers.php
     *
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {
        // MODIFICATION START.
        global $USER, $COURSE, $CFG, $DB;
        // MODIFICATION END.

        if ($this->page->include_region_main_settings_in_header_actions() && !$this->page->blocks->is_block_present('settings')) {
            // Only include the region main settings if the page has requested it and it doesn't already have
            // the settings block on it. The region main settings are included in the settings block and
            // duplicating the content causes behat failures.
            $this->page->add_header_action(html_writer::div(
                    $this->region_main_settings_menu(),
                    'd-print-none',
                    ['id' => 'region-main-settings-menu']
            ));
        }

        $header = new stdClass();
        // MODIFICATION START.
        // Show the context header settings menu on all pages except for the profile page as we replace
        // it with an edit button there and if we are not on the content bank view page (contentbank/view.php)
        // as this page only adds header actions.
        if ($this->page->pagelayout != 'mypublic' && $this->page->bodyid != 'page-contentbank') {
            $header->settingsmenu = $this->context_header_settings_menu();
        }
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START
        $header->settingsmenu = $this->context_header_settings_menu();
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd
        // Boost Campus
        /*
         $header->contextheader = $this->context_header();
         $header->hasnavbar = empty($this->page->layout_options['nonavbar']);
         */
        $sitecontextheader = '<div class="page-context-header"><div class="page-header-headings"><h1>'.$COURSE->fullname.'</h1></div></div>';

        $headertext = (!empty($this->context_header())) ? $this->context_header() : $sitecontextheader;

        //Little hack to add back missing header for dashboard
        //The context header the comes through is not formated properly
        if($this->page->pagelayout=="mydashboard"){
            $headertext = $sitecontextheader;
        }
		
		//if($this->page->pagelayout=="mydashboard"){
			// declaration hack
			$header->declaration_notice = $this->check_declaration_notice();
			//}
		
        $header->contextheader = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'">'.$headertext.'</a>';  

        // JL EDIT: If user is editing a course, overwrite $header->contextheader with inplace editable
        if ($this->page->user_is_editing() && $COURSE->id !== SITEID) {
            $can_edit_name = has_capability('moodle/course:changefullname', context_course::instance($COURSE->id));
            $course_link = '<h1 class="d-inline"><a href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'">'.$COURSE->fullname.'</a></h1>';
            $course_editname_template = new \core\output\inplace_editable('theme_urcourses_default', 'coursename', $COURSE->id, $can_edit_name, $course_link, format_string($COURSE->fullname));
            $course_editname_html = $this->render($course_editname_template);
            $header->contextheader = $course_editname_html;
        }

        $header->hasnavbar = empty($this->page->layout_options['nonavbar']);
        $header->navbar = $this->navbar();
        
        // TODO: Show notice if course is hidden AND editing is turned on OR on course edit page
        $header->toggle_course_availability = $this->get_course_toggle_availability();

        // MODIFICATION START.
        // Show the page heading button on all pages except for the profile page.
        // There we replace it with an edit profile button.
        if ($this->page->pagelayout != 'mypublic') {
            $header->pageheadingbutton = $this->page_heading_button();
        } else {
            // Get the id of the user for whom the profile page is shown.
            $userid = optional_param('id', $USER->id, PARAM_INT);
            // Check if the shown and the operating user are identical.
            $currentuser = $USER->id == $userid;
            if (($currentuser || is_siteadmin($USER)) &&
                has_capability('moodle/user:update', \context_system::instance())) {
                $url = new moodle_url('/user/editadvanced.php', array('id'       => $userid, 'course' => $COURSE->id,
                                                                      'returnto' => 'profile'));
                $header->pageheadingbutton = $this->single_button($url, get_string('editmyprofile', 'core'));
            } else if ((has_capability('moodle/user:editprofile', \context_user::instance($userid)) &&
                    !is_siteadmin($USER)) || ($currentuser &&
                    has_capability('moodle/user:editownprofile', \context_system::instance()))) {
                $url = new moodle_url('/user/edit.php', array('id'       => $userid, 'course' => $COURSE->id,
                                                              'returnto' => 'profile'));
                $header->pageheadingbutton = $this->single_button($url, get_string('editmyprofile', 'core'));
            }
        }
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START
        $header->pageheadingbutton = $this->page_heading_button();
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd
        $header->courseheader = $this->course_header();
        $header->headeractions = $this->page->get_header_actions();
        
        $header->instructors = $this->course_authornames();
        $instnum = substr_count($this->course_authornames(), 'href');
        if ($instnum > 2) {
            $header->instructnum = "largelist"; 
        }
        else  $header->instructnum = "smalllist"; 
        
        $hascoursecat = $this->ur_check_course_cat();
        
        $coursecat = (!empty($hascoursecat)) ? $hascoursecat['name'] : '';
        $header->facultydep = $coursecat;
        
        // JL EDIT - Add course image uploader button.
        // This button should only appear if user is editing.
        // If we are on the main page of a course, add the cover image selector (COPIED FROM SNAP).
        if ($COURSE->id !== SITEID) {
            if (strpos($this->page->url, '/course/view.php')) {
                $header->course_image_uploader = $this->get_course_image_uploader();
                $header->course_header_style = $this->get_course_header_style();
            }
            
            //check if course has alternate header style in database
            if($record = $DB->get_record('theme_urcourses_hdrstyle', array('courseid'=>$COURSE->id, 'hdrstyle'=>1))){
                $headerstyle = 1;
            } else {
                $headerstyle = 0;   
            }
            
            
        } else $headerstyle = 1;
        
        $header->headerstyle = $headerstyle;
        
        $context = \context_course::instance($COURSE->id);
        
        $urenderer = $this->page->get_renderer('core');
        $exporter = new course_summary_exporter($COURSE, ['context' => $context]);
        $cobits = $exporter->export($urenderer);
        
        $header->courseimage = $cobits->courseimage;
        if ($COURSE->id == 1) $header->courseimage = $CFG->wwwroot.'/theme/urcourses_default/pix/siteheader.jpg';
        
        // modal_help edit
        $header->context_id = $this->page->context->id;
        $header->local_url = $this->page->url->out_as_local_url();
        
        // MODIFICATION START:
        // Change this to add the result in the html variable to be able to add further features below the header.
        // Render from the own header template if we are not on the content bank view page (contentbank/view.php).
        if ($this->page->bodyid == 'page-contentbank') {
            $html = $this->render_from_template('core/full_header', $header);
        } else {
            $html = $this->render_from_template('theme_urcourses_default/full_header', $header);
        }
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START
        return $this->render_from_template('core/full_header', $header);
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd

        // MODIFICATION START:
        // If the setting showhintcoursehidden is set, the visibility of the course is hidden and
        // a hint for the visibility will be shown.
        if (get_config('theme_urcourses_default', 'showhintcoursehidden') == 'yes'
                && has_capability('theme/urcourses_default:viewhintinhiddencourse', \context_course::instance($COURSE->id))
                && $this->page->has_set_url()
                && $this->page->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
                && $COURSE->visible == false) {
            $html .= html_writer::start_tag('div', array('class' => 'course-hidden-infobox alert alert-warning'));
            $html .= html_writer::start_tag('div', array('class' => 'media'));
            $html .= html_writer::start_tag('div', array('class' => 'mr-3 icon-size-5'));
            $html .= html_writer::tag('i', null, array('class' => 'fa fa-exclamation-circle fa-3x'));
            $html .= html_writer::end_tag('div');
            $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));
            $html .= get_string('showhintcoursehiddengeneral', 'theme_urcourses_default', $COURSE->id);
            // If the user has the capability to change the course settings, an additional link to the course settings is shown.
            if (has_capability('moodle/course:update', context_course::instance($COURSE->id))) {
                $html .= html_writer::tag('div', get_string('showhintcoursehiddensettingslink',
                    'theme_urcourses_default', array('url' => $CFG->wwwroot.'/course/edit.php?id='. $COURSE->id)));
            }
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
        }
        // MODIFICATION END.

        // MODIFICATION START:
        // If the setting showhintcourseguestaccess is set, a hint for users that view the course with guest access is shown.
        // We also check that the user did not switch the role. This is a special case for roles that can fully access the course
        // without being enrolled. A role switch would show the guest access hint additionally in that case and this is not
        // intended.
        if (get_config('theme_urcourses_default', 'showhintcourseguestaccess') == 'yes'
            && is_guest(\context_course::instance($COURSE->id), $USER->id)
            && $this->page->has_set_url()
            && $this->page->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
            && !is_role_switched($COURSE->id)) {
            $html .= html_writer::start_tag('div', array('class' => 'course-guestaccess-infobox alert alert-warning'));
            $html .= html_writer::start_tag('div', array('class' => 'media'));
            $html .= html_writer::start_tag('div', array('class' => 'mr-3 icon-size-5'));
            $html .= html_writer::tag('i', null, array('class' => 'fa fa-exclamation-circle fa-3x'));
            $html .= html_writer::end_tag('div');
            $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));
            $html .= get_string('showhintcourseguestaccessgeneral', 'theme_urcourses_default',
                array('role' => role_get_name(get_guest_role())));
            $html .= theme_urcourses_default_get_course_guest_access_hint($COURSE->id);
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
        }
        // MODIFICATION END.

        // MODIFICATION START:
        // If the setting showhintcourseselfenrol is set, a hint for users is shown that the course allows unrestricted self
        // enrolment. This hint is only shown if the course is visible, the self enrolment is visible and if the user has the
        // capability "theme/urcourses_default:viewhintcourseselfenrol".
        if (get_config('theme_urcourses_default', 'showhintcourseselfenrol') == 'yes'
                && has_capability('theme/urcourses_default:viewhintcourseselfenrol', \context_course::instance($COURSE->id))
                && $this->page->has_set_url()
                && $this->page->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
                && $COURSE->visible == true) {
            // Get the active enrol instances for this course.
            $enrolinstances = enrol_get_instances($COURSE->id, true);
            // Prepare to remember when self enrolment is / will be possible.
            $selfenrolmentpossiblecurrently = false;
            $selfenrolmentpossiblefuture = false;
            foreach ($enrolinstances as $instance) {
                // Check if unrestricted self enrolment is possible currently or in the future.
                $now = (new \DateTime("now", \core_date::get_server_timezone_object()))->getTimestamp();
                if ($instance->enrol == 'self' && empty($instance->password) && $instance->customint6 == 1 &&
                        (empty($instance->enrolenddate) || $instance->enrolenddate > $now)) {

                    // Build enrol instance object with all necessary information for rendering the note later.
                    $instanceobject = new stdClass();

                    // Remember instance name.
                    if (empty($instance->name)) {
                        $instanceobject->name = get_string('pluginname', 'enrol_self') .
                                " (" . get_string('defaultcoursestudent', 'core') . ")";
                    } else {
                        $instanceobject->name = $instance->name;
                    }

                    // Remember type of unrestrictedness.
                    if (empty($instance->enrolenddate) && empty($instance->enrolstartdate)) {
                        $instanceobject->unrestrictedness = 'unlimited';
                        $selfenrolmentpossiblecurrently = true;
                    } else if (empty($instance->enrolstartdate) &&
                            !empty($instance->enrolenddate) && $instance->enrolenddate > $now) {
                        $instanceobject->unrestrictedness = 'until';
                        $selfenrolmentpossiblecurrently = true;
                    } else if (empty($instance->enrolenddate) &&
                            !empty($instance->enrolstartdate) && $instance->enrolstartdate > $now) {
                        $instanceobject->unrestrictedness = 'from';
                        $selfenrolmentpossiblefuture = true;
                    } else if (empty($instance->enrolenddate) &&
                            !empty($instance->enrolstartdate) && $instance->enrolstartdate <= $now) {
                        $instanceobject->unrestrictedness = 'since';
                        $selfenrolmentpossiblecurrently = true;
                    } else if (!empty($instance->enrolstartdate) && $instance->enrolstartdate > $now &&
                            !empty($instance->enrolenddate) && $instance->enrolenddate > $now) {
                        $instanceobject->unrestrictedness = 'fromuntil';
                        $selfenrolmentpossiblefuture = true;
                    } else if (!empty($instance->enrolstartdate) && $instance->enrolstartdate <= $now &&
                            !empty($instance->enrolenddate) && $instance->enrolenddate > $now) {
                        $instanceobject->unrestrictedness = 'sinceuntil';
                        $selfenrolmentpossiblecurrently = true;
                    } else {
                        // This should not happen, thus continue to next instance.
                        continue;
                    }

                    // Remember enrol start date.
                    if (!empty($instance->enrolstartdate)) {
                        $instanceobject->startdate = $instance->enrolstartdate;
                    } else {
                        $instanceobject->startdate = null;
                    }

                    // Remember enrol end date.
                    if (!empty($instance->enrolenddate)) {
                        $instanceobject->enddate = $instance->enrolenddate;
                    } else {
                        $instanceobject->enddate = null;
                    }

                    // Remember this instance.
                    $selfenrolinstances[$instance->id] = $instanceobject;
                }
            }

            // If there is at least one unrestricted enrolment instance,
            // show the hint with information about each unrestricted active self enrolment in the course.
            if (!empty($selfenrolinstances) &&
                    ($selfenrolmentpossiblecurrently == true || $selfenrolmentpossiblefuture == true)) {
                // Start hint box.
                $html .= html_writer::start_tag('div', array('class' => 'course-selfenrol-infobox alert alert-info'));
                $html .= html_writer::start_tag('div', array('class' => 'media'));
                $html .= html_writer::start_tag('div', array('class' => 'mr-3 icon-size-5'));
                $html .= html_writer::tag('i', null, array('class' => 'fa fa-sign-in fa-3x'));
                $html .= html_writer::end_tag('div');
                $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));

                // Show the start of the hint depending on the fact if enrolment is already possible currently or
                // will be in the future.
                if ($selfenrolmentpossiblecurrently == true) {
                    $html .= get_string('showhintcourseselfenrolstartcurrently', 'theme_urcourses_default');
                } else if ($selfenrolmentpossiblefuture == true) {
                    $html .= get_string('showhintcourseselfenrolstartfuture', 'theme_urcourses_default');
                }
                $html .= html_writer::empty_tag('br');

                // Iterate over all enrolment instances to output the details.
                foreach ($selfenrolinstances as $selfenrolinstanceid => $selfenrolinstanceobject) {
                    // If the user has the capability to config self enrolments, enrich the instance name with the settings link.
                    if (has_capability('enrol/self:config', \context_course::instance($COURSE->id))) {
                        $url = new moodle_url('/enrol/editinstance.php', array('courseid' => $COURSE->id,
                                'id' => $selfenrolinstanceid, 'type' => 'self'));
                        $selfenrolinstanceobject->name = html_writer::link($url, $selfenrolinstanceobject->name);
                    }

                     // Show the enrolment instance information depending on the instance configuration.
                     if ($selfenrolinstanceobject->unrestrictedness == 'unlimited') {
                        $html .= get_string('showhintcourseselfenrolunlimited', 'theme_urcourses_default',
                                array('name' => $selfenrolinstanceobject->name));
                    } else if ($selfenrolinstanceobject->unrestrictedness == 'until') {
                        $html .= get_string('showhintcourseselfenroluntil', 'theme_urcourses_default',
                                array('name' => $selfenrolinstanceobject->name,
                                        'until' => userdate($selfenrolinstanceobject->enddate)));
                    } else if ($selfenrolinstanceobject->unrestrictedness == 'from') {
                        $html .= get_string('showhintcourseselfenrolfrom', 'theme_urcourses_default',
                                array('name' => $selfenrolinstanceobject->name,
                                        'from' => userdate($selfenrolinstanceobject->startdate)));
                    } else if ($selfenrolinstanceobject->unrestrictedness == 'since') {
                        $html .= get_string('showhintcourseselfenrolsince', 'theme_urcourses_default',
                                array('name' => $selfenrolinstanceobject->name,
                                        'since' => userdate($selfenrolinstanceobject->startdate)));
                    } else if ($selfenrolinstanceobject->unrestrictedness == 'fromuntil') {
                        $html .= get_string('showhintcourseselfenrolfromuntil', 'theme_urcourses_default',
                                array('name' => $selfenrolinstanceobject->name,
                                        'until' => userdate($selfenrolinstanceobject->enddate),
                                        'from' => userdate($selfenrolinstanceobject->startdate)));
                    } else if ($selfenrolinstanceobject->unrestrictedness == 'sinceuntil') {
                        $html .= get_string('showhintcourseselfenrolsinceuntil', 'theme_urcourses_default',
                                array('name' => $selfenrolinstanceobject->name,
                                        'until' => userdate($selfenrolinstanceobject->enddate),
                                        'since' => userdate($selfenrolinstanceobject->startdate)));
                    }

                    // Add a trailing space to separate this instance from the next one.
                    $html .= ' ';
                }

                // If the user has the capability to config self enrolments, add the call for action.
                if (has_capability('enrol/self:config', \context_course::instance($COURSE->id))) {
                    $html .= html_writer::empty_tag('br');
                    $html .= get_string('showhintcourseselfenrolinstancecallforaction', 'theme_urcourses_default');
                }

                // End hint box.
                $html .= html_writer::end_tag('div');
                $html .= html_writer::end_tag('div');
                $html .= html_writer::end_tag('div');
            }
        }
        // MODIFICATION END.

        // MODIFICATION START.
        // Only use this if setting 'showswitchedroleincourse' is active.
        if (get_config('theme_urcourses_default', 'showswitchedroleincourse') === 'yes') {
            // Check if the user did a role switch.
            // If not, adding this section would make no sense and, even worse,
            // user_get_user_navigation_info() will throw an exception due to the missing user object.
            if (is_role_switched($COURSE->id)) {
                // Get the role name switched to.
                $opts = \user_get_user_navigation_info($USER, $this->page);
                $role = $opts->metadata['rolename'];
                // Get the URL to switch back (normal role).
                $url = new moodle_url('/course/switchrole.php',
                    array('id'        => $COURSE->id, 'sesskey' => sesskey(), 'switchrole' => 0,
                          'returnurl' => $this->page->url->out_as_local_url(false)));
                $html .= html_writer::start_tag('div', array('class' => 'switched-role-infobox alert alert-info'));
                $html .= html_writer::start_tag('div', array('class' => 'media'));
                $html .= html_writer::start_tag('div', array('class' => 'mr-3 icon-size-5'));
                $html .= html_writer::tag('i', null, array('class' => 'fa fa-user-circle fa-3x'));
                $html .= html_writer::end_tag('div');
                $html .= html_writer::start_tag('div', array('class' => 'media-body align-self-center'));
                $html .= html_writer::start_tag('div');
                $html .= get_string('switchedroleto', 'theme_urcourses_default');
                // Give this a span to be able to address via CSS.
                $html .= html_writer::tag('span', $role, array('class' => 'switched-role'));
                $html .= html_writer::end_tag('div');
                // Return to normal role link.
                $html .= html_writer::start_tag('div');
                $html .= html_writer::tag('a', get_string('switchrolereturn', 'core'),
                    array('class' => 'switched-role-backlink', 'href' => $url));
                $html .= html_writer::end_tag('div'); // Return to normal role link: end div.
                $html .= html_writer::end_tag('div');
                $html .= html_writer::end_tag('div');
                $html .= html_writer::end_tag('div');
            }
        }
        // MODIFICATION END.
        return $html;
    }

    /**
     * Gets markup for image uploader button.
     * @return string - Upload button markup, or false if user is not editing and doesn't have permission.
     */
    public function get_course_image_uploader() {
        global $CFG, $COURSE;
        
        if ($this->page->user_is_editing()) {
            $context = [
                'supported_types' => 'image/png,image/jpeg,image/gif',
                'maxbytes' => get_max_upload_file_size($CFG->maxbytes),
                'courseid' => $COURSE->id,
            ];
            return $this->render_from_template('theme_urcourses_default/header_course_image_uploader', $context);
        }
        else {
            return false;
        }
    }

    
    public function get_course_header_style() {
        global $CFG, $DB, $COURSE;
        
        
        //check if course has alternate style in database
        if($record = $DB->get_record('theme_urcourses_hdrstyle', array('courseid'=>$COURSE->id, 'hdrstyle'=>1))){
            $headerstyle = 1;
        } else {
            $headerstyle = 0;
        }
        
        if ($this->page->user_is_editing()) {
            $context = [
                'courseid' => $COURSE->id,
                'headerstyle' => $headerstyle
            ];
            return $this->render_from_template('theme_urcourses_default/header_course_style_chooser', $context);
        }
        else {
            return false;
        }
    }
    
    
    public function get_course_toggle_availability() {
        global $CFG, $DB, $COURSE, $USER;
        
        /*
        $sql = <<<EOD
                    select crn, subject, course, section, title from ur_course_info
                    where semester='{$CFG->ur_current_semester}' and crn in (
                        select crn from ur_instructors where
                            semester='{$CFG->ur_current_semester}' and
                            username='{$USER->username}'
                    ) and crn in (
                        select crn from ur_crn_map where semester='{$CFG->ur_current_semester}'
                    ) order by subject, course, section
        EOD;
        
        $enrol_output = array();
        
        $rs = $DB->get_recordset_sql($sql);
        foreach($rs as $course) {
            $enrol_output[$course->crn] = 'CRN: '.$course->crn.' '.$course->subject.' '.$course->section;
        }
        */
        
        //only show availability bar when on course main page, but not site home
        $allowurl = $CFG->wwwroot.'/course/view.php?id='.$COURSE->id;
        $allowurl2 = $CFG->wwwroot.'/course/edit.php?id='.$COURSE->id;
        
        $page = basename($_SERVER['PHP_SELF']);

        if ($COURSE->id==1||($this->page->url!=$allowurl && $this->page->url!=$allowurl2 )) return false;

        $isedit = false;
        if($page == "edit.php")
            $isedit = true;
        
        if ($this->page->user_is_editing()||(has_capability('moodle/course:update', context_course::instance($COURSE->id))&&$COURSE->visible==0)||$isedit) {

            $dates = $this->get_course_visibility();
            $past = $dates[0];
            $current = $dates[1];
            $future = $dates[2];
            $ongoing = $dates[3];

            if(URCOURSEREQUEST){
                $enrollment = array("isenrollment"=>theme_urcourses_default_get_course_state($COURSE->id)); 
            }else{
                $enrollment = false;
            }

            if($this->page->user_is_editing()){
                $context = [
                    'courseid' => $COURSE->id,
                    'availability' => $COURSE->visible,
                    'past' => $past,
                    'current' => $current,
                    'future' => $future,
                    'ongoing' => $ongoing,
                    'enrollment' => $enrollment,
                    'coursetools' => $this->get_course_tools()
                ];
            }else{
                $context = [
                    'courseid' => $COURSE->id,
                    'availability' => $COURSE->visible,
                    'past' => $past,
                    'current' => $current,
                    'future' => $future,
                    'ongoing' => $ongoing,
                    'enrollment' => $enrollment,
                    'coursetools' => ""
                ];
            }
            return $this->render_from_template('theme_urcourses_default/header_toggle_course_availability', $context);
        }
        else {
            return false;
        }
    }
    
    public function get_course_visibility() {
        global $CFG, $DB, $COURSE, $USER;

        // check if date is past, current, active
        $pastterm = strtotime("-4 months", time());
        $ongoingdate = 946706400; //Jan 01, 2000 set date for ongoing courses

        //check if course is starting in the future
        if(time() < $COURSE->startdate){
            return array(false,false,"future",false);
        }
        //if continuing course date it set
        if($ongoingdate == $COURSE->startdate){
            return array(false,false,false,"ongoing");
        }
        //if end date is set 
        if($COURSE->enddate  != 0 && isset($COURSE->enddate) && $COURSE->enddate < time() ){
            return array("past",false,false,false);
        }else if(($COURSE->enddate  == 0 || !isset($COURSE->enddate))&& $COURSE->startdate < $pastterm ){
            //if there is no end date and startdate is greater then four month
            //place in past year
            return array("past",false,false,false);
        }else if($COURSE->enddate > time() && $COURSE->startdate <= $pastterm){
            return array(false,"current",false,false);
        }else{
            return array(false,"current",false,false);
        }
    }  	
    
    function get_course_tools() {
        global $COURSE;

        $context = [
            'courseid' => $COURSE->id,
            'availability' => $COURSE->visible,
            'enrolment_state' => $this->get_course_enrolment(),
            'course_state' => $this->get_course_request(),
        ];
        return $this->render_from_template('theme_urcourses_default/header_course_tools', $context);
    }
    
    function get_course_request() {
        global $USER,$COURSE;

        $context = [
            'availability' => $COURSE->visible,
            'username'=> $USER->username,
            'studentaccount'=> theme_urcourses_default_check_test_account($USER->username),
            'studentenrolled'=> theme_urcourses_default_test_account_enrollment($USER->username, $COURSE->id),
            'templatelist'=> json_encode(theme_urcourses_default_get_course_templates()),
            'categories'=> json_encode(theme_urcourses_default_get_catergories($COURSE)),
            'course'=> json_encode(array("id"=>$COURSE->id,
                                "coursename"=>$COURSE->fullname,
                                "shortname"=>$COURSE->shortname,
                                "category"=>$COURSE->category,
                                "startdate"=>usergetdate($COURSE->startdate),
                                "enddate"=>usergetdate($COURSE->enddate),
            )),
        ];
      
        return $this->render_from_template('theme_urcourses_default/header_course_request', $context);
    }
    
    function get_course_enrolment() {
        global $COURSE, $CFG;

        if(URCOURSEREQUEST){
            $context = [
                'availability' => $COURSE->visible,
                'semesters' => theme_urcourses_default_get_semesters(),
                'active' =>  theme_urcourses_default_get_course_state($COURSE->id),
                'course'=> json_encode(array("id"=>$COURSE->id,
                                "coursename"=>$COURSE->fullname,
                                "shortname"=>$COURSE->shortname,
                                "category"=>$COURSE->category,
                                "startdate"=>usergetdate($COURSE->startdate),
                                "enddate"=>usergetdate($COURSE->enddate),
                )),
                'semesterdates' => json_encode(theme_urcourses_default_get_semesterdates()),
                'categories'=> json_encode(theme_urcourses_default_get_catergories($COURSE)),
                'templatelist'=> json_encode(theme_urcourses_default_get_course_templates()),
                "homeurl"=>$CFG->wwwroot,
                
            ];
            return $this->render_from_template('theme_urcourses_default/header_course_enrolment', $context);
            
        }

        return ""; 
    }
    /**
     * Override to display course settings on every course site for permanent access
     *
     * This is an optional menu that can be added to a layout by a theme. It contains the
     * menu for the course administration, only on the course main page.
     *
     * MODIFICATION: This renderer function is copied and modified from /lib/outputrenderers.php.
     *
     * @return string
     */
    public function context_header_settings_menu() {
        $context = $this->page->context;
        $menu = new action_menu();

        $items = $this->page->navbar->get_items();
        $currentnode = end($items);

        $showcoursemenu = false;
        $showfrontpagemenu = false;
        $showusermenu = false;

        // We are on the course home page.
        // MODIFICATION START.
        // REASON: With the original code, the course settings icon will only appear on the course main page.
        // Therefore the access to the course settings and related functions is not possible on other
        // course pages as there is no omnipresent block anymore. We want these to be accessible
        // on each course page.
        if (($context->contextlevel == CONTEXT_COURSE || $context->contextlevel == CONTEXT_MODULE) && !empty($currentnode)) {
            $showcoursemenu = true;
        }
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START.
        if (($context->contextlevel == CONTEXT_COURSE) &&
                !empty($currentnode) &&
                ($currentnode->type == navigation_node::TYPE_COURSE || $currentnode->type == navigation_node::TYPE_SECTION)) {
            $showcoursemenu = true;
        }
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd

        $courseformat = course_get_format($this->page->course);
        // This is a single activity course format, always show the course menu on the activity main page.
        if ($context->contextlevel == CONTEXT_MODULE &&
                !$courseformat->has_view_page()) {

            $this->page->navigation->initialise();
            $activenode = $this->page->navigation->find_active_node();
            // If the settings menu has been forced then show the menu.
            if ($this->page->is_settings_menu_forced()) {
                $showcoursemenu = true;
            } else if (!empty($activenode) && ($activenode->type == navigation_node::TYPE_ACTIVITY ||
                        $activenode->type == navigation_node::TYPE_RESOURCE)) {

                // We only want to show the menu on the first page of the activity. This means
                // the breadcrumb has no additional nodes.
                if ($currentnode && ($currentnode->key == $activenode->key && $currentnode->type == $activenode->type)) {
                    $showcoursemenu = true;
                }
            }
        }

        // This is the site front page.
        if ($context->contextlevel == CONTEXT_COURSE &&
                !empty($currentnode) &&
                $currentnode->key === 'home') {
            $showfrontpagemenu = true;
        }

        // This is the user profile page.
        if ($context->contextlevel == CONTEXT_USER &&
                !empty($currentnode) &&
                ($currentnode->key === 'myprofile')) {
            $showusermenu = true;
        }

        if ($showfrontpagemenu) {
            $settingsnode = $this->page->settingsnav->find('frontpage', navigation_node::TYPE_SETTING);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $this->page->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', $text));
                    $menu->add_secondary_action($link);
                }
            }
        } else if ($showcoursemenu) {
            $settingsnode = $this->page->settingsnav->find('courseadmin', navigation_node::TYPE_COURSE);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $this->page->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', $text));
                    $menu->add_secondary_action($link);
                }
            }
        } else if ($showusermenu) {
            // Get the course admin node from the settings navigation.
            $settingsnode = $this->page->settingsnav->find('useraccount', navigation_node::TYPE_CONTAINER);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $this->build_action_menu_from_navigation($menu, $settingsnode);
            }
        }

        return $this->render($menu);
    }

    /**
     * Override to use theme_urcourses_default login template
     * Renders the login form.
     *
     * MODIFICATION: This renderer function is copied and modified from lib/outputrenderers.php
     *
     * @param \core_auth\output\login $form The renderable.
     * @return string
     */
    public function render_login(\core_auth\output\login $form) {
        global $CFG, $SITE, $OUTPUT;

        $context = $form->export_for_template($this);

        // Override because rendering is not supported in template yet.
        if ($CFG->rememberusername == 0) {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabledonlysession');
        } else {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabled');
        }
        $context->errorformatted = $this->error_text($context->error);
        $url = $this->get_logo_url();
        if ($url) {
            $url = $url->out(false);
        }
        $context->logourl = $url;
        $context->loginlogourl = $OUTPUT->image_url('uofr_logo_primary_blk', 'theme');
        $context->sitename = format_string($SITE->fullname, true,
                ['context' => context_course::instance(SITEID), "escape" => false]);
        // MODIFICATION START.
        // Only if setting "loginform" is checked, then call own login.mustache.
        if (get_config('theme_urcourses_default', 'loginform') == 'yes') {
            return $this->render_from_template('theme_urcourses_default/loginform', $context);
        } else {
            return $this->render_from_template('core/loginform', $context);
        }
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START.
        return $this->render_from_template('core/loginform', $context);
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd
    }

    /**
     * Implementation of user image rendering.
     *
     * MODIFICATION: This renderer function is copied and modified from lib/outputrenderers.php
     *
     * @param help_icon $helpicon A help icon instance
     * @return string HTML fragment
     */
    protected function render_help_icon(help_icon $helpicon) {
        $context = $helpicon->export_for_template($this);
        // MODIFICATION START.
        // ID needed for modal dialog.
        $context->linkid = $helpicon->component.'-'.$helpicon->identifier;
        // Fill body variable needed for modal mustache with text value.
        $context->body = $context->text;
        if (get_config('theme_urcourses_default', 'helptextmodal') == 'yes') {
            return $this->render_from_template('theme_urcourses_default/help_icon', $context);
        } else {
            return $this->render_from_template('core/help_icon', $context);
        }
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START.
        $context = $helpicon->export_for_template($this);
        return $this->render_from_template('core/help_icon', $context);
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd
    }
    
    public function activity_navigation() {
            return '';
        }
    
    public function course_authornames() {

    global $CFG, $USER, $DB, $OUTPUT, $COURSE;

    // expecting $course

    //$context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
    $context = context_course::instance($COURSE->id);


    /// first find all roles that are supposed to be displayed
    if (!empty($CFG->coursecontact)) {
        $managerroles = explode(',', $CFG->coursecontact);
        $namesarray = array();
        $rusers = array();

        if (!isset($COURSE->managers)) {
            $rusers = get_role_users($managerroles, $context, true,
                'ra.id AS raid, u.id, u.username, u.firstname, u.lastname,
                 u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename,
                 r.name AS rolename, r.sortorder, r.id AS roleid',
                'r.sortorder ASC, u.lastname ASC');
        } else {
            //  use the managers array if we have it for perf reasosn
            //  populate the datastructure like output of get_role_users();
            foreach ($COURSE->managers as $manager) {
                $u = new stdClass();
                $u = $manager->user;
                $u->roleid = $manager->roleid;
                $u->rolename = $manager->rolename;

                $rusers[] = $u;
            }
        }

        /// Rename some of the role names if needed
        if (isset($context)) {
            $aliasnames = $DB->get_records('role_names', array('contextid'=>$context->id), '', 'roleid,contextid,name');
        }

        $namesarray = array();
        $canviewfullnames = has_capability('moodle/site:viewfullnames', $context);
        foreach ($rusers as $ra) {
            if (isset($namesarray[$ra->id])) {
                //  only display a user once with the higest sortorder role
                continue;
            }

            if (isset($aliasnames[$ra->roleid])) {
                $ra->rolename = $aliasnames[$ra->roleid]->name;
            }

            $fullname = fullname($ra, $canviewfullnames);
            $usr_img = '<img class="instr-avatar img-rounded" src="'.$CFG->wwwroot.'/user/pix.php/'.$ra->id.'/f2.jpg" height="24" width="24" title="Profile picture of '.$fullname.'" alt="Profile picture of '.$fullname.'" />';
            $namesarray[$ra->id] = html_writer::link(new moodle_url('/user/view.php', array('id'=>$ra->id, 'course'=>$COURSE->id)), $usr_img.' <span>'.$fullname.'</span>');
        }

        if (!empty($namesarray)) {
            $course_authornames = html_writer::start_tag('div', array('class'=>'teacherlist'));
            $course_authornames .= implode('', $namesarray);
            $course_authornames .= html_writer::end_tag('div');
            
            return $course_authornames;
        } else return '';
    }

}


/**
 * The standard tags (typically performance information and validation links,
 * if we are in developer debug mode) that should be output in the footer area
 * of the page. Designed to be called in theme layout.php files.
 *
 * @return string HTML fragment.
 */
 function standard_footer_html() {
    global $CFG, $SCRIPT;

    $output = '';
    if (during_initial_install()) {
        // Debugging info can not work before install is finished,
        // in any case we do not want any links during installation!
        return $output;
    }

    // Give plugins an opportunity to add any footer elements.
    // The callback must always return a string containing valid html footer content.
    $pluginswithfunction = get_plugins_with_function('standard_footer_html', 'lib.php');
    foreach ($pluginswithfunction as $plugins) {
        foreach ($plugins as $function) {
            $output .= $function();
        }
    }

    // This function is normally called from a layout.php file in {@link core_renderer::header()}
    // but some of the content won't be known until later, so we return a placeholder
    // for now. This will be replaced with the real content in {@link core_renderer::footer()}.
    $output .= $this->unique_performance_info_token;
    if ($this->page->devicetypeinuse == 'legacy') {
        // The legacy theme is in use print the notification
        $output .= html_writer::tag('div', get_string('legacythemeinuse'), array('class'=>'legacythemeinuse'));
    }

    // Get links to switch device types (only shown for users not on a default device)
    //$output .= $this->theme_switch_links();

    if (!empty($CFG->debugpageinfo)) {
        $output .= '<div class="performanceinfo pageinfo">' . get_string('pageinfodebugsummary', 'core_admin',
            $this->page->debug_summary()) . '</div>';
    }
    if (debugging(null, DEBUG_DEVELOPER) and has_capability('moodle/site:config', context_system::instance())) {  // Only in developer mode
        // Add link to profiling report if necessary
        if (function_exists('profiling_is_running') && profiling_is_running()) {
            $txt = get_string('profiledscript', 'admin');
            $title = get_string('profiledscriptview', 'admin');
            $url = $CFG->wwwroot . '/admin/tool/profiling/index.php?script=' . urlencode($SCRIPT);
            $link= '<a title="' . $title . '" href="' . $url . '">' . $txt . '</a>';
            $output .= '<div class="profilingfooter">' . $link . '</div>';
        }
        $purgeurl = new moodle_url('/admin/purgecaches.php', array('confirm' => 1,
            'sesskey' => sesskey(), 'returnurl' => $this->page->url->out_as_local_url(false)));
        $output .= '<div class="purgecaches">' .
                html_writer::link($purgeurl, get_string('purgecaches', 'admin')) . '</div>';
    }
    return $output;
}

function check_declaration_notice() {
	global $USER,$CFG,$SESSION;
        
       /*
       $DB = \moodle_database::get_driver_instance($CFG->dbtype, $CFG->dblibrary);

       try { 
               $DB->connect('host','user','password','database',false);
       } catch (\moodle_exception $e) {
               $declaration_notice = 'Failed to connect to db';
       }
       */
	   
       //$rs = $DB->get_records('students', null, $sort='', $fields='*', $limitfrom=0, $limitnum=3);
               
       //$declaration_notice .= var_dump($rs);
	
	   if ($record = $DB->get_record('student_list', array('username'=> $USER->username))) {
       //if ($USER->username == 'urstudent4'&&!isset($SESSION->declaration_shown)) {
               
               $rs = $DB->get_record('student_msg', array('msg_id'=> $record->msg_id));

               $declaration_notice = '';
               $declaration_notice = '<div id="myDeclarationModal" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
			  <div class="modal-dialog" role="document">
			    <div class="modal-content">
			      <div class="modal-header bg-warning">
			        <h5 class="modal-title"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
 <b>'.$rs->msg_label.'</b></h5>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          <span aria-hidden="true">&times;</span>
			        </button>
			      </div>
                  <div class="modal-body">';
				  
               $declaration_notice .= $rs->msg_text;     

       
               $declaration_notice .= '</div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
			      </div>
			    </div>
			  </div>
			</div>';
	
			//error_log('added declaration_notice modal');
			
                       if ($record->action_code == 'LO') {
                           redirect($CFG->wwwroot."/login/logout.php?sesskey=".sesskey(), $rs->msg_text, 10); 
                        } else if (!isset($SESSION->declaration_shown)) {
                            $SESSION->declaration_shown = 1;
                        } else {
                            $declaration_notice = '';
                        }
/*
			$cm = get_coursemodule_from_id('', 1, 0, false, MUST_EXIST);
			$context = context_course::instance(1);

			$params = array(
			    'context' => $context,
			    'objectid' => $USER->id
			);
			$event = \theme_urcourses_default\event\declaration_notice_updated::create($params);
			$event->add_record_snapshot('course_modules', $cm);
			$event->add_record_snapshot('course', 1);
			$event->trigger();
*/


	} else {
		$declaration_notice = '';
	}
	
	//$declaration_notice = var_dump($row);
	
	return $declaration_notice;
}

function ur_check_course_cat() {
    global $CFG,$DB,$COURSE;

    $ur_categories = array('','default'=>'',
                            'arts'=>'Faculty of Arts',
                            'business'=>'Faculty of Business Administration',
                            'campion'=>'Campion College',
                            'cnpp'=>'Collaborative Nurse Practitioner Program',
                            'education'=>'Faculty of Education',
                            'engineering'=>'Faculty of Engineering and Applied Science',
                            'fnuniv'=>'First Nations University of Canada',
                            'gbus'=>'Kenneth Levene Graduate School of Business',
                            'jsgspp'=>'Johnson Shoyama Graduate School of Public Policy',
                            'khs'=>'Faculty of Kinesiology and Health Studies',
                            'lacite'=>'La Cit&eacute;',
                            'luther'=>'Luther College',
                            'luthervssn'=>'Luther College VSSN',
                            'map'=>'Faculty of Media, Art, and Performance',
                            'nursing'=>'Faculty of Nursing',
                            'scbscn'=>'',
                            'science'=>'Faculty of Science',
                            'socialwork'=>'Faculty of Social Work');
    if ($COURSE->theme != 'urcourses_default' && $COURSE->theme !== NULL && !empty($COURSE->theme)) {
        $currthemeelms = explode('_',$COURSE->theme);
        return array('css'=>'','name'=>$ur_categories[$currthemeelms[1]]);
    }
    
    $sql = "SELECT a.name FROM {$CFG->prefix}course_categories a, {$CFG->prefix}course b WHERE a.id = b.category AND b.id = {$COURSE->id}";
    $check_course_category = $DB->get_record_sql($sql);
    if ($check_course_category) {
        $key = array_search($check_course_category->name,$ur_categories);
        return array('css'=>$key,'name'=>$check_course_category->name);
    } else {
        return array('css'=>'','name'=>'');
    }
}

public function user_menu($user = null, $withlinks = null) {
    global $USER, $CFG, $DB;
    require_once($CFG->dirroot . '/user/lib.php');

    if (is_null($user)) {
        $user = $USER;
    }

    // Note: this behaviour is intended to match that of core_renderer::login_info,
    // but should not be considered to be good practice; layout options are
    // intended to be theme-specific. Please don't copy this snippet anywhere else.
    if (is_null($withlinks)) {
        $withlinks = empty($this->page->layout_options['nologinlinks']);
    }

    // Add a class for when $withlinks is false.
    $usermenuclasses = 'usermenu';
    if (!$withlinks) {
        $usermenuclasses .= ' withoutlinks';
    }

    $returnstr = "";

    // If during initial install, return the empty return string.
    if (during_initial_install()) {
        return $returnstr;
    }

    $loginpage = $this->is_login_page();
    $loginurl = get_login_url();
    // If not logged in, show the typical not-logged-in string.
    if (!isloggedin()) {
        $returnstr = get_string('loggedinnot', 'moodle');
        if (!$loginpage) {
            $returnstr .= " (<a href=\"$loginurl\">" . get_string('login') . '</a>)';
        }
        return html_writer::div(
            html_writer::span(
                $returnstr,
                'login'
            ),
            $usermenuclasses
        );

    }

    // If logged in as a guest user, show a string to that effect.
    if (isguestuser()) {
        $returnstr = get_string('loggedinasguest');
        if (!$loginpage && $withlinks) {
            $returnstr .= " (<a href=\"$loginurl\">".get_string('login').'</a>)';
        }

        return html_writer::div(
            html_writer::span(
                $returnstr,
                'login'
            ),
            $usermenuclasses
        );
    }

    // Get some navigation opts.
    $opts = user_get_user_navigation_info($user, $this->page);

    if ($usedarkmode = $DB->get_record('theme_urcourses_darkmode', array('userid'=>$USER->id, 'darkmode'=>1))) {
        //changes url to opposite of whatever the toggle currently is to set dark mode in db under columns2.php
        $darkchk = $usedarkmode->darkmode;
    } else {
        $darkchk = 0;
    }
    $usedarkmodeurl = ($darkchk == 1) ? 0 : 1;
    //dark mode variable for if on/off to swap icon
    $mynodelabel = ($darkchk == 1) ? "i/item" : "i/marker";
    $darkstate = ($darkchk == 1) ? "off" : "on";

    //creating dark mode object 
    $mynode = new stdClass();
    $mynode->itemtype = "link";
    $mynode->url = new moodle_url($this->page->url,array("darkmode"=>$usedarkmodeurl));
    $mynode->title = "Darkmode " . $darkstate;
    $mynode->titleidentifier = "darkmode, theme_urcourses_default";
    $mynode->pix = $mynodelabel;


    $lnode = $opts->navitems[5]; //get logout node
    $opts->navitems[5] = $mynode; //dark node placed in 5
    $opts->navitems[] = $lnode; //placing log out back in at the end

    $avatarclasses = "avatars";
    $avatarcontents = html_writer::span($opts->metadata['useravatar'], 'avatar current');
    $usertextcontents = $opts->metadata['userfullname'];

    // Other user.
    if (!empty($opts->metadata['asotheruser'])) {
        $avatarcontents .= html_writer::span(
            $opts->metadata['realuseravatar'],
            'avatar realuser'
        );
        $usertextcontents = $opts->metadata['realuserfullname'];
        $usertextcontents .= html_writer::tag(
            'span',
            get_string(
                'loggedinas',
                'moodle',
                html_writer::span(
                    $opts->metadata['userfullname'],
                    'value'
                )
            ),
            array('class' => 'meta viewingas')
        );
    }

    // Role.
    if (!empty($opts->metadata['asotherrole'])) {
        $role = core_text::strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['rolename'])));
        $usertextcontents .= html_writer::span(
            $opts->metadata['rolename'],
            'meta role role-' . $role
        );
    }

    // User login failures.
    if (!empty($opts->metadata['userloginfail'])) {
        $usertextcontents .= html_writer::span(
            $opts->metadata['userloginfail'],
            'meta loginfailures'
        );
    }

    // MNet.
    if (!empty($opts->metadata['asmnetuser'])) {
        $mnet = strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['mnetidprovidername'])));
        $usertextcontents .= html_writer::span(
            $opts->metadata['mnetidprovidername'],
            'meta mnet mnet-' . $mnet
        );
    }

    $returnstr .= html_writer::span(
        html_writer::span($usertextcontents, 'usertext mr-1') .
        html_writer::span($avatarcontents, $avatarclasses),
        'userbutton'
    );

    // Create a divider (well, a filler).
    $divider = new action_menu_filler();
    $divider->primary = false;

    $am = new action_menu();
    $am->set_menu_trigger(
        $returnstr
    );
    $am->set_action_label(get_string('usermenu'));
    $am->set_alignment(action_menu::TR, action_menu::BR);
    $am->set_nowrap_on_items();
    if ($withlinks) {
        $navitemcount = count($opts->navitems);
        $idx = 0;
        foreach ($opts->navitems as $key => $value) {

            switch ($value->itemtype) {
                case 'divider':
                    // If the nav item is a divider, add one and skip link processing.
                    $am->add($divider);
                    break;

                case 'invalid':
                    // Silently skip invalid entries (should we post a notification?).
                    break;

                case 'link':
                    // Process this as a link item.
                    $pix = null;
                    if (isset($value->pix) && !empty($value->pix)) {
                        $pix = new pix_icon($value->pix, '', null, array('class' => 'iconsmall'));
                    } else if (isset($value->imgsrc) && !empty($value->imgsrc)) {
                        $value->title = html_writer::img(
                            $value->imgsrc,
                            $value->title,
                            array('class' => 'iconsmall')
                        ) . $value->title;
                    }

                    $al = new action_menu_link_secondary(
                        $value->url,
                        $pix,
                        $value->title,
                        array('class' => 'icon')
                    );
                    if (!empty($value->titleidentifier)) {
                        $al->attributes['data-title'] = $value->titleidentifier;
                    }
                    $am->add($al);
                    break;
            }

            $idx++;

            // Add dividers after the first item and before the last item.
            if ($idx == 1 || $idx == $navitemcount - 1 || $value->pix == $mynodelabel) {
                $am->add($divider);
            }
        }
    }

    return html_writer::div(
        $this->render($am),
        $usermenuclasses
    );
}


function search_small() {
    global $CFG;

    // Accessing $CFG directly as using \core_search::is_global_search_enabled would
    // result in an extra included file for each site, even the ones where global search
    // is disabled.
    if (empty($CFG->enableglobalsearch) || !has_capability('moodle/search:query', \context_system::instance())) {
        return '';
    }
    return html_writer::tag('a', $this->pix_icon('a/search', get_string('search', 'search'), 'moodle'), array('class' => 'd-inline-flex nav-link', 'href' => $CFG->wwwroot . '/search/index.php'));
}

    /*
     * This renders the navbar.
     * Uses bootstrap compatible html.
     */
     public function navbar() {
        if(strpos($this->page->bodyid, 'admin')){
            return $this->render_from_template('core/navbar', $this->page->navbar);

        } else {
            return $this->render_from_template('theme_urcourses_default/breadcrumbs', $this->page->navbar);
        }
    }


    /**
     * Internal implementation of user image rendering. For participants page
     * to show user browser info for IT Support roles and admin
     *
     * @param user_picture $userpicture
     * @return string
     */
  /*  protected function render_user_picture(\user_picture $userpicture) {

        global $SESSION;


        error_log(print_r("PAGE TYPE",TRUE));
        error_log(print_r($this->page->pagetype,TRUE));

        //if ($this->page->pagetype == 'site-index') {

            //check if role is admin or IT Support


           // require_once("$CFG->dirroot/user/profile/lib.php");
            
            error_log(print_r("User picture",TRUE));
            error_log(print_r($userpicture,TRUE));
            error_log(print_r("USER EMAIL??",TRUE));
            error_log(print_r($SESSION['USER']->email,TRUE));
            error_log(print_r("USER EMAIL??",TRUE));
            error_log(print_r($SESSION[$userpicture->user->id]->email,TRUE));
            //get all the user profile data 
            $userp = profile_user_record($USER->id);
        }
        return parent::render_user_picture($userpicture);
    }*/
}
