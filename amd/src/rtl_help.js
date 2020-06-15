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
 * Theme Boost Campus - Script to setup the help modal.
 *
 * @package    theme_urcourses_default
 * @author     John Lane
 *
 */

define(
[
    'jquery',
    'theme_urcourses_default/modal_help',
    'core/modal_factory'
],
function(
    $,
    ModalHelp,
    ModalFactory
) {

    var SELECTORS = {
        RTL_BTN: '#rtl_trigger'
    };

    var init = function() {
        ModalFactory.create({
            type: ModalHelp.TYPE,
            large: true
        }, $(SELECTORS.RTL_BTN));
    };

    return {
        init: init
    };

});
