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

use html_writer;

require_once($CFG->dirroot . '/theme/urcourses_default/locallib.php');

class coursehint_enrol implements \renderable, \templatable {

    public $hasenrolment;
    public $latestenrolmentsemester;
    public $contextid;

    public $courseid;

    public function __construct(bool $hasenrolment, string $latestenrolmentsemester, int $contextid, int $courseid) {
        $this->hasenrolment = $hasenrolment;
        $this->latestenrolmentsemester = $latestenrolmentsemester;
        $this->contextid = $contextid;
        $this->courseid = $courseid;
    }

    public function export_for_template(\core\output\renderer_base $output) {
        $data = new \stdClass();

        if (!$this->hasenrolment) {
            $button = new \core\output\single_button(
                new \moodle_url('/admin/tool/urcourserequest/index.php', ['contextid' => $this->contextid]),
                get_string('addenrolment', 'theme_urcourses_default'),
                'post',
                \core\output\single_button::BUTTON_WARNING
            );
        } else {
            $button = new \core\output\single_button(
                new \moodle_url('/local/duplicate_course/duplicate_course.php', ['id' => $this->courseid]),
                get_string('duplicatecourse', 'theme_urcourses_default'),
                'post',
                \core\output\single_button::BUTTON_WARNING
            );
        }

        $duplicatelink = html_writer::link(
            new \moodle_url('/local/duplicate_course/duplicate_course.php', ['id' => $this->courseid]),
            get_string('duplicatethecourse', 'theme_urcourses_default')
        );

        $data->button = $button->export_for_template($output);
        $data->duplicatelink = $duplicatelink;
        $data->hasenrolment = $this->hasenrolment;
        $data->semester = !empty($this->latestenrolmentsemester)
            ? theme_urcourses_default_get_semester_string($this->latestenrolmentsemester)
            : '';

        return $data;
    }
}