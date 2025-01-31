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
 * Theme UR Courses - Core renderer
 *
 * @package    theme_urcourses_default
 */

namespace theme_urcourses_default\output;

use moodle_url;

/**
 * Extending the core_renderer interface.
 *
 * @package    theme_urcourses_default
 */
class core_renderer extends \theme_boost_union\output\core_renderer {
    public function get_compact_logo_url($maxwidth = 100, $maxheight = 100) {
        global $OUTPUT;
        return $OUTPUT->image_url('logo', 'theme_urcourses_default');
    }

    public function get_compact_logosmall_url($maxwidth = 100, $maxheight = 100) {
        global $OUTPUT;
        return $OUTPUT->image_url('logo', 'theme_urcourses_default');
    }

    /**
     * Renders the login form.
     *
     * This renderer function is copied and modified from /lib/classes/output/core_renderer.php
     *
     * @param \core_auth\output\login $form The renderable.
     * @return string
     */
    public function render_login(\core_auth\output\login $form) {
        global $CFG, $SITE;

        $context = $form->export_for_template($this);

        $context->errorformatted = $this->error_text($context->error);
        $url = $this->get_logo_url();
        if ($url) {
            $url = $url->out(false);
        }
        $context->logourl = $url;
        $context->sitename = format_string(
            $SITE->fullname,
            true,
            ['context' => \context_course::instance(SITEID), "escape" => false]
        );

        // Check if the local login form is enabled.
        $loginlocalloginsetting = get_config('theme_boost_union', 'loginlocalloginenable');
        $showlocallogin = ($loginlocalloginsetting != false) ? $loginlocalloginsetting : THEME_BOOST_UNION_SETTING_SELECT_YES;
        if ($showlocallogin == THEME_BOOST_UNION_SETTING_SELECT_YES) {
            // Add marker to show the local login form to template context.
            $context->showlocallogin = true;
        }

        // Check if the local login intro is enabled.
        $loginlocalshowintrosetting = get_config('theme_boost_union', 'loginlocalshowintro');
        $showlocalloginintro = ($loginlocalshowintrosetting != false) ?
            $loginlocalshowintrosetting : THEME_BOOST_UNION_SETTING_SELECT_NO;
        if ($showlocalloginintro == THEME_BOOST_UNION_SETTING_SELECT_YES) {
            // Add marker to show the local login intro to template context.
            $context->showlocalloginintro = true;
        }

        // Check if the IDP login intro is enabled.
        $loginidpshowintrosetting = get_config('theme_boost_union', 'loginidpshowintro');
        $showidploginintro = ($loginidpshowintrosetting != false) ?
                $loginidpshowintrosetting : THEME_BOOST_UNION_SETTING_SELECT_YES;
        if ($showidploginintro == THEME_BOOST_UNION_SETTING_SELECT_YES) {
            // Add marker to show the IDP login intro to template context.
            $context->showidploginintro = true;
        }

        // Custom context
        $casurl = new moodle_url('index.php', ['authCAS' => 'CAS']);
        $context->newstudenturl = 'https://novapp.cc.uregina.ca/perl/studentlookup.cgi';
        $context->forgotpasswordurl = 'https://novapp.cc.uregina.ca/perl/resetpass.cgi';
        $context->activateurl = 'https://novapp.cc.uregina.ca/perl/activate.cgi';
        $context->casurl = $casurl->out();

        return $this->render_from_template('core/loginform', $context);
    }

    /**
     * Renders the context header for the page.
     *
     * @param array $headerinfo Heading information.
     * @param int $headinglevel What 'h' level to make the heading.
     * @return string A rendered context header.
     */
    public function context_header($headerinfo = null, $headinglevel = 1): string {
        global $COURSE, $DB, $USER, $CFG;
        require_once($CFG->dirroot . '/user/lib.php');
        $context = $this->page->context;
        $heading = null;
        $imagedata = null;
        $userbuttons = null;

        // Make sure to use the heading if it has been set.
        if (isset($headerinfo['heading'])) {
            $heading = $headerinfo['heading'];
        } else {
            $heading = $this->page->heading;
        }

        // The user context currently has images and buttons. Other contexts may follow.
        if ((isset($headerinfo['user']) || $context->contextlevel == CONTEXT_USER) && $this->page->pagetype !== 'my-index') {
            if (isset($headerinfo['user'])) {
                $user = $headerinfo['user'];
            } else {
                // Look up the user information if it is not supplied.
                $user = $DB->get_record('user', array('id' => $context->instanceid));
            }

            // If the user context is set, then use that for capability checks.
            if (isset($headerinfo['usercontext'])) {
                $context = $headerinfo['usercontext'];
            }

            // Only provide user information if the user is the current user, or a user which the current user can view.
            // When checking user_can_view_profile(), either:
            // If the page context is course, check the course context (from the page object) or;
            // If page context is NOT course, then check across all courses.
            $course = ($this->page->context->contextlevel == CONTEXT_COURSE) ? $this->page->course : null;

            if (user_can_view_profile($user, $course)) {
                // Use the user's full name if the heading isn't set.
                if (empty($heading)) {
                    $heading = fullname($user);
                }

                $imagedata = $this->user_picture($user, array('size' => 100));

                // Check to see if we should be displaying a message button.
                if (!empty($CFG->messaging) && has_capability('moodle/site:sendmessage', $context)) {
                    $userbuttons = array(
                        'messages' => array(
                            'buttontype' => 'message',
                            'title' => get_string('message', 'message'),
                            'url' => new moodle_url('/message/index.php', array('id' => $user->id)),
                            'image' => 't/message',
                            'linkattributes' => \core_message\helper::messageuser_link_params($user->id),
                            'page' => $this->page
                        )
                    );

                    if ($USER->id != $user->id) {
                        $iscontact = \core_message\api::is_contact($USER->id, $user->id);
                        $isrequested = \core_message\api::get_contact_requests_between_users($USER->id, $user->id);
                        $contacturlaction = '';
                        $linkattributes = \core_message\helper::togglecontact_link_params(
                            $user,
                            $iscontact,
                            true,
                            !empty($isrequested),
                        );
                        // If the user is not a contact.
                        if (!$iscontact) {
                            if ($isrequested) {
                                // We just need the first request.
                                $requests = array_shift($isrequested);
                                if ($requests->userid == $USER->id) {
                                    // If the user has requested to be a contact.
                                    $contacttitle = 'contactrequestsent';
                                } else {
                                    // If the user has been requested to be a contact.
                                    $contacttitle = 'waitingforcontactaccept';
                                }
                                $linkattributes = array_merge($linkattributes, [
                                    'class' => 'disabled',
                                    'tabindex' => '-1',
                                ]);
                            } else {
                                // If the user is not a contact and has not requested to be a contact.
                                $contacttitle = 'addtoyourcontacts';
                                $contacturlaction = 'addcontact';
                            }
                            $contactimage = 't/addcontact';
                        } else {
                            // If the user is a contact.
                            $contacttitle = 'removefromyourcontacts';
                            $contacturlaction = 'removecontact';
                            $contactimage = 't/removecontact';
                        }
                        $userbuttons['togglecontact'] = array(
                                'buttontype' => 'togglecontact',
                                'title' => get_string($contacttitle, 'message'),
                                'url' => new moodle_url('/message/index.php', array(
                                        'user1' => $USER->id,
                                        'user2' => $user->id,
                                        $contacturlaction => $user->id,
                                        'sesskey' => sesskey())
                                ),
                                'image' => $contactimage,
                                'linkattributes' => $linkattributes,
                                'page' => $this->page
                            );
                    }

                    $this->page->requires->string_for_js('changesmadereallygoaway', 'moodle');
                }
            } else {
                $heading = null;
            }
        }

        $prefix = null;
        if ($context->contextlevel == CONTEXT_MODULE) {
            if ($this->page->course->format === 'singleactivity') {
                $heading = format_string($this->page->course->fullname, true, ['context' => $context]);
            } else {
                $heading = $this->page->cm->get_formatted_name();
                $iconurl = $this->page->cm->get_icon_url();
                $iconclass = $iconurl->get_param('filtericon') ? '' : 'nofilter';
                $iconattrs = [
                    'class' => "icon activityicon $iconclass",
                    'aria-hidden' => 'true'
                ];
                $imagedata = \html_writer::img($iconurl->out(false), '', $iconattrs);
                $purposeclass = plugin_supports('mod', $this->page->activityname, FEATURE_MOD_PURPOSE);
                $purposeclass .= ' activityiconcontainer icon-size-6';
                $purposeclass .= ' modicon_' . $this->page->activityname;
                $isbranded = component_callback('mod_' . $this->page->activityname, 'is_branded', [], false);
                $imagedata = \html_writer::tag('div', $imagedata, ['class' => $purposeclass . ($isbranded ? ' isbranded' : '')]);
                if (!empty($USER->editing)) {
                    $prefix = get_string('modulename', $this->page->activityname);
                }
            }
        }

        $instructors = [];
        if ($context->contextlevel == CONTEXT_COURSE) {
            $instructors = $this->get_instructors($COURSE->id);
        }

        $contextheader = new \theme_urcourses_default\output\context_header($heading, $headinglevel, $imagedata, $userbuttons, $prefix, $instructors);
        return $this->render($contextheader);
    }

    private function get_instructors($courseid) {
        global $CFG, $DB;
        if ($courseid == SITEID) {
            return '';
        }

        $users = [];
        $instructors = [];
        $alreadylisted = [];
        $context = \context_course::instance($courseid);
        $roles = $DB->get_records_list('role', 'shortname', ['teacher', 'editingteacher']);
        $userfields = 'u.id,u.picture,u.firstname,u.lastname,u.firstnamephonetic,u.lastnamephonetic,u.middlename,u.alternatename,u.imagealt,u.email';

        foreach ($roles as $role) {
            if ($role) {
                $users = array_merge($users, get_role_users(roleid: $role->id, context: $context, fields: $userfields));
            }
        }

        foreach ($users as $user) {
            if (!in_array($user->id, $alreadylisted)) {
                $instructorurl = new moodle_url('/user/view.php', ['id' => $user->id, 'course' => $courseid]);
                $instructor = new \stdClass();
                $instructor->fullname = fullname($user);
                $instructor->picturesrc = "$CFG->wwwroot/user/pix.php/$user->id/f2.jpg";
                $instructor->instructorurl = $instructorurl->out();
                $instructors[] = $instructor;
                $alreadylisted[] = $user->id;
            }
        }

        return $instructors;
    }

    public function render_coursehint_enrol(\theme_urcourses_default\output\coursehint_enrol $coursehint_enrol) {
        $data = $coursehint_enrol->export_for_template($this);
        return $this->render_from_template('theme_urcourses_default/course-hint-enrol', $data);
    } 
}