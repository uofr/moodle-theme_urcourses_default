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
        return new external_single_structure(array('success' => new external_value(PARAM_BOOL)));
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

    /**
     * Checks if the current user is an instructor.
     * Users with the teacher, editingteacher, manager, and coursecreator roles are considered instructors.
     * If we are in the site context, check if the user is an instructor anywhere.
     * Otherwiser, check if the user is an instructor in the given context.
     *
     * @param context $context
     * @return bool
     */
    public static function user_is_instructor($context) {
        global $USER, $DB;

        $role_query_cond = 'shortname = :a OR shortname = :b OR shortname = :c OR shortname = :d';
        $role_query_arr = ['a' => 'editingteacher', 'b' => 'teacher', 'c' => 'manager', 'd' => 'coursecreator'];
        $instructor_roles = $DB->get_fieldset_select('role', 'id', $role_query_cond, $role_query_arr);
        
        $roleassign_query_cond = 'userid = :uid AND (roleid = :r0 OR roleid = :r1 OR roleid = :r2 OR roleid = :r3)';
        $roleassign_query_arr = [
            'uid' => $USER->id,
            'r0' => $instructor_roles[0],
            'r1' => $instructor_roles[1],
            'r2' => $instructor_roles[2],
            'r3' => $instructor_roles[3]
        ];

        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            $roleassign_query_cond .= 'AND contextid = :cid';
            $roleassign_query_arr['cid'] = $context->id;
        }

        return $DB->record_exists_select('role_assignments', $roleassign_query_cond, $roleassign_query_arr);
    }

    /**
     * Returns description of get_landing_page's parameters.
     *
     * @return external_function_parameters
     */
    public static function get_landing_page_parameters() {
        return new external_function_parameters(array(
            'contextid' => new external_value(PARAM_INT)
        ));
    }

    /**
     * Returns landing page data for help modal.
     *
     * @param int $courseid
     * @param int $currenturl
     * @return array
     */
    public static function get_landing_page($contextid) {
        global $PAGE;

        $params = self::validate_parameters(self::get_landing_page_parameters(), array(
            'contextid' => $contextid
        ));

        $context = context::instance_by_id($params['contextid']);
        self::validate_context($context);

        $content_url = new moodle_url("/guides/urmodal.json/p:mod_$PAGE->activityname");
        $json_output = json_decode(file_get_contents($content_url->out()));

        return array(
            'html' => $json_output->jsondata->param_p_data[0]->content,
            'contenturls' => $json_output->jsondata->param_p_data[0]->contenturls
        );
    }

    /**
     * Returns description of get_landing_page return value.
     *
     * @return external_single_structure
    */
    public static function get_landing_page_returns() {
        return new external_single_structure(array(
            'html' => new external_value(PARAM_RAW),
            'contenturls' => new external_multiple_structure(new external_single_structure(array(
                'name' => new external_value(PARAM_TEXT),
                'url' => new external_value(PARAM_URL)
            )))
        ));
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

        $content_url = new moodle_url('/guides/social/sample-b.json');
        $json_output = json_decode(file_get_contents($content_url));
        $topic_list_full = $json_output->jsondata->page_data[0]->all_pages;

        foreach ($topic_list_full as $topic) {
            $url = substr($topic->url, strpos($topic->url, '/', 1));
            $topic->url = $url;
            $topic->title = htmlspecialchars_decode($topic->title);
        }

        if (self::user_is_instructor($context)) {
            $topic_list = array_filter($topic_list_full, function($item) {
                return $item->prefix === 'Instructor' && $item->title !== 'Instructor';
            });
        }
        else {
            $topic_list = array_filter($topic_list_full, function($item) {
                return $item->prefix === 'Students' && $item->title !== 'Student';
            });
        }

        usort($topic_list, function($a, $b) {
            return $a->title < $b->title ? -1 : 1;
        });

        return $topic_list;
    }

    /**
     * Returns description of get_topic_list return value.
     *
     * @return external_multiple_structure
     */
    public static function get_topic_list_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'prefix' => new external_value(PARAM_TEXT, '', VALUE_OPTIONAL),
            'title' => new external_value(PARAM_TEXT, '', VALUE_OPTIONAL),
            'url' => new external_value(PARAM_TEXT, '', VALUE_OPTIONAL),
            'excerpt' => new external_value(PARAM_TEXT, '', VALUE_OPTIONAL)
        )));
    }

    /**
     * Returns description of params passed to get_guide_page.
     *
     * @return external_function_parameters
     */
    public static function get_guide_page_parameters() {
        return new external_function_parameters(array(
            'url' => new external_value(PARAM_TEXT)
        ));
    }

    /**
     * Returns guide page data.
     *
     * @param string $url 
     */
    public static function get_guide_page($url) {
        $params = self::validate_parameters(self::get_guide_page_parameters(), array(
            'url' => $url
        ));

        $content_url = new moodle_url($params['url'] . '.json');
        $json_output = json_decode(file_get_contents($content_url));
        $html = ($json_output->content) ? $json_output->content : $json_output->jsondata->page_data[0]->content;
        $title = $json_output->jsondata ? $json_output->jsondata->page_data[0]->title : '';

        return array(
            'html' => $html,
            'title' => $title
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
            'title' => new external_value(PARAM_TEXT)
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
        $json_output = json_decode(file_get_contents($search_url));

        $search_results = $json_output->jsondata;
        foreach($search_results as $result) {
            $url = substr($result->url, strpos($result->url, '/', 1));
            $result->url = $url;
        }
        
        return array(
            'results' => $search_results,
            'query' => $params['query']
        );
    }

    /**
     * Returns description of modal_help_serach's return value.
     *
     * @return external_single_structure
     */
    public static function modal_help_search_returns() {
        return new external_single_structure(array(
            'results' => new external_multiple_structure(new external_single_structure(array(
                'page-date' => new external_value(PARAM_TEXT),
                'page-modified-date' => new external_value(PARAM_TEXT),
                'url' => new external_value(PARAM_URL),
                'search-prefix' => new external_value(PARAM_TEXT),
                'page-title' => new external_value(PARAM_TEXT),
                'excerpt' => new external_value(PARAM_TEXT)
            ))),
            'query' => new external_value(PARAM_TEXT)
        ));
    }

    /**
     * Returns description of modal_help_search's parameters.
     *
     * @return external_function_parameters
     */
    public static function create_test_account_parameters() {
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
    public static function create_test_account($courseid,$username) {
        global $USER, $DB, $CFG;

        // get params
        $params = self::validate_parameters(
            self::create_test_account_parameters(),
            array(
            'courseid' => $courseid,
            'username' => $username,
            )
        );

        // ensure user has permissions to change image
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);

        //get username to create email
        $email = $USER->username."+student@uregina.ca";
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
                $sql = "SELECT * FROM mdl_enrol WHERE courseid =".$courseid." AND enrol = 'manual';";
                $enroll = $DB->get_record_sql($sql, null, MUST_EXIST);

                $sql = "SELECT * FROM mdl_role WHERE shortname = 'student';";
                $role = $DB->get_record_sql($sql, null, MUST_EXIST);
                //enroll user in course as student
                $dataobject = array(
                    "status"=>0,
                    "enrolid"=>$enroll->id,
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
            $user->username= $user->username."student";
        
            //call external create user function
            $userid = user_create_user($user, false, true);
            //enroll user in course
            //get enroll id for manual enrollment for current course
            $sql = "SELECT * FROM mdl_enrol WHERE courseid =".$courseid." AND enrol = 'manual';";
            $enroll = $DB->get_record_sql($sql, null, MUST_EXIST);

            $sql = "SELECT * FROM mdl_role WHERE shortname = 'student';";
            $role = $DB->get_record_sql($sql, null, MUST_EXIST);

            //enroll user in course as student
            $dataobject = array(
                "status"=>0,
                "enrolid"=>$enroll->id,
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
            
        }
        return $return;
    }

      /**
     * Returns description of get_guide_page return value.
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
     * Returns description of modal_help_search's parameters.
     *
     * @return external_function_parameters
     */
    public static function duplicate_course_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT),
            'coursename' => new external_value(PARAM_TEXT),
            'shortname' => new external_value(PARAM_TEXT),
            'categoryid' => new external_value(PARAM_INT),
        ));
    }
     /**
     * Creates test student account and enrols in current course
     *
     * @param int $courseid
     * @param int $username of current user
     * @return array
     */
    public static function duplicate_course($courseid,$coursename,$shortname,$categoryid) {
        global $USER, $DB, $CFG;

        // get params
        $params = self::validate_parameters(
            self::duplicate_course_parameters(),
            array(
            'courseid' => $courseid,
            'coursename' => $coursename,
            'shortname' => $shortname,
            'categoryid'=> $categoryid
            )
        );

        // ensure user has permissions to change image
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
        //get course from DB
        $sql = "SELECT * FROM mdl_course as c WHERE c.id ='{$courseid}'";
        $course = $DB->get_record_sql($sql);
        error_log(print_r($course,TRUE));

        //add in new variables, clear id
        $course->id = "";
        //sanitize
        $course->shortname = $shortname;
        $course->fullname = $coursename;
        $course->category = $categoryid;
        //send to external function
        $newcourse = create_course($course);
        error_log(print_r($newcourse,TRUE));

        //check if instructor is enrolled in the template
        $newcontext = \context_course::instance($newcourse->id);
        //self::validate_context($templatecontext);

        $admins = get_admins(); 
        $isadmin = false; 
        foreach ($admins as $admin) { 
            if ($USER->id == $admin->id) 
            { $isadmin = true; break; } } 
        
        if(!$isadmin){
            $ogisenrolled =is_enrolled($newcontext, $USER->id);
            //if not enroll the instructor in template
            if(!$ogisenrolled){
                $isenrolled = self:: enroll_user($courseid, $newcourse->id,$USER->id);
                if(!$isenrolled)
                {
                    return array('courseid'=>0, 'url'=>"","error"=>"Was unable to enroll user in course");
                }
            }
        }
        
        //use old id and new id to tranfer data to new course
        $importer = new core_course_external();
        $importer->import_course($courseid, $newcourse->id);

         //return new course id & url
         $url = $CFG->wwwroot.'/course/view.php?id=' . $newcourse->id;

         error_log(print_r($url,TRUE));
 
         return array('courseid'=>$newcourse->id, 'url'=>$url, 'error'=>"");
    }
      /**
     * Returns description of get_guide_page return value.
     *
     * @return external_single_structure
     */
    public static function duplicate_course_returns() {
        return new external_single_structure(array(
            'courseid' => new external_value(PARAM_INT),
            'url' => new external_value(PARAM_TEXT),
            'error' => new external_value(PARAM_TEXT),
        ));
    }
        /**
     * Returns description of modal_help_search's parameters.
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
        ));
    }
     /**
     * Creates test student account and enrols in current course
     *
     * @param int $courseid
     * @param int $username of current user
     * @return array
     */
    public static function create_course($courseid,$templateid,$coursename,$shortname,$categoryid) {

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
            )
        );

        error_log(print_r("categroyid",TRUE));
        error_log(print_r($categoryid,TRUE));

        // ensure user has permissions to change image
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);

        //CHECK IF DEFAULT TEMPLATE
        if($templateid == 0){

            //create brand new course not from template
            $course = new stdClass();
            $course->id="";
            $course->category =$categoryid;
            $course->sortorder = 1;
            $course->shortname = $shortname;
            $course->fullname = $coursename;
            $course->summary ="";
            $course->summaryformat =1;
            $course->format ="topics";
            $course->showgrades =1;
            $course->newsitems =1;
            $course->newsitems =1;
            $course->startdate =time();
            $course->enddate =0;
            $course->relativedatesmode =0;
            $course->marker =0;
            $course->maxbytes =0;
            $course->legacyfiles =0;
            $course->showreports =0;
            $course->visible =1;
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
            error_log(print_r($course,TRUE));
             //ENROLL CURRENT INSTRUCTOR INTO COURSE
            $isenrolled = self:: enroll_user($courseid, $newcourse->id,$USER->id);
            if(!$isenrolled)
            {
                return array("error"=>"Was unable to enroll user in course");
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
            
            if(!$isadmin){
                $ogisenrolled =is_enrolled($templatecontext, $USER->id);
                //if not enroll the instructor in template
                if(!$ogisenrolled){
                    $isenrolled = self:: enroll_user($courseid, $params['templateid'],$USER->id);
                    if(!$isenrolled)
                    {
                        return array('courseid'=>0, 'url'=>"","error"=>"Was unable to enroll user in course");
                    }
                }
            }

            //get template course from DB
            $sql = "SELECT * FROM mdl_course as c WHERE c.id ='{$templateid}'";
            $course = $DB->get_record_sql($sql);

            //add in new variables, clear id
            $course->id = "";
            //sanitize
            $course->shortname = $shortname;
            $course->fullname = $coursename;
            $course->category = $categoryid;
            //send to external function
            $newcourse = create_course($course);

             //ENROLL CURRENT INSTRUCTOR INTO COURSE
             if(!$isadmin){
                $isenrolled = self:: enroll_user($courseid, $newcourse->id,$USER->id);
                if(!$isenrolled)
                {
                    return array("error"=>"Was unable to enroll user in course");
                }
             }

            //use old id and new id to tranfer data to new course
            $importer = new core_course_external();
            $options = array(array('name'=>'activities','value'=>1),array('name'=>'blocks', 'value'=>1),array('name'=>'filters','value'=>1));
            $importer->import_course($templateid, $newcourse->id,0,$options);

            //UNENROLL INSTRUCTOR FROM TEMPLATE
            if(!$ogisenrolled && !$isadmin){
                error_log(print_r("IN UNENROLL ",TRUE));
                //get instance that can unenrol
                $instances = $DB->get_records('enrol', array('courseid' => $params['templateid']));
                error_log(print_r($instances,TRUE));
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
     * Returns description of get_guide_page return value.
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
     * Returns description of get_guide_page return value.
     *
     * @return external_single_structure
     */
    public static function enroll_user($ogcourseid,$newcourseid, $userid) {

        global $DB, $CFG;

        $isenrolled = false;
        $ogcontext = \context_course::instance($ogcourseid);
        $newcontext = \context_course::instance($newcourseid);

        error_log(print_r("in enroller",TRUE));
        error_log(print_r($ogcourseid,TRUE));
        error_log(print_r($userid,TRUE));


        //get enroll id for manual enrollment for current course
        $sql = "SELECT * FROM mdl_enrol WHERE courseid =".$newcourseid." AND enrol = 'manual';";
        $enroll = $DB->get_record_sql($sql, null, MUST_EXIST);

        error_log(print_r("in enroller0",TRUE));
        error_log(print_r($newcourseid,TRUE));


        //GET CURRENT ROLE OF USER IN COURSE
        if ($roles = get_user_roles($ogcontext, $userid)) {
            foreach ($roles as $role) {

                error_log(print_r("THE ROLE ",TRUE));
                error_log(print_r($role->roleid,TRUE));
                error_log(print_r($role->shortname,TRUE));
                error_log(print_r($role,TRUE));

                //enroll user in course with same role in current course
                /*$dataobject = array(
                    "status"=>0,
                    "enrolid"=>$enroll->id,
                    "userid"=>$userid,
                    "timestart"=>time(),
                    "timeend"=>0,
                    "modififerid"=>$userid,
                    "timecreated"=>time(),
                    "timemodified"=>time()
                );
                $isenrolled =$DB->insert_record("user_enrolments", $dataobject, true, false);

                error_log(print_r("in roller2",TRUE));
                error_log(print_r($isenrolled,TRUE));

                $dataobject = array(
                    "roleid"=>$role->roleid,
                    "contextid"=>$newcontext->id,
                    "userid"=>$userid,
                    "timemodified"=>time(),
                    "modififerid"=>$userid,
                    "itemid"=>0,
                    "sortorder"=>0
                );
                $roleassigned = $DB->insert_record("role_assignments", $dataobject, true, false);

                error_log(print_r("in roller2b",TRUE));
                error_log(print_r($roleassigned,TRUE));*/
                $isenrolled = self::check_enrolment($newcourseid, $userid, $role->roleid, 'manual');

            }
        }
        error_log(print_r("in roller 3 ",TRUE));
        error_log(print_r($isenrolled,TRUE));
        return $isenrolled;
    }

    public static function check_enrolment($courseid, $userid, $roleid, $enrolmethod = 'manual'){
   
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
}

