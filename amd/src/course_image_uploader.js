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
    'core/fragment',
    'core/yui'
], function(
    $,
    Ajax,
    Notification,
    Str,
    ModalFactory,
    ModalEvents,
    Fragment,
    Y
) {

    var _root;
    var _courseid;
    var _contextid;
    var _modal;

    var SELECTORS = {
        UPLOADER: '#course_image_uploader',
    };

    var init = function(root, maxbytes, courseid, contextid) {
        _root = $(root);
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
            _modal = modal;
            _modal.setLarge();
            // Reset form each time it is opened.
            _modal.getRoot().on(ModalEvents.hidden, function() {
                _modal.setBody(getModalBody());
            });
            _modal.getRoot().on(ModalEvents.save, submitForm);
            _modal.getRoot().on('submit', 'form', submitFormAjax);
        });
    };

    var getModalBody = function(formdata) {
        if (typeof formdata === 'undefined') {
            formdata = {};
        }
        var params = {
            jsonformdata: JSON.stringify(formdata)
        };
        return Fragment.loadFragment('theme_urcourses_default', 'image_form', _contextid, params);
    };

    var submitForm = function(event) {
        event.preventDefault();
        _modal.getRoot().find('form').submit();
    };

    var submitFormAjax = function(event) {
        event.preventDefault();

        var changeEvent = document.createEvent('HTMLEvents');
        changeEvent.initEvent('change', true, true);

        _modal.getRoot().find(':input').each(function(index, element) {
            element.dispatchEvent(changeEvent);
        });

        var invalid = $.merge(
            _modal.getRoot().find('[aria-invalid="true"]'),
            _modal.getRoot().find('.error')
        );

        if (invalid.length) {
            invalid.first().focus();
            return;
        }

        var formData = _modal.getRoot().find('form').serialize();

        Ajax.call([{
            methodname: 'theme_urcourses_default_upload_course_image',
            args: {formdata: JSON.stringify(formData), courseid: _courseid},
            done: submitDone,
            fail: submitFail
        }]);
    };

    var submitDone = function(response) {
        console.log(response);
        _modal.hide();
    };

    var submitFail = function(data) {
        Notification.exception(data);
    };

    return {init: init};

});