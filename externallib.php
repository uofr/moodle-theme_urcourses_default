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
 * Webservices for Boost Campus.
 *
 * @package    theme_urcourses_default
 * @author     John Lane
 *
 */

use block_recentlyaccesseditems\external;
use Symfony\Component\Console\Descriptor\JsonDescriptor;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/course/lib.php");
require_once($CFG->dirroot . "/course/externallib.php");
require_once($CFG->dirroot . "/user/lib.php");
if (is_file($CFG->dirroot.'/blocks/urcourserequest/lib.php')){
    require_once($CFG->dirroot.'/blocks/urcourserequest/lib.php');
}

require_once($CFG->dirroot.'/theme/urcourses_default/locallib.php');

class theme_urcourses_default_external extends external_api {

    /**
     * Describes upload_course_image parameters.
     * @return external_function_parameters
     */
    public static function upload_course_image_parameters() {
        return new external_function_parameters(
            array(
            'courseid' => new external_value(PARAM_INT),
            'imagedata' => new external_value(PARAM_TEXT),
            'imagename' => new external_value(PARAM_TEXT),
            )
        );
    }

    public static function choose_header_style_parameters() {
        return new external_function_parameters(
            array(
            'courseid' => new external_value(PARAM_INT),
            'headerstyle' => new external_value(PARAM_INT),
            )
        );
    }

    public static function toggle_course_availability_parameters() {
        return new external_function_parameters(
            array(
            'courseid' => new external_value(PARAM_INT),
            'availability' => new external_value(PARAM_INT),
            )
        );
    }

    /**
     * Describes upload_couse_image return value.
     * @return external_single_structure
     */
    public static function upload_course_image_returns() {
        return new external_single_structure(array('success' => new external_value(PARAM_BOOL)));
    }

    /**
     * Describes choose_header_style return value.
     * @return external_single_structure
     */
    public static function choose_header_style_returns() {
        return new external_single_structure(array('success' => new external_value(PARAM_INT)));
    }

    /**
     * Describes choose_header_style return value.
     * @return external_single_structure
     */
    public static function toggle_course_availability_returns() {
        return new external_single_structure(array('success' => new external_value(PARAM_BOOL)));
    }

    /**
     * Changes course header image.
     * @param int $courseid - ID of course.
     * @param string $imagedata - Raw image data.
     * @param string $imagename - Name of image.
     * @return bool $success - Whether or not the image was uploaded properly.
     */
    public static function upload_course_image($courseid, $imagedata, $imagename) {
        global $CFG;

        // get params
        $params = self::validate_parameters(
            self::upload_course_image_parameters(),
            array(
            'courseid' => $courseid,
            'imagedata' => $imagedata,
            'imagename' => $imagename,
            )
        );

        // ensure user has permissions to change image
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('moodle/course:changesummary', $context);

        $filestorage = get_file_storage();
        $filetype = strtolower(pathinfo($params['imagename'], PATHINFO_EXTENSION));
        $filetype === 'jpeg' ? 'jpg' : $filetype;
        $new_filename = 'courseimage_'.time().'.' . $filetype;

        $binary_data = base64_decode($params['imagedata']);

        // verify size
        if (strlen($binary_data) > get_max_upload_file_size($CFG->maxbytes)) {
            throw new \moodle_exception('error:courseimageexceedsmaxbytes', 'theme_urcourses_default', $CFG->maxbytes);
        }

        // verify filetype
        if ($filetype !== 'jpg' && $filetype !== 'png' && $filetype !== 'gif') {
            throw new \moodle_exception('error:courseimageinvalidfiletype', 'theme_urcourses_default');
        }

        if ($context->contextlevel === CONTEXT_COURSE) {
            $fileinfo = array(
                'contextid' => $context->id,
                'component' => 'course',
                'filearea' => 'overviewfiles',
                'itemid' => 0,
                'filepath' => '/',
                'filename' => $new_filename
            );

            // Remove any old course summary image files for this context.
            $filestorage->delete_area_files($context->id, $fileinfo['component'], $fileinfo['filearea']);

            // Create and set new image.
            $storedfile = $filestorage->create_file_from_string($fileinfo, $binary_data);
            $success = $storedfile instanceof \stored_file;
        }

        // return
        return array('success' => $success);
    }


    public static function choose_header_style($courseid, $headerstyle) {
        global $CFG, $DB;

        // get params
        $params = self::validate_parameters(
            self::choose_header_style_parameters(),
            array(
            'courseid' => $courseid,
            'headerstyle' => $headerstyle,
            )
        );

        // ensure user has permissions to change image
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('moodle/course:changesummary', $context);

        //update the db
        $table = 'theme_urcourses_hdrstyle';

        $newrecord = new stdClass();
        $newrecord->courseid = $courseid;
        $newrecord->hdrstyle = $headerstyle;

        //database check if user has a record, insert if not
        if ($record = $DB->get_record($table, array('courseid'=>$courseid))) {
            //if has a record, update record to $setdarkmode

            $newrecord->id = $record->id;
            $success = $DB->update_record($table, $newrecord);
        } else {
            //create a record
            $success = $DB->insert_record($table, $newrecord);
        }

        return array('success' => $success);

    }

    public static function toggle_course_availability($courseid, $availability) {
        global $CFG, $DB;

        // get params
        $params = self::validate_parameters(
            self::toggle_course_availability_parameters(),
            array(
            'courseid' => $courseid,
            'availability' => $availability,
            )
        );

        // ensure user has permissions to change image
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('moodle/course:changesummary', $context);

        $table = 'course';

        $newrecord = new stdClass();
        $newrecord->courseid = $courseid;
        $newrecord->visible = $availability==1?0:1;

        if ($record = $DB->get_record($table, array('id'=>$courseid))) {
            $newrecord->id = $record->id;
            $success = $DB->update_record($table, $newrecord);

            return array('success' => $success);
        } else {
            return array('error' => 'Record not found');
        }
    }

    public static function user_is_instructor_parameters() {
        return new external_function_parameters([]);
    }

    public static function user_is_instructor() {
        return theme_urcourses_default_user_is_instructor();
    }

    public static function user_is_instructor_returns() {
        return new external_value(PARAM_BOOL);
    }

    /**
     * Returns description of get_landing_page's parameters.
     *
     * @return external_function_parameters
     */
    public static function get_landing_page_parameters() {
        return new external_function_parameters(array(
            'contextid' => new external_value(PARAM_INT),
            'localurl' => new external_value(PARAM_TEXT)
        ));
    }

    /**
     * Returns landing page data for help modal.
     *
     * @param int $courseid
     * @param int $currenturl
     * @return array
     */
    public static function get_landing_page($contextid, $localurl) {
        global $PAGE;

        $params = self::validate_parameters(self::get_landing_page_parameters(), array(
            'contextid' => $contextid,
            'localurl' => $localurl
        ));

        $context = context::instance_by_id($params['contextid']);
        self::validate_context($context);

        $is_instructor = theme_urcourses_default_user_is_instructor();
        $url = $is_instructor ? new moodle_url('/guides/instructor.json') : new moodle_url('/guides/student.json');
        $target = '';
        if ($is_instructor) {
            if (strpos($params['localurl'], '/course/') === 0) {
                $url = new moodle_url('/guides/instructor/courseadministration.json');
            }
            else if (strpos($params['localurl'], '/mod/assign/') === 0) {
                $url = new moodle_url('/guides/instructor/assignments.json');
                $target = '#assignment_activity';
            }
            else if (strpos($params['localurl'], '/mod/turnitintooltwo/') === 0) {
                $url = new moodle_url('/guides/instructor/assignments.json');
                $target = '#turnitin';
            }
            else if (strpos($params['localurl'], '/mod/kalvidassign/') === 0) {
                $url = new moodle_url('/guides/instructor/assignments.json');
                $target = '#media';
            }
            else if (strpos($params['localurl'], '/mod/attendance/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#attendance';
            }
            else if (strpos($params['localurl'], '/mod/oublog/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#blog';
            }
            else if (strpos($params['localurl'], '/mod/chat/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#chat';
            }
            else if (strpos($params['localurl'], '/mod/choice/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#choice';
            }
            else if (strpos($params['localurl'], '/mod/mail/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#course_email';
            }
            else if (strpos($params['localurl'], '/mod/data/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#database';
            }
            else if (strpos($params['localurl'], '/mod/feedback/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#feedback';
            }
            else if (strpos($params['localurl'], '/mod/glossary/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#glossary';
            }
            else if (strpos($params['localurl'], '/mod/lesson/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#lesson';
            }
            else if (strpos($params['localurl'], '/mod/questionnaire/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#questionnaire';
            }
            else if (strpos($params['localurl'], '/mod/scorm/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#scorm';
            }
            else if (strpos($params['localurl'], '/mod/survey/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#survey';
            }
            else if (strpos($params['localurl'], '/mod/wiki/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#wiki';
            }
            else if (strpos($params['localurl'], '/mod/workshop/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#workshop';
            }
            else if (strpos($params['localurl'], '/mod/lti/') === 0) {
                $url = new moodle_url('/guides/instructor/more.json');
                $target = '#external_tool';
            }
            else if (strpos($params['localurl'], '/mod/imscp/') === 0) {
                $url = new moodle_url('/guides/instructor/more.json');
                $target = '#ims';
            }
            else if (strpos($params['localurl'], '/mod/lightboxgallery/') === 0) {
                $url = new moodle_url('/guides/instructor/more.json');
                $target = '#lightbox_gallery';
            }
            else if (strpos($params['localurl'], '/mod/scheduler/') === 0) {
                $url = new moodle_url('/guides/instructor/more.json');
                $target = '#scheduler';
            }
            else if (strpos($params['localurl'], '/mod/skype/') === 0) {
                $url = new moodle_url('/guides/instructor/more.json');
                $target = '#skype';
            }
            else if (strpos($params['localurl'], '/mod/book/') === 0) {
                $url = new moodle_url('/guides/instructor/resources.json');
                $target = '#book';
            }
            else if (strpos($params['localurl'], '/mod/folder/') === 0) {
                $url = new moodle_url('/guides/instructor/resources.json');
                $target = '#folder';
            }
            else if (strpos($params['localurl'], '/mod/kalvidres/') === 0) {
                $url = new moodle_url('/guides/instructor/resources.json');
                $target = '#media';
            }
            else if (strpos($params['localurl'], '/mod/page/') === 0) {
                $url = new moodle_url('/guides/instructor/resources.json');
                $target = '#page';
            }
            else if (strpos($params['localurl'], '/local/mymedia/') === 0) {
                $url = new moodle_url('/guides/instructor/kaltura.json');
                $target = '#my_media';
            }
            else if (strpos($params['localurl'], '/mod/quiz/') === 0) {
                $url = new moodle_url('/guides/instructor/quizzes.json');
            }
            else if (strpos($params['localurl'], '/mod/forum/') === 0) {
                $url = new moodle_url('/guides/instructor/forums.json');
            }
            else if (strpos($params['localurl'], '/mod/zoom/') === 0) {
                $url = new moodle_url('/guides/instructor/zoom.json');
            }
        } else {
            if (strpos($params['localurl'], '/mod/mail/') === 0) {
                $url = new moodle_url('/guides/student/courseemail.json');
            }
            else if (strpos($params['localurl'], '/mod/forum/') === 0) {
                $url = new moodle_url('/guides/student/forums.json');
            }
            else if (strpos($params['localurl'], '/mod/chat/') === 0) {
                $url = new moodle_url('/guides/student/chat.json');
            }
            else if (strpos($params['localurl'], '/mod/zoom/') === 0) {
                $url = new moodle_url('/guides/student/zoom.json');
            }
            else if (strpos($params['localurl'], '/mod/assign/') === 0) {
                $url = new moodle_url('/guides/student/assignments.json');
            }
            else if (strpos($params['localurl'], '/mod/turnitintooltwo/') === 0) {
                $url = new moodle_url('/guides/student/turnitin.json');
            }
            else if (strpos($params['localurl'], '/mod/quiz/') === 0) {
                $url = new moodle_url('/guides/student/quizzesexams.json');
            }
            else if (strpos($params['localurl'], '/mod/oublog/') === 0) {
                $url = new moodle_url('/guides/student/blogs.json');
            }
            else if (strpos($params['localurl'], '/mod/wiki/') === 0) {
                $url = new moodle_url('/guides/student/wikis.json');
            }
            else if (strpos($params['localurl'], '/mod/book/') === 0) {
                $url = new moodle_url('/guides/student/bookshelf.json');
            }
        }

        return [
            'url' => $url->get_path(),
            'target' => $target
        ];

    }

    /**
     * Returns description of get_landing_page return value.
     *
     * @return external_single_structure
    */
    public static function get_landing_page_returns() {
        return new external_single_structure([
            'url' => new external_value(PARAM_TEXT),
            'target' => new external_value(PARAM_TEXT, '', VALUE_OPTIONAL, '')
        ]);
    }

    /**
     * Returns description of get_topic_list's parameters.
     *
     * @return external_function_parameters
     */
    public static function get_topic_list_parameters() {
        return new external_function_parameters(array(
            'contextid' => new external_value(PARAM_INT)
        ));
    }

    /**
     * Gets list of help topics from the guides.
     *
     * @return array
     */
    public static function get_topic_list($contextid) {
        $params = self::validate_parameters(self::get_topic_list_parameters(), array(
            'contextid' => $contextid
        ));

        $context = context::instance_by_id($params['contextid']);
        self::validate_context($context);

        $content_url_absolute = new moodle_url('/guides/social/sample-b.json');

        return [
            'url' => $content_url_absolute->get_path(),
            'instructor' => theme_urcourses_default_user_is_instructor($context)
        ];
    }

    /**
     * Returns description of get_topic_list return value.
     *
     * @return external_multiple_structure
     */
    public static function get_topic_list_returns() {
        return new external_single_structure([
            'url' => new external_value(PARAM_TEXT),
            'instructor' => new external_value(PARAM_BOOL)
        ]);
    }

    /**
     * Returns description of params passed to get_guide_page.
     *
     * @return external_function_parameters
     */
    public static function get_guide_page_parameters() {
        return new external_function_parameters(array(
            'url' => new external_value(PARAM_TEXT),
            'contextid' => new external_value(PARAM_INT)
        ));
    }

    /**
     * Returns guide page data.
     *
     * @param string $url 
     */
    public static function get_guide_page($url, $contextid) {
        $params = self::validate_parameters(self::get_guide_page_parameters(), array(
            'url' => $url,
            'contextid' => $contextid
        ));

        $context = context::instance_by_id($params['contextid']);
        self::validate_context($context);

        // get url of format /guides/.../page.json
        $relative_path = substr($params['url'], strpos($params['url'], '/guides/'));
        $url_trimmed = (strpos($relative_path, '/index.html') !== false)
            ? str_replace('/index.html', '', $relative_path)
            : rtrim($relative_path, '/');
        list($content_url, $target) = explode('#', $url_trimmed);
        $target = $target ? "#$target" : null;
        
        $content_url = new moodle_url($content_url . '.json');
        $json_output = json_decode(file_get_contents($content_url));
        $html = ($json_output->content) ? $json_output->content : $json_output->jsondata->page_data[0]->content;
        $title = $json_output->jsondata ? $json_output->jsondata->page_data[0]->title : '';

        // convert links in the html
        //$base = $context->contextlevel === CONTEXT_MODULE ? '../../guides/' : '../guides/';

        //$reg1 = '/\.\/|\.\.\//';
        //$reg2 = '/href="\b(?!https|http\b)/';
        //$reg3 = '/src="\b(?!https|http\b)/';
 
        //$html = preg_replace($reg1, $base, $html);
        //$html = preg_replace($reg2, 'href="' . $base, $html);





        //$html = preg_replace($reg3, 'src="' . $base, $html);

        return array(
            'html' => $html,
            'title' => $title,
            'target' => $target
        );
    }

    /**
     * Returns description of get_guide_page return value.
     *
     * @return external_single_structure
     */
    public static function get_guide_page_returns() {
        return new external_single_structure(array(
            'html' => new external_value(PARAM_RAW),
            'title' => new external_value(PARAM_TEXT),
            'target' => new external_value(PARAM_TEXT, '', VALUE_OPTIONAL, null)
        ));
    }

    /**
     * Returns description of modal_help_search's parameters.
     *
     * @return external_function_parameters
     */
    public static function modal_help_search_parameters() {
        return new external_function_parameters(array(
            'contextid' => new external_value(PARAM_INT),
            'query' => new external_value(PARAM_TEXT)
        ));
    }

    /**
     * Searches the guides.
     *
     * @param int $contextid
     * @param string $query
     * @return array
     */
    public static function modal_help_search($contextid, $query) {
        $params = self::validate_parameters(self::modal_help_search_parameters(), array(
            'contextid' => $contextid,
            'query' => $query
        ));

        $context = context::instance_by_id($params['contextid']);
        self::validate_context($context);

        $query = urlencode($params['query']);
        $search_url = new moodle_url("/guides/search.json/query:$query");
        return $search_url->get_path();
    }

    /**
     * Returns description of modal_help_serach's return value.
     *
     * @return external_single_structure
     */
    public static function modal_help_search_returns() {
        return new external_value(PARAM_TEXT);
    }

    /**
     * Returns description of create_test_account's parameters.
     *
     * @return external_function_parameters
     */
    public static function create_test_account_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT),
            'username' => new external_value(PARAM_TEXT),
            'checked' => new external_value(PARAM_BOOL),
        ));
    }
     /**
     * Creates test student account and enrols in current course
     *
     * @param int $courseid
     * @param int $username of current user
     * @return array
     */
    public static function create_test_account($courseid,$username, $checked) {
        global $USER, $DB, $CFG;

        // get params
        $params = self::validate_parameters(
            self::create_test_account_parameters(),
            array(
            'courseid' => $courseid,
            'username' => $username,
            'checked' => $checked,
            )
        );

        // ensure user has permissions to change image
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);

        //get username to create email
        $email = $USER->username."+urstudent@uregina.ca";
        //check if test user account has already been created
        $select = 'SELECT * FROM mdl_user WHERE email ='.$email.';';

        $sql = "SELECT * FROM mdl_user as u WHERE u.email ='{$email}'";
	
        $user = $DB->get_record_sql($sql);

        //if created
        if($user){

            //check if user is enrolled already
            $isenrolled =is_enrolled($context, $user, 'mod/assignment:submit');

            if($isenrolled){
                $return=array("userid"=>$user->id,"username"=>$user->username,"enrolled"=>false,"created"=>false );
            }else{
                //get enroll id for manual enrollment for current course
                $sql = "SELECT * FROM mdl_enrol WHERE courseid =".$params['courseid']." AND enrol = 'manual';";
                $enroll = $DB->get_record_sql($sql, null, MUST_EXIST);

                if(!$enroll){
                    $enrolid = $DB->insert_record('enrol', (object)array(
                        'enrol' => 'manual',
                        'courseid' => $params['courseid'],
                        'roleid' => 5
                    ));
                }else{
                    $enrolid = $enroll->id;
                }

                $sql = "SELECT * FROM mdl_role WHERE shortname = 'student';";
                $role = $DB->get_record_sql($sql, null, MUST_EXIST);
                //enroll user in course as student
                $dataobject = array(
                    "status"=>0,
                    "enrolid"=>$enrolid,
                    "userid"=>$user->id,
                    "timestart"=>time(),
                    "timeend"=>0,
                    "modififerid"=>$USER->id,
                    "timecreated"=>time(),
                    "timemodified"=>time()
                );
                $isenrolled =$DB->insert_record("user_enrolments", $dataobject, true, false);

                $dataobject = array(
                    "roleid"=>$role->id,
                    "contextid"=>$context->id,
                    "userid"=>$user->id,
                    "timemodified"=>time(),
                    "modififerid"=>$USER->id,
                    "itemid"=>0,
                    "sortorder"=>0
                );
                $roleassigned =$DB->insert_record("role_assignments", $dataobject, true, false);

                $return=array("userid"=>$user->id,"username"=>$user->username,"enrolled"=>$isenrolled,"created"=>false );
            }
        }else{
            //if not created
            //get User password & other info
            $sql = "SELECT * FROM mdl_user as u WHERE u.id ={$USER->id}";
            $user = $DB->get_record_sql($sql);

            //replace User email with new email same for username
            $user->email = $email;
            $user->username= $user->username."-urstudent";
            $user->lastname= $user->lastname." (urstudent)";
            $user->auth= "manual";
        
            //call external create user function
            $userid = user_create_user($user, false, true);
            $sql = "SELECT * FROM mdl_user as u WHERE u.id ={$userid}";
            $user = $DB->get_record_sql($sql);

            //reset password and email out to new user
            //reset_password_and_mail($user);

            $site  = get_site();
            $supportuser = core_user::get_support_user();
        
            $userauth = get_auth_plugin($user->auth);
            if (!$userauth->can_reset_password() or !is_enabled_auth($user->auth)) {
                trigger_error("Attempt to reset user password for user $user->username with Auth $user->auth.");
                $return=array("userid"=>$userid,"username"=>$user->username,"enrolled"=>false,"created"=>false );
            }
        
            $newpassword = generate_password();
        
            if (!$userauth->user_update_password($user, $newpassword)) {
                print_error("cannotsetpassword");
            }
        
            $a = new stdClass();
            $a->firstname   = $user->firstname;
            $a->lastname    = $user->lastname;
            $a->sitename    = format_string($site->fullname);
            $a->username    = $user->username;
            $a->newpassword = $newpassword;
            $a->link        = $CFG->wwwroot .'/login';
            $a->signoff     = generate_email_signoff();
        
            $message = get_string('newtestaccount', 'theme_urcourses_default', $a);
        
            $subject  = format_string($site->fullname) .': '. get_string('newtestuser','theme_urcourses_default');
        
            unset_user_preference('create_password', $user); // Prevent cron from generating the password.
        
            // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
            $issent = email_to_user($user, $supportuser, $subject, $message);
            if(!$issent){
                trigger_error("Could not send email to $user->username");
            }

             //forced password change
			set_user_preference('auth_forcepasswordchange', 1, $user);

            if($checked){
                //enroll user in course

                //get enroll id for manual enrollment for current course
                $sql = "SELECT * FROM mdl_enrol WHERE courseid =".$params['courseid']." AND enrol = 'manual';";
                $enroll = $DB->get_record_sql($sql, null, MUST_EXIST);

                if(!$enroll){
                    $enrolid = $DB->insert_record('enrol', (object)array(
                        'enrol' => 'manual',
                        'courseid' => $params['courseid'],
                        'roleid' => 5
                    ));
                }else{
                    $enrolid = $enroll->id;
                }

                $sql = "SELECT * FROM mdl_role WHERE shortname = 'student';";
                $role = $DB->get_record_sql($sql, null, MUST_EXIST);

                //enroll user in course as student
                $dataobject = array(
                    "status"=>0,
                    "enrolid"=>$enrolid,
                    "userid"=>$userid,
                    "timestart"=>time(),
                    "timeend"=>0,
                    "modififerid"=>$USER->id,
                    "timecreated"=>time(),
                    "timemodified"=>time()
                );
                $isenrolled =$DB->insert_record("user_enrolments", $dataobject, true, false);

                $dataobject = array(
                    "roleid"=>$role->id,
                    "contextid"=>$context->id,
                    "userid"=>$userid,
                    "timemodified"=>time(),
                    "modififerid"=>$USER->id,
                    "itemid"=>0,
                    "sortorder"=>0
                );
                $roleassigned = $DB->insert_record("role_assignments", $dataobject, true, false);

                //return userid, username, created true, and enrolled true
                $return=array("userid"=>$userid,"username"=>$user->username,"enrolled"=>$isenrolled,"created"=>true );
            }else{
                $return=array("userid"=>$userid,"username"=>$user->username,"enrolled"=>false,"created"=>true );
            }
            
        }
        return $return;
    }

      /**
     * Returns description of create_test_account return value.
     *
     * @return external_single_structure
     */
    public static function create_test_account_returns() {
        return new external_single_structure(array(
            'userid' => new external_value(PARAM_INT),
            'username' => new external_value(PARAM_TEXT),
            'enrolled' => new external_value(PARAM_TEXT),
            'created' => new external_value(PARAM_TEXT)
        ));
    }

    /**
     * Returns description of create_test_account's parameters.
     *
     * @return external_function_parameters
     */
    public static function unenroll_test_account_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT),
            'username' => new external_value(PARAM_TEXT)
        ));
    }
     /**
     * Creates test student account and enrols in current course
     *
     * @param int $courseid
     * @param int $username of current user
     * @return array
     */
    public static function unenroll_test_account($courseid,$username) {
        global $USER, $DB, $CFG;

        // get params
        $params = self::validate_parameters(
            self::unenroll_test_account_parameters(),
            array(
            'courseid' => $courseid,
            'username' => $username,
            )
        );

        // ensure user has permissions to change image
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);

        //get username to create email
        $email = $USER->username."+urstudent@uregina.ca";
        //check if test user account has already been created
        $select = 'SELECT * FROM mdl_user WHERE email ='.$email.';';

        $sql = "SELECT * FROM mdl_user as u WHERE u.email ='{$email}'";
	
        $user = $DB->get_record_sql($sql);

        //if created
        if($user){

            //check if user is enrolled 
            $isenrolled =is_enrolled($context, $user, 'mod/assignment:submit');

            if($isenrolled){
                //get enroll id for manual enrollment for current course
                $sql = "SELECT * FROM mdl_enrol WHERE courseid =".$params['courseid']." AND enrol = 'manual';";
                $enroll = $DB->get_record_sql($sql, null, MUST_EXIST);

                $sql = "SELECT * FROM mdl_role WHERE shortname = 'student';";
                $role = $DB->get_record_sql($sql, null, MUST_EXIST);
                //enroll user in course as student
                $dataobject = array( 
                    "enrolid"=>$enroll->id,
                    "userid"=>$user->id,        
                );
                $unenrolled = $DB->delete_records("user_enrolments", $dataobject);

                if($unenrolled){
                    $dataobject = array(
                        "roleid"=>$role->id,
                        "contextid"=>$context->id,
                        "userid"=>$user->id,
                    );
               
                    $roleassigned = $DB->delete_records("role_assignments", $dataobject);

                    return array("userid"=>$user->id,"username"=>$user->username,"unenroll"=>$roleassigned);
                }
            }else{
                return array("userid"=>$user->id,"username"=>$user->username,"unenroll"=>true); 
            }
        }
        
        return array("userid"=>$user->id,"username"=>$user->username,"unenroll"=>false); 
    }

    /**
     * Returns description of unenroll_test_account return value.
     *
     * @return external_single_structure
     */
    public static function unenroll_test_account_returns() {
        return new external_single_structure(array(
            'userid' => new external_value(PARAM_INT),
            'username' => new external_value(PARAM_TEXT),
            'unenroll' => new external_value(PARAM_BOOL),
        ));
    }
     /**
     * Returns description of create_test_account's parameters.
     *
     * @return external_function_parameters
     */
    public static function test_account_info_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT),
            'username' => new external_value(PARAM_TEXT)
        ));
    }
     /**
     * Creates test student account and enrols in current course
     *
     * @param int $courseid
     * @param int $username of current user
     * @return array
     */
    public static function test_account_info($courseid,$username) {
        global $USER, $DB, $CFG;

        // get params
        $params = self::validate_parameters(
            self::test_account_info_parameters(),
            array(
            'courseid' => $courseid,
            'username' => $username,
            )
        );

        // ensure user has permissions to change image
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);

        //get username to create email
        $email = $USER->username."+urstudent@uregina.ca";

        //check if test user account has already been created
        $select = 'SELECT * FROM mdl_user WHERE email ='.$email.';';

        $sql = "SELECT * FROM mdl_user as u WHERE u.email ='{$email}'";
	
        $user = $DB->get_record_sql($sql);

        //if created
        if($user){
            return array("userid"=>$user->id,"username"=>$user->username,"email"=>$user->email,"datecreated"=> userdate($user->timecreated)); 
        }
        
        return array("userid"=>0,"username"=>"","email"=>"","datecreated"=>""); 
    }

    /**
     * Returns description of test_account_info return value.
     *
     * @return external_single_structure
     */
    public static function test_account_info_returns() {
        return new external_single_structure(array(
            'userid' => new external_value(PARAM_INT),
            'username' => new external_value(PARAM_TEXT),
            'email' => new external_value(PARAM_TEXT),
            'datecreated' => new external_value(PARAM_TEXT),
        ));
    }

      /**
     * Returns description of reset_test_account's parameters.
     *
     * @return external_function_parameters
     */
    public static function reset_test_account_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT),
            'username' => new external_value(PARAM_TEXT)
        ));
    }
     /**
     * Creates test student account and enrols in current course
     *
     * @param int $courseid
     * @param int $username of current user
     * @return array
     */
    public static function reset_test_account($courseid,$username) {
        global $USER, $DB, $CFG;

        // get params
        $params = self::validate_parameters(
            self::reset_test_account_parameters(),
            array(
            'courseid' => $courseid,
            'username' => $username,
            )
        );

        // ensure user has permissions to change image
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);

        //get username to create email
        $email = $USER->username."+urstudent@uregina.ca";

        //check if test user account has already been created
        $select = 'SELECT * FROM mdl_user WHERE email ='.$email.';';

        $sql = "SELECT * FROM mdl_user as u WHERE u.email ='{$email}'";
	
        $user = $DB->get_record_sql($sql);
    
        //if created
        if($user){
            //reset password and email out to new user
            $reset = reset_password_and_mail($user);
            return array("userid"=>$user->id, "username"=>$user->username,"reset"=>$reset); 
        }
        
        return array("userid"=>0,"reset"=>false); 
    }

    /**
     * Returns description of reset_test_account return value.
     *
     * @return external_single_structure
     */
    public static function reset_test_account_returns() {
        return new external_single_structure(array(
            'userid' => new external_value(PARAM_INT),
            'username' => new external_value(PARAM_TEXT),
            'reset' => new external_value(PARAM_BOOL),
        ));
    }

    /**
     * Returns description of duplicate_course's parameters.
     *
     * @return external_function_parameters
     */
    public static function duplicate_course_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT),
            'coursename' => new external_value(PARAM_TEXT),
            'shortname' => new external_value(PARAM_TEXT),
            'categoryid' => new external_value(PARAM_INT),
            'startdate' => new external_value(PARAM_TEXT),
            'enddate' => new external_value(PARAM_TEXT),
        ));
    }
     /**
     * Creates a new course, imports activites from current course to duplicate
     *
     * @param int $courseid
     * @param string $coursename of new course
     * @param string $shortname of new course
     * @param int $categoryid of new course
     * @return array
     */
    public static function duplicate_course($courseid,$coursename,$shortname,$categoryid,$startdate,$enddate) {
        global $USER, $DB, $CFG;
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        // get params
        $params = self::validate_parameters(
            self::duplicate_course_parameters(),
            array(
            'courseid' => $courseid,
            'coursename' => $coursename,
            'shortname' => $shortname,
            'categoryid'=> $categoryid,
            'startdate' => $startdate,
            'enddate' => $enddate,
            )
        );


        $admins = get_admins(); 
        $isadmin = false; 
        foreach ($admins as $admin) { 
            if ($USER->id == $admin->id) 
            { $isadmin = true; break; } } 

        //check if user has Instructional Designer role in course
        $context = \context_course::instance($params['courseid']);
        // ensure user has permissions to duplicate
        self::validate_context($context);
        $roles = get_user_roles($context, $USER->id, true);
        $role = key($roles);
        $rolename = $roles[$role]->shortname;
            
        $isid = false; 

        if($rolename == "instdesigner"  ){
            $isid=true;
        }

        //split dates and get appropriate timestamp
        $start =  explode("-", $params['startdate']);
        
        $starttimestamp = make_timestamp($start[2],$start[1],$start[0],$start[3],$start[4]);
        $endtimestamp=0;
        if($params['enddate'] != "0"){
            $end =  explode("-", $params['enddate']);
            $endtimestamp = make_timestamp($end[2],$end[1],$end[0],$end[3],$end[4]);
        }

        //get course from DB
        $sql = "SELECT * FROM mdl_course as c WHERE c.id ='{$params['courseid']}'";
        $course = $DB->get_record_sql($sql);
        
        $newcourse =  new stdClass();
        //add in new variables, clear id
        $newcourse->id = "";
        $newcourse->startdate =$starttimestamp;
        $newcourse->enddate =$endtimestamp;

        if($course->idnumber == ""){
            $newcourse->idnumber = self:: create_course_idnumber($params['shortname'],$USER->username, 001);
        }else{
            //grab and split id to get last $version number
            $idnumber = $course->idnumber;
            $idpieces = explode("_", $idnumber);
            $subject = $idpieces[0];
            $name = $idpieces[1];
            $version = $idpieces[2];

            //check the format 
            if(count($idpieces)> 3){
                $subject = $idpieces[0];
                $name = $idpieces[2];
                $version = $idpieces[3];
            }

            //check for proper ownership before continuing
            if($USER->username != $name && !$isadmin && !$isid){
                $sql = "SELECT * FROM mdl_user as u WHERE u.username ='{$name}'";
                $owner = $DB->get_record_sql($sql);
                return array('courseid'=>0, 'url'=>"", 'error'=>"Please request a copy of this course from owner ",'user'=>fullname($owner));
            }
            
            //check format, if not ### replace with 001
            $pattern = "/^[0-9]{3}$/";
            if(!preg_match($pattern, $version)){
                $version = 001;
            }else{
                $version = $version +1;
            }
            //begin loop to check if record exits with that id number
            while ($DB->record_exists("course", array("idnumber"=>$subject."_".$name."_".str_pad($version,3,"0",STR_PAD_LEFT)))) {
                //increment until id not found
                $version = $version +1;
            }

            $newcourse->idnumber = $subject."_".$name."_".str_pad($version,3,"0",STR_PAD_LEFT);
        }

        $visible =0;        
        $backupdefaults = array(
            'activities' => 1,
            'blocks' => 1,
            'filters' => 1,
            'users' => 0,
            //'enrolments' => 0,
            'role_assignments' => 0,
            'comments' => 0,
            'userscompletion' => 0,
            'logs' => 0,
            'grade_histories' => 0
        );

        $backupsettings = array();
        $adminuser = get_admin();

        // The backup controller check for this currently, this may be redundant.
        if(!$isid && !$isadmin){
            require_capability('moodle/course:create', $context);
            require_capability('moodle/restore:restorecourse', $context);
            require_capability('moodle/backup:backupcourse', $context);
        }

        // Check if the shortname is used.
        if ($foundcourses = $DB->get_records('course', array('shortname'=>$params['shortname']))) {
            foreach ($foundcourses as $foundcourse) {
                $foundcoursenames[] = $foundcourse->fullname;
            }

            $foundcoursenamestring = implode(',', $foundcoursenames);
            throw new moodle_exception('shortnametaken', '', '', $foundcoursenamestring);
        }

        // Backup the course.
        $bc = new backup_controller(backup::TYPE_1COURSE, $course->id, backup::FORMAT_MOODLE,
        backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $adminuser->id);

        foreach ($backupdefaults as $name => $value) {
            if ($setting = $bc->get_plan()->get_setting($name)) {
                $bc->get_plan()->get_setting($name)->set_value($value);
            }
        }

        $backupid       = $bc->get_backupid();
        $backupbasepath = $bc->get_plan()->get_basepath();

        $bc->execute_plan();
        $results = $bc->get_results();
        $file = $results['backup_destination'];

        $bc->destroy();

        // Restore the backup immediately.

        // Check if we need to unzip the file because the backup temp dir does not contains backup files.
        if (!file_exists($backupbasepath . "/moodle_backup.xml")) {
            $file->extract_to_pathname(get_file_packer('application/vnd.moodle.backup'), $backupbasepath);
        }

       
        // Create new course.
        $newcourseid = restore_dbops::create_new_course($params['coursename'], $params['shortname'], $params['categoryid']);

        $rc = new restore_controller($backupid, $newcourseid,
                backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $adminuser->id, backup::TARGET_NEW_COURSE);

        foreach ($backupsettings as $name => $value) {
            $setting = $rc->get_plan()->get_setting($name);
            if ($setting->get_status() == backup_setting::NOT_LOCKED) {
                $setting->set_value($value);
            }
        }

        if (!$rc->execute_precheck()) {
            $precheckresults = $rc->get_precheck_results();
            if (is_array($precheckresults) && !empty($precheckresults['errors'])) {
                if (empty($CFG->keeptempdirectoriesonbackup)) {
                    fulldelete($backupbasepath);
                }

                $errorinfo = '';

                foreach ($precheckresults['errors'] as $error) {
                    $errorinfo .= $error;
                }

                if (array_key_exists('warnings', $precheckresults)) {
                    foreach ($precheckresults['warnings'] as $warning) {
                        $errorinfo .= $warning;
                    }
                }
                throw new moodle_exception('backupprecheckerrors', 'webservice', '', $errorinfo);
            }
        }

        $rc->execute_plan();
        $rc->destroy();

        $course = $DB->get_record('course', array('id' => $newcourseid), '*', MUST_EXIST);
        $course->fullname = $params['coursename'];
        $course->shortname = $params['shortname'];
        $course->visible = $visible;

        // Set shortname and fullname back.
        $DB->update_record('course', $course);

        if (empty($CFG->keeptempdirectoriesonbackup)) {
            fulldelete($backupbasepath);
        }

        // Delete the course backup file created by this WebService. Originally located in the course backups area.
        $file->delete();


        $newcourse->id = $newcourseid;

        //update id number and date of course
        $DB->update_record("course", $newcourse, false);

        //check if instructor is enrolled in the template
        $newcontext = \context_course::instance($newcourse->id);
        
        $ogisenrolled =true; 
        if(!$isadmin){
            $ogisenrolled =is_enrolled($newcontext, $USER->id);
            //if not enroll the instructor in template
            if(!$ogisenrolled){
                $isenrolled = self:: enroll_user($params['courseid'], $newcourse->id,$USER->id);
                if(!$isenrolled)
                {
                    return array('courseid'=>0, 'url'=>"","error"=>"Was unable to enroll user in course",'user'=>"");
                }
            }
        }
        
         //return new course id & url
         $url = $CFG->wwwroot.'/course/view.php?id=' . $newcourse->id;
 
         return array('courseid'=>$newcourse->id, 'url'=>$url, 'error'=>"",'user'=>"");
    }
      /**
     * Returns description of duplicate_course return value.
     *
     * @return external_single_structure
     */
    public static function duplicate_course_returns() {
        return new external_single_structure(array(
            'courseid' => new external_value(PARAM_INT),
            'url' => new external_value(PARAM_TEXT),
            'error' => new external_value(PARAM_TEXT),
            'user' => new external_value(PARAM_TEXT),
        ));
    }
    /**
     * Returns description of create_course's parameters.
     *
     * @return external_function_parameters
     */
    public static function create_course_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT),
            'templateid' => new external_value(PARAM_INT),
            'coursename' => new external_value(PARAM_TEXT),
            'shortname' => new external_value(PARAM_TEXT),
            'categoryid' => new external_value(PARAM_INT),
            'startdate' => new external_value(PARAM_TEXT),
            'enddate' => new external_value(PARAM_TEXT),
        ));
    }
     /**
     * Creates new course. Either blank or duplicated via import from template course
     *
     * @param int $courseid
     * @param int $templateid whether template or new course(0)
     * @param string $coursename  name of new course
     * @param string $shortname shortname of new course
     * @param int $categoryid of new course
     * @param string start date of course eg. dd-mm-yyyy-hh-mm
     * @param string end date of course eg. dd-mm-yyyy-hh-mm
     * @return array
     */
    public static function create_course($courseid,$templateid,$coursename,$shortname,$categoryid,$startdate,$enddate) {

        global $DB, $CFG, $USER;

        // get params
        $params = self::validate_parameters(
            self::create_course_parameters(),
            array(
            'courseid' => $courseid,
            'templateid' => $templateid,
            'coursename' => $coursename,
            'shortname' => $shortname,
            'categoryid' => $categoryid,
            'startdate' => $startdate,
            'enddate' => $enddate,
            )
        );

        // ensure user has permissions to change image
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);

        $admins = get_admins(); 
        $isadmin = false; 
        foreach ($admins as $admin) { 
            if ($USER->id == $admin->id) 
            { $isadmin = true; break; } } 


        //split dates and get appropriate timestamp
        $start =  explode("-", $params['startdate']);

        $starttimestamp = make_timestamp($start[2],$start[1],$start[0],$start[3],$start[4]);
        $endtimestamp=0;
        if($params['enddate'] != "0"){
            $end =  explode("-", $params['enddate']);
            $endtimestamp = make_timestamp($end[2],$end[1],$end[0],$end[3],$end[4]);
        }

        $username = $USER->username;

        //CHECK IF DEFAULT TEMPLATE
        if($templateid == 0){

            //create brand new course not from template
            $course = new stdClass();
            $course->id="";
            $course->category =$params['categoryid'];
            $course->sortorder = 1;
            $course->shortname = $params['shortname'];
            $course->fullname = $params['coursename'];
            $course->summary ="";
            $course->idnumber = self:: create_course_idnumber($params['shortname'],$username, 001);
            $course->summaryformat =1;
            $course->format ="topics";
            $course->showgrades =1;
            $course->newsitems =1;
            $course->newsitems =1;
            $course->startdate =$starttimestamp;
            $course->enddate =$endtimestamp;
            $course->relativedatesmode =0;
            $course->marker =0;
            $course->maxbytes =0;
            $course->legacyfiles =0;
            $course->showreports =0;
            $course->visible =0;
            $course->visibleold =1;
            $course->groupmode =0;
            $course->groupmodeforce =0;
            $course->defaultgroupingid =0;
            $course->timecreated =time();
            $course->timemodified =time();
            $course->requested =0;
            $course->enablecompletion =0;
            $course->completionnotify =0;
        
            $newcourse = create_course($course);

             //add course email to new course'
             $mail = array(
                "course"=>$newcourse->id,
                "name"=>"Course Email",
                "intro"=>"",
                "introformat"=>1,
                "timecreated" =>time());

            $instance = $DB->insert_record("mail", $mail);
            $module = $DB->get_record("modules", array("name"=>"mail"), $fields='*', $strictness=MUST_EXIST);
            $section =$DB->get_record("course_sections", array("course"=>$newcourse->id), $fields='*', $strictness=MUST_EXIST);

            $coursemod = array(
                'course'=>$newcourse->id,
                'module'=>$module->id,
                'instance'=>$instance,
                'section'=> $section->id,
                'added'=>time() ,
                'score'=>0,
                'indent'=>0,
                'visible'=>1,
                'visibleoncoursepage'=>1,
                'visibleold'=>1,
                'groupmode'=>0,
                'groupingid'=>0,
                'completion'=>0,
                'completionview'=>0,
                'completionexpected'=>0,
                'showdescription'=>0,
                'deletioninprogress'=>0
            );

            
            $cmid = $DB->insert_record("course_modules", $coursemod);

            //now update section
            $section = $DB->get_record('course_sections',
                array('course' => $newcourse->id, 'section' => 0), '*', MUST_EXIST);
    
            $newsequence = "$section->sequence,$cmid";
            $DB->set_field("course_sections", "sequence", $newsequence, array("id" => $section->id));

             //ENROLL CURRENT INSTRUCTOR INTO COURSE
             if(!$isadmin){
                $isenrolled = self:: enroll_user($params['courseid'], $newcourse->id,$USER->id);
                if(!$isenrolled)
                {
                    return array('courseid'=>0, 'url'=>"","error"=>"Was unable to enroll user in course");
                }
             }
        }else{

            //check if instructor is enrolled in the template
            $templatecontext = \context_course::instance($params['templateid']);
            //self::validate_context($templatecontext);

            $admins = get_admins(); 
            $isadmin = false; 
            foreach ($admins as $admin) { 
                if ($USER->id == $admin->id) 
                { $isadmin = true; break; } } 
            
            $ogisenrolled =true;
            if(!$isadmin){
                $ogisenrolled =is_enrolled($templatecontext, $USER->id);
                //if not enroll the instructor in template
                if(!$ogisenrolled){
                    $isenrolled = self:: enroll_user($params['courseid'], $params['templateid'],$USER->id);
                    if(!$isenrolled)
                    {
                        return array('courseid'=>0, 'url'=>"","error"=>"Was unable to enroll user in course");
                    }
                }
            }

            //get template course from DB
            $sql = "SELECT * FROM mdl_course as c WHERE c.id ='{$params['templateid']}'";
            $course = $DB->get_record_sql($sql);

            //add in new variables, clear id
            $course->id = "";
            //sanitize
            $course->shortname = $params['shortname'];
            $course->fullname = $params['coursename'];
            $course->category = $params['categoryid'];
            $course->startdate =$starttimestamp;
            $course->enddate =$endtimestamp;
            $course->summary="";
            $course->visible =0;
            $course->idnumber = self:: create_course_idnumber($params['shortname'],$username, 001);
            //send to external function
            $newcourse = create_course($course);

            //remove site announcments if template course has it already
            $sql = "SELECT * FROM mdl_forum WHERE course = '{$params['templateid']}' AND type ='news'";
            $test = $DB->record_exists_sql($sql, null);
            if($test){
                $DB->delete_records("forum", array("course"=>$newcourse->id));
            }
            
             //ENROLL CURRENT INSTRUCTOR INTO COURSE
             if(!$isadmin){
                $isenrolled = self:: enroll_user($params['courseid'], $newcourse->id,$USER->id);
                if(!$isenrolled)
                {
                    return array('courseid'=>$newcourse->id, 'url'=>"","error"=>"Was unable to enroll user in course");
                }
             }

            //use old id and new id to tranfer data to new course
            $importer = new core_course_external();
            $options = array(array('name'=>'activities','value'=>1),array('name'=>'blocks', 'value'=>1),array('name'=>'filters','value'=>1));
            $importer->import_course($params['templateid'], $newcourse->id,0,$options);

            //UNENROLL INSTRUCTOR FROM TEMPLATE
            if(!$ogisenrolled && !$isadmin){
                //get instance that can unenrol
                $instances = $DB->get_records('enrol', array('courseid' => $params['templateid']));

                foreach ($instances as $instance) {
                    $plugin = enrol_get_plugin($instance->enrol);
                    $plugin->unenrol_user($instance, $USER->id);
                   
                }
            }
        }

        //return new course id & url
        $url = $CFG->wwwroot.'/course/view.php?id=' . $newcourse->id;
     
        return array('courseid'=>$newcourse->id, 'url'=>$url, 'error'=>"");
    }

      /**
     * Returns description of create_course return value.
     *
     * @return external_single_structure
     */
    public static function create_course_returns() {
        return new external_single_structure(array(
            'courseid' => new external_value(PARAM_INT),
            'url' => new external_value(PARAM_TEXT),
            'error' => new external_value(PARAM_TEXT),
        ));
    }

     /**
     * Internal function to enroll user into course based on previous course enrollment.
     *
     * @return external_single_structure
     */
    private static function enroll_user($ogcourseid,$newcourseid, $userid) {

        global $DB, $CFG;

        $isenrolled = false;
        $ogcontext = \context_course::instance($ogcourseid);
        $newcontext = \context_course::instance($newcourseid);

        //get enroll id for manual enrollment for current course
        $sql = "SELECT * FROM mdl_enrol WHERE courseid =".$newcourseid." AND enrol = 'manual';";
        $enroll = $DB->get_record_sql($sql, null, MUST_EXIST);

        //GET CURRENT ROLE OF USER IN COURSE
        if ($roles = get_user_roles($ogcontext, $userid)) {
            foreach ($roles as $role) {
                //enroll user in course with same role in current course
                $isenrolled = self::check_enrolment($newcourseid, $userid, $role->roleid, 'manual');
            }
        }
        return $isenrolled;
    }

    /**
     * Internal function to enroll user into course based on previous course enrollment.
     *
     * @return external_single_structure
     */
    private static function check_enrolment($courseid, $userid, $roleid, $enrolmethod = 'manual'){
   
        global $DB;
        $user = $DB->get_record('user', array('id' => $userid, 'deleted' => 0), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $context = context_course::instance($course->id);
        if (!is_enrolled($context, $user)) {
          $enrol = enrol_get_plugin($enrolmethod);
          if ($enrol === null) {
             return false;
          }
         $instances = enrol_get_instances($course->id, true);
         $manualinstance = null;
         foreach ($instances as $instance) {
             if ($instance->name == $enrolmethod) {
                 $manualinstance = $instance;
                 break;
             }
         }
         if ($manualinstance !== null) {
             $instanceid = $enrol->add_default_instance($course);
             if ($instanceid === null) {
                 $instanceid = $enrol->add_instance($course);
             }
             $instance = $DB->get_record('enrol', array('id' => $instanceid));
         }

         $enrol->enrol_user($instance, $userid, $roleid);
     }
     return true;
 }

   /**
     * Returns description of enrollment_info's parameters.
     *
     * @return external_function_parameters
     */
    public static function enrollment_info_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT),
            'semester' => new external_value(PARAM_INT),
        ));
    }

    /**
     * Grabs all info needed for user to enrol students in course from banner. Grabs available crn banner course, based on 
     * semester and user name. Checks if course was already activated somewhere else. Also grabs used crn banner courses for that
     * semester
     *
     * @param int $courseid
     * @param int $semester
     * @return arrays
     */
    public static function enrollment_info($courseid, $semester) {

        global $USER, $DB;

        // get params
        $params = self::validate_parameters(
            self::enrollment_info_parameters(),
            array(
            'courseid' => $courseid,
            'semester' => $semester,
            )
        );

        // ensure user has permissions to change image
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);

        //get course
        $course = $DB->get_record('course', array('id' => $params['courseid']));

        $admins = get_admins(); 
        $isadmin = false; 
        foreach ($admins as $admin) { 
            if ($USER->id == $admin->id) 
            { $isadmin = true; break; } } 
        
        //if user is admin get instructor info for course
        if($isadmin){

            $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
            $context = get_context_instance(CONTEXT_COURSE, $courseid);
            $teachers = get_role_users($role->id, $context);
            $courseinfo = block_urcourserequest_crn_info($params['semester'], reset($teachers)->username);

            $activated=array();
            if(empty($courseinfo)){
                for($i=1; $i<count($teachers); $i++){
                    $courseinfo = block_urcourserequest_crn_info($params['semester'], $teachers[$i]->username);

                    if(!empty($courseinfo)){
                        $activated= block_urcourserequest_activated_courses_id($params['courseid'], $params['semester']);
                        break 1;
                    }
                }
            }
            $activated= block_urcourserequest_activated_courses_id($params['courseid'],$params['semester']);
        }else{
            $courseinfo = block_urcourserequest_crn_info($params['semester'], $USER->username);
            $activated= block_urcourserequest_activated_courses_id($params['courseid'],$params['semester']);
        }

        $isavailable = true;
        if ($courseinfo) {
            //check if course is already activated in a different semester
             $activecourse = "select * from ur_crn_map where courseid='$course->idnumber' AND archived = '0' ORDER BY semester";
             $active = $DB->get_records_sql($activecourse);

             if(!empty($active)){
                $isavailable=false;
                $inuse = 0;
                
                $crnstring ="";
                $iscurrent =false;

                 foreach($active as $activecrn){
                    //if ( $c->semester == $semester) {
                    //    $isavailable = true;
                    // }
                    if ($activecrn->archived == 0 && ($activecrn->semester != $semester) ) {
                        $inuse = 1;
                    }
                    if($activecrn->archived == 0 && $activecrn->semester == $semester){
                        $iscurrent=true;
                    }
                 }
                 if ($inuse ) {
                    if($iscurrent){   
                        $isavailable=true;
                    }
                 }else{
                    $isavailable=true;
                 }
             }
         }
        return array("courseinfo"=>$courseinfo, "activated"=>$activated, "semester"=>block_urcourserequest_semester_string($params['semester']),"isavaliable"=>$isavailable);
    }
    /**
     * Returns description of enrollment_info return value.
     *
     * @return external_single_structure
     */
    public static function enrollment_info_returns() {
        return new external_single_structure(array(
            'courseinfo' => new external_multiple_structure(new external_single_structure(array(
                'crn' => new external_value(PARAM_INT),
                'subject' => new external_value(PARAM_TEXT),
                'course' => new external_value(PARAM_TEXT),
                'section' => new external_value(PARAM_TEXT),
                'title' => new external_value(PARAM_TEXT)
            )))  ,
            'activated' => new external_multiple_structure(new external_single_structure(array(
                'crn' => new external_value(PARAM_INT),
                'subject' => new external_value(PARAM_TEXT),
                'course' => new external_value(PARAM_TEXT),
                'section' => new external_value(PARAM_TEXT),
                'title' => new external_value(PARAM_TEXT),
                'fullname'=> new external_value(PARAM_TEXT),
                'urid'=> new external_value(PARAM_INT),
                'linked'=> new external_value(PARAM_INT),
                'canremove'=> new external_value(PARAM_BOOL),
            ))),
            'semester' =>new external_value(PARAM_TEXT),
            'isavaliable' =>new external_value(PARAM_BOOL),
        ));
    }

     /**
     * Internal function to enroll user into course based on previous course enrollment.
     *
     * @return external_single_structure
     */
    private static function create_course_idnumber($shortname,$instructorname,$version) {

        global $DB;

         $strings = explode(" ",$shortname);
 
         if(count($strings)>1)
             $first = $strings[0].$strings[1];
         else    
             $first = $strings[0];
 
         $cleaned = preg_replace('/[^a-zA-Z0-9]+/','',$first);
         $final = preg_replace("/\s+/", "", $cleaned);
 
         if(strlen($final)>80){
             $final = substr($string,0,80);
         }
 
         while ($DB->record_exists("course", array("idnumber"=>$final."_".$instructorname."_".str_pad($version,3,"0",STR_PAD_LEFT)))) {
            //increment until id not found
            $version = $version +1;
        }
 
         return $final."_".$instructorname."_".str_pad($version,3,"0",STR_PAD_LEFT);
     }

     /**
     * Returns description of activate_course's parameters.
     *
     * @return external_function_parameters
     */
    public static function activate_course_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT),
            'semester' => new external_value(PARAM_INT),
            'crns' => new external_multiple_structure(
                        new external_single_structure(
                             array(
                                'crn'=>  new external_value(PARAM_INT),
            ))),
            'groupcheck' => new external_value(PARAM_INT),
            'startdate' => new external_value(PARAM_TEXT),
            'enddate' => new external_value(PARAM_TEXT),
            'isoriginal' => new external_value(PARAM_BOOL),
        ));
    }

    /**
     * Enrolls students from select banner section into course
     * Activates the course by adding it to the crn_map table
     *
     * @param int $courseid
     * @param int $semester of term to activate course in
     * @param string $crn code of banner section
     * @return array
     */
    public static function activate_course($courseid, $semester, $crns,$groupcheck, $startdate, $enddate, $isoriginal) {

        global $USER, $DB;

        // get params
        $params = self::validate_parameters(
            self::activate_course_parameters(),
            array(
            'courseid' => $courseid,
            'semester' => $semester,
            'crns' => $crns,
            'groupcheck' => $groupcheck,
            'startdate' => $startdate,
            'enddate' => $enddate,
            'isoriginal' => $isoriginal,
            )
        );

        // ensure user has permissions to change image
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);

        //split dates and get appropriate timestamp
        $start =  explode("-", $params['startdate']);

        $starttimestamp = make_timestamp($start[2],$start[1],$start[0],$start[3],$start[4]);
        $endtimestamp=0;
        if($params['enddate'] != "0"){
            $end =  explode("-", $params['enddate']);
            $endtimestamp = make_timestamp($end[2],$end[1],$end[0],$end[3],$end[4]);
        }

        $value = true;
        $result="";
        for($i=0; $i<count($params['crns']); $i++){
            foreach ($params['crns'][$i] as $crn) {
                $result .= " ";
                $result .= block_urcourserequest_activate_urcourse($params['courseid'], $crn, $params['semester'], $starttimestamp,$endtimestamp,$params['groupcheck'],$params['isoriginal']);

                if(stripos($result, 'success') === false){
                    $value = false;
                    break 2;
                }
            }
        }

        return array("result"=>$result, "value"=>$value, "semester"=>block_urcourserequest_semester_string($semester));
    }
    /**
     * Returns description of activate_course return value.
     *
     * @return external_single_structure
     */
    public static function activate_course_returns() {
        return new external_single_structure(array(
            'result' => new external_value(PARAM_TEXT),  
            'value' => new external_value(PARAM_BOOL),  
            'semester' => new external_value(PARAM_TEXT),  
        ));
    }

    /**
    * Returns description of delete_enrollment's parameters.
    *
    * @return external_function_parameters
    */
    public static function delete_enrollment_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT),
            'semester' => new external_value(PARAM_INT),
            'crn'=>  new external_value(PARAM_INT),
        ));
    }

    /*
     * Deletes enrollment from course by removing banner section
     * Activates the course by adding it to the crn_map table
     *
     * @param int $courseid
     * @param int $semester of term to activate course in
     * @param string $crn code of banner section
     * @return array
     */
    public static function delete_enrollment($courseid, $semester, $crn) {

        global $USER, $DB;

        // get params
        $params = self::validate_parameters(
            self::delete_enrollment_parameters(),
            array(
            'courseid' => $courseid,
            'semester' => $semester,
            'crn' => $crn,
            )
        );

        // ensure user has permissions to change image
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
       
        $result .= block_urcourserequest_delete_enrollment($params['courseid'],$params['semester'],$params['crn']);

        return array("result"=>$result);
    }
    /**
     * Returns description of delete_enrollment return value.
     *
     * @return external_single_structure
     */
    public static function delete_enrollment_returns() {
        return new external_single_structure(array(
            'result' => new external_value(PARAM_TEXT),  
        ));
    }
}