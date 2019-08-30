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
    'core/modal_events',
    'core/fragment'
], function(
    $,
    Ajax,
    Notification,
    Str,
    ModalFactory,
    ModalEvents,
    Fragment
) {
    var _root;
    var _maxbytes;
    var _courseid;
    var _contextid;
    var _imagedata;
    var _imagename;
    var _modal;

    var SELECTORS = {
        UPLOADER: '#course_image_uploader',
    };

    var init = function(root, maxbytes, courseid, contextid) {
        _root = $(root);
        _maxbytes = maxbytes;
        _courseid = courseid;
        _contextid = contextid;

        Str.get_string('modal_header', 'theme_urcourses_default')
        .then(function(title) {
            return ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                title: title,
                body: getModalBody()
            }, $(SELECTORS.UPLOADER));
        })
        .then(function(modal) {
            modal.setLarge();
            modal.getRoot().on(ModalEvents.hidden, function() {
                modal.setBody(getModalBody());
            });
            modal.getRoot().on(ModalEvents.save, submitForm);
            modal.getRoot().on('submit', 'form', submitFormAjax);
            _modal = modal;
        });
    };

    var getModalBody = function() {
        return Fragment.loadFragment('theme_urcourses_default', 'image_form', _contextid, {jsonformdata: JSON.stringify({})});
    };

    var submitForm = function(event) {
        event.preventDefault();
        _modal.getRoot().find('form').submit();
    };

    var submitFormAjax = function(event) {
        event.preventDefault();
    };

    return {init: init};

});