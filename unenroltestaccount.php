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
 * Manual enrolment plugin - support for user self unenrolment.
 *
 * @package    enrol_manual
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

global $USER;

$courseid = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$action = optional_param('action', 0, PARAM_BOOL);

$context = context_course::instance($courseid, MUST_EXIST);

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'manual'), '*');


require_login();
if (!is_enrolled($context)) {
    redirect(new moodle_url('/'));
}
require_login($course);


//check if has a student account
//get username to create email
$email = $USER->username."+urstudent@uregina.ca";
//check if test user account has already been created
$sql = "SELECT * FROM mdl_user as u WHERE u.email ='{$email}'";
$user = $DB->get_record_sql($sql);

if($user){

    $plugin = enrol_get_plugin('manual');
    
    $PAGE->set_url('/theme/urcourses_default/unenroltestaccount.php', array('id'=>$course->id));
    $PAGE->set_title(format_string($course->fullname, true, array('context' => $context)));
    $PAGE->set_heading(format_string($course->fullname, true, array('context' => $context)));
    //check if user is enrolled already
    $enrolled =is_enrolled($context, $user->id, '', true);

    if($enrolled){

        $message = "Unenrol ".$email." test account from ".$course->shortname."?";
    
        if ($confirm and confirm_sesskey()) {
            $plugin->unenrol_user($instance, $user->id);
    
            redirect(new moodle_url('/theme/urcourses_default/unenroltestaccount.php', array('id'=>$course->id,'action'=>1)));
        }
    }else{

        $message = "Enrol ".$email." test account into ".$course->shortname."?";
        
        if ($confirm and confirm_sesskey()) {
            
             //enroll user in course
            //get enroll id for manual enrollment for current course
            $sql = "SELECT * FROM mdl_enrol WHERE courseid =".$course->id." AND enrol = 'manual';";
            $enroll = $DB->get_record_sql($sql, null, MUST_EXIST);

            if(!$enroll){
                $enrolid = $DB->insert_record('enrol', (object)array(
                    'enrol' => 'manual',
                    'courseid' => $course->id,
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
            $roleassigned = $DB->insert_record("role_assignments", $dataobject, true, false);
    
            //redirect(new moodle_url('/index.php'));
            redirect(new moodle_url('/theme/urcourses_default/unenroltestaccount.php', array('id'=>$course->id,'action'=>1)));
        }
    
    }
    echo $OUTPUT->header();
    $yesurl = new moodle_url($PAGE->url, array('confirm'=>1, 'sesskey'=>sesskey()));
    $nourl = new moodle_url('/course/view.php', array('id'=>$course->id));

    if($action){

        echo html_writer::start_tag('div', array('class' => 'alert alert-success modal-dialog'));
     

        if($enrolled){
            echo html_writer::div('') . 'Test student account successfully enrolled in course.' . html_writer::end_span();
        }else{
            echo html_writer::div('') . 'Test student account successfully removed from course.' . html_writer::end_span();
        }  

        echo html_writer::end_tag('div');
    }

    
    echo $OUTPUT->confirm($message, $yesurl, $nourl);
    echo $OUTPUT->footer();
}
