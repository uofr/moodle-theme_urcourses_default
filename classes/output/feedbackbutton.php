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
 * Theme Boost Union - Feedback button renderable.
 *
 * @package    theme_urcourses_default
 * @copyright  2025 John Lane
 */

namespace theme_urcourses_default\output;

require_once($CFG->dirroot . '/theme/urcourses_default/locallib.php');

class feedbackbutton implements \renderable, \templatable {
    public $contextid;

    public function __construct(int $contextid) {
        $this->contextid = $contextid;
    }

    public function export_for_template(\core\output\renderer_base $output) {
        $data = new \stdClass();
        $data->contextid = $this->contextid;
        return $data;
    }
}