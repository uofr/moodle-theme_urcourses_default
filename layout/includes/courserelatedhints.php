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
 * Theme Boost Union - course related hints include.
 *
 * @package    theme_urcourses_default
 * @copyright  2025 John Lane
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/theme/boost_union/layout/includes/courserelatedhints.php');

$courserelatedhintshtml = theme_urcourses_default_get_course_related_hints();
if (!empty($courserelatedhintshtml)) {
    $templatecontext['courserelatedhints'] .= $courserelatedhintshtml;
}