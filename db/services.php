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

);