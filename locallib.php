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

 
 function theme_uofr_conservatory_get_course_related_hints() {
	 return theme_boost_union_get_course_related_hints();
 }


 /**
  * Build the link to the imprint page.
  *
  * @return string.
  */
 function theme_uofr_conservatory_get_imprint_link() {
	 return theme_boost_union_get_imprint_link();
 }

 /**
  * Build the page title of the imprint page.
  *
  * @return string.
  */
 function theme_uofr_conservatory_get_imprint_pagetitle() {
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
 function theme_uofr_conservatory_infobanner_is_shown_on_page($bannerno) {
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
 function theme_uofr_conservatory_infobanner_compare_order($a, $b) {
	 return theme_boost_union_infobanner_compare_order($a, $b);
 }
 
  
 /**
  * Helper function to reset the visibility of a given info banner.
  *
  * @param int $no The number of the info banner.
  *
  * @return bool True if everything went fine, false if at least one user couldn't be resetted.
  */
 function theme_uofr_conservatory_infobanner_reset_visibility($no) {
	 return theme_boost_union_infobanner_reset_visibility($no);
} 
  
  
/**
 * Get the random number for displaying the background image on the login page randomly.
 *
 * @return int|null
 * @throws coding_exception
 * @throws dml_exception
 */
function theme_uofr_conservatory_get_random_loginbackgroundimage_number() {
	return theme_boost_union_get_random_loginbackgroundimage_number();
}
  
  
/**
 * Get a random class for body tag for the background image of the login page.
 *
 * @return string
 */
function theme_uofr_conservatory_get_random_loginbackgroundimage_class() {
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
function theme_uofr_conservatory_get_loginbackgroundimage_files() {
	return theme_boost_union_get_loginbackgroundimage_files();
}	



/**
 * Add background images from setting 'loginbackgroundimage' to SCSS.
 *
 * @return string
 */
function theme_uofr_conservatory_get_loginbackgroundimage_scss() {
	return theme_boost_union_get_loginbackgroundimage_scss();
}


/**
 * Get the text that should be displayed for the randomly displayed background image on the login page.
 *
 * @return array (of two strings, holding the text and the text color)
 * @throws coding_exception
 * @throws dml_exception
 */
function theme_uofr_conservatory_get_loginbackgroundimage_text() {
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
function theme_uofr_conservatory_get_additionalresources_templatecontext() {
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
function theme_uofr_conservatory_get_customfonts_templatecontext() {
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
function theme_uofr_conservatory_register_webfonts_filetypes() {
	return theme_boost_union_register_webfonts_filetypes();
}	

/* Get the course image if added to course.
 *
 * @param object $course
 * @return string url of course image
 */
function theme_uofr_conservatory_get_course_image($course) {
	return theme_boost_union_get_course_image($course);
}
