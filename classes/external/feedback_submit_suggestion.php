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

require_once($CFG->dirroot . '/mod/questionnaire/locallib.php');
require_once($CFG->dirroot.'/mod/questionnaire/questionnaire.class.php');

class feedback_submit_suggestion extends external_api {
    public static function execute_parameters() {
        return new external_function_parameters([
            'questionnaireid' => new external_value(PARAM_INT),
            'questionname' => new external_value(PARAM_TEXT),
            'suggestion' => new external_value(PARAM_TEXT)
        ]);
    }

    public static function execute_returns() {
        return new external_value(PARAM_INT);
    }

    public static function execute($questionnaireid, $questionname, $suggestion) {
        global $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'questionnaireid' => $questionnaireid,
            'questionname' => $questionname,
            'suggestion' => $suggestion
        ]);

        $context = \context_user::instance($USER->id);
        self::validate_context($context);

        list($cm, $course, $questionnaire) = questionnaire_get_standard_page_items($params['questionnaireid']);

        $responsedata = new \stdClass();
        $responsedata->referer = '';
        $responsedata->a = $questionnaire->id;
        $responsedata->sid = $questionnaire->sid;
        $responsedata->rid = 0;
        $responsedata->sec = 1;
        $responsedata->sesskey = sesskey();
        $responsedata->{$params['questionname']} = $params['suggestion'];
        $responsedata->submittype = 'Submit Survey';
        $responsedata->submit = 'Submit questionnaire';

        $questionnaire = new \questionnaire($course, $cm, 0, $questionnaire);

        $rid = $questionnaire->response_insert($responsedata, $USER->id);
        self::response_commit($rid);

        return $rid;
    }

    /**
     * Commit the specified response.
     * @param int $rid
     * @return bool
     */
    private static function response_commit($rid) {
        global $DB;

        $record = new \stdClass();
        $record->id = $rid;
        $record->complete = 'y';
        $record->submitted = time();

        return $DB->update_record('questionnaire_response', $record);
    }
}