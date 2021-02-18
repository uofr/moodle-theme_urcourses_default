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

 use \core_course\external\course_summary_exporter;
 defined('MOODLE_INTERNAL') || die();
 
    /**
     * Return the files from the loginbackgroundimage file area.
     * This function always loads the files from the filearea that is not really performant.
     * However, we accept this at the moment as it is only invoked on the login page.
     *
     * @return array|null
     * @throws coding_exception
     * @throws dml_exception
     */
    function theme_urcourses_default_get_loginbackgroundimage_files() {
        
        // Static variable to remember the files for subsequent calls of this function.
        static $files = null;
        
        if ($files == null) {
            // Get the system context.
            $systemcontext = \context_system::instance();
            
            // Get filearea.
            $fs = get_file_storage();
            
            // Get all files from filearea.
            $files = $fs->get_area_files($systemcontext->id, 'theme_urcourses_default', 'loginbackgroundimage',
                                         false, 'itemid', false);
        }
        
        return $files;
    }
    
    /**
     * Get the random number for displaying the background image on the login page randomly.
     *
     * @return int|null
     * @throws coding_exception
     * @throws dml_exception
     */
    function theme_urcourses_default_get_random_loginbackgroundimage_number() {
        
        // Static variable.
        static $number = null;
        
        if ($number == null) {
            // Get all files for loginbackgroundimages.
            $files = theme_urcourses_default_get_loginbackgroundimage_files();
            
            // Get count of array elements.
            $filecount = count($files);
            
            // We only return a number if images are uploaded to the loginbackgroundimage file area.
            if ($filecount > 0) {
                // Generate random number.
                $number = rand(1, $filecount);
            }
        }
        
        return $number;
    }
    
    /**
     * Get a random class for body tag for the background image of the login page.
     *
     * @return string
     */
    function theme_urcourses_default_get_random_loginbackgroundimage_class() {
        // Get the static random number.
        $number = theme_urcourses_default_get_random_loginbackgroundimage_number();
        
        // Only create the class name with the random number if there is a number (=files uploaded to the file area).
        if ($number != null) {
            return "loginbackgroundimage" . $number;
        } else {
            return "";
        }
    }
    
    /**
     * Get the text that should be displayed for the randomly displayed background image on the login page.
     *
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    function theme_urcourses_default_get_loginbackgroundimage_text() {
        // Get the random number.
        $number = theme_urcourses_default_get_random_loginbackgroundimage_number();
        
        // Only search for the text if there's a background image.
        if ($number != null) {
            
            // Get the files from the filearea loginbackgroundimage.
            $files = theme_urcourses_default_get_loginbackgroundimage_files();
            // Get the file for the selected random number.
            $file = array_slice($files, ($number - 1), 1, false);
            // Get the filename.
            $filename = array_pop($file)->get_filename();
            
            // Get the config for loginbackgroundimagetext and make array out of the lines.
            $lines = explode("\n", get_config('theme_urcourses_default', 'loginbackgroundimagetext'));
            
            // Proceed the lines.
            foreach ($lines as $line) {
                $settings = explode("|", $line);
                // Compare the filenames for a match and return the text that belongs to the randomly selected image.
                if (strcmp($filename, $settings[0]) == 0) {
                    return format_string($settings[1]);
                    break;
                }
            }
        } else {
            return "";
        }
    }
    
    /**
     * Add background images from setting 'loginbackgroundimage' to SCSS.
     *
     * @return string
     */
    function theme_urcourses_default_get_loginbackgroundimage_scss() {
        $count = 0;
        $scss = "";
        
        // Get all files from filearea.
        $files = theme_urcourses_default_get_loginbackgroundimage_files();
        
        // Add URL of uploaded images to eviqualent class.
        foreach ($files as $file) {
            $count++;
            // Get url from file.
            $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                                                   $file->get_itemid(), $file->get_filepath(), $file->get_filename());
            // Add this url to the body class loginbackgroundimage[n] as a background image.
            $scss .= '$loginbackgroundimage' . $count.': "' . $url . '";';
        }
        
        return $scss;
    }

/**
 * Create information needed for the imagearea.mustache file.
 *
 * @return array
 */
function theme_urcourses_default_get_imageareacontent() {
    // Get cache.
    $themeboostcampuscache = cache::make('theme_urcourses_default', 'imagearea');
    // If cache is filled, return the cache.
    $cachecontent = $themeboostcampuscache->get('imageareadata');
    if (!empty($cachecontent)) {
        return $cachecontent;
    } else { // Create cache.
        // Fetch context.
        $systemcontext = \context_system::instance();
        // Get filearea.
        $fs = get_file_storage();
        // Get all files from filearea.
        $files = $fs->get_area_files($systemcontext->id, 'theme_urcourses_default', 'imageareaitems', false, 'itemid', false);

        // Only continue processing if there are files in the filearea.
        if (!empty($files)) {
            // Get the content from the setting imageareaitemslink and explode it to an array by the delimiter "new line".
            // The string contains: the image identifier (uploaded file name) and the corresponding link URL.
            $lines = explode("\n", get_config('theme_urcourses_default', 'imageareaitemslink'));
            // Parse item settings.
            foreach ($lines as $line) {
                $line = trim($line);
                // If the setting is empty.
                if (strlen($line) == 0) {
                    // Create an array with a dummy entry because the function array_key_exists need a
                    // not empty array for parameter 2.
                    $links = array('foo');
                    $alttexts = array('bar');
                    continue;
                } else {
                    $settings = explode("|", $line);
                    // Check if parameter 2 or 3 is set.
                    if (!empty($settings[1]) || !empty($settings[2])) {
                        foreach ($settings as $i => $setting) {
                            $setting = trim($setting);
                            if (!empty($setting)) {
                                switch ($i) {
                                    // Check for the first param: link.
                                    case 1:
                                        // The name of the image is the key for the URL that will be set.
                                        $links[$settings[0]] = $settings[1];
                                        break;
                                    // Check for the second param: alt text.
                                    case 2:
                                        // The name of the image is the key for the alt text that will be set.
                                        $alttexts[$settings[0]] = $settings[2];
                                        break;
                                }
                            }
                        }
                    }
                }
            }
            // Initialize the array which holds the data which is later stored in the cache.
            $imageareacache = [];
            // Traverse the files.
            foreach ($files as $file) {
                // Get the Moodle url for each file.
                $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                        $file->get_itemid(), $file->get_filepath(), $file->get_filename());
                // Get the path to the file.
                $filepath = $url->get_path();
                // Get the filename.
                $filename = $file->get_filename();
                // If filename and link value from the imageareaitemsattributes setting entry match.
                if (array_key_exists($filename, $links)) {
                    $linkpath = $links[$filename];
                } else {
                    $linkpath = "";
                }
                // If filename and alt text value from the imageareaitemsattributes setting entry match.
                if (array_key_exists($filename, $alttexts)) {
                    $alttext = $alttexts[$filename];
                } else {
                    $alttext = "";
                }
                // Add the file.
                $imageareacache[] = array('filepath' => $filepath, 'linkpath' => $linkpath, 'alttext' => $alttext);
            }
            // Sort array alphabetically ascending to the key "filepath".
            usort($imageareacache, function($a, $b) {
                return strcmp($a["filepath"], $b["filepath"]);
            });
            // Fill the cache.
            $themeboostcampuscache->set('imageareadata', $imageareacache);
            return $imageareacache;
        } else { // If no images are uploaded, then cache an empty array.
            $themeboostcampuscache->set('imageareadata', array());
            return array();
        }
    }
}

/**
 * Returns a modified flat_navigation object.
 *
 * @param flat_navigation $flatnav The flat navigation object.
 * @return flat_navigation.
 */
function theme_urcourses_default_process_flatnav(flat_navigation $flatnav) {
    global $USER;
    // If the setting defaulthomepageontop is enabled.
    if (get_config('theme_urcourses_default', 'defaulthomepageontop') == 'yes') {
        // Only proceed processing if we are in a course context.
        if (($coursehomenode = $flatnav->find('coursehome', global_navigation::TYPE_CUSTOM)) != false) {
            // If the site home is set as the default homepage by the admin.
            if (get_config('core', 'defaulthomepage') == HOMEPAGE_SITE) {
                // Return the modified flat_navigation.
                $flatnavreturn = theme_urcourses_default_set_node_on_top($flatnav, 'home', $coursehomenode);
            } else if (get_config('core', 'defaulthomepage') == HOMEPAGE_MY) { // If the dashboard is set as the default homepage
                // by the admin.
                // Return the modified flat_navigation.
                $flatnavreturn = theme_urcourses_default_set_node_on_top($flatnav, 'myhome', $coursehomenode);
            } else if (get_config('core', 'defaulthomepage') == HOMEPAGE_USER) { // If the admin defined that the user can set
                // the default homepage for himself.
                // Site home.
                if (get_user_preferences('user_home_page_preference') == 0) {
                    // Return the modified flat_navigtation.
                    $flatnavreturn = theme_urcourses_default_set_node_on_top($flatnav, 'home', $coursehomenode);
                } else if (get_user_preferences('user_home_page_preference') == 1 || // Dashboard.
                    get_user_preferences('user_home_page_preference') === false) { // If no user preference is set,
                    // use the default value of core setting default homepage (Dashboard).
                    // Return the modified flat_navigtation.
                    $flatnavreturn = theme_urcourses_default_set_node_on_top($flatnav, 'myhome', $coursehomenode);
                } else { // Should not happen.
                    // Return the passed flat navigation without changes.
                    $flatnavreturn = $flatnav;
                }
            } else { // Should not happen.
                // Return the passed flat navigation without changes.
                $flatnavreturn = $flatnav;
            }
        } else { // Not in course context.
            // Return the passed flat navigation without changes.
            $flatnavreturn = $flatnav;
        }
    } else { // Defaulthomepageontop not enabled.
        // Return the passed flat navigation without changes.
        $flatnavreturn = $flatnav;
    }

    return $flatnavreturn;
}

/**
 * Modifies the flat_navigation to add the node on top.
 *
 * @param flat_navigation $flatnav The flat navigation object.
 * @param string $nodename The name of the node that is to modify.
 * @param navigation_node $beforenode The node before which the to be modified node shall be added.
 * @return flat_navigation.
 */
function theme_urcourses_default_set_node_on_top(flat_navigation $flatnav, $nodename, $beforenode) {
    // Get the node for which the sorting shall be changed.
    $pageflatnav = $flatnav->find($nodename, global_navigation::TYPE_SYSTEM);

    // If user is logged in as a guest pageflatnav is false. Only proceed here if the result is true.
    if (!empty($pageflatnav)) {
        // Set the showdivider of the new top node to false that no empty nav-element will be created.
        $pageflatnav->set_showdivider(false);
        // Add the showdivider to the coursehome node as this is the next one and this will add a margin top to it.
        $beforenode->set_showdivider(true, $beforenode->text);
        // Remove the site home navigation node that it does not appear twice in the menu.
        $flatnav->remove($nodename);
        // Set the collection label for this node.
        $flatnav->set_collectionlabel($pageflatnav->text);
        // Add the saved site home node before the $beforenode.
        $flatnav->add($pageflatnav, $beforenode->key);
    }

    // Return the modified changes.
    return $flatnav;
}


/**
 * Provides the node for the in-course course or activity settings.
 *
 * @return navigation_node.
 */
function theme_urcourses_default_get_incourse_settings() {
    global $COURSE, $PAGE;
    // Initialize the node with false to prevent problems on pages that do not have a courseadmin node.
    $node = false;
    // If setting showsettingsincourse is enabled.
    if (get_config('theme_urcourses_default', 'showsettingsincourse') == 'yes') {
        // Only search for the courseadmin node if we are within a course or a module context.
        if ($PAGE->context->contextlevel == CONTEXT_COURSE || $PAGE->context->contextlevel == CONTEXT_MODULE) {
            // Get the courseadmin node for the current page.
            $node = $PAGE->settingsnav->find('courseadmin', navigation_node::TYPE_COURSE);
            // Check if $node is not empty for other pages like for example the langauge customization page.
            if (!empty($node)) {
                // If the setting 'incoursesettingsswitchtoroleposition' is set either set to the option 'yes'
                // or to the option 'both', then add these to the $node.
                if (((get_config('theme_urcourses_default', 'incoursesettingsswitchtoroleposition') == 'yes') ||
                    (get_config('theme_urcourses_default', 'incoursesettingsswitchtoroleposition') == 'both'))
                    && !is_role_switched($COURSE->id)) {
                    // Build switch role link
                    // We could only access the existing menu item by creating the user menu and traversing it.
                    // So we decided to create this node from scratch with the values copied from Moodle core.
                    $roles = get_switchable_roles($PAGE->context);
                    if (is_array($roles) && (count($roles) > 0)) {
                        // Define the properties for a new tab.
                        $properties = array('text' => get_string('switchroleto', 'theme_urcourses_default'),
                                            'type' => navigation_node::TYPE_CONTAINER,
                                            'key'  => 'switchroletotab');
                        // Create the node.
                        $switchroletabnode = new navigation_node($properties);
                        // Add the tab to the course administration node.
                        $node->add_node($switchroletabnode);
                        // Add the available roles as children nodes to the tab content.
                        foreach ($roles as $key => $role) {
                            $properties = array('action' => new moodle_url('/course/switchrole.php',
                                array('id'         => $COURSE->id,
                                      'switchrole' => $key,
                                      'returnurl'  => $PAGE->url->out_as_local_url(false),
                                      'sesskey'    => sesskey())),
                                                'type'   => navigation_node::TYPE_CUSTOM,
                                                'text'   => $role);
                            $switchroletabnode->add_node(new navigation_node($properties));
                        }
                    }
                }
            }
        }
        return $node;
    }
}

/**
 * Provides the node for the in-course settings for other contexts.
 *
 * @return navigation_node.
 */
function theme_urcourses_default_get_incourse_activity_settings() {
    global $PAGE;
    $context = $PAGE->context;
    $node = false;
    // If setting showsettingsincourse is enabled.
    if (get_config('theme_urcourses_default', 'showsettingsincourse') == 'yes') {
        // Settings belonging to activity or resources.
        if ($context->contextlevel == CONTEXT_MODULE) {
            $node = $PAGE->settingsnav->find('modulesettings', navigation_node::TYPE_SETTING);
        } else if ($context->contextlevel == CONTEXT_COURSECAT) {
            // For course category context, show category settings menu, if we're on the course category page.
            if ($PAGE->pagetype === 'course-index-category') {
                $node = $PAGE->settingsnav->find('categorysettings', navigation_node::TYPE_CONTAINER);
            }
        } else {
            $node = false;
        }
    }
    return $node;
}

/**
 * Build the guest access hint HTML code.
 *
 * @param int $courseid The course ID.
 * @return string.
 */
function theme_urcourses_default_get_course_guest_access_hint($courseid) {
    global $CFG;
    require_once($CFG->dirroot . '/enrol/self/lib.php');

    $html = '';
    $instances = enrol_get_instances($courseid, true);
    $plugins = enrol_get_plugins(true);
    foreach ($instances as $instance) {
        if (!isset($plugins[$instance->enrol])) {
            continue;
        }
        $plugin = $plugins[$instance->enrol];
        if ($plugin->show_enrolme_link($instance)) {
            $html = html_writer::tag('div', get_string('showhintcourseguestaccesslink',
                'theme_urcourses_default', array('url' => $CFG->wwwroot . '/enrol/index.php?id=' . $courseid)));
            break;
        }
    }

    return $html;
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
 * Return array of template courses for creation
 * @return array
 */

function theme_urcourses_default_get_course_templates() {

    global $CFG, $DB, $PAGE;
    
    //get category for Template Course
    $sql = "SELECT * FROM mdl_course_categories WHERE name = 'TEMPLATES' OR name = 'Template';";
    $category = $DB->get_record_sql($sql, null, IGNORE_MISSING);
	
    if (!(array)$category || !$category ){
        return 0;
    }

     //if no Template category
     if (!(array)$category || !$category) {
        return 0;
    }
    //else use category id to find all courses in the templates category
    $sql = "SELECT * FROM mdl_course WHERE category = {$category->id} ORDER BY shortname;";
    $courses = $DB->get_records_sql($sql);
  
    //if no Template category
    if (!(array)$courses) {
        return 0;
    }
    
    $rendercourse = array();
    foreach($courses as $course){

        $temp= array();
        $temp["id"]= $course->id;
        $temp["fullname"]= $course->fullname;

        //used to get images within course summary, and course image
        $urenderer = $PAGE->get_renderer('core');
        $context = \context_course::instance($course->id);
        $exporter = new course_summary_exporter($course, ['context' => $context]);
        $cobits = $exporter->export($urenderer);
        
        $temp["summary"]= $cobits->summary;
        $temp["courseimage"]= $cobits->courseimage;

        $rendercourse[]=$temp;
    }

    return $rendercourse;
}

/**
 * Return a list of course categories for the site
 * @return array
 */

function theme_urcourses_default_get_catergories(){
    global $CFG, $DB;

    //get category for Template Course
    $sql = "SELECT * FROM mdl_course_categories;";
    $categories = $DB->get_records_sql($sql, null, IGNORE_MISSING);
    return $categories;
}

/**
 * Return an array of semesters
 * @return array
 */

function theme_urcourses_default_get_semesters(){

    $year = date("Y");
    $month = date("m");

    if($month >= 1 && $month <=4 ){
        $term = 10;
    }elseif($month >= 5 && $month <=8 ){
        $term = 20;
    }elseif($month >= 9 && $month <=12){
        $term = 30;
    }

    $semesters=array();

    for($i=0; $i<4; $i++){
       
        if ($term > 30) {
            $year += 1;
            $term = 10;
        }
        $semstring = $year;
        if ($term == 10) {
            $semstring .= " " . get_string('winter', 'theme_urcourses_default');
        }elseif ($term == 20) {
            $semstring .= " " . get_string('spring', 'theme_urcourses_default');
        }elseif ($term == 30) {
            $semstring .= " " . get_string('fall', 'theme_urcourses_default');
        }else {
            $semstring = $semester;
        }

        $temp=array("id"=> $year.$term, "title"=>$semstring);
        $semesters[]=$temp;
        $term += 10;
    }

    return $semesters;
}
/**
 * Return an array of semesters with start and end dates
 * @return array
 */

function theme_urcourses_default_get_semesterdates(){

    if(URCOURSEREQUEST){
        return SEMESTERDATES;
    }
    return array();
}

/**
 * Return current semester course is enrolled in
 * @return array
 */

function theme_urcourses_default_get_course_state($courseid){

    global $DB;
    //get course idnumber 

    //use idnumber to sort through map table
    $sql = "SELECT *
            FROM mdl_course 
            WHERE id='$courseid';";

    $course = $DB->get_record_sql($sql,null);

    if(!$course){
        return false;
    }
    
    //return false, or the semester string if found
    return block_urcourserequest_get_course_state($course->idnumber);

}

/**
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
 * Return true or false is current user has a test account 
 * enrolled in current courese
 * @return bool
 */
function theme_urcourses_default_test_account_enrollment($username, $courseid){
    global $DB;

    //get username to create email
    $email = $username."+urstudent@uregina.ca";
    //check if test user account has already been created
    $select = 'SELECT * FROM mdl_user WHERE email ='.$email.';';
    $sql = "SELECT * FROM mdl_user as u WHERE u.email ='{$email}'";
    $user = $DB->get_record_sql($sql);
 
    //if created
    if($user){

        $context = \context_course::instance($courseid);
        //check if user is enrolled already
        $isenrolled = is_enrolled($context, $user, 'mod/assignment:submit');
 
        if($isenrolled){
            return true;
        }
    }
     return false;
}
    /**
     * Checks if the current user is an instructor.
     * Users with the teacher, editingteacher, manager, and coursecreator roles are considered instructors.
     * If we are in the site context, check if the user is an instructor anywhere.
     * Otherwiser, check if the user is an instructor in the given context.
     *
     * @return bool
     */
    function theme_urcourses_default_user_is_instructor() {
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
        
        return $DB->record_exists_select('role_assignments', $roleassign_query_cond, $roleassign_query_arr);
    }
