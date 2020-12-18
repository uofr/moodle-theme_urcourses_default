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
 * Responsible for registering all events for Course Tools including:
 * Course Creation-> calls Course Action Library to handle modals and ajax request
 * Course Duplication-> calls Course Action Library to handle modals and ajax request
 * Create Student Account-> performs ajax request to create a student account for instructor
 * Course Description Edit-> Reroutes to Course Edit page, and add marker to go right to Course Summary Section
 *
 * @package    theme_urcourses_default
 * @author     Brooke Clary
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

    /** Jquery selector strings. */
    var SELECTORS = {
        HEADER: '#page-header .header-body',
        HEADER_TOP: "#page-header .page-head",
        BTN_COURSETOOLS: '#btn_coursetools',
        BTN_DUPLICATE: '#btn_duplicate',
        BTN_NEW: '#btn_new',
        BTN_STUDENT_ACCOUNT: '#btn_student_account',
        BTN_STUDENT_ENROL: '#btn_student_enrol',
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

       //course constructor, incase any create or duplicate course
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
        _root.on('click', SELECTORS.BTN_DESCRIPTION, _editCourseDescription);
        _root.on('click', SELECTORS.BTN_STUDENT_ACCOUNT, _createStudentAction);
        _root.on('click', SELECTORS.BTN_STUDENT_ENROL, _enrollStudentAction);
    };

    /**
     * If course has been used in previous term, options to
     * duplicate or create are given. If either duplicate or create are 
     * clicked they are passed to this function, which calls on the 
     * Course Action Library to provide the correct modals, and perform 
     * any ajax requests
     */
    var _courseAction = function() {

        var button = "btn_new";
        var _element = $(this);

        courseActionsLib.coursereqAction(_element,button, _course.category, _course.startdate, _course.enddate);
    }

     /**
     * Modal after button click to create student account
     */
    var _createStudentAction =  function(e) {


        //look if created or not
        var value =$(SELECTORS.BTN_STUDENT_ACCOUNT).data("value");
        var username =$(SELECTORS.BTN_STUDENT_ACCOUNT).data("username");

        if(value){
            //view student account info
             getStudentAccountInfo(username);
        }else{
        
            var modaltitle = 'Create a test student for UR Courses';
            var modalaction = '<b>The following test user account will be created: </b><br><br>'
                            + '<b>Email address </b>'
                            +'<a class="btn btn-link p-0" role="button" data-toggle="modal" data-placement="right" data-target="moodle-email-modal-dialogue" data-html="true">'
                                +'<i class="icon fa fa-question-circle text-info fa-fw " title="Help with test student account email" aria-label="Help with test student account email"></i>'
                            +'</a><br>'+username+'+urstudent@uregina.ca'
                            +'<br><b>Username </b><br>'+username+'-urstudent'
                            +'<hr/>'
         
                            +'<br> You can enrol this account as a student for the ability to test and experience the course as a student.'
                            +'<br><br>'
                            +'<b>Would you like to create the test student account?</b>'
                            +'<br><label class="form-check  fitem  ">'
                            +'<input type="checkbox" name="id_enroll_test" class="form-check-input " id="id_enroll_test" value="1" size="" checked>'
                            +'Enrol test student into this course'
                            +'</label>'
                        
                           // +'<li class="divider"></li>'
                            
                             ;
            
            //adding in confirmation modal in case buttons accidentally clicked
            ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                title: modaltitle,
                body: "<p>"+ modalaction +"</p>"
            }).then(function(modal) {
                
                modal.setSaveButtonText('Create');
                var root = modal.getRoot();
                root.on(ModalEvents.cancel, function(){
                    return;
                });

                $(root).find('.fa-question-circle').parent().click(function() {
                    ModalFactory.create({
                        type: ModalFactory.types.CANCEL,
                        title: "Help with test student account email",
                        body:'<p>Email sent to this address will appear in your username@uregina.ca inbox</p>',
                    }).then(function(modal) {
                        modal.show();
                    });
                });

                root.on(ModalEvents.save, function(e){
                    e.preventDefault();  
                    $(root).find('button[data-action="save"]').append(' <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="sr-only">Loading...</span>');
                    var checked = $(root).find("#id_enroll_test").is(":checked");
                    _createStudentAccount(username, checked);
                });

                modal.show();
            });
        }
    };

    /**
     * After modal info has been entered call ajax request
     */
    var getStudentAccountInfo =  function(username) {
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
            methodname: 'theme_urcourses_default_header_test_account_info',
            args: args,
            fail: notification.exception
        };

        // initiate ajax call
        var promise =  ajax.call([ajaxCall]);
        promise[0].done(function(data) {

            var modaltitle = 'User details for test student account';

            if(data.userid != 0){
               info = '<b>Email address </b>'
                    +'<a class="btn btn-link p-0" role="button" data-toggle="modal" data-placement="right" data-target="moodle-email-modal-dialogue" data-html="true">'
                        +'<i class="icon fa fa-question-circle text-info fa-fw " title="Help with test student account email" aria-label="Help with test student account email"></i>'
                    +'</a><br>'+data.email
                    +'<br><b>Username </b><br>'+data.username
                    +'<br><b>Account Creation Date </b><br>'+data.datecreated+'<br/>'
                    +'<br><div class="alert alert-warning d-flex justify-content-between bd-highlight " role="alert">'
                        +'<div class="">Reset test account password </div>'  
                        +'<button id="test_account_reset" type="button" class="btn btn-primary" >Reset Password</button>' 
                    +'</div>'
               
            }else{
               info= '<div class="alert alert-warning" role="alert">'
                    +' Test student account could not be found!</div>';
            }
            
            //adding in confirmation modal in case buttons accidentally clicked
            ModalFactory.create({
                type: ModalFactory.types.CANCEL,
                title: modaltitle,
                body:info,

            }).then(function(modal) {
            
                var root = modal.getRoot();
            
                $(root).find('button[data-action="cancel"]').text("Close");

                $(root).find('.fa-question-circle').parent().click(function() {
                    ModalFactory.create({
                        type: ModalFactory.types.CANCEL,
                        title: "Help with test student account email",
                        body:'<p>Email sent to this address will appear in your username@uregina.ca inbox</p>',
                    }).then(function(modal) {
                        modal.show();
                    });
                });

                 //remove modal on hide
                 root.on(ModalEvents.hidden, function(){
                    //remove inputs otherwise duplicates are made causing id problems
                    $( "#test_account_reset" ).remove();
                });

                modal.show();
            }).done(function(modal) {
                $("#test_account_reset").bind('click', function() { _resetStudentAccount($(this), username); } );
            });
        }).fail(function(ex) {
            notification.exception;
        });	

    }

     /**
     * After modal info has been entered call ajax request
     */
    var _createStudentAccount = function(username, checked) {

        // return if required values aren't set
        if (!_course.id) {
            return;
        }

        // set args
        var args = {
            courseid: _course.id,
            username: username,
            checked: checked
        };

        // set ajax call
        var ajaxCall = {
            methodname: 'theme_urcourses_default_header_create_test_account',
            args: args,
            fail: notification.exception
        };

        // initiate ajax call
        var promise = ajax.call([ajaxCall]);
        promise[0].done(function(response) {
            _studentAccountResponse(response);
        }).fail(function(ex) {
            notification.exception;
        });	
    };
    /**
     * Handles ajax return and response to return data
     * @param {Object} response 
     */
    var _studentAccountResponse = function(data) {
    
        if(typeof data.unenroll != "undefined"){

            title = "Success: ";
            info='<div class="alert alert-success" role="alert">'
                    +data.username+" was removed from course. </div>";

            if(!data.unenroll){
                title = "ERROR:"
                info='<div class="alert alert-warning" role="alert">';
                info += data.username+" could not be removed from course. </div>";
            }
        }else if(typeof data.reset != "undefined"){
            title = "Success: ";
            info='<div class="alert alert-success" role="alert">'
                    +data.username+" password was reset. You will recieve an email with new credentials. It may take a few minutes to process.</div>";

            if(!data.reset && data.userid !=0){
                title = "ERROR:"
                info='<div class="alert alert-warning" role="alert">';
                info += data.username+"'s password could not be reset, email failed to send </div>";
            }else if(!data.reset && data.userid ==0){
                title = "ERROR:"
                info='<div class="alert alert-warning" role="alert">';
                info += data.username+"'s account could not be found </div>";
            }
        }else{

            //create new modal
            title = "Success: ";
            info='<div class="alert alert-success" role="alert">';

            //if both actions were done
            if(data.created != false && data.enrolled !=false){
                info +=data.username+" was created and enrolled into this course. New account information has been emailed to you. </div>";
            }else if(data.created != false && data.enrolled ==false ){
                info += data.username+" was created, but was NOT enrolled into this course. New account information has been emailed to you. </div>";
            }else if(data.created == false && data.enrolled !=false ){
                info += data.username+" has been enrolled into this course.</div>";
            }else{
                title = "ERROR:"
                info='<div class="alert alert-warning" role="alert">';
                info += data.username+" already exists and is enrolled into the course. </div>";
            }
        }
        
        //adding in confirmation modal in case buttons accidentally clicked
        ModalFactory.create({
            type: ModalFactory.types.CANCEL,
            title: title,
            body:  info 
        })
        .then(function(modal) {
            
            var root = modal.getRoot();
            $(root).find('button[data-action="cancel"]').text("Close");
            root.on(ModalEvents.cancel, function(){
                if(typeof data.reset == "undefined"){
                    location.reload();
                }
                return;
            });
           
            
            root.find(".close").click(function() {
                if(typeof data.reset == "undefined"){
                    location.reload();
                }
                return;
            });

            modal.show();
        });
    };


    /**
     * After modal info has been entered call ajax request
     */
    var _enrollStudentAction = function() {

        //look if created or not
        var value =$(SELECTORS.BTN_STUDENT_ENROL).data("value");
        var username =$(SELECTORS.BTN_STUDENT_ENROL).data("username");
        
        var modaltitle =  (value) ? 'Unenrol test student account in course' :'Enrol test student account in course';
        var modalaction =  (value) ? 'remove ':  'enrol the test student account ';
        modalaction = modalaction+username+'+urstudent@uregina.ca from course';
            
        //adding in confirmation modal in case buttons accidentally clicked
        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: modaltitle,
            body: "<p><b>Do you want to "+ modalaction +"?</b><br /></p>"
        }).then(function(modal) {
                
            (value) ?modal.setSaveButtonText('Remove'):modal.setSaveButtonText('Enrol');
            var root = modal.getRoot();
            root.on(ModalEvents.cancel, function(){
                return;
            });

            root.on(ModalEvents.save, function(e){
                e.preventDefault();  
                $(root).find('button[data-action="save"]').append(' <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="sr-only">Loading...</span>');
                (value) ? _unenrollStudentAccount(username) : _createStudentAccount(username,true);
            });

            modal.show();
        });
    }

    /**
    * After modal info has been entered call ajax request
    */
    var _resetStudentAccount = function(e, username) { 

        var modaltitle = 'Unenrol test student account';
        var modalaction = 'do you want to reset the password for student account '+username+'+urstudent@uregina.ca.';
        
        //adding in confirmation modal in case buttons accidentally clicked
        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: modaltitle,
            body: "<p><b>Do you want to "+ modalaction +"?</b><br /></p>"
        }).then(function(modal) {
            
            modal.setSaveButtonText('Reset Password');
            var root = modal.getRoot();
          
            root.on(ModalEvents.cancel, function(){
                return;
            });

            root.on(ModalEvents.save, function(e){

                e.preventDefault();  
                $(root).find('button[data-action="save"]').append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="sr-only">Loading...</span>');
                
                // set args
                var args = {
                    courseid: _course.id,
                    username: username
                };

                // set ajax call
                var ajaxCall = {
                    methodname: 'theme_urcourses_default_header_reset_test_account',
                    args: args,
                    fail: notification.exception
                };

                // initiate ajax call
                var promise = ajax.call([ajaxCall]);
                promise[0].done(function(response) {
                    $(root).find('button[data-action="cancel"]').click();
                    _studentAccountResponse(response);
                    return;
                }).fail(function(ex) {
                    notification.exception;
                });
            });

            modal.show();
        });
    }
    /**
    * After modal info has been entered call ajax request
    */
    var _unenrollStudentAccount = function(username) {

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
            methodname: 'theme_urcourses_default_header_unenroll_test_account',
            args: args,
            fail: notification.exception
        };

        // initiate ajax call
        var promise = ajax.call([ajaxCall]);
        promise[0].done(function(response) {
            _studentAccountResponse(response);
        }).fail(function(ex) {
            notification.exception;
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