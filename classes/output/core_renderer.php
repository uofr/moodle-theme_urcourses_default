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
 * Theme Boost Union - Core renderer
 *
 * @package    theme_boost_union
 * @copyright  2022 Moodle an Hochschulen e.V. <kontakt@moodle-an-hochschulen.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_urcourses_default\output;
/*
use stdClass;
use context_course;
use html_writer;
use moodle_url;
*/

use coding_exception;
use core\plugininfo\enrol;
use html_writer;
use tabobject;
use tabtree;
use context_system;
use custom_menu_item;
use custom_menu;
use block_contents;
use navigation_node;
use action_link;
use stdClass;
use moodle_url;
use preferences_groups;
use action_menu;
use help_icon;
use single_button;
use single_select;
use paging_bar;
use url_select;
use context_course;
use pix_icon;
use user_picture;
use action_menu_filler;
use action_menu_link_secondary;
use core_text;



use \core_course\external\course_summary_exporter;



/**
 * Extending the core_renderer interface.
 *
 * @package    theme_boost_union
 * @copyright  2022 Moodle an Hochschulen e.V. <kontakt@moodle-an-hochschulen.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_boost_union\output\core_renderer {
	
    public function favicon() {
		global $CFG;
        $favicon = $CFG->wwwroot . '/theme/urcourses_default/pix/favicon.ico';
		return $favicon;
    }
    /**
     * Get the compact logo URL.
     *
     * @return string
     */
    public function get_compact_logo_url($maxwidth = 100, $maxheight = 100) {
        global $OUTPUT;
        return $OUTPUT->image_url('logo', 'theme_urcourses_default');
    }
    public function get_compact_logosmall_url($maxwidth = 100, $maxheight = 100) {
        global $OUTPUT;
        return $OUTPUT->image_url('logo', 'theme_urcourses_default');
    }
	
    /**
     * Returns the services and support link for the help pop-up.
     *
     * @return string
     */
    public function services_support_link(): string {
        global $CFG;

        if (during_initial_install() ||
            (isset($CFG->showservicesandsupportcontent) && $CFG->showservicesandsupportcontent == false) ||
            !is_siteadmin()) {
            return '';
        }

        $liferingicon = $this->pix_icon('t/life-ring', '', 'moodle', ['class' => 'fa fa-life-ring']);
        $newwindowicon = $this->pix_icon('i/externallink', get_string('opensinnewwindow'), 'moodle', ['class' => 'ml-1']);
        $link = 'https://urcourses.uregina.ca/guides';
        $content = $liferingicon . get_string('moodleservicesandsupport') . $newwindowicon;

        return html_writer::tag('a', $content, ['target' => '_blank', 'href' => $link]);
    }
	
    /**
     * Renders the header bar.
     *
     * @param context_header $contextheader Header bar object.
     * @return string HTML for the header bar.
     */
   protected function render_context_header(\context_header $contextheader) {

       // Generate the heading first and before everything else as we might have to do an early return.
       if (!isset($contextheader->heading)) {
           $heading = $this->heading($this->page->heading, $contextheader->headinglevel, 'h2');
       } else {
           $heading = $this->heading($contextheader->heading, $contextheader->headinglevel, 'h2');
       }

       // All the html stuff goes here.
       $html = html_writer::start_div('page-context-header');

       // Image data.
       if (isset($contextheader->imagedata)) {
           // Header specific image.
           $html .= html_writer::div($contextheader->imagedata, 'page-header-image mr-2');
       }

       // Headings.
       if (isset($contextheader->prefix)) {
           $prefix = html_writer::div($contextheader->prefix, 'text-muted text-uppercase small line-height-3');
           //$heading = $prefix . $heading;
		   // Change order of activity type
           $heading = $heading . $prefix;
       }
       $html .= html_writer::tag('div', $heading, array('class' => 'page-header-headings'));

       // Buttons.
       if (isset($contextheader->additionalbuttons)) {
           $html .= html_writer::start_div('btn-group header-button-group');
           foreach ($contextheader->additionalbuttons as $button) {
               if (!isset($button->page)) {
                   // Include js for messaging.
                   if ($button['buttontype'] === 'togglecontact') {
                       \core_message\helper::togglecontact_requirejs();
                   }
                   if ($button['buttontype'] === 'message') {
                       \core_message\helper::messageuser_requirejs();
                   }
                   $image = $this->pix_icon($button['formattedimage'], $button['title'], 'moodle', array(
                       'class' => 'iconsmall',
                       'role' => 'presentation'
                   ));
                   $image .= html_writer::span($button['title'], 'header-button-title');
               } else {
                   $image = html_writer::empty_tag('img', array(
                       'src' => $button['formattedimage'],
                       'role' => 'presentation'
                   ));
               }
               $html .= html_writer::link($button['url'], html_writer::tag('span', $image), $button['linkattributes']);
           }
           $html .= html_writer::end_div();
       }
       $html .= html_writer::end_div();

       return $html;
   }
	
}

