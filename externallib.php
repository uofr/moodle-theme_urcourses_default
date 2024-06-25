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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/course/lib.php");
require_once($CFG->dirroot . "/course/externallib.php");
require_once($CFG->dirroot . "/user/lib.php");


class theme_urcourses_default_external extends external_api {


    /**
     * Returns description of create_test_account's parameters.
     *
     * @return external_function_parameters
     */
    public static function create_test_account_parameters() {
        return new external_function_parameters(array(
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
    public static function create_test_account($username) {
        global $USER, $DB, $CFG;

        //get username to create email
        $email = $USER->username."+urstudent@uregina.ca";
        //check if test user account has already been created
        $sql = "SELECT * FROM mdl_user as u WHERE u.email ='{$email}'";
        $user = $DB->get_record_sql($sql);

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        //if created
        if($user){
            $return=array("userid"=>$user->id,"username"=>$user->username,"created"=>false );
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

            $site  = get_site();
            $supportuser = core_user::get_support_user();
        
            $userauth = get_auth_plugin($user->auth);
            if (!$userauth->can_reset_password() or !is_enabled_auth($user->auth)) {
                trigger_error("Attempt to reset user password for user $user->username with Auth $user->auth.");
                $return=array("userid"=>$userid,"username"=>$user->username,"created"=>false );
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

            $return=array("userid"=>$userid,"username"=>$user->username,"created"=>true ); 
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
    public static function test_account_info($username) {
        global $USER, $DB, $CFG;

        // get params
        $params = self::validate_parameters(
            self::test_account_info_parameters(),
            array(
            'username' => $username,
            )
        );

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
    public static function reset_test_account($username) {
        global $USER, $DB, $CFG;

        // get params
        $params = self::validate_parameters(
            self::reset_test_account_parameters(),
            array(
            'username' => $username,
            )
        );

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        //get username to create email
        $email = $USER->username."+urstudent@uregina.ca";

        //check if test user account has already been created
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
     * Internal function to enroll user into course based on previous course enrollment.
     *
     * @return external_single_structure
     */
    private static function enroll_user($courseid) {

        global $DB, $CFG, $USER;

        // get params
        $params = self::validate_parameters(
            self::enroll_user_parameters(),
            array(
            'courseid' => $courseid,
            )
        );

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        //check if has a student account
        //get username to create email
        $email = $USER->username."+urstudent@uregina.ca";
        //check if test user account has already been created
        $sql = "SELECT * FROM mdl_user as u WHERE u.email ='{$email}'";
        $user = $DB->get_record_sql($sql);

        //check if user is enrolled already
        $isenrolled =is_enrolled($context, $user, 'mod/assignment:submit');

        if($isenrolled){
            $return=array("userid"=>$user->id,"username"=>$user->username,"enrolled"=>false,"created"=>false );
        }else{
    
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
        }
    }
 

   /**
     * Returns description of reset_test_account return value.
     *
     * @return external_single_structure
     */
    public static function enroll_user_returns() {
        return new external_single_structure(array(
            'userid' => new external_value(PARAM_INT),
            'username' => new external_value(PARAM_TEXT),
            'reset' => new external_value(PARAM_BOOL),
        ));
    }

}