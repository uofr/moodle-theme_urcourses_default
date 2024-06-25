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
 * Services for theme_urcourses_default.
 *
 * @author  John Lane
 */

defined('MOODLE_INTERNAL') || die();

 $functions = array(
    'theme_urcourses_default_toggle_course_visibility' => array(
        'classname'     => 'theme_urcourses_default\external\course_visibility_toggle',
        'methodname'    => 'execute',
        'description'   => 'Toggle course visibility between Show and Hide.',
        'type'          => 'write',
        'ajax'          => true
    ),
    'theme_urcourses_default_create_test_account' => array(
       'classname'     => 'theme_urcourses_default_external',
       'methodname'    => 'create_test_account',
       'classpath'     => 'theme/urcourses_default/externallib.php',
       'description'   => 'Creates and enrolls a test student account based on username.',
       'type'          => 'write',
       'ajax'          => 'true'
    ),
    'theme_urcourses_default_unenroll_test_account' => array(
       'classname'     => 'theme_urcourses_default_external',
       'methodname'    => 'unenroll_test_account',
       'classpath'     => 'theme/urcourses_default/externallib.php',
       'description'   => 'Removes user test student account from course',
       'type'          => 'write',
       'ajax'          => 'true'
    ),
    'theme_urcourses_default_test_account_info' => array(
       'classname'     => 'theme_urcourses_default_external',
       'methodname'    => 'test_account_info',
       'classpath'     => 'theme/urcourses_default/externallib.php',
       'description'   => 'Returns info on test account',
       'type'          => 'read',
       'ajax'          => 'true'
    ),
    'theme_urcourses_default_reset_test_account' => array(
       'classname'     => 'theme_urcourses_default_external',
       'methodname'    => 'reset_test_account',
       'classpath'     => 'theme/urcourses_default/externallib.php',
       'description'   => 'Reset password for student test account',
       'type'          => 'write',
       'ajax'          => 'true'
    )
);