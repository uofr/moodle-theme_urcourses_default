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
 * Test student info external function.
 *
 * @module  theme_urcourses_default
 * @author  2024 John Lane <john.lane@uregina.ca>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_urcourses_default\external;

use \core_external\external_api;
use \core_external\external_function_parameters;
use \core_external\external_value;
use \core_external\external_single_structure;

require_once($CFG->dirroot . '/theme/urcourses_default/locallib.php');

defined('MOODLE_INTERNAL') || die();

class test_student_info extends external_api {
    public static function execute_parameters() {
        return new external_function_parameters([]);
    }

    public static function execute_returns() {
        return new external_single_structure(
            [
                'userid' => new external_value(PARAM_INT, '', VALUE_DEFAULT),
                'username' => new external_value(PARAM_TEXT, '', VALUE_DEFAULT),
                'email' => new external_value(PARAM_TEXT, '',  VALUE_DEFAULT),
                'emailoriginal' => new external_value(PARAM_TEXT, '', VALUE_DEFAULT),
                'datecreated' => new external_value(PARAM_TEXT, '', VALUE_DEFAULT)
            ]
        );
    }

    public static function execute() {
        global $DB, $USER;

        if (!theme_urcourses_default_can_create_test_student($USER->id)) {
            throw new \moodle_exception('teststudentnotallowed', 'theme_urcourses_default');
        }

        $email = "$USER->username+urstudent@uregina.ca";

        if ($teststudent = $DB->get_record('user', ['email' => $email])) {
            return [
                'userid' => $teststudent->id,
                'username' => $teststudent->username,
                'email' => $teststudent->email,
                'emailoriginal' => "$USER->username@uregina.ca",
                'datecreated' => userdate($teststudent->timecreated)
            ];
        } else {
            return [
                'username' => "$USER->username-urstudent",
                'email' => $email,
                'emailoriginal' => "$USER->username@uregina.ca"
            ];
        }
    }
}