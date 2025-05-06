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
 * Test student enrol external function.
 *
 * @module  theme_urcourses_default
 * @author  2024 John Lane <john.lane@uregina.ca>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_urcourses_default\external;

use \core_external\external_api;
use \core_external\external_function_parameters;
use \core_external\external_value;

defined('MOODLE_INTERNAL') || die();

class feedback_submit_ticket extends external_api {
    public static function execute_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT),
            'problem' => new external_value(PARAM_TEXT)
        ]);
    }

    public static function execute_returns() {
        return new external_value(PARAM_BOOL);
    }

    public static function execute($contextid, $problem) {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
            'problem' => $problem
        ]);

        $courseid = $params['contextid'];

        $context = \context_user::instance($courseid);
        self::validate_context($context);

        require_capability('moodle/course:changesummary', $context);

        $is_course_exist = $DB->record_exists('course', array('id' => $courseid));

        if ($is_course_exist) {
            $course = get_course($courseid);
            $new_visibility = !($course->visible);

            $updated_course_record = new \stdClass();
            $updated_course_record->id = $courseid;
            $updated_course_record->visible = $new_visibility;

            $DB->update_record('course', $updated_course_record);

            return true;
        }
        else {
            return false;
        }
    }
}