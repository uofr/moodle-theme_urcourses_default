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
    'core/modal_factory', 'core/modal_events','theme_urcourses_default/course_actions'], function($, ajax, notification, str, ModalFactory, ModalEvents,courseActionsLib) {

    /** Container jquery object. */
    var _root;
    /** Course ID */
    var _course;
    var _categories;
    var _templatelist;
    var _element;

   

    /** Jquery selector strings. */
    var SELECTORS = {
        HEADER: '#page-header .header-body',
        HEADER_TOP: "#page-header .page-head",
        BTN_COURSETOOLS: '#btn_coursetools',
        BTN_DUPLICATE: '#btn_duplicate',
        BTN_NEW: '#btn_new',
        BTN_STUDENT: '#btn_student',
        BTN_DESCRIPTION: '#btn_editdescription',
    };

    /**
     * Initializes global variables.
     * @param {string} root - Jquery selector for container.
     * @param {int} headerstyle - selected header style.
     * @param {int} courseid - ID of current course.
     * @return void
     */
    var _setGlobals = function(root, course,templatelist, categories) {
       _root = $(root);
       _course = course;
       _templatelist = templatelist;
       _categories = categories;

       courseActionsLib = new courseActionsLib(_course.id,_course.coursename, _course.shortname, _course.startdate, _course.enddate, _templatelist, _categories);
    };

    /**
     * Sets up event listeners.
     * @return void
     */
    var _registerEventListeners = function() {

        _root.on('click', SELECTORS.BTN_COURSETOOLS, _courseAction);
        _root.on('click', SELECTORS.BTN_DUPLICATE, _courseAction);
        _root.on('click', SELECTORS.BTN_NEW, _courseAction);
        _root.on('click', SELECTORS.BTN_STUDENT, _createStudentAction);
        _root.on('click', SELECTORS.BTN_DESCRIPTION, _editCourseDescription);
    };

    var _courseAction = function() {

        var button = "btn_new";
        var _element = $(this);

        courseActionsLib.coursereqAction(_element,button, _course.category, _course.startdate, _course.enddate);
    }


     /**
     * Modal after button click to create student account
     */
    var _createStudentAction = function() {

        var username =this.getAttribute("aria-username");
        var modaltitle = 'Create and enroll test student account in course';
        var modalaction = 'create the test student account '+username+'+student@uregina.ca';
		
        //adding in confirmation modal in case buttons accidentally clicked
        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: modaltitle,
            body: "<p><b>Do you want to "+ modalaction +"?</b><br /></p>"
        })
        .then(function(modal) {
            
            modal.setSaveButtonText('Create');
            var root = modal.getRoot();
            root.on(ModalEvents.cancel, function(){
                return;
            });

            root.on(ModalEvents.save, function(){
                _createStudentAccount(username)
            });

            modal.show();
        });
    };

     /**
     * After modal info has been entered call ajax request
     */
    var _createStudentAccount = function(username) {

        // return if required values aren't set
        if (!_course.id) {
            return;
        }

        // set args
        var args = {
            courseid: _course.id,
            username: username
        };

        // set ajax call
        var ajaxCall = {
            methodname: 'theme_urcourses_default_header_create_test_account',
            args: args,
            success: function (data) { 
                _createdAccount(data);
            },
            fail: notification.exception
        };

        // initiate ajax call
        var promise = ajax.call([ajaxCall]);
        promise[0].done(function(response) {
            _createdAccount(response);
        }).fail(function(ex) {
            notification.exception;
        });	
    };
    /**
     * Handles ajax return and response to return data
     * @param {Object} response 
     */
    var _createdAccount = function(data) {
    
        //create new modal
        title = "Success: ";
        info="";
        action=" Would you like to logout, inorder to login to new account?";

        //if both actions were done
        if(data.created != false && data.enrolled !=false){
            info =data.username+" was created and enrolled into this course. Login password is the same as current account.";
        }else if(data.created != false && data.enrolled ==false ){
            info = data.username+" was created, but FAILED to be enrolled into this course";
        }else if(data.created == false && data.enrolled !=false ){
            info = data.username+" already existed, and has now been enrolled into this course. Login password is the same as current account.";
        }else{
            title = "ERROR:"
            info = data.username+" already exists and is enrolled into the course";

            ModalFactory.create({
                title: title,
                body: '<p><b>'+info+'</b><br></p>',
              })
              .done(function(modal) {
                modal.show();
              });
              
            return;
        }
        
        //adding in confirmation modal in case buttons accidentally clicked
        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: title,
            body: "<p><b>"+ info +"</b><br>"+action+"<br /></p>"
        })
        .then(function(modal) {
            
            modal.setSaveButtonText('Logout');
            var root = modal.getRoot();
            root.on(ModalEvents.cancel, function(){
                return;
            });

            root.on(ModalEvents.save, function(){
                var href = $('a[data-title="logout,moodle"]').attr('href');
                window.location.href = href;
                $("#username").value= data.username;      
            });
            modal.show();
        });
    };

     /**
     * Link to course edit page to edit course description
     */
    var _editCourseDescription = function() {

        var origin   = window.location.href;    
         
        origin =origin.substring(0,origin.lastIndexOf('/'));

        var target = origin + "/edit.php?id="+_course.id+"#id_descriptionhdr";

        window.location.href = target;
    };

     /**
     * Check if on course edit page and if hash has been added to url
     */
    var _checkCourseEdit = function() {

        var origin   = window.location.href;     

        if (origin.indexOf("course/edit.php") >= 0){
         
            if(window.location.hash != ""){
                var navHeight = $('.navbar').outerHeight();
                $('html, body').animate({
                    scrollTop: $(window.location.hash).offset().top - navHeight
                }, 2000);
            }
        }
    };


    /**
     * Entry point to module. Sets globals and registers event listeners.
     * @param {String} root Jquery selector for container.
     * @return void
     */
    var init = function(root, course, templatelist, categories) {
        _setGlobals(root, course, templatelist,categories);
        _registerEventListeners();

        //little hacky way to use a scroll jump on course edit page without editing course edit
        _checkCourseEdit();
    };

    return {
        init: init
    };

});