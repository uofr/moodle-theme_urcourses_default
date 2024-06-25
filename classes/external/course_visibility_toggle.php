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
 * Definition for theme_urcourses_default\external\course_visibility_toggle
 *
 * @author  John Lane
 */

namespace theme_urcourses_default\external;

use \core_external\external_api;
use \core_external\external_function_parameters;
use \core_external\external_value;
use \core_external\external_single_structure;

defined('MOODLE_INTERNAL') || die();

/**
 * External class for course availability toggle.
 * Can change the visibility of a given course (provided the user has permission).
 *
 * @author  John Lane
 */
class course_visibility_toggle extends external_api {
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT)
        ]);
    }

    public static function execute($courseid) {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), ['courseid' => $courseid]);

        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('moodle/course:changesummary', $context);

        $is_course_exist = $DB->record_exists('course', array('id' => $params['courseid']));
        $response = array();

        if ($is_course_exist) {
            $course = get_course($params['courseid']);
            $new_visibility = !($course->visible);
    
            $updated_course_record = new \stdClass();
            $updated_course_record->id = $params['courseid'];
            $updated_course_record->visible = $new_visibility;

            $db_response = $DB->update_record('course', $updated_course_record);

            $response = array('response' => $db_response);
        }
        else {
            $response = array('response' => false);
        }

        return $response;
    }

    public static function execute_returns() {
        return new external_single_structure([
            'response' => new external_value(PARAM_BOOL)
        ]);
    }
}