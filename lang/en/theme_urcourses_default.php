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
 * Theme Boost Union - Language pack
 *
 * @package    theme_boost_union
 * @copyright  2022 Moodle an Hochschulen e.V. <kontakt@moodle-an-hochschulen.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// General.
$string['pluginname'] = 'UR Courses: Default';
$string['choosereadme'] = 'UR Courses: Default is a child theme of Boost Union, used for University of Regina courses.';

// UofR Customizations
$string['darkmode'] = 'Darkmode';
$string['darkmodepref'] = 'Darkmode preferences';

// Course Availability Toggle
$string['timestatus_current'] = 'This course is in progress.';
$string['timestatus_current_noenddate'] = 'This course is in progress (end date not set).';
$string['timestatus_past'] = 'This course ended {$a}.';
$string['timestatus_future'] = 'This course begins {$a}.';
$string['hasenrollment'] = 'There is active enrollment ({$a}).';
$string['noenrollment'] = 'There is no active enrollment.';
$string['visible'] = 'The course is visible to students.';
$string['notvisible'] = 'The course is hidden from students.';
$string['visible_noenrollment'] = 'The course is visible.';
$string['notvisible_noenrollment'] = 'The course is hidden.';

// Course Availability Toggle Modal
$string['showcourse'] = 'Show Course';
$string['hidecourse'] = 'Hide Course';
$string['showtitle'] = 'Show Course?';
$string['showbody'] = 'Are you sure you want to show this course? The course will be visible to students enrolled in the {$a} semester.';
$string['showbody_noenrollment'] = 'Are you sure you want to show this course? If students are enrolled, the course will be visible to them.';
$string['hidetitle'] = 'Hide Course?';
$string['hidebody'] = 'Are you sure you want to hide this course? This course will be hidden from students enrolled in the {$a} semester.';
$string['hidebody_noenrollment'] = 'Are you sure you want to hide this course? If students are enrolled, the course will be hidden from them.';
$string['confirmbutton'] = 'Confirm';