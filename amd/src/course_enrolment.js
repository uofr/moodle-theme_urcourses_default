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
    'core/modal_factory', 'core/modal_events','theme_urcourses_default/course_actions'], function($, ajax, notification, str, ModalFactory, ModalEvents,courseActionsLib) {

    /** Container jquery object. */
    var _root;
    /** Course ID */
    var _course;
    var _element;
    var _semester;
    var _semesterdates;
    var _categories;
    var _templatelist;

    /** Jquery selector strings. */
    var SELECTORS = {
        HEADER: '#page-header .header-body',
        HEADER_TOP: "#page-header .page-head",
        BTN_COURSEENROL: '#btn_courseenrol',
        BTN_ENROLTERM: '.btn_enrolterm',
        STARTHOLDER: '#course_tools_startholder',
        ENDHOLDER: '#course_tools_endholder',
        STARTDAY: '#course_tools_start_day',
        STARTMONTH: '#course_tools_start_month',
        STARTYEAR: '#course_tools_start_year',
        STARTHOUR: '#course_tools_start_hour',
        STARTMINUTE: '#course_tools_start_minute',
        ENDDAY: '#course_tools_end_day',
        ENDMONTH: '#course_tools_end_month',
        ENDYEAR: '#course_tools_end_year',
        ENDHOUR: '#course_tools_end_hour',
        ENDMINUTE: '#course_tools_end_minute',
        ENDENABLE: '#course_tools_enddate_enabled',
        ERR_START: '#error_course_tools_start',
        ERR_END: '#error_course_tools_end',
    };
    const TEMPLATES = {
        MODAL_COURSE_ACTION_CONTENT: 'theme_urcourses_default/modal_course_action_date'
    };

    /**
     * Initializes global variables.
     * @param {string} root - Jquery selector for container.
     * @param {int} headerstyle - selected header style.
     * @param {int} courseid - ID of current course.
     * @return void
     */
    var _setGlobals = function(root, course,semesterdates,categories,templatelist) {
       _root = $(root);
       _course = course;
       _semesterdates = semesterdates;
       _categories = categories;
       _templatelist = templatelist;

       courseActionsLib = new courseActionsLib(_course.id,_course.coursename, _course.shortname, _course.startdate, _course.enddate, _templatelist,_categories);

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
    var _registerSelectorEventListeners = function(_element) {
        //set event listners for template options
        $('.tmpl-label').bind('click', function() { _setActiveTemplate($(this)) } );   
    } 
    /**
     * Sets up event listeners.
     * @return void
     */
    var _registerModalButtons = function() {
        //set event listners for template options
        $('#btn_duplicatemodal').bind('click', function() { _courseAction($(this)) } );   
        $('#btn_newmodal').bind('click', function() { _courseAction($(this)) } );   
    }  
    
    /**
     * Sets up event listeners.
     * @return void
     */
    var _registerDeleteButtons = function() {
        //set event listners for template options
        $('button[data-role="delete_button"').bind('click', function() { _deleteEnrollment($(this)); } );
    }

     /**
     * Initiate ajax call to get enrollment info 
     * to create modal with choices
     */
    var _getEnrolInfo = function() {

        _element = $(this);
        _semester = _element.attr('id');

        // return if required values aren't set
        if (!_course.id) {
            return;
        }
        // set args
        var args = {
            courseid: _course.id,
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
        }).fail(function(ex) {
            notification.exception;
        });	
    }

    /**
    * Switch choosen template based on click in template list
    */
    var _setActiveTemplate = function(e) {

        templateHolder = $('div[data-role="templateholder"');
        //on click check if already active
        if(e.hasClass("active")){
            //remove active class
            e.removeClass("active");
            //and uncheck checkbox
            e.find(".fa-check-square-o").addClass("fa-square-o");
            e.find(".fa-square-o").removeClass("fa-check-square-o");
        }else{
            //add active class
            e.addClass("active");
            //and check checkbox
            e.find(".fa-square-o").addClass("fa-check-square-o");
            e.find(".fa-check-square-o").removeClass("fa-square-o");
        }
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
            templatelist = '<div class="form-control-feedback invalid-feedback" id="error_course_tools_section">'
                            +'</div> <div data-role="templateholder" class = "list-group" >';
            //check if any data exists
            if(data.courseinfo.length !=0){

                /*if(data.courseinfo.length>6){
    
                    templatelist += '<label  class="col-form-label d-inline " for="bannerselect">Select Banner Section:</label><br> ';
                    templatelist += '<div class="form-control-feedback invalid-feedback" id="error_bannerselect"></div> ';
                    templatelist += '<select class="custom-select" id="bannerselect" ';
               
                    $.each( data.courseinfo, function( key, value ) {  
                        templatelist+= '<option value = ' +value.subject + ' ' +value.course + '-' + value.section +'>' +value.subject + ' ' +value.course + '-' + value.section +' </option>';  
                    });
                    templatelist += '</select>';
    
                }else{*/
                    //special clicking list
                    $.each( data.courseinfo, function( key, value ) {
                            templatelist += '<div data-role = "bannerselect" class="tmpl-label list-group-item list-group-item-action " id = '+value.crn+'>'+
                                        '<h6><i class="fa fa-square-o" aria-hidden="true"></i>  '+
                                        value.subject + ' ' +value.course + '-' + value.section +'</h6>'+
                                        '</div>';
                    });

                    //add group division checkbox
                    templatelist += '<br><div class="form-check">'
                                    +'<input class="form-check-input" type="checkbox" value="" id="course_tools_groups">'
                                    +'<label class="form-check-label" for="course_tools_groups">'
                                    +' Create groups for each section'
                                    +' </label>'
                                    +' </div>';
                //}
            }else{
                // else not error message
                //add default template option even if there is no template category
                templatelist += '<div data-role = "templateselect" class="tmpl-label list-group-item list-group-item-action active" id = "0" >'+
                '<p>There are no available Banner Sections for this course </p>'+
                '</div>';
            }

            if(data.activated.length !=0){
                activatedtitle = "<small>The following Banner sections are already enrolled for the selected term:</small>";
                activatedlist += '<div class="list-group">';
                $.each( data.activated, function( key, value ) {
                    activatedlist +='<div class="list-group-item list-group-item-secondary">'+
                    '<button data-role="delete_button" type="button" class="btn btn-primary float-right" id="delete_'+value.crn+'_'+value.urid+'">Delete Enrolment</button>'+
                                    '<h6>'+value.subject + ' ' +value.course + '-' + value.section + '<br>'+
                                    'enrolled into '+ value.fullname+'</h6>'+
                                    '</div>';
                });
    
                activatedlist += '</div><br>';
            }

            templatelist += '</div>';
        }
      
        //adding in confirmation modal in case buttons accidentally clicked
        ModalFactory.create({
            type: (data.courseinfo.length != 0) ? ModalFactory.types.SAVE_CANCEL :ModalFactory.types.CANCEL ,
            title: modaltitle,
            body: ((isavailable) ? "<p><b>Are you sure you want to modify enrolment for this course?</b><br />"
                    + "<small>The following CRNs are available for the selected term:</small>"
                   + templatelist
                   +"</br>"
                   + activatedtitle 
                    + activatedlist 
                   + "</p>" : output )

        }).then(function(modal) {

            if(isavailable && data.courseinfo.length != 0){
                modal.setSaveButtonText('Save');
                var root = modal.getRoot();
                root.on(ModalEvents.cancel, function(){
                    return;
                });
                    
                root.on(ModalEvents.save, function(e){

                    //if(data.courseinfo.length>6){
                       // templateid = $('#bannerselect').val();
                    //}else{
                        templateHolder = $('div[data-role="templateholder"');
                        selectedTemplates = $(templateHolder).find('.active');
                        //no selected template error
                        if(selectedTemplates.length <=0){
                            e.preventDefault(); 
                            $("#error_course_tools_section").text("Please select a banner section");
                            $("#error_course_tools_section").attr("display", "block");
                            $("#error_course_tools_section").show();
                         
                        }else{
                            var templateids = [];
                            for(var i = 0; i<selectedTemplates.length; i++){
                                //templateids.push( {"crn": $(selectedTemplates[i]).attr('id')});
                                templateids.push( {"crn": $(selectedTemplates[i]).attr('id')});
                            }
                            
                            var groupcheck = $("#course_tools_groups").prop("checked") 

                            console.log("groupcheck 1");
                            console.log(groupcheck);
                            _addEnrolmentDateConfirm(data.courseinfo, templateids, groupcheck,data.semester); 
                        }
                    //}
                     
                });
            }else{
                var root = modal.getRoot();
                root.on(ModalEvents.cancel, function(){
                    return;
                });
            }
            //remove modal on hide
            root.on(ModalEvents.hidden, function(e){
                //remove inputs otherwise duplicates are made causing id problems
                $( "div[data-role='templateholder']" ).remove();
            });
            modal.show();
        }).done(function(modal) {
            if(isavailable){
                if(/*data.courseinfo.length<=6 && */data.courseinfo.length>0 ){
                    _registerSelectorEventListeners(_element);
                }
                
            }else{
                _registerModalButtons();
            }
            if(data.activated.length !=0){
                _registerDeleteButtons();

            }
        });
    };

    /**
     * Handles ajax return and response to create modal with options
     * @param {Object} response 
     */
    var _addEnrolmentDateConfirm = async function(data, templateids,groupcheck, semester) {

        // create modal with current selection as header
        var modaltitle = 'Change course dates to: ';
        var template =  await self.render(TEMPLATES.MODAL_COURSE_ACTION_CONTENT);
      
        //adding in confirmation modal in case buttons accidentally clicked
        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: modaltitle,
            body: ("<p><b>Confirm Date change?</b><br />"+template )

        }).then(function(modal) {

                modal.setSaveButtonText('Save');
                var root = modal.getRoot();
                root.on(ModalEvents.cancel, function(){
                    return;
                });
                    
                root.on(ModalEvents.save, function(e){

                    if(!validate()){
                        e.preventDefault();   
                    }else{
                        _addEnrolment(data, templateids,groupcheck); 
                    }     
                });
            
            //remove modal on hide
            root.on(ModalEvents.hidden, function(e){
                //remove inputs otherwise duplicates are made causing id problems
                $( SELECTORS.ENDHOLDER).remove();
                $( SELECTORS.STARTHOLDER).remove();
            });
            modal.show();
        }).done(function(modal) {
            
            var startdate = {"year": (new Date()).getFullYear(), "mon": (new Date()).getMonth(),"mday":(new Date()).getDate()};
            var enddate = {"year": (new Date()).getFullYear() +1, "mon": (new Date()).getMonth(),"mday":(new Date()).getDate()};

            jQuery.each(_semesterdates, function(index, item) {
                if(index == _semester){
                    var starttemp = item.startdate.split("-");
                    var endtemp = item.enddate.split("-");

                    startdate = {"year": starttemp[2], "mon": starttemp[1],"mday":starttemp[0]};
                    enddate = {"year": endtemp[2], "mon": endtemp[1],"mday":endtemp[0]};
                }
            });

            courseActionsLib.populateDateSelects(startdate,enddate);  
            courseActionsLib.registerDateEventListeners(_element);

        });
    };
    /**
     * For Duplicate and Create course.
     * Validate if course name and shortname have been entered
     */
    var validate = function() {

        var startday = $(SELECTORS.STARTDAY).val();
        var startmonth = $(SELECTORS.STARTMONTH).val();
        var startyear = $(SELECTORS.STARTYEAR).val();
        var endyear = $(SELECTORS.ENDYEAR).val();
        var endday = $(SELECTORS.ENDDAY).val();
        var endmonth = $(SELECTORS.ENDMONTH).val();
   
        var test =true; 
        
        var startdate = new Date(startyear+"."+startmonth+"."+startday).getTime()/1000;
        var enddate = new Date(endyear+"."+endmonth+"."+endday).getTime()/1000;
      
        if(enddate < startdate && $(SELECTORS.ENDENABLE).is(":checked") ){
            $(SELECTORS.ERR_START).text("Course end date can not be before start date");
            $(SELECTORS.ERR_START).attr("display", "block");
            $(SELECTORS.ERR_START).show();
            test = false;
        }else{
            $(SELECTORS.ERR_START).text("");
            $(SELECTORS.ERR_END).text("");
        }

        return test;
    }
    
    /**
     * After modal info has been entered call ajax request
     */
    var _addEnrolment = function(courseinfo, templateids, groupcheck) {

        // return if required values aren't set
        if (!_course.id) {
            return;
        }

        var startday = $(SELECTORS.STARTDAY).val();
        var startmonth = $(SELECTORS.STARTMONTH).val();
        var startyear = $(SELECTORS.STARTYEAR).val();
        var starthour = $(SELECTORS.STARTHOUR).val();
        var startminute = $(SELECTORS.STARTMINUTE).val();
        var startdate = startday+"-"+startmonth+"-"+startyear+"-"+starthour+"-"+startminute;

        var enddate = "0";
        if($(SELECTORS.ENDENABLE).is(":checked") ){
            var endday = $(SELECTORS.ENDDAY).val();
            var endmonth= $(SELECTORS.ENDMONTH).val();
            var endyear = $(SELECTORS.ENDYEAR).val();
            var endhour = $(SELECTORS.ENDHOUR).val();
            var endminute = $(SELECTORS.ENDMINUTE).val();
            enddate = endday+"-"+endmonth+"-"+endyear+"-"+endhour+"-"+endminute;
        }

        if(groupcheck == "" || groupcheck == false){
            groupcheck = 0;

        }else{
            groupcheck = 1;
        }

        // set args
        var args = {
            courseid: _course.id,
            semester: _semester,
            crns: templateids, 
            groupcheck: groupcheck,   
            startdate: startdate,
            enddate: enddate
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

        }).fail(function(ex) {
            notification.exception;
        });  
        
    };
    
    /**
     * After modal info has been entered call ajax request
     */
    var _deleteEnrollment = function(e) {
        
        var crn = e.attr('id').split("_");

        console.log("made it");
        console.log(crn);
        console.log(_semester);

        // set args
        var args = {
            courseid:crn[2],
            semester: _semester,
            crn: crn[1], 
        };

        // set ajax call
        var ajaxCall = {
            methodname: 'theme_urcourses_default_header_delete_enrollment',
            args: args,
            fail: notification.exception
        };

        // initiate ajax call
        var promise = ajax.call([ajaxCall]);
        promise[0].done(function(response) {

            //2nd popup saying enrolment removed with filled in names from element

            //remove from activated list
            //add to available list










                
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

        }).fail(function(ex) {
            notification.exception;
        });  
        
    };

    var _courseAction = function(e) {

        var button = "btn_newmodal";
        var startdate = {"year": (new Date()).getFullYear(), "mon": (new Date()).getMonth(),"mday":(new Date()).getDate()};
        var enddate = {"year": (new Date()).getFullYear() +1, "mon": (new Date()).getMonth(),"mday":(new Date()).getDate()};

        jQuery.each(_semesterdates, function(index, item) {
            if(index == _semester){
                var starttemp = item.startdate.split("-");
                var endtemp = item.enddate.split("-");

                startdate = {"year": starttemp[2], "mon": starttemp[1],"mday":starttemp[0]};
                enddate = {"year": endtemp[2], "mon": endtemp[1],"mday":endtemp[0]};
            }
        });

        courseActionsLib.coursereqAction(e, button, _course.category, startdate, enddate);
    }



    /**
     * Entry point to module. Sets globals and registers event listeners.
     * @param {String} root Jquery selector for container.
     * @return void
     */
    var init = function(root, course, semesterdates,categories, templatelist) {
        _setGlobals(root, course, semesterdates,categories,templatelist);
        _registerEventListeners();
    };

    return {
        init: init
    };
});