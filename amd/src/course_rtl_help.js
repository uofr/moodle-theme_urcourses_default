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
 * Theme Boost Campus - Code for course header image uploader.
 *
 * @package    theme_urcourses_default
 * @author     John Lane
 *
 */

define(
[
    'jquery',
    'core/ajax',
    'core/notification',
    'core/str',
    'core/modal_factory',
    'core/modal_events'
],
function(
    $,
    ajax,
    notification,
    str,
    ModalFactory,
    ModalEvents
) {
    var _root;

    var SELECTORS = {
        RTL_BTN: '#rtl_trigger'
    };

    var getRemtlHelp = function() {
        var args = {
            param: 'hi there'
        };
        var ajaxCall = {
            methodname: 'theme_urcourses_default_get_remtl_help',
            args: args
        };
        return ajax.call([ajaxCall])[0];
    };

    /**
     * Entry point to module. Sets globals and registers event listeners.
     * @param {String} root Jquery selector for container.
     * @return void
     */
    var init = function(root) {
        _root = $(root);
        ModalFactory.create({
            type: ModalFactory.types.CANCEL,
            title: 'RTL Guide',
            large: true
        }, $(SELECTORS.RTL_BTN))
        .done(function(modal) {
            var modalRoot = modal.getRoot();
            modalRoot.on(ModalEvents.shown, function() {
               getRemtlHelp()
               .then(function(response) {
                   var json_output = JSON.parse(response.json_output);
                   modal.setBody(json_output.content);
               });
            });
        });
    };

    return {
        init: init
    };

});