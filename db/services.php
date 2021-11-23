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
 * Services
 * @author    John Lane
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'theme_uofr_conservatory_upload_course_image' => array(
        'classname'     => 'theme_uofr_conservatory_external',
        'methodname'    => 'upload_course_image',
        'classpath'     => 'theme/uofr_conservatory/externallib.php',
        'description'   => 'Changes course image.',
        'type'          => 'write',
        'ajax'          => 'true',
    ),
    'theme_uofr_conservatory_header_choose_style' => array(
        'classname'     => 'theme_uofr_conservatory_external',
        'methodname'    => 'choose_header_style',
        'classpath'     => 'theme/uofr_conservatory/externallib.php',
        'description'   => 'Changes header style',
        'type'          => 'write',
        'ajax'          => 'true',
    ),
    'theme_uofr_conservatory_toggle_course_availability' => array(
        'classname'     => 'theme_uofr_conservatory_external',
        'methodname'    => 'toggle_course_availability',
        'classpath'     => 'theme/uofr_conservatory/externallib.php',
        'description'   => 'Toggles course availability',
        'type'          => 'write',
        'ajax'          => 'true',
    ),
    'theme_uofr_conservatory_get_landing_page' => array(
        'classname'     => 'theme_uofr_conservatory_external',
        'methodname'    => 'get_landing_page',
        'classpath'     => 'theme/uofr_conservatory/externallib.php',
        'description'   => 'Gets landing page data for help modal.',
        'type'          => 'read',
        'ajax'          => 'true'
    ),
    'theme_uofr_conservatory_get_topic_list' => array(
        'classname'     => 'theme_uofr_conservatory_external',
        'methodname'    => 'get_topic_list',
        'classpath'     => 'theme/uofr_conservatory/externallib.php',
        'description'   => 'Gets topic list for help menu.',
        'type'          => 'read',
        'ajax'          => 'true'
	),
	'theme_uofr_conservatory_get_guide_page' => array(
		'classname'     => 'theme_uofr_conservatory_external',
        'methodname'    => 'get_guide_page',
        'classpath'     => 'theme/uofr_conservatory/externallib.php',
        'description'   => 'Gets a page from the guides.',
        'type'          => 'read',
        'ajax'          => 'true'
    ),
    'theme_uofr_conservatory_modal_help_search' => array(
        'classname'     => 'theme_uofr_conservatory_external',
        'methodname'    => 'modal_help_search',
        'classpath'     => 'theme/uofr_conservatory/externallib.php',
        'description'   => 'Searches the guides.',
        'type'          => 'read',
        'ajax'          => 'true'
    ),
    'theme_uofr_conservatory_user_is_instructor' => array(
        'classname'     => 'theme_urcourses_default_external',
        'methodname'    => 'user_is_instructor',
        'classpath'     => 'theme/urcourses_default/externallib.php',
        'description'   => 'Checks if current user is instructor.',
        'type'          => 'write',
        'ajax'          => 'true'
    )
);