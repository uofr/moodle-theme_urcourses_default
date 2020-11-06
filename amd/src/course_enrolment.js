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
 * @author     Brooke Clary
 * 
 */

define(['jquery', 'core/ajax', 'core/notification', 'core/str',
    'core/modal_factory', 'core/modal_events'], function($, ajax, notification, str, ModalFactory, ModalEvents) {

    /** Container jquery object. */
    var _root;
    /** Course ID */
    var _courseid;
    var _element;
    var _semester;

    /** Jquery selector strings. */
    var SELECTORS = {
        HEADER: '#page-header .header-body',
        HEADER_TOP: "#page-header .page-head",
        BTN_COURSEENROL: '#btn_courseenrol',
        BTN_ENROLTERM: '.btn_enrolterm',
    };

    /**
     * Initializes global variables.
     * @param {string} root - Jquery selector for container.
     * @param {int} headerstyle - selected header style.
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
        _root.on('click', SELECTORS.BTN_ENROLTERM, _getEnrolInfo);
    };
     /**
     * Sets up event listeners.
     * @return void
     */
    var _registerSelectorEventListeners = function() {
        //set event listners for template options
        $('.tmpl-label').bind('click', function() { _setActiveTemplate($(this)) } );   
    } 
    /**
     * Sets up event listeners.
     * @return void
     */
    var _registerModalButtons = function() {
        //set event listners for template options
        $('#btn_duplicatemodal').bind('click', function() { _coursereqAction($(this)) } );   
        $('#btn_newmodal').bind('click', function() { _coursereqAction($(this)) } );   
    }

     /**
     * Initiate ajax call to get enrollment info 
     * to create modal with choices
     */
    var _getEnrolInfo = function() {

        _element = $(this);
        _semester = _element.attr('id');

        // return if required values aren't set
        if (!_courseid) {
            return;
        }
        // set args
        var args = {
            courseid: _courseid,
            semester: _semester
        };

        // set ajax call
        var ajaxCall = {
            methodname: 'theme_urcourses_default_header_enrollment_info',
            args: args
        };

        // initiate ajax call
        var promise = ajax.call([ajaxCall]);
        promise[0].done(function(response) {
            _populateModal(response);
        }).fail(function() {
            notification.exception;
        });	
    }

    /**
    * Switch choosen template based on click in template list
    */
    var _setActiveTemplate = function(e) {

        templateHolder = $('div[data-role="templateholder"');
        $(templateHolder).find('.active').removeClass("active");
        e.addClass("active");
    };
    
    /**
     * Handles ajax return and response to create modal with options
     * @param {Object} response 
     */
    var _populateModal = function(data) {

        // create modal with current selection as header
        var modaltitle = 'Modify enrolment for '+data.semester;
            
        activatedlist = "";
        activatedtitle = "";
        isavailable = data.isavaliable

        if(isavailable ==false){

            //spinner incase one is clicked
            output = '<div class="d-flex justify-content-center">'
                    +'<div id= "mainspinner" class="spinner-border " role="status"style="display: none;" >'
                    +'<span class="sr-only">Loading...</span>'
                    +'</div>'
                    +'</div>'
                    +'<div id="infoholder" class="">';

            //We want an alert saying this course was activate in semester ...
            output += '<div class="alert alert-warning" role="alert">'
                    +'This course was used in a different semester. Please choose one of the '
                    +'following options:'
                    +'</div>';

            //Duplicate this course? 
            output +=//'<div data-role="templateholder" '
                    '<button class="btn btn-secondary" id="btn_duplicatemodal" type="button">'
                    +'<i class="fa fa-files-o" aria-hidden="true"></i>'
                    +'Duplicate this course'
                    +'</button>';
            //or
            //create new course?
            output += '<button class="btn btn-secondary" id="btn_newmodal"style="float: right;" type="button">'
                    +'<i class="fa fa-plus" aria-hidden="true"></i> '
                    +'Create blank course'  
                    + '</button> </div></div>';

        }else{

            if(data.activated.length !=0){
                activatedtitle = "<small>The following Banner sections are already enrolled for the selected term:</small>";
                activatedlist += '<div class="list-group">';
                $.each( data.activated, function( key, value ) {
                    activatedlist += '<div class="list-group-item list-group-item-secondary">'+
                                    '<h6>'+value.subject + ' ' +value.course + '-' + value.section +'</h6>'+
                                    '</div>';
                });
    
                activatedlist += '</div><br>';
            }
    
            templatelist = '<div data-role="templateholder" class = "list-group" >';
            //check if any data exists
            if(data.courseinfo.length !=0){

                if(data.courseinfo.length>6){
    
                    templatelist += '<label  class="col-form-label d-inline " for="bannerselect">Select Banner Section:</label><br> ';
                    templatelist += '<div class="form-control-feedback invalid-feedback" id="error_bannerselect"></div> ';
                    templatelist += '<select class="custom-select" id="bannerselect" ';
               
                    $.each( data.courseinfo, function( key, value ) {
                        
                        templatelist+= '<option value = ' +value.subject + ' ' +value.course + '-' + value.section +'</option>';
                       
                    });
                    templatelist += '</select>';
    
                }else{
                    //special clicking list
                    count=1;
                    $.each( data.courseinfo, function( key, value ) {
                        if(count==1){
                        templatelist += '<div data-role = "bannerselect" class="tmpl-label list-group-item list-group-item-action active" id = '+value.crn+'>'+
                                        '<h6>'+value.subject + ' ' +value.course + '-' + value.section +'</h6>'+
                                        '</div>';
                        }else{
                            templatelist += '<div data-role = "bannerselect" class="tmpl-label list-group-item list-group-item-action " id = '+value.crn+'>'+
                                        '<h6>'+value.subject + ' ' +value.course + '-' + value.section +'</h6>'+
                                        '</div>';
                        }
                        count++;
                    });
                }
            }else{
                // else not error message
                //add default template option even if there is no template category
                templatelist += '<div data-role = "templateselect" class="tmpl-label list-group-item list-group-item-action active" id = "0" >'+
                '<p>There are no available Banner Sections for this course </p>'+
                '</div>';
            }
            templatelist += '</div>';
        }
      
        //adding in confirmation modal in case buttons accidentally clicked
        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: modaltitle,
            body: ((isavailable) ? "<p><b>Are you sure you want to modify enrolment for this course?</b><br />"
                    + activatedtitle 
                    + activatedlist 
                    + "<small>The following CRNs are available for the selected term:</small>"
                   + templatelist
                   + "</p>" : output )

        }).then(function(modal) {

            if(isavailable){
                modal.setSaveButtonText('Save');
                var root = modal.getRoot();
                root.on(ModalEvents.cancel, function(){
                    return;
                });
                    
                root.on(ModalEvents.save, function(){
                    _addEnrolment(data.courseinfo);    
                });
            }else{
                var root = modal.getRoot();
                root.on(ModalEvents.cancel, function(){
                    return;
                });
            }
           
            //remove modal on hide
            root.on(ModalEvents.hidden, function(){
                //remove inputs otherwise duplicates are made causing id problems
                $( "div[data-role='templateholder']" ).remove();
            });
            modal.show();
        }).done(function() {
            if(isavailable){
                if(data.courseinfo.length<6 && data.courseinfo.length>0 ){
                    _registerSelectorEventListeners(_element);
                }
            }else{
                _registerModalButtons();
            }
        });
    };
    
    /**
     * After modal info has been entered call ajax request
     */
    var _addEnrolment = function(courseinfo) {
        
        // return if required values aren't set
        if (!_courseid) {
            return;
        }
        
        if(courseinfo.length>6){
            templateid = $('#bannerselect').val();
        }else{
            templateHolder = $('div[data-role="templateholder"');
            selectedTemplate = $(templateHolder).find('.active')
            templateid = $(selectedTemplate).attr('id');
        }

        if(templateid !=0){
            //duplicate course option selected
            // set args
            var args = {
                courseid: _courseid,
                semester: _semester,
                crn: templateid,    
            };

            // set ajax call
            var ajaxCall = {
                methodname: 'theme_urcourses_default_header_activate_course',
                args: args,
                fail: notification.exception
            };

            // initiate ajax call
            var promise = ajax.call([ajaxCall]);
            promise[0].done(function(response) {
                
                if(response.result!=""){
                    title = "Response:"
                    info = response.result;
                }
                //enrollment was a success
                if(response.value){
                    messageHolder = $('#enrollment_span');
                    messageHolder.text("Enrolment: "+response.semester);
                }
                    
                ModalFactory.create({
                        title: title,
                        body: '<p><b>'+info+'</b><br></p>',
                }).done(function(modal) {
                    modal.show();
                });

            }).fail(function() {
                notification.exception;
            });  
        }
    };

     /**
     * Used for new course and duplicate course creation on button clicks
     */
    var _coursereqAction = function() {

        _element = $(this);

        var modaltitle = (_element.attr('id') == 'btn_newmodal') ? 'Create new course' : 'Duplicate this course';
        var modalaction = (_element.attr('id') == 'btn_newmodal') ? 'create a new' : 'duplicate this';
            
        templatenew = '<div data-role="templateholder" class = "list-group" >';
        if(_templatelist !=0){

            if(_templatelist.length>6){

                templatenew += '<label  class="col-form-label d-inline " for="templateselect">Template Select:</label><br> ';
                templatenew += '<div class="form-control-feedback invalid-feedback" id="error_templateselect"></div> ';
                templatenew += '<select class="custom-select" id="templateselect" ';
           
                templatenew += '<option value = 0 > Default Blank Course</option>';
                templatenew += '<option value = 0 > Default Blank Course</option>';
                $.each( _templatelist, function( key, value ) {
                    templatenew += '<option value = '+value.id+'>'+value.fullname +'</option>';
                });
                templatenew += '</select>';

            }else{
                $.each( _templatelist, function( key, value ) {
                    templatenew += '<div data-role = "templateselect" class="tmpl-label list-group-item list-group-item-action " id = '+value.id+'>'+
                                    '<h6>'+value.fullname +'</h6>'+
                                    '<p><small>'+value.summary+'</small></p>'+
                                    '</div>';
                });

                //add default template option even if there is no template category
                templatenew += '<div data-role = "templateselect" class="tmpl-label list-group-item list-group-item-action active" id = "0" >'+
                '<h6>Default Course</h6>'+
                '<p><small>Basic course template. No activites included </small></p>'+
                '</div>';
            }
        }else{

            //add default template option even if there is no template category
            templatenew += '<div data-role = "templateselect" class="tmpl-label list-group-item list-group-item-action active" id = "0" >'+
            '<h6>Default Course</h6>'+
            '<p><small>Basic course template. No activites included </small></p>'+
            '</div>';
        }

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
                + ((_element.attr('id') == 'btn_newmodal') ? "<small>Select from the templates below:</small>" : "<small>Student data will not be included in the duplicated course.</small>")
                + ((_element.attr('id') == 'btn_newmodal') ? templatenew : '' )
                + (!(_element.attr('id') == 'btn_newmodal') ? templateduplicate : '' )
                + "</p>"
        }).then(function(modal) {

            modal.setSaveButtonText((_element.attr('id') == 'btn_newmodal') ? 'Create new course' : 'Duplicate this course');
            var root = modal.getRoot();
            root.on(ModalEvents.cancel, function(){
                return;
            });
                
            if((_element.attr('id') == 'btn_newmodal')){
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
        }).done(function() {
            if((_element.attr('id') == 'btn_newmodal')){
                _registerSelectorEventListeners(_element);
            }
        });
    };

    /**
     * Switch choosen template based on click in template list
     */
    var _setActiveTemplate = function(e) {

        templateHolder = $('div[data-role="templateholder"');
        $(templateHolder).find('.active').removeClass("active");
        e.addClass("active");
    };

    /**
     * For Duplicate and Create course.
     * Validate if course name and shortname have been entered
     */
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

    /**
     * After modal info has been entered call ajax request
     */
    var _createCourse = function() {
        
        // return if required values aren't set
        if (!_courseid) {
            return;
        }
        
        if(_templatelist.length>6){
            templateid = $('#templateselect').val();
        }else{
            templateHolder = $('div[data-role="templateholder"');
            selectedTemplate = $(templateHolder).find('.active')
            templateid = $(selectedTemplate).attr('id');
        }

        coursename = $('#coursename').val();
        shortname = $('#shortname').val();
        categoryid = $('#category').val();
        $('#mainspinner') .show();
        $('#infoholder').addClass("block_urcourserequest_overlay");

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
            fail: notification.exception
        };

        // initiate ajax call
        var promise = ajax.call([ajaxCall]);
        promise[0].done(function(response) {
            $('#mainspinner') .hide();
            $('#infoholder').removeClass("block_urcourserequest_overlay");
            if(response.error!=""){

                title = "ERROR:"
                info = response.error;

                ModalFactory.create({
                    title: title,
                    body: '<p><b>'+info+'</b><br></p>',
                })
                .done(function(modal) {
                    modal.show();
                });
            }
            //ADD REDIRECT TO NEW COURSE USING NEW ID
            if(response.url !=""){
                window.location.href = response.url;
            }
        }).fail(function() {
            notification.exception;
        });  
    };

     /**
     * After modal info has been entered call ajax request
     */
    var _duplicateCourse = function() {
        
        // return if required values aren't set
        if (!_courseid) {
            return;
        }
        coursename = $('#coursename').val();
        shortname = $('#shortname').val();
        categoryid = $('#category').val();

        $('#mainspinner') .show();
        $('#infoholder').addClass("block_urcourserequest_overlay");

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
            fail: notification.exception
        };

        // initiate ajax call
        var promise = ajax.call([ajaxCall]);
        promise[0].done(function(response) {
            $('#mainspinner') .hide();
            $('#infoholder').removeClass("block_urcourserequest_overlay");
            if(response.error!=""){

                title = "ERROR:"
                info = response.error;

                ModalFactory.create({
                    title: title,
                    body: '<p><b>'+info+'</b><br></p>',
                })
                .done(function(modal) {
                    modal.show();
                });
            }
            //ADD REDIRECT TO NEW COURSE USING NEW ID
            if(response.url !=""){
                window.location.href = response.url;
            }
        }).fail(function() {
            notification.exception;
        });  
    };

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