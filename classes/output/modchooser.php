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
 * The modchooser renderable.
 *
 * @package    core_course
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_urcourses_default\output;
defined('MOODLE_INTERNAL') || die();

use core\output\chooser;
use core\output\chooser_section;
use context_course;
use lang_string;
use moodle_url;
use pix_icon;
use renderer_base;
use stdClass;

/**
 * The modchooser renderable class.
 *
 * @package    core_course
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class modchooser extends chooser {

    /** @var stdClass The course. */
    public $course;

    /**
     * Constructor.
     *
     * @param stdClass $course The course.
     * @param stdClass[] $modules The modules.
     */
    public function __construct(stdClass $course, array $modules) {
        $this->course = $course;

        $sections = [];
        $context = context_course::instance($course->id);
		
		// hack - cunnintr - switched the order of resources and activities, so resources display first (used more often)
		/*
        // Activities.
        $activities = array_filter($modules, function($mod) {
            return ($mod->archetype !== MOD_ARCHETYPE_RESOURCE && $mod->archetype !== MOD_ARCHETYPE_SYSTEM);
        });
        if (count($activities)) {
            $sections[] = new chooser_section('activities', new lang_string('activities'),
                array_map(function($module) use ($context) {
                    return new modchooser_item($module, $context);
                }, $activities)
            );
        }
		*/
		
		/*			
        $resources = array_filter($modules, function($mod) {
            return ($mod->archetype === MOD_ARCHETYPE_RESOURCE);
        });
		*/
		
		$extras_list = array('IMS content package', 'Bootstrap Elements', 'Reading List (inline)', 'Reading List', 'Kaltura Video Presentation','RecordingsBN','Assignment Media','BigBlueButtonBN','Certificate','Checklist','Etherpad','External Tool', 'Lightbox Gallery', 'OU blog', 'Scheduler', 'Scorm Package', 'Skype');
				
		// Resources.
		$resources = array_filter($modules, function($mod) use ($extras_list) {
			return (($mod->archetype === MOD_ARCHETYPE_RESOURCE) && !in_array($mod->title,$extras_list));
		});
		
		// reading list hack - let's move it to resources
		$ltis = array_filter($modules, function($mod) {
			return ((substr($mod->name,0,4) == 'lti:') && $mod->title=='Reading List');
		});
		
		$sorthack = [array_pop($resources)];		
		$resources = array_merge($resources, $ltis);
		$resources = array_merge($resources, $sorthack);

		
        if (count($resources)) {
            $sections[] = new chooser_section('resources', new lang_string('resources'),
                array_map(function($module) use ($context) {
                    return new modchooser_item($module, $context);
                }, $resources)
            );
        }
		
        // Activities.
        $activities = array_filter($modules, function($mod) use ($extras_list) {
            return ($mod->archetype !== MOD_ARCHETYPE_RESOURCE && $mod->archetype !== MOD_ARCHETYPE_SYSTEM && !in_array($mod->title,$extras_list));
        });
        if (count($activities)) {
            $sections[] = new chooser_section('activities', new lang_string('activities'),
                array_map(function($module) use ($context) {
                    return new modchooser_item($module, $context);
                }, $activities)
            );
        }
		
		// Extras Hack
		// remove reading list because we included it in resources
		$more = array_filter($modules, function($mod) use ($extras_list) {
			return in_array($mod->title,$extras_list) && $mod->title != 'Reading List';
		});
		if (count($more)) {
	 	   $sections[] = new chooser_section('more', new lang_string('morenavigationlinks'),
	     		array_map(function($module) use ($context) {
	         	   return new modchooser_item($module, $context);
	     	  	}, $more));
	   	} 
	   	// end hack
		
        $actionurl = new moodle_url('/course/jumpto.php');
        $title = new lang_string('addresourceoractivity');
        parent::__construct($actionurl, $title, $sections, 'jumplink');

        $this->set_instructions(new lang_string('selectmoduletoviewhelp'));
        $this->add_param('course', $course->id);
    }

    /**
     * Export for template.
     *
     * @param renderer_base  The renderer.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = parent::export_for_template($output);
        $data->courseid = $this->course->id;
        return $data;
    }

}
