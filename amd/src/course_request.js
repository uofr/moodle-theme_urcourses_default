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
        BTN_COURSETOOLS: '#btn_coursetools',
        BTN_DUPLICATE: '#btn_duplicate',
        BTN_NEW: '#btn_new',
        BTN_STUDENT: '#btn_student',
    };

    /**
     * Initializes global variables.
     * @param {string} root - Jquery selector for container.
     * @param {int} headerstyle - selected header style.
     * @param {int} courseid - ID of current course.
     * @return void
     */
    var _setGlobals = function(root, courseid,templatelist, categories) {
       _root = $(root);
       _courseid = courseid;
       _templatelist = templatelist;
       _categories = categories;
    };

    /**
     * Sets up event listeners.
     * @return void
     */
    var _registerEventListeners = function() {
        _root.on('click', SELECTORS.BTN_COURSETOOLS, _coursereqAction);
        _root.on('click', SELECTORS.BTN_DUPLICATE, _coursereqAction);
        _root.on('click', SELECTORS.BTN_NEW, _coursereqAction);
        _root.on('click', SELECTORS.BTN_STUDENT, _createStudentAction);
    };

     /**
     * Sets up event listeners.
     * @return void
     */
    var _registerSelectorEventListeners = function(_element) {
        if(_element.attr('id') == 'btn_new'){
            //set event listners for template options
            $('.tmpl-label').bind('click', function() { _setActiveTemplate($(this)) } );
        }
    }

    /**
     * Initiate ajax call to upload and set new image.
     */
    var _coursereqAction = function() {

        _element = $(this);

        var modaltitle = (_element.attr('id') == 'btn_new') ? 'Create new course' : 'Duplicate this course';
        var modalaction = (_element.attr('id') == 'btn_new') ? 'create a new' : 'duplicate this';
            
        templatenew = '<div data-role="templateholder" class = "list-group" >';
        if(_templatelist !=0){
            $.each( _templatelist, function( key, value ) {
                templatenew += '<div data-role = "templateselect" class="tmpl-label list-group-item list-group-item-action " id = '+value.id+'>'+
                                '<h6>'+value.fullname +'</h6>'+
                                '<p><small>'+value.summary+'</small></p>'+
                                '</div>';
            });
        }
        //add default template option even if there is no template category
        templatenew += '<div data-role = "templateselect" class="tmpl-label list-group-item list-group-item-action active" id = "0" >'+
                '<h6>Default Course</h6>'+
                '<p><small>Basic course template. No activites included </small></p>'+
                '</div>';

        templatebase = '</div>';
        templatebase += '<br><br>'
        templatebase += '<div class="form-control-feedback invalid-feedback" id="error_coursename"></div> ';
        templatebase += '<label  class="col-form-label d-inline " for="coursename">Course Full Name:</label><input maxlength="254" class="form-control " type="text" id="coursename" name="coursename"><br> ';
        templatebase += '<div class="form-control-feedback invalid-feedback" id="error_shortname"></div> ';
        templatebase += '<label class="col-form-label d-inline " for="shortname">Course Short Name:</label><input maxlength="254" class="form-control " type="text" id="shortname" name="shortname"><br> ';


        templatebase += '<label  class="col-form-label d-inline " for="category">Course Category:</label><br> ';
        templatebase += '<div class="form-control-feedback invalid-feedback" id="error_category"></div> ';
        templatebase += '<select class="custom-select" id="category" ';
        if(_categories !=0){
            $.each( _categories, function( key, value ) {
                templatebase += '<option value = '+value.id+'>'+value.name +'</option>';
            });
        }
        templatebase += '</select';

        templatenew += templatebase;  
        templateduplicate = templatebase;
        
        //adding in confirmation modal in case buttons accidentally clicked
        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: modaltitle,
            body: "<p><b>Are you sure you want to "+ modalaction +" course?</b><br />"
                + ((_element.attr('id') == 'btn_new') ? "<small>Select from the templates below:</small>" : "<small>Student data will not be included in the duplicated course.</small>")
                + ((_element.attr('id') == 'btn_new') ? templatenew : '' )
                + (!(_element.attr('id') == 'btn_new') ? templateduplicate : '' )
                + "</p>"
        }).then(function(modal) {

            modal.setSaveButtonText((_element.attr('id') == 'btn_new') ? 'Create new course' : 'Duplicate this course');
            var root = modal.getRoot();
            root.on(ModalEvents.cancel, function(){
                return;
            });
                
            if((_element.attr('id') == 'btn_new')){
                root.on(ModalEvents.save, function(e){
                    if(!_validate()){
                        e.preventDefault();   
                    }else{
                        _createCourse(_element.attr('id') );
                    }    
                });
            }else{
                root.on(ModalEvents.save, function(e){
                    if(!_validate()){
                        e.preventDefault();   
                    }else{
                        _duplicateCourse(_element.attr('id') );
                    }
                });
            }
            //remove modal on hide
            root.on(ModalEvents.hidden, function(e){
                //remove inputs otherwise duplicates are made causing id problems
                $( "#coursename" ).remove();
                $( "#shortname" ).remove();
                $( "#category" ).remove();
            });

            modal.show();
        }).done(function(modal) {
            if((_element.attr('id') == 'btn_new')){
                _registerSelectorEventListeners(_element);
            }
        });
    };

    var _setActiveTemplate = function(e) {

        templateHolder = $('div[data-role="templateholder"');
        $(templateHolder).find('.active').removeClass("active");
        e.addClass("active");
    };


    var _validate = function() {

        coursename = $('#coursename').val();
        shortname = $('#shortname').val();

        error_coursename = $('#error_coursename');
        error_shortname = $('#error_shortname');

        var test =true; 
        if(coursename.length ==0){
            $(error_coursename).text("Please enter a name for the course");
            $(error_coursename).attr("display", "block");
            $(error_coursename).show();
            test = false;
        }

        if(shortname.length ==0){
            $(error_shortname).text("Please enter a short name for the course");
            $(error_shortname).attr("display", "block");
            $(error_shortname).show();
            test = false;
        }

        if(!test){
            return false;
        }
        $(error_coursename).text("");
        $(error_shortname).text("");
        return true;
    };

    var _createCourse = function(elementid) {
        
        // return if required values aren't set
        if (!_courseid) {
            return;
        }
        
        templateHolder = $('div[data-role="templateholder"');
        selectedTemplate = $(templateHolder).find('.active')
        templateid = $(selectedTemplate).attr('id');

        coursename = $('#coursename').val();
        shortname = $('#shortname').val();
        categoryid = $('#category').val();

        //duplicate course option selected
        // set args
        var args = {
            courseid: _courseid,
            templateid: templateid,    
            coursename: coursename,
            shortname: shortname,
            categoryid: categoryid,
        };

        // set ajax call
        var ajaxCall = {
            methodname: 'theme_urcourses_default_header_create_course',
            args: args,
            done: _choiceDone,
            fail: notification.exception
        };

        // initiate ajax call
        var promise = ajax.call([ajaxCall]);
        promise[0].done(function(response) {
            if(response.error!=""){

                var trigger = $('#create-modal');
                ModalFactory.create({
                  title: 'Error:',
                  body: response.error,
                  
                }, trigger)
                .done(function(modal) {
                  // Do what you want with your new modal.
                });
            }
            //ADD REDIRECT TO NEW COURSE USING NEW ID
            if(response.url !=""){
                window.location.href = response.url;
            }


        }).fail(function(ex) {
            notification.exception;
        });  
    };

    var _duplicateCourse = function(elementid) {
        
        // return if required values aren't set
        if (!_courseid) {
            return;
        }
        coursename = $('#coursename').val();
        shortname = $('#shortname').val();
        categoryid = $('#category').val();

        //duplicate course option selected
        // set args
        var args = {
            courseid: _courseid,
            coursename: coursename,
            shortname: shortname,
            categoryid: categoryid,
        };

        // set ajax call
        var ajaxCall = {
            methodname: 'theme_urcourses_default_header_duplicate_course',
            args: args,
            done: _choiceDone,
            fail: notification.exception
        };

        // initiate ajax call
        var promise = ajax.call([ajaxCall]);
        promise[0].done(function(response) {
            if(response.error!=""){
                var trigger = $('#create-modal');
                ModalFactory.create({
                  title: 'Error:',
                  body: response.error,
                  
                }, trigger)
                .done(function(modal) {
                  // Do what you want with your new modal.
                });
            }
            //ADD REDIRECT TO NEW COURSE USING NEW ID
            if(response.url !=""){
                window.location.href = response.url;
            }
        }).fail(function(ex) {
            notification.exception;
        });  
    };

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

    var _createStudentAccount = function(username) {

        // return if required values aren't set
        if (!_courseid) {
            return;
        }

        // set args
        var args = {
            courseid: _courseid,
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
     * Handles theme_urcourses_default_upload_course_image response data.
     * @param {Object} response 
     */
    var _createdAccount = function(data) {
    
        //create new modal
        title = "Success: "
        info="";
        action=" Would you like to logout, inorder to login to new account?"

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
     * Handles theme_urcourses_default_upload_course_image response data.
     * @param {Object} response 
     */
    var _choiceDone = function() {
        str.get_string('success:coursestylechosen', 'theme_urcourses_default')
            .done(_createSuccessPopup);
    };

    /**
     * Creates a bootstrap dismissable containing success text.
     * Dismissable should appear in header and should cover upload button.
     * @param {string} text Success text.
     */
    var _createSuccessPopup = function(text) {
        // create bootstrap 4 dismissable
        var popup = $('<div></div>');
        popup.text(text);
        popup.prop('id','cstyle_success_popup');
        popup.css({'position' : 'absolute'});
        popup.css({'right' : '5px'});
        popup.css({'bottom' : '30px'});
        popup.css({'z-index' : '1200'});
        popup.addClass('alert alert-success alert-dismissable fade show');

        // create dismiss button
        var dismissBtn = $('<button></button>');
        dismissBtn.html('&times;');
        dismissBtn.addClass('close');
        dismissBtn.attr('type', 'button');
        dismissBtn.attr('data-dismiss', 'alert');

        // append dismiss button to popup
        popup.append(dismissBtn);

        // add to header's course image area
        $(SELECTORS.HEADER_TOP).append(popup);

        // makes the alert disapear after a set amout of time.
        setTimeout(function() {
            $('#cstyle_success_popup').fadeTo(500, 0).slideUp(500, function(){
                $(this).remove();
            });
        },1800);
    };

    /**
     * Entry point to module. Sets globals and registers event listeners.
     * @param {String} root Jquery selector for container.
     * @return void
     */
    var init = function(root, courseid, templatelist, categories) {
        _setGlobals(root, courseid,templatelist,categories);
        _registerEventListeners();
    };

    return {
        init: init
    };

});