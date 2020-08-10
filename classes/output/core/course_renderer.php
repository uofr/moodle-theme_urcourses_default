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
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package   theme_urcourses_default
 * @copyright 2017 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 *            copyright based on code from theme_boost by 2016 Frédéric Massart - FMCorz.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_urcourses_default\output\core;

defined('MOODLE_INTERNAL') || die;

use moodle_url;
use html_writer;
global $CFG,$PAGE;

require_once($CFG->dirroot . '/course/renderer.php');

/**
 * Extending the course_renderer interface.
 *
 * @copyright 2017 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package theme_urcourses_default
 * @category output
 */
class course_renderer extends \theme_boost\output\core\course_renderer {

    /**
     * Displays one course in the list of courses.
     *
     * This is an internal function, to display an information about just one course
     * please use {@link core_course_renderer::course_info_box()}
     *
     * KIZ MODIFICATION: This renderer function is copied and modified from /course/renderer.php
     *
     * @param \coursecat_helper       $chelper           various display options
     * @param \core_course_list_element|\stdClass $course
     * @param string                  $additionalclasses additional classes to add to the main <div> tag (usually
     *                                                   depend on the course position in list - first/last/even/odd)
     *
     * @return string
     */
	
	protected function coursecat_coursebox(\coursecat_helper $chelper, $course, $additionalclasses = '') {
		global $CFG, $DB, $PAGE;
		
        if (!isset($this->strings->summary)) {
            $this->strings->summary = get_string('summary');
        }
        if ($chelper->get_show_courses() <= self::COURSECAT_SHOW_COURSES_COUNT) {
            return '';
        }
        if ($course instanceof stdClass) {
            $course = new \core_course_list_element($course);
        }
        $content = '';
        $classes = trim('coursebox clearfix ' . $additionalclasses);
        if ($chelper->get_show_courses() >= self::COURSECAT_SHOW_COURSES_EXPANDED) {
            $nametag = 'h3';
        } else {
            $classes .= ' collapsed';
            $nametag = 'div';
        }
        
        $key = theme_urcourses_default_get_ur_category_class($course->id);
		if (!empty($key)) {
            $classes .= ' ' . $key;
        }
		
		$ur_categories = array('','misc'=>'','khs'=>'Faculty of Kinesiology and Health Studies','edu'=>'Faculty of Education','sci'=>'Faculty of Science','grad'=>'Grad Studies','fa'=>'Faculty of Fine Arts','map'=>'Faculty of Media, Art, and Performance','engg'=>'Faculty of Engineering','bus'=>'Business Administration','arts'=>'Faculty of Arts','sw'=>'Faculty of Social Work','nur'=>'Faculty of Nursing','misc'=>'Custom Themes');
		
        $coursename = $chelper->get_course_formatted_name($course);
        $coursenamelink = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)),
            $coursename, array('class' => $course->visible ? '' : 'dimmed'));
		
        if ($icons = enrol_get_course_info_icons($course)) {
            $content .= html_writer::start_tag('div', array('class' => 'enrolmenticons'));
            foreach ($icons as $pixicon) {
                $content .= $this->render($pixicon);
            }
            $content .= html_writer::end_tag('div'); // .enrolmenticons
        }	
		
        $context = \context_system::instance();
        
		$courseboj = $DB->get_record('course', array('id' => $course->id)); 
        $urenderer = $PAGE->get_renderer('core');
        $exporter = new course_summary_exporter($courseboj, ['context' => $context]);
        $cobits = $exporter->export($urenderer);
		
	        $context = [
	            'classes'         => $classes,
	            'data-courseid' => $course->id,
	            'data-type'     => self::COURSECAT_TYPE_COURSE,
				'coursename'    => $coursename,
				'coursenamelink'    => $coursenamelink,
				'summary'           => $this->coursecat_coursebox_content($chelper, $course),
				'courseimage'       => $cobits->courseimage,
				'viewurl'       => $cobits->viewurl,
				'infourl'       => $cobits->infourl,
				'ishidden'       => $cobits->ishidden,
				'visible'       => $cobits->visible,
				'coursecategory'       => $cobits->coursecategory,
	        ];
	        return $this->render_from_template('theme_urcourses_default/core/site_summary', $context);
	}

    public function course_modchooser($modules, $course) {
        // This HILLBROOK function is overridden here to refer to the local theme's copy of modchooser to render a modified.
        // Activity chooser for Hillbrook.
        if (!$this->page->requires->should_create_one_time_item_now('core_course_modchooser')) {
            return '';
        }
        $modchooser = new \theme_urcourses_default\output\modchooser($course, $modules);
        return $this->render($modchooser);
    }
}


