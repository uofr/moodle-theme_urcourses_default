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
 * Theme UR Courses Default - Version file
 *
 * @package    theme_urcourses_default
 * @copyright  2025 John Lane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'theme_urcourses_default';
$plugin->release = 'v4.3-r1';
$plugin->version = 2025030500;
$plugin->requires = 2023100906; // Requires Moodle 4.3.6 or later.
$plugin->supported = [403, 405];
$plugin->maturity = MATURITY_STABLE;
$plugin->dependencies = ['theme_boost_union' => 2023102042];
