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

namespace theme_urcourses_default\output;

/**
 * Renderable for the main page header.
 *
 * @package theme_urcourses_default
 */

class context_header extends \core\output\context_header {
    public $instructors;

    public function __construct($heading = null, $headinglevel = 1, $imagedata = null, $additionalbuttons = null, $prefix = null, $instructors = null) {
        parent::__construct($heading, $headinglevel, $imagedata, $additionalbuttons, $prefix);

        $this->instructors = $instructors;
    }

    public function export_for_template(\renderer_base $output): array {
        $templatecontext = parent::export_for_template($output);
        $templatecontext['instructors'] = $this->instructors;

        return $templatecontext;
    }
}