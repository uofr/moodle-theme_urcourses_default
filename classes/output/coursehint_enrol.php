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

    public function __construct(array $enrolments) {
        $this->enrolments = $enrolments;
    }

    public function export_for_template(\core\output\renderer_base $output) {
        if (empty($this->enrolments)) {
            $data = new \stdClass();
            $data->latestenrolment = [];
            return $data;
        }

        $latestenrolment = $this->enrolments[array_key_first($this->enrolments)];
        $latestenrolmentsemester = theme_urcourses_default_get_semester_string($latestenrolment->semester);

        $data = new \stdClass();
        $data->latestenrolment = [
            'semester' => $latestenrolmentsemester
        ];

        return $data;
    }
}