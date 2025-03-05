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
 * Theme Boost Union - Primary navigation render.
 *
 * @package    theme_urcourses_default
 * @copyright  2025 John Lane
 */

namespace theme_urcourses_default\output;

require_once($CFG->dirroot . '/theme/urcourses_default/locallib.php');

class coursehint_enrol implements \renderable, \templatable {

    public $enrolments;
    public $contextid;

    public function __construct(array $enrolments, int $contextid) {
        $this->enrolments = $enrolments;
        $this->contextid = $contextid;
    }

    public function export_for_template(\core\output\renderer_base $output) {
        $data = new \stdClass();

        $hasenrolment = !empty($this->enrolments);

        $enrolbutton = new \core\output\single_button(
            new \moodle_url('/admin/tool/urcourserequest/index.php', ['contextid' => $this->contextid]),
            get_string($hasenrolment ? 'editenrolment' : 'addenrolment', 'theme_urcourses_default'),
            'post',
            \core\output\single_button::BUTTON_SECONDARY
        );

        $data->enrolbutton = $enrolbutton->export_for_template($output);

        if (!$hasenrolment) {
            $data->hasenrolment = false;
            return $data;
        }

        $latestenrolment = $this->enrolments[array_key_first($this->enrolments)];
        $latestenrolmentsemester = theme_urcourses_default_get_semester_string($latestenrolment->semester);

        $data->hasenrolment = true;
        $data->semester = $latestenrolmentsemester;

        return $data;
    }
}