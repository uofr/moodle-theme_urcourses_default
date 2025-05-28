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

class get_course_summary extends external_api {
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT)
        ]);
    }

    public static function execute_returns() {
        return new external_single_structure([
            'summary' => new external_value(PARAM_RAW),
            'fullname' => new external_value(PARAM_TEXT),
            'caneditsummary' => new external_value(PARAM_BOOL),
            'editlink' => new external_value(PARAM_TEXT)
        ]);
    }

    public static function execute($courseid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid
        ]);

        $context = \context_course::instance($params['courseid']);

        $course = get_course($params['courseid']);
        $canedit = has_capability('moodle/course:changesummary', $context);
        $editlink = $canedit
            ? (new \moodle_url('/course/edit.php', ['id' => $params['courseid']]))->out()
            : '';

        return [
            'summary' => $course->summary,
            'fullname' => $course->fullname,
            'caneditsummary' => $canedit,
            'editlink' => $editlink
        ];
    }
}