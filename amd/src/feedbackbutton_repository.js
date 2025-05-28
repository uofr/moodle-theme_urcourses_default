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

import Ajax from 'core/ajax';

export const submitSuggestion = args => {
    const request = {
        methodname: 'theme_urcourses_default_submit_suggestion',
        args: args
    };

    return Ajax.call([request])[0];
};

export const submitTicket = args => {
    const request = {
        methodname: 'theme_urcourses_default_submit_ticket',
        args: args
    };

    return Ajax.call([request])[0];
};
