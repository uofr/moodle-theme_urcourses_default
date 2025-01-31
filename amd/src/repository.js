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
 * External functions repository for theme_urcourses_default.
 *
 * @module  theme_urcourse
 * @author  2024 John Lane <john.lane@uregina.ca>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';

export const testAccountInfo = () => {
    const request = {
        methodname: 'theme_urcourses_default_test_student_info',
        args: {}
    };

    return Ajax.call([request])[0];
};

export const createTestStudent = () => {
    const request = {
        methodname: 'theme_urcourses_default_create_test_student',
        args: {}
    };

    return Ajax.call([request])[0];
};

export const resetTestStudent = () => {
    const request = {
        methodname: 'theme_urcourses_default_reset_test_student',
        args: {}
    };

    return Ajax.call([request])[0];
};

export const enrolTestStudent = (courseid) => {
    const request = {
        methodname: 'theme_urcourses_default_enrol_test_student',
        args: {
            courseid: courseid
        }
    };

    return Ajax.call([request])[0];
};

export const unenrolTestStudent = (courseid) => {
    const request = {
        methodname: 'theme_urcourses_default_unenrol_test_student',
        args: {
            courseid: courseid
        }
    };

    return Ajax.call([request])[0];
};