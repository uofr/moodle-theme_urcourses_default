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

class coursehint_date implements \renderable, \templatable {

    public $courseid;
    public $enddate;
    public function __construct(int $courseid, int $enddate) {
        $this->courseid = $courseid;
        $this->enddate = $enddate;
    }

    public function export_for_template(\core\output\renderer_base $output) {
        $data = new \stdClass();

        $data->enddate = $this->enddate;

        $datebutton = new \core\output\single_button(
            new \moodle_url('/course/edit.php', ['id' => $this->courseid]),
            get_string('datebutton', 'theme_urcourses_default'),
            'post',
            \core\output\single_button::BUTTON_WARNING
        );

        $data->datebutton = $datebutton->export_for_template($output);

        return $data;
    }
}