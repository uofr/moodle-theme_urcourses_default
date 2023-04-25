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
 * @module  theme_urcourses_default/toggle_course_visibility
 * @author  2023 John Lane
 */

import Ajax from 'core/ajax';

export const toggleCourseVisibility = (courseid) => {
    const request = {
        methodname: 'theme_urcourses_default_toggle_course_visibility',
        args: {
            courseid: courseid
        }
    };

    return Ajax.call([request])[0];
};