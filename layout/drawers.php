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
 * Theme Boost Union - Drawers page layout.
 *
 * This layoutfile is based on theme/boost/layout/drawers.php
 *
 * Modifications compared to this layout file:
 * * Include footnote
 * * Render theme_boost_union/drawers instead of theme_boost/drawers template
 * * Include course related hints
 * * Include back to top button
 * * Include activity navigation
 *
 * @package   theme_boost_union
 * @copyright 2022 Luca Bösch, BFH Bern University of Applied Sciences luca.boesch@bfh.ch
 * @copyright based on code from theme_boost by Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG,$PAGE,$DB,$COURSE;

require_once($CFG->dirroot . '/theme/urcourses_default/layout/drawers.php');
