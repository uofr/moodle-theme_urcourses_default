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
 * Theme UR Courses - Navbar layout include.
 *
 * @package    theme_urcourses_default
 * @copyright  2024 John Lane <john.lane@uregina.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $DB;

// Extend boost union navbar.
require_once($CFG->dirroot . '/theme/boost_union/layout/includes/navbar.php');

// Custom items for user menu.
$customitems = [];
$customitems[] = theme_urcourses_default_create_darkmode_link();
if (theme_urcourses_default_can_create_test_student($USER->id)) {
    $hasteststudentaccount = theme_urcourses_default_has_test_student_account($USER->username);
    $customitems[] = theme_urcourses_default_create_teststudent_link($hasteststudentaccount);
}

$customitems[array_key_last($customitems)]->divider = true;

$templatecontext['usermenu']['items'] = theme_urcourses_default_add_custom_user_menu_items(
    $templatecontext['usermenu']['items'],
    $customitems
);
