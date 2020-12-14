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
 * Responsible for:
 * creation of modals to change enrolment,
 * creation of date modal to update date for enrolment
 * performs ajax requests to enrol a banner section,
 * performs ajax requests to delete a banner enrolment
 * calls create and duplicate course library in event course has been used in a different semester
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
    var _homeurl;

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
    var _setGlobals = function(root, course,semesterdates,categories,templatelist,homeurl) {
       _root = $(root);
       _course = course;
       _semesterdates = semesterdates;
       _categories = categories;
       _templatelist = templatelist;
       _homeurl = homeurl;

       //constructor for current course, incase of duplication, creation, or date modals being needed
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
     * Sets up event listeners.
     * @return void
     */
    var _registerDeleteButtons = function() {
        //set event listners for template options
        $('div[data-role="delete_button"]').bind('click', function() { _deleteConfirmation($(this)); } );
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
     * Handles ajax return and response to create modal with options
     * @param {Object} response 
     */
    var _populateModal = function(data) {

        // create modal with current selection as header
        var modaltitle = 'Modify enrolment for '+data.semester;
            
        activatedlist = "";
        activatedtitle = "";
        isavailable = data.isavaliable

        templatelist = '<div class="form-control-feedback invalid-feedback" id="error_course_tools_section">'
                        +'</div> <div data-role="templateholder" class = "list-group" >';
        //check if any data exists
        if(data.courseinfo.length !=0){
            //special selecting box
            $.each( data.courseinfo, function( key, value ) {
                templatelist += '<div data-role = "bannerselect" class="tmpl-label list-group-item list-group-item-action " id = '+value.crn+'>'+
                                '<h6><i class="fa fa-square-o" aria-hidden="true"></i>  '+
                                value.subject + ' ' +value.course + '-' + value.section +'</h6>'+
                                '</div>';
            });
            //add group division checkbox
            templatelist += '<br><div class="form-check ml-3">'
                        +'<input class="form-check-input" type="checkbox" value="" id="course_tools_groups">'
                        +'<label class="form-check-label" for="course_tools_groups">'
                        +'<h6> Create groups for each section </h6>'
                        +' </label>'
                        +' </div>';
        }else{
            // else not error message
            //add default template option even if there is no template category
            templatelist += '<div data-role = "templateselect" class="tmpl-label list-group-item list-group-item-action" id = "0" >'+
                '<p>There are no additional sections available at this time </p>'+
                '</div>';
        }

        //For banner sections already enrolled in current semester
        if(data.activated.length !=0){
            activatedtitle = '<label class="col-form-label d-inline"><b> Current enrolments:  </b></label></br>';
            activatedlist += '<div class="list-group">';
            $.each( data.activated, function( key, value ) {
                var linkedClass = "alert-warning";
                if(value.linked){
                    linkedClass = "alert-success";
                }

                activatedlist +='<div data-role="delete_button" data-fullname ="'+value.fullname+'" id="delete_'+value.crn+'_'+value.urid+'" class="list-group-item list-group-item-secondary enrollment-delete '+linkedClass+' ">'+
                                '<h6 class ="ml-3 d-inline">'+value.subject + ' ' +value.course + '-' + value.section +
                                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'+
                                '<span aria-hidden="true">&times;</span>'+
                                '</button></h6>'+
                                '</div>';
                });
                activatedlist += '</div><br>';
        }
        templatelist += '</div>';


        inSemester = true;
        jQuery.each(_semesterdates, function(index, item) {
         
            if(index == _semester){
            
                starttemp = item.startdate.split("-")
                start = new Date(starttemp[2]+"-"+starttemp[1]+"-"+starttemp[0] );
                var current = new Date();
                
                if(current.getTime() < start.getTime() ){
                    console.log("made it");
                    inSemester=false;
                } 
            }
        });

        if(inSemester){

            // create modal with current selection as header
            var modaltitle = 'Modify enrolment'; 

            var templatelist = '<div data-role = "templateselect" class="tmpl-label list-group-item list-group-item-action" id = "0" >'+
            '<p>This is a past semester. Enrolment can not be modified. </p>'+
            '</div><br>';

            //For banner sections already enrolled in current semester
            if(data.activated.length !=0){
     
                activatedlist = "";
                activatedlist += '<div class="list-group">';
                $.each( data.activated, function( key, value ) {
                    var linkedClass = "alert-warning";
                    if(value.linked){
                        linkedClass = "alert-success";
                    }

                    activatedlist +='<div class="list-group-item list-group-item-secondary '+linkedClass+' ">'+
                                    '<h6 class ="ml-3 d-inline">'+value.subject + ' ' +value.course + '-' + value.section +'</h6>'+
                                    '</div>';
                    });
                    activatedlist += '</div><br>';
            }

            //adding in confirmation modal in case buttons accidentally clicked
            ModalFactory.create({
                type: ModalFactory.types.CANCEL,
                title: modaltitle,
                body: templatelist+activatedtitle+activatedlist,
            }).then(function(modal) {

                modal.footer.prepend('<a type="button" class="btn btn-primary justify-content-start mr-auto p-2" href="'+_homeurl+'/blocks/urcourserequest?semester='+_semester +'">My enrolment overview</a>');
                modal.show();
            });
        }else{
        
            //adding in confirmation modal in case buttons accidentally clicked
            ModalFactory.create({
                type: (data.courseinfo.length != 0) ? ModalFactory.types.SAVE_CANCEL :ModalFactory.types.CANCEL,
                title: modaltitle,
                body: ((isavailable) ? '<label class="col-form-label d-inline"><b> Available enrolments:  </b></label></br>'
                        + templatelist
                        +"</br>"
                        + activatedtitle 
                        + activatedlist  : ""),
            }).then(function(modal) {

                modal.footer.prepend('<a type="button" class="btn btn-primary justify-content-start mr-auto p-2" href="'+_homeurl+'/blocks/urcourserequest?semester='+_semester +'">My enrolment overview</a>');

                if(isavailable && data.courseinfo.length != 0){
                    modal.setSaveButtonText('Save');
                    var root = modal.getRoot();
                    root.on(ModalEvents.cancel, function(){
                        return;
                    });
                        
                    root.on(ModalEvents.save, function(e){

                        templateHolder = $('div[data-role="templateholder"');
                        selectedTemplates = $(templateHolder).find('.active');
                        //no selected template error
                        if(selectedTemplates.length <=0){
                            e.preventDefault(); 
                            $("#error_course_tools_section").text("Please select a section");
                            $("#error_course_tools_section").attr("display", "block");
                            $("#error_course_tools_section").show();
                        }else{
                            var templateids = [];
                            for(var i = 0; i<selectedTemplates.length; i++){
                                templateids.push( {"crn": $(selectedTemplates[i]).attr('id')});
                            }
                            var groupcheck = $("#course_tools_groups").prop("checked") 
                            _addEnrolmentDateConfirm(data, templateids, groupcheck); 
                        }   
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
                    if(data.courseinfo.length>0 ){
                        _registerSelectorEventListeners(_element);
                    }else{
                        $('button[data-action="cancel"]').text("Close");
                    }
                }
                if(data.activated.length !=0){
                    _registerDeleteButtons();
                }
            });
        }
    };

    /**
     * Handles ajax return and response to create modal with options
     * @param {Object} response 
     */
    var _addEnrolmentDateConfirm = async function(data, templateids,groupcheck) {


        var nextyear = (new Date()).getFullYear()+1;
        var newstart = (new Date()).getMonth()+' '+(new Date()).getDate()+', '+(new Date()).getFullYear();
        var newend = (new Date()).getMonth()+' '+(new Date()).getDate()+', '+nextyear;
        var formatednewstart ="";

        jQuery.each(_semesterdates, function(index, item) {
            if(index == _semester){
                var starttemp = item.startdate.split("-");
                var endtemp = item.enddate.split("-");

                newstart = parseInt(starttemp[1],10)+" "+starttemp[0]+", " +starttemp[2];
                newend = parseInt(endtemp[1],10)+" "+endtemp[0]+", " +endtemp[2];
                var d = new Date(starttemp[2],starttemp[1]-1,starttemp[0]);
                formatednewstart = d.toLocaleString('default', { month: 'short' })+" "+starttemp[0]+", " +starttemp[2];
            }
        });

        var startdate = _course.startdate.mon+' '+_course.startdate.mday+", "+_course.startdate.year;
        var enddate = _course.enddate.mon+' '+_course.enddate.mday+", "+_course.enddate.year;

        if( startdate == newstart && enddate == newend){
            _addEnrolment(templateids,groupcheck,true); 
        }else{

            // create modal with current selection as header
            var modaltitle = 'Would you like to change course dates?';
            var template =  await self.render(TEMPLATES.MODAL_COURSE_ACTION_CONTENT);
        
            var startdate = _course.startdate.month+' '+_course.startdate.mday+", "+_course.startdate.year;
            var enddate = _course.enddate.month+' '+_course.enddate.mday+", "+_course.enddate.year;
           
            //adding in confirmation modal in case buttons accidentally clicked
            ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                title: modaltitle,
                body: ("<p><b>Current Course Dates:<br/></b><div class='alert alert-warning' role='alert'>"
                        +((_course.enddate.year < _course.startdate.year) ? "Start Date: "+startdate+"<br> End Date: No endate set" :startdate+ " until "+enddate)
                        +"</div>"
                        +((formatednewstart=="") ? "":"The "+data.semester+" term will begin "+formatednewstart+"<br/><br/>")
                        +template ), 
                backdrop: 'static',
                keyboard: false,
                dismissible:false

            }).then(function(modal) {

                modal.setSaveButtonText('Update');

                //pointer-event: none;


                var root = modal.getRoot();
                root.on(ModalEvents.cancel, function(){
                    _addEnrolment(templateids,groupcheck,true); 
                });
                        
                root.on(ModalEvents.save, function(e){
                    if(!validate()){
                        e.preventDefault();   
                    }else{
                        _addEnrolment(templateids,groupcheck, false); 
                    }     
                });
                
                modal.show();

                modaltemp = modal.modal;
                $(modaltemp).find(".close").attr("id","closemodal");
                $("#closemodal").bind('click', function() { _closeDateConfirm($(this), modal) } );
                $(modaltemp).find(".close").addClass( "dateconfirm" );
                $(root).click(function(e) {
                    if(!$(e.target).is("button") && !$(e.target).parent().is("button")){
                        e.preventDefault;
                        modal.show();
                    }else{
                        $( SELECTORS.ENDHOLDER).remove();
                        $( SELECTORS.STARTHOLDER).remove();
                        $( SELECTORS.ERR_START).remove();
                        $( SELECTORS.ERR_END).remove();
                        modal.hide();
                    }
                });
            }).done(function() {

                var startdatefill = {"year": (new Date()).getFullYear(), "mon": (new Date()).getMonth(),"mday":(new Date()).getDate()};
                var enddatefill = {"year": (new Date()).getFullYear() +1, "mon": (new Date()).getMonth(),"mday":(new Date()).getDate()};

                jQuery.each(_semesterdates, function(index, item) {
                    if(index == _semester){
                        var starttemp = item.startdate.split("-");
                        var endtemp = item.enddate.split("-");

                        startdatefill = {"year": starttemp[2], "mon": starttemp[1],"mday":starttemp[0]};
                        enddatefill = {"year": endtemp[2], "mon": endtemp[1],"mday":endtemp[0]};
                    }
                });

                $('button[data-action="cancel"]').text("Continue without updating");
                //add numbers to date selectors
                courseActionsLib.populateDateSelects(startdatefill,enddatefill);  
                courseActionsLib.registerDateEventListeners(_element);

            });
        }
    };

    var _closeDateConfirm = function(e, modal){

        $( SELECTORS.ENDHOLDER).remove();
        $( SELECTORS.STARTHOLDER).remove();
        $( SELECTORS.ERR_START).remove();
        $( SELECTORS.ERR_END).remove();
        modal.hide();
    }
    /**
     * For Enrolemnt date selectors
     * Check if dates are valid 
     * Return bool 
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
    var _addEnrolment = function(templateids, groupcheck, isoriginal) {

        // return if required values aren't set
        if (!_course.id) {
            return;
        }

        if(isoriginal){
            var startdate = _course.startdate.mday+"-"+_course.startdate.mon+"-"+_course.startdate.year+"-"+_course.startdate.hours+"-"+_course.startdate.minutes;
            var enddate = _course.enddate.mday+"-"+_course.enddate.mon+"-"+_course.enddate.year+"-"+_course.enddate.hours+"-"+_course.enddate.minutes;
        }else{
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

            var title= "ERROR:";
            info = '<div class="alert alert-warning" role="alert">'+response.result+'<br></div>';
            if(response.result!=""){
                title = "Enrolment assigned successfully"
                info = '<div class="alert alert-success" role="alert">'+response.result+'<br></div>';
            }
                     
            ModalFactory.create({
                type: ModalFactory.types.CANCEL,
                title: title,
                body: info,
            }).then(function(modal) {
                modal.show();
                var root = modal.getRoot();
                $('button[data-action="cancel"]').text("Close");
                 //remove modal on hide
                 root.on(ModalEvents.hidden, function(){
                    location.reload();
                 });

                
            })
        }).fail(function(ex) {
            notification.exception;
        });  
    };
    
     /**
     * If delete button has been clicked for an
     * activated banner enrollment, call an ajax
     * request to remove the selected enrolment
     */
    var _deleteConfirmation = function(element) {

        var fullname = element.data( "fullname" );
    
        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: "Delete Enrolment",
            body: "<p><b>Are you sure you want to delete enrolment for "+fullname+"?</b><br />",
        }).then(function(modal) {

            modal.setSaveButtonText('Delete');
            var root = modal.getRoot();
            root.on(ModalEvents.cancel, function(){
                return;
            });        
            root.on(ModalEvents.save, function(e){
                _deleteEnrollment(element);
            });
            modal.show();
            
        });
    };

    /**
     * If delete button has been clicked for an
     * activated banner enrollment, call an ajax
     * request to remove the selected enrolment
     */
    var _deleteEnrollment = function(e) {
        
        var crn = e.attr('id').split("_");

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
            //close previous modal
            $( 'button[data-action="cancel"] ').trigger( "click" );

            var title= "ERROR:";
            info = '<div class="alert alert-warning" role="alert">'+response.result+'<br></div>';
            if(response.result!=""){
                title = "Successfully Removed Enrolment"
                info = '<div class="alert alert-success" role="alert">'+response.result+'<br></div>';
            }
            
            ModalFactory.create({
                type: ModalFactory.types.CANCEL,
                title: title,
                body: info,
            }).then(function(modal) {
                modal.show();
                var root = modal.getRoot();
                //remove modal on hide
                root.on(ModalEvents.hidden, function(){
                    location.reload();
                 });
                $('button[data-action="cancel"]').text("Close");
            })
        }).fail(function(ex) {
            notification.exception;
        });  
    };

    /**
     * Entry point to module. Sets globals and registers event listeners.
     * @param {String} root Jquery selector for container.
     * @return void
     */
    var init = function(root, course, semesterdates,categories, templatelist, homeurl) {
        _setGlobals(root, course, semesterdates,categories,templatelist, homeurl);
        _registerEventListeners();
    };

    return {
        init: init
    };
});