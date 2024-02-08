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
 * Theme Boost Campus - Locallib file
 *
 * @package   theme_urcourses_default
 * @copyright 2017 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 
 function theme_urcourses_default_get_course_related_hints() {
	return theme_boost_union_get_course_related_hints();
 }


 /**
  * Build the link to the imprint page.
  *
  * @return string.
  */
 function theme_urcourses_default_get_imprint_link() {
	return theme_boost_union_get_imprint_link();
 }

 /**
  * Build the page title of the imprint page.
  *
  * @return string.
  */
 function theme_urcourses_default_get_imprint_pagetitle() {
    return theme_boost_union_get_imprint_pagetitle();
 }
 
 /**
  * Helper function to check if a given info banner should be shown on this page.
  * This function checks
  * a) if the banner is enabled at all
  * b) if the banner has any content (i.e. is not empty)
  * b) if the banner is configured to be shown on the given page
  * c) if the banner is configured to be shown now (in case it is a time-based banner)
  *
  * @copyright  2022 Moodle an Hochschulen e.V. <kontakt@moodle-an-hochschulen.de>
  * @copyright  based on code from theme_boost_campus by Kathrin Osswald.
  *
  * @param int $bannerno The counting number of the info banner.
  *
  * @return boolean.
  */
 function theme_urcourses_default_infobanner_is_shown_on_page($bannerno) {
	return theme_boost_union_infobanner_is_shown_on_page($bannerno);
 }
 
 
 /**
  * Helper function to compare two infobanner orders.
  *
  * @param int $a The first value
  * @param int $b The second value
  *
  * @return boolean.
  */
 function theme_urcourses_default_infobanner_compare_order($a, $b) {
	return theme_boost_union_infobanner_compare_order($a, $b);
 }
 
  
 /**
  * Helper function to reset the visibility of a given info banner.
  *
  * @param int $no The number of the info banner.
  *
  * @return bool True if everything went fine, false if at least one user couldn't be resetted.
  */
 function theme_urcourses_default_infobanner_reset_visibility($no) {
	return theme_boost_union_infobanner_reset_visibility($no);
} 
  
  
/**
 * Get the random number for displaying the background image on the login page randomly.
 *
 * @return int|null
 * @throws coding_exception
 * @throws dml_exception
 */
function theme_urcourses_default_get_random_loginbackgroundimage_number() {
	return theme_boost_union_get_random_loginbackgroundimage_number();
}
  
  
/**
 * Get a random class for body tag for the background image of the login page.
 *
 * @return string
 */
function theme_urcourses_default_get_random_loginbackgroundimage_class() {
	return theme_boost_union_get_random_loginbackgroundimage_class();
}	
	
/**
 * Return the files from the loginbackgroundimage file area.
 * This function always loads the files from the filearea which is not really performant.
 * However, we accept this at the moment as it is only invoked on the login page.
 *
 * @return array|null
 * @throws coding_exception
 * @throws dml_exception
 */
function theme_urcourses_default_get_loginbackgroundimage_files() {
	return theme_boost_union_get_loginbackgroundimage_files();
}	



/**
 * Add background images from setting 'loginbackgroundimage' to SCSS.
 *
 * @return string
 */
function theme_urcourses_default_get_loginbackgroundimage_scss() {
	return theme_boost_union_get_loginbackgroundimage_scss();
}


/**
 * Get the text that should be displayed for the randomly displayed background image on the login page.
 *
 * @return array (of two strings, holding the text and the text color)
 * @throws coding_exception
 * @throws dml_exception
 */
function theme_urcourses_default_get_loginbackgroundimage_text() {
	return theme_boost_union_get_loginbackgroundimage_text();
}

/**
 * Return the files from the additionalresources file area as templatecontext structure.
 * It was designed to compose the files for the settings-additionalresources-filelist.mustache template.
 * This function always loads the files from the filearea which is not really performant.
 * Thus, you have to take care where and how often you use it (or add some caching).
 *
 * @return array|null
 * @throws coding_exception
 * @throws dml_exception
 */
function theme_urcourses_default_get_additionalresources_templatecontext() {
	return theme_boost_union_get_additionalresources_templatecontext();
}	

/**
 * Return the files from the customfonts file area as templatecontext structure.
 * It was designed to compose the files for the settings-customfonts-filelist.mustache template.
 * This function always loads the files from the filearea which is not really performant.
 * Thus, you have to take care where and how often you use it (or add some caching).
 *
 * @return array|null
 * @throws coding_exception
 * @throws dml_exception
 */
function theme_urcourses_default_get_customfonts_templatecontext() {
	return theme_boost_union_get_customfonts_templatecontext();
}

/**
 * Helper function which makes sure that all webfont file types are registered in the system.
 * The webfont file types need to be registered in the system, otherwise the admin settings filepicker wouldn't allow restricting
 * the uploadable file types to webfonts only.
 *
 * @return void
 * @throws coding_exception
 */
function theme_urcourses_default_register_webfonts_filetypes() {
	return theme_boost_union_register_webfonts_filetypes();
}	

/* Get the course image if added to course.
 *
 * @param object $course
 * @return string url of course image
 */
function theme_urcourses_default_get_course_image($course) {
	return theme_urcourses_default_get_course_image_url($course);
}


function theme_urcourses_default_get_course_image_old($course) {
    global $CFG;
    $courseinlist = new \core_course_list_element($course);
    foreach ($courseinlist->get_course_overviewfiles() as $file) {
        if ($file->is_valid_image()) {
            $pathcomponents = [
                '/pluginfile.php',
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea() . $file->get_filepath() . $file->get_filename()
            ];
            $path = implode('/', $pathcomponents);
            return (new moodle_url($path))->out();
        }
    }
    return false;
}

// UOFR HACK dapiawej September 29, 2023
// Retrieve course image and handle spaces, special characters in file names.
function theme_urcourses_default_get_course_image_url() {
    global $PAGE;

    if (isset($PAGE->course->id) && $PAGE->course->id == SITEID) {
        return null;
    }
    // Get the course image.
    $courseimage = \core_course\external\course_summary_exporter::get_course_image($PAGE->course);

    // If the course has a course image.
    if ($courseimage) {
        // Then return it.
        return $courseimage;

        // Otherwise, if a fallback image is configured.
    } else if (get_config('theme_boost_union', 'courseheaderimagefallback')) {
       
        $systemcontext = \context_system::instance();

        $fs = get_file_storage();

        $files = $fs->get_area_files($systemcontext->id, 'theme_boost_union', 'courseheaderimagefallback',
            false, 'itemid', false);

        $file = reset($files);

        return moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
            $file->get_itemid(), $file->get_filepath(), $file->get_filename());
    }

    //As no image was found, return null.
    return null;
}


/**
* UR HACK
* Return true or false if current user has a test account
* for course
* @return bool
*/
function theme_urcourses_default_check_test_account($username){
    global $DB;

    //get username to create email
    $email = $username."+urstudent@uregina.ca";
    //check if test user account has already been created
    $select = 'SELECT * FROM mdl_user WHERE email ='.$email.';';
    $sql = "SELECT * FROM mdl_user as u WHERE u.email ='{$email}'";  
    $user = $DB->get_record_sql($sql);
    
    //if created
    if($user){
        return true;
    }

    return false;
}

/**
 * Return the UR Category class for a given course id.
 * @param int $courseid
 * @return string
 */

 function theme_urcourses_default_get_ur_category_class($courseid) {
	global $CFG, $DB;
	
	$ur_css_class = '';
	
	$ur_categories = array('','misc'=>'',
		'khs'=>'Faculty of Kinesiology and Health Studies',
		'edu'=>'Faculty of Education',
		'sci'=>'Faculty of Science',
		'map'=>'Faculty of Media, Art, and Performance',
		'engg'=>'Faculty of Engineering',
		'bus'=>'Business Administration',
		'arts'=>'Faculty of Arts',
		'sw'=>'Faculty of Social Work',
		'nur'=>'Faculty of Nursing',
		'scbscn'=>'Saskatchewan Collaborative Bachelor of Science in Nursing',
		'luther'=>'Luther College',
		'campion'=>'Campion College',
		'cnpp'=>'Collaborative Nurse Practitioner Program',
		'lacite'=>'La Cité universitaire francophone',
		'fnuniv'=>'First Nations University of Canada',
		'gbus'=>'Kenneth Levene Graduate School of Business',
		'jsgspp'=>'Johnson-Shoyama Graduate School of Public Policy',
		'misc'=>'Custom Themes');

	
	// Check theme first
	
	if (!is_numeric($courseid)) { $courseid=0; } // some IDs not numeric? check...
		
	$sql = "SELECT `theme` FROM mdl_course WHERE id={$courseid}";	
	
    $check_course_theme = $DB->get_record_sql($sql);
    //debugging("Themes: " . $check_course_theme->theme  . "Course ID: " . $courseid, DEBUG_DEVELOPER);
	
	if (!empty($check_course_theme->theme)) {
		$clean_theme_key = substr($check_course_theme->theme, 0, 16); //'urcourses_clean_'
        $default_theme_key = substr($check_course_theme->theme, 0, 10); //'urcourses_'
        
        if ($clean_theme_key == 'urcourses_clean_') {
            $theme_val = substr($check_course_theme->theme, 16);
        }
        else if ($default_theme_key == 'urcourses_') {
            $theme_val = substr($check_course_theme->theme, 10);
        } else {
        	$theme_val = '';
        }
		
		
		$exc_themes = array('sw'=>'socialwork',
			'map'=>'finearts',
			'edu'=>'education',
			'bus'=>'business',
			'nur'=>'nursing',
			'sci'=>'science');
		
		$key = array_search($theme_val,$exc_themes);
		if (!empty($key)) $theme_val = $key;	
		
        return $theme_val;
	}
	
		
	//if default theme, then check category
	
	$sql = "SELECT a.name FROM {$CFG->prefix}course_categories a, {$CFG->prefix}course b WHERE a.id = b.category AND b.id = {$courseid}";
	
	$check_course_category = $DB->get_record_sql($sql);
	if ($check_course_category) {
		$key = array_search($check_course_category->name,$ur_categories);
		if (!empty($key)) $ur_css_class = $key;
	}
	
	return $ur_css_class;
}

/**
 * Check whether or not the course visibility toggle should be shown.
 * The toggle is shown when the user is editing a course page, or if the user is looking at a hidden course.
 * 
 * @return bool True if tool should be shown. Otherwise, False.
 */
function theme_urcourses_default_is_show_visibility_toggle() {
    global $COURSE, $PAGE;

    $context = \context_course::instance($COURSE->id, IGNORE_MISSING);
    $canviewhidden = has_capability('moodle/course:viewhiddencourses', $context);

    $courseid = $COURSE->id;
    $pageurl = $PAGE->url;
    $course_viewurl = new moodle_url('/course/view.php', array('id' => $courseid));
    $course_editurl = new moodle_url('/course/edit.php', array('id' => $courseid));

    $is_on_course_view = $pageurl->compare($course_viewurl, URL_MATCH_BASE);
    $is_on_course_edit = $pageurl->compare($course_editurl, URL_MATCH_BASE);

    $is_on_course_page = $is_on_course_view || $is_on_course_edit;
    $is_user_editing = $PAGE->user_is_editing();
    $is_course_visible = $canviewhidden && $COURSE->visible == 0;

    return ($is_on_course_page && ($is_user_editing || $is_course_visible));
}

/**
 * UR HACK
 * Set the question list to active and show in quiz add button
 */
function theme_urcourses_add_quiz_question_edits(){
    echo '<script>
    const navLinks = document.querySelectorAll("a.moduletypetitle");
    
    const menu = document.querySelector("#modchooser_questions");
    navLinks[0].classList.add("active");
    menu.classList.add("active");
    menu.classList.add("show");
    
    </script>';
}

/**
 * Gets enrollment information for the course specified by $courseid.
 * 
 * @return array
 */
function theme_urcourses_default_get_course_enrollment(int $courseid) {
    global $CFG, $DB;
    
    $is_urcourserequest_exist = is_file($CFG->dirroot.'/admin/tool/urcourserequest/lib.php');
    $course_exists = $DB->record_exists('course', array('id' => $courseid));

    if ($is_urcourserequest_exist && $course_exists) {
        require_once($CFG->dirroot.'/admin/tool/urcourserequest/lib.php');

        $course = get_course($courseid);
        $enrollment = tool_urcourserequest_get_course_state($course->idnumber);

        return $enrollment === false ? array() : $enrollment;
    }
    else {
        return array();
    }
}