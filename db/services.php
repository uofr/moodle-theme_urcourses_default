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
 * Services for theme_urcourses_default
 *
 * @module  theme_urcourses_default
 * @author  2024 John Lane <john.lane@uregina.ca>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'theme_urcourses_default_test_student_info' => [
        'classname'     => 'theme_urcourses_default\external\test_student_info',
        'methodname'    => 'execute',
        'description'   => 'Get test student account info for the current user (if one exists).',
        'type'          => 'read',
        'ajax'          => true
    ],
    'theme_urcourses_default_reset_test_student' => [
        'classname'     => 'theme_urcourses_default\external\reset_test_student',
        'methodname'    => 'execute',
        'description'   => 'Reset password for test student belonging to current user.',
        'type'          => 'write',
        'ajax'          => true
    ],
    'theme_urcourses_default_create_test_student' => [
        'classname'     => 'theme_urcourses_default\external\create_test_student',
        'methodname'    => 'execute',
        'description'   => 'Create test student for current user',
        'type'          => 'write',
        'ajax'          => true
    ],
    'theme_urcourses_default_get_enrolled_courses_by_timeline_classification' => [
        'classname'     => 'theme_urcourses_default\external\get_enrolled_courses',
        'methodname'    => 'get_enrolled_courses_by_timeline_classification',
        'description'   => 'Grabs list of courses in specific timeline, based on code in core with
                            teacher vs student tweaks.',
        'type'          => 'read',
        'ajax'          => 'true',
    ],
    'theme_urcourses_default_get_course_summary' => [
        'classname'     => 'theme_urcourses_default\external\get_course_summary',
        'methodname'    => 'execute',
        'description'   => 'self explanatory',
        'type'          => 'read',
        'ajax'          => 'true',
    ],
    'theme_urcourses_default_enrol_test_student' => [
        'classname'     => 'theme_urcourses_default\external\enrol_test_student',
        'methodname'    => 'execute',
        'description'   => 'self explanatory',
        'type'          => 'write',
        'ajax'          => 'true',
    ],
    'theme_urcourses_default_unenrol_test_student' => [
        'classname'     => 'theme_urcourses_default\external\unenrol_test_student',
        'methodname'    => 'execute',
        'description'   => 'self explanatory',
        'type'          => 'write',
        'ajax'          => 'true',
    ]
];
