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
 * Theme UR Courses Default - Language pack
 *
 * @package    theme_urcourses_default
 * @copyright  2023 Daniel Poggenpohl <daniel.poggenpohl@fernuni-hagen.de> and Alexander Bias <bias@alexanderbias.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Let codechecker ignore some sniffs for this file as it is perfectly well ordered, just not alphabetically.
// phpcs:disable moodle.Files.LangFilesOrdering.UnexpectedComment
// phpcs:disable moodle.Files.LangFilesOrdering.IncorrectOrder

// General.
$string['pluginname'] = 'UR Courses Default';
$string['choosereadme'] = 'UR Courses Default theme.';
$string['configtitle'] = 'UR Courses Default';
$string['settingsoverview_buc_desc'] = 'UR Courses theme settings.';

// Settings: General settings tab.
// ... Section: Inheritance.
$string['inheritanceheading'] = 'Inheritance';
$string['inheritanceinherit'] = 'Inherit';
$string['inheritanceduplicate'] = 'Duplicate';
$string['inheritanceoptionsexplanation'] = 'Most of the time, inheriting will be perfectly fine. However, it may happen that imperfect code is integrated into Boost Union which prevents simple SCSS inheritance for particular Boost Union features. If you encounter any issues with Boost Union features which seem not to work in UR Courses as well, try to switch this setting to \'Dupliate\' and, if this solves the problem, report an issue on Github (see the README.md file for details how to report an issue).';
// ... ... Setting: Pre SCSS inheritance setting.
$string['prescssinheritancesetting'] = 'Pre SCSS inheritance';
$string['prescssinheritancesetting_desc'] = 'With this setting, you control if the pre SCSS code from Boost Union should be inherited or duplicated.';
// ... ... Setting: Extra SCSS inheritance setting.
$string['extrascssinheritancesetting'] = 'Extra SCSS inheritance';
$string['extrascssinheritancesetting_desc'] = 'With this setting, you control if the extra SCSS code from Boost Union should be inherited or duplicated.';

// Privacy API.
$string['privacy:metadata'] = 'The UR Courses theme does not store any personal data about any user.';

/**************************************************************
 * EXTENSION POINT:
 * Add your language strings for your settings here.
 *************************************************************/

// Dark Mode
$string['enabledarkmode'] = 'Enable dark mode';
$string['disabledarkmode'] = 'Disable dark mode';

// UR Student Account
$string['resetmodal_title'] = 'Reset Password for Test Student Account';
$string['resetmodal_button'] = 'Reset Password';
$string['resetmodal_confirm'] = 'Are you sure you want to reset the password for your test student account <strong>{$a}</strong>?';
$string['resetmodal_date'] = 'Account Created On';
$string['resetmodal_helptext'] = 'After clicking <strong>reset password</strong>, an email will be sent to your <strong>{$a}</strong> account with instructions on how to set a new password.';
$string['resetsuccess_title'] = 'Password Reset';
$string['resetsuccess_body'] = 'Your test student password has been reset. An email has be sent to your <strong>{$a}</strong> account with instructions on how to set a new password for your test student.';
$string['resetfail_title'] = 'Password Reset Failed';
$string['resetfail_body'] = 'Test student password reset failed.';
$string['createmodal_title'] = 'Create Test Student Account';
$string['createmodal_button'] = 'Create';
$string['createmodal_intro'] = 'The following test student account will be created:';
$string['createmodal_email'] = 'Email';
$string['createmodal_username'] = 'Username';
$string['createmodal_email_helptext'] = 'Email sent to <strong>{$a->email}</strong> will be directed to your <strong>{$a->emailoriginal}</strong> account.';
$string['createmodal_helptext'] = 'After clicking <strong>create</strong>, an email will be sent to your <strong>{$a->emailoriginal}</strong> account with instructions on how to log in with your new test student account.';
$string['createmodal_confirm'] = 'Are you sure you want to create the test student account {$a}?';
$string['createsuccess_title'] = 'Test Student Account Created';
$string['createsuccess_body'] = 'Information on how to log in with your new test student account has be sent to your <strong>{$a}</strong> account.';
$string['createfail_title'] = 'Test Student Account Failed';
$string['createfail_body'] = 'Test student account creation failed.';
$string['enrolurstudent'] = 'Enrol test student account';
$string['unenrolurstudent'] = 'Unenrol test student account';
$string['createteststudent'] = 'Create test student';
$string['modifyteststudent'] = 'Modify test student';
$string['newtestuser'] = 'New test student account';
$string['resettestuser'] = 'Test student account password reset.';
$string['teststudentenrol_title'] = 'Enrol Test Student?';
$string['teststudentenrol_body'] = 'Are you sure you want to enrol your test student account in this course?';
$string['teststudentenrol_button'] = 'Enrol';
$string['teststudentunenrol_title'] = 'Unenrol Test Student?';
$string['teststudentunenrol_body'] = 'Are you sure you want to unenrol your test student account from this course?';
$string['teststudentunenrol_button'] = 'Unenrol';
$string['teststudentenrolled_title'] = 'Test student enrolled';
$string['teststudentenrolled_body'] = 'Your test student account has been enrolled in this course.';
$string['teststudentunenrolled_title'] = 'Test student unenrolled';
$string['teststudentunenrolled_body'] = 'Your test student account has been removed from this course.';
$string['newtestaccount_email'] = '<p>Hi {$a->firstname},</p>
<p>Your new test student account at \'{$a->sitename}\' has been created.</p>
<p>To log in with your test student account:</p>
<ol>
   <li>Log out of UR Courses if you are currently logged in.</li>
   <li>Go to {$a->link}.</li>
   <li>Click <strong>log in with other credentials</strong>.</li>
   <li>Enter your test student username and temporary password.</li>
   <ul>
      <li>username: {$a->username}</li>
      <li>password: {$a->newpassword}</li>
   </ul>
   <li>Press the <strong>Log in</strong> button.</li>
</ol>
<p>After you log in, you will have to create a new password for your test student account.</p>';

$string['resetteststudent_email'] = '<p>Hi {$a->firstname},</p>
<p>Your test student account password has been reset.</p>
<p>To set a new password for your test student account:</p>
<ol>
   <li>Log out of UR Courses if you are currently logged in.</li>
   <li>Go to {$a->link}.</li>
   <li>Click <strong>log in with other credentials</strong>.</li>
   <li>Enter your test student username and new temporary password.</li>
   <ul>
      <li>username: {$a->username}</li>
      <li>password: {$a->newpassword}</li>
   </ul>
   <li>Press the <strong>Log in</strong> button.</li>
</ol>
<p>After you log in, you will be able to create a new password for your test student account.</p>';

// Feedback
$string['feedback_label'] = 'Feedback on UR Courses';
$string['feedback_modal_header'] = 'Feedback';
$string['feedback_modal_body'] = 'Give us your feedback on UR Courses.';
$string['feedback_modal_problem'] = 'Submit a Ticket';
$string['feedback_modal_problem_label'] = 'Describe the problem you encountered.';
$string['feedback_modal_problem_confirm_title'] = 'Ticket Submitted';
$string['feedback_modal_problem_confirm'] = 'Your ticket has been submitted and will be reviewed by the IS Service Desk. You should receive an email shortly confirming the submission of your ticket.';
$string['feedback_modal_suggestion'] = 'Make a Suggestion';
$string['feedback_modal_suggestion_label'] = 'What can we do better?';
$string['feedback_modal_suggestion_confirm_title'] = 'Feedback Submitted';
$string['feedback_modal_suggestion_confirm'] = 'Your suggestion has been submitted and will be reviewed by our team. Thank you for your feedback!';

// My Courses (block_myoverview) Customizations
$string['notavailabletostudents'] = 'Course unavailable to students';
$string['coursehasended'] = 'Course ended {$a}';
$string['coursenotstarted'] = 'Course begins {$a}';
$string['coursesummarybuttontitle'] = 'The course summary is always available to students';
$string['coursesummarybuttontext'] = 'View Course Summary';
$string['coursesummarymissingtext'] = 'No course summary has been provided at this time.';
$string['coursesummaryedit'] = 'Edit course summary';
$string['strtimemonthdayyear'] = '%B %d, %Y';

// Errors
$string['teststudentnotallowed'] = 'You do not have permission to use the test student account feature. You must be an instructor, manager, or course editor in at least one course.';
$string['teststudentexists'] = 'You cannot create a test student account. You already have a test student account.';
$string['teststudentdoesntexist'] = 'You do not have a test student account.';
$string['teststudentcoultnotsetpassword'] = 'There was an error while trying to set your test student account password.';
$string['teststudentcoultnotresetpassword'] = 'There was an error while trying to reset your test student account password.';
$string['teststudentcouldnotemail'] = 'Failed to send test student account email.';
$string['teststudentnotexist'] = 'You do not have a test student account.';
$string['teststudentalreadyenrolled'] = 'Your test student account is already enrolled in this course.';
$string['teststudentnotenrolled'] = 'Your test student account is not enrolled in this course.';
$string['teststudentcouldnotenrol'] = 'Failed to enrol test student in this course.';
$string['teststudentcouldnotunenrol'] = 'Failed to unenrol test student from this course.';
$string['noenrolmethod'] = 'Could not find manual enrolment plugin for this course.';

// Login Form
$string['maintenance_mode'] = 'UR Courses is in maintenance mode.';
$string['logincas'] = 'Log in with CAS';
$string['logincas_subtitle'] = 'I have a uregina username and password';
$string['loginother'] = 'Log in with other credentials';
$string['loginhelp_header'] = 'Need help logging in?';
$string['loginhelp_newstudent'] = 'Are you a new student?';
$string['loginhelp_forgotpassword'] = 'Forgot your username or password?';
$string['loginhelp_activate'] = 'Activate your account?';
$string['login_help'] = 'For further assistance, please contact <a href="mailto:Service.Desk@uregina.ca">Service.Desk@uregina.ca</a> or call <a href="tel:+1-306-585-4685">(306) 585-4685</a>';

// Course Header
$string['instructorpicture_alt'] = 'Profile picture of {$a}';

// Enrolment Course Hint
$string['winter'] = 'Winter';
$string['springsummer'] = 'Spring/Summer';
$string['fall'] = 'Fall';
$string['hasenrolment'] = 'This course has active Banner enrolment for {$a}.';
$string['haspastenrolment'] = 'This course page has Banner enrolment from {$a}.';
$string['haspastenrolment_help'] = 'If you would like to reuse this course page, {$a} and add enrolment to the course copy.';
$string['noenrolment'] = 'This course page does not have Banner enrolment.';
$string['addenrolment'] = 'Add Enrolment';
$string['duplicatecourse'] = 'Duplicate Course';
$string['duplicatethecourse'] = 'duplicate the course';
$string['editenrolment'] = 'Instructions';

// Date Course Hint
$string['hint_coursehasended'] = 'The end date for this course page is in the past ({$a}).';
$string['hint_coursenotstarted'] = 'Course begins {$a}.';
$string['datebutton'] = 'Edit Dates';

// Status Hint
$string['timestatus_current'] = 'This course is in progress.';
$string['timestatus_current_noenddate'] = 'This course is in progress (end date not set).';
$string['timestatus_past'] = 'This course ended {$a}.';
$string['timestatus_future'] = 'This course begins {$a}.';
$string['hasenrollment'] = 'There is active enrolment ({$a}).';
$string['noenrollment'] = 'There is no active enrolment.';
$string['visible'] = 'The course is visible to students.';
$string['notvisible'] = 'The course is hidden from students.';
$string['visible_noenrollment'] = 'The course is visible.';
$string['notvisible_noenrollment'] = 'The course is hidden.';
$string['showcourse'] = 'Show Course';
$string['hidecourse'] = 'Hide Course';
$string['showtitle'] = 'Show Course?';
$string['showbody'] = 'Are you sure you want to show this course? The course will be visible to students enrolled in the {$a} semester.';
$string['showbody_noenrollment'] = 'Are you sure you want to show this course? If students are enrolled, the course will be visible to them.';
$string['hidetitle'] = 'Hide Course?';
$string['hidebody'] = 'Are you sure you want to hide this course? This course will be hidden from students enrolled in the {$a} semester.';
$string['hidebody_noenrollment'] = 'Are you sure you want to hide this course? If students are enrolled, the course will be hidden from them.';
$string['confirmbutton'] = 'Confirm';

// Settings
$string['colourtab'] = 'Colours';
$string['colourheading'] = 'Colours';
$string['brandcoloursetting'] = 'Brand Colour';
$string['brandcoloursetting_desc'] = 'Change the brand colour for this theme.';
$string['feedbacktab'] = 'Feedback';
$string['feedbackheading'] = 'Feedback';
$string['questionnaireid'] = 'Questionnaire';
$string['questionnaireid_desc'] = 'Questionnaire activity from the home page where suggestions will be stored.';
$string['questionname'] = 'Questionnaire Question';
$string['questionname_desc'] = 'Which question we will associate suggestions with.';


// Capabilities
$string['urcourses_default:viewenrolhint'] = 'View course enrolment hint.';