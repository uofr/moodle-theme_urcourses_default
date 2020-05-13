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

define(['jquery', 'core/ajax', 'core/notification', 'core/str',
    'core/modal_factory', 'core/modal_events'], function($, ajax, notification, str, ModalFactory, ModalEvents) {

    /** Container jquery object. */
    var _root;
    /** Course ID */
    var _courseid;
    var _element;

    /** Jquery selector strings. */
    var SELECTORS = {
        HEADER: '#page-header .header-body',
        HEADER_TOP: "#page-header .page-head",
        RTL_BTN: '#rtl_trigger',
    };

    /**
     * Initializes global variables.
     * @param {string} root - Jquery selector for container.
     * @param {int} courseid - ID of current course.
     * @return void
     */

    var _setGlobals = function(root, courseid) {
       _root = $(root);
       _courseid = courseid;
    };


    /**
     * Sets up event listeners.
     * @return void
     */
    var _registerEventListeners = function() {
        _root.on('click', SELECTORS.RTL_BTN, _openRTL);
    };


    /**
     * Initiate ajax call to upload and set new image.
     */
    var _openRTL = function() {
        _element = $(this);

        //if current style is clicked, do nothing...
        //console.log('_headerstyle:'+_headerstyle);
        /*
        if ('#'+_element.attr('id') == SELECTORS.HDRSTYLEA_BTN && _headerstyle == 0) {
            return;
        } else if ('#'+_element.attr('id') == SELECTORS.HDRSTYLEB_BTN && _headerstyle == 1) {
            return;
        }
        */

        //adding in confirmation modal in case buttons accidentally clicked
        ModalFactory.create({
            type: ModalFactory.types.CANCEL,
            title: 'RTL Guide',
            body: "<p><b>loading...</b><br />"
                  + '<iframe src="https://urcourses.uregina.ca/guides/instructor" width="100%" height="500"></iframe>'
                  + ".</p>"
        })
        .then(function(modal) {
            var root = modal.getRoot();
            root.on(ModalEvents.cancel, function(){
                return;
            });
            //root.on(ModalEvents.save, _styleChange);
            modal.show();
        });
    };
    /**
     * Handles theme_urcourses_default_upload_course_image response data.
     * @param {Object} response
     */
/*
    var _choiceDone = function() {
        str.get_string('success:coursestylechosen', 'theme_urcourses_default')
            .done(_createSuccessPopup);
    };
*/
    /**
     * Entry point to module. Sets globals and registers event listeners.
     * @param {String} root Jquery selector for container.
     * @return void
     */
    var init = function(root, courseid) {
        _setGlobals(root, courseid);
        _registerEventListeners();
    };

    return {
        init: init
    };

});