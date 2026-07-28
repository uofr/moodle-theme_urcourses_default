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
 * Class for exporting a course summary from an stdClass.
 *
 * @package    theme_urcourses_default
 */
namespace theme_urcourses_default\external;
defined('MOODLE_INTERNAL') || die();

use renderer_base;

/**
 * Class for exporting a course summary from an stdClass.
 *
 */
class course_summary_exporter extends \block_currentcourses\external\course_summary_exporter {

    protected function get_other_values(renderer_base $output) {
        $othervalues = parent::get_other_values($output);
        $othervalues['started'] = self::is_course_started($this->data);
        $othervalues['ended'] = self::is_course_ended($this->data);
        return $othervalues;
    }

    public static function define_other_properties() {
        $otherproperties = parent::define_other_properties();
        $otherproperties['started'] = ['type' => PARAM_BOOL];
        $otherproperties['ended'] = ['type' => PARAM_BOOL];
        return $otherproperties;
    }

    /**
     * Returns true if the course end date has passed.
     * Returns false otherwise.
     * 
     * @param object $course
     * @return bool
     */
    public static function is_course_ended($course) {
        if ($course->enddate == 0) {
            return false;
        }
        else {
            return (time() > $course->enddate);
        }
    }

    /**
     * Returns true if the course start date has passed.
     * Returns false otherwise.
     * @param object $course
     * @return bool
     */
    public static function is_course_started($course) {
        return (time() > $course->startdate);
    }
}
