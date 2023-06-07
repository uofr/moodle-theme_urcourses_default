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
 * Defines the course_visibility_toggle renderable class.
 *
 * @package     theme_urcourses_default
 * @author      2023 John Lane
 */
namespace theme_urcourses_default\renderable;
defined('MOODLE_INTERNAL') || die();

/**
 * Formats data needed to render the course visibility toggle (templates/header_toggle_course_availability).
 *
 * @package     theme_urcourses_default
 * @author      2023 John Lane
 */
class course_visibility_toggle implements \renderable, \templatable {

    /** @var int Course id. */
    private int $courseid;

    /** @var int UNIX timestamp for course start date. */
    private int $startdate;

    /** @var int UNIX timestamp for course end date. */
    private int $enddate;

    /** @var bool True if course visibility set to 'Show', False if set to 'Hide' */
    private bool $is_visible;

    /** @var array array('id' => 'Semester ID Number eg yyyytt', 'name' => 'Semester Name eg Winter 2023') */
    private array $enrollment;

    public function __construct(int $courseid, int $startdate, int $enddate, bool $is_visible, array $enrollment) {
        $this->courseid = $courseid;
        $this->startdate = $startdate;
        $this->enddate = $enddate;
        $this->is_visible = $is_visible;
        $this->enrollment = $enrollment;
    }

    public function export_for_template(\renderer_base $output) {
        $data = new \stdClass();

        $timestatus = self::get_course_time_status();
        $timestatus_msg = '';
        if ($timestatus === 'ongoing') {
            $timestatus_msg = get_string('timestatus_current', 'theme_urcourses_default');
        }
        else if ($timestatus === 'current') {
            if ($this->enddate == 0) {
                $timestatus_msg = get_string('timestatus_current_noenddate', 'theme_urcourses_default');
            }
            else {
                $timestatus_msg = get_string('timestatus_current', 'theme_urcourses_default');
            }
        }
        else if ($timestatus === 'past' && $this->enddate != 0) {
            $str_enddate = date('F j, Y', $this->enddate);
            $timestatus_msg = get_string('timestatus_past', 'theme_urcourses_default', $str_enddate);
        }
        else if ($timestatus === 'future') {
            $str_startdate = date('F j, Y', $this->startdate);
            $timestatus_msg = get_string('timestatus_future', 'theme_urcourses_default', $str_startdate);
        }

        $enrollment_msg = '';
		/*
        if (empty($this->enrollment)) {
            $enrollment_msg = get_string('noenrollment', 'theme_urcourses_default');
        }
        else {
            $enrollment_msg = get_string('hasenrollment', 'theme_urcourses_default', $this->enrollment['name']);
        }*/

        $availability_button_msg = '';
        if ($this->is_visible) {
            $availability_button_msg = get_string('hidecourse', 'theme_urcourses_default');
        }
        else {
            $availability_button_msg = get_string('showcourse', 'theme_urcourses_default');
        }

        $availability_msg = '';
        if ($this->is_visible) {
            $availability_msg = get_string('visible', 'theme_urcourses_default', $this->enrollment['name']);
        }
        else {
            $availability_msg = get_string('notvisible', 'theme_urcourses_default', $this->enrollment['name']);
        }

        
            $modalstrings = array(
                'showtitle' => get_string('showtitle', 'theme_urcourses_default'),
                'showbody' => get_string('showbody_noenrollment', 'theme_urcourses_default'),
                'hidetitle' => get_string('hidetitle', 'theme_urcourses_default'),
                'hidebody' => get_string('hidebody_noenrollment', 'theme_urcourses_default'),
                'confirmbutton' => get_string('confirmbutton', 'theme_urcourses_default')
            );
        

        $data->timestatus_msg = $timestatus_msg;
        $data->enrollment_msg = $enrollment_msg;
        $data->availability_msg = $availability_msg;
        $data->availability_button_msg = $availability_button_msg;
        $data->courseid = $this->courseid;
        $data->visible = $this->is_visible;
        $data->modalstrings = $modalstrings;

        return $data;
    }

    /**
     * Checks if the course is current, ongoing, in the past, or in the future.
     * 
     * @return string - Will return 'ongoing', 'past', 'future', or 'current'.
     */
    private function get_course_time_status() {
        $currenttime = time();
        $ongoingdate = 946706400; // Jan 01, 2000, 06:00 (date for ongoing courses)

        // Check if the start date is set to the 'ongoing courses' date.
        if ($this->startdate == $ongoingdate) {
            return 'ongoing';
        }
        // If startdate is greater than the currenttime, the course is in the future.
        if ($this->startdate > $currenttime) {
            return 'future';
        }
        // If the enddate is set, and the currenttime is after the enddate, the course is in the past.
        if ((isset($this->enddate) && $this->enddate != 0) && $this->enddate < $currenttime) {
            return 'past';
        }

        return 'current';
    }

}