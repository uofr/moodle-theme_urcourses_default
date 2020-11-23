import $ from 'jquery';
import ajax from 'core/ajax';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import notification from 'core/notification';
import Templates from 'core/templates';

const TEMPLATES = {
    MODAL_COURSE_ACTION_CONTENT: 'theme_urcourses_default/modal_course_action_content'
};

const SELECTORS = {
    COURSENAME: '#course_tools_coursename',
    SHORTNAME: '#course_tools_shortname',
    CATEGORY: '#course_tools_category',
    MODALHOLDER: '#course_tools_modal_content',
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
    ERR_COURSENAME: '#error_course_tools_coursename',
    ERR_SHORTNAME: '#error_course_tools_shortname',
    ERR_CATEGORY: '#error_course_tools_category',
    ERR_START: '#error_course_tools_start',
    ERR_END: '#error_course_tools_end',
};

export default class courseActionsLib {
    _courseid =0;
    _coursename="";
    _shortname="";
    _startdate="";
    _enddate="";
    _templatelist = "";
    _categories = "";
    self ;

    constructor(courseid,coursename, shortname, startdate, enddate, templatelist, categories){
        
        self = this;

        self._courseid = courseid;
        self._coursename = coursename;
        self._shortname = shortname;
        self._startdate = startdate;
        self._enddate = enddate;
        self._templatelist = templatelist;
        self._categories = categories;
    }
    /**
     * Used for new course and duplicate course creation on button clicks
     */
    async coursereqAction(_element,button,selectcatergory, selectstart, selectend) {
                                                                                                                                         
        var modaltitle = (_element.attr('id') == button) ? 'Create new course' : 'Duplicate this course';
        var modalaction = (_element.attr('id') == button) ? 'create a new' : 'duplicate this';

        var templatenew = (_element.attr('id') == button) ? true : "";
        var templateshort = (self._templatelist.length>6) ? "" : true;
        var istemplatelist = (self._templatelist.length>0) ? true : "";

        var template =  await self.render(TEMPLATES.MODAL_COURSE_ACTION_CONTENT, templatenew,templateshort,istemplatelist);

        //adding in confirmation modal in case buttons accidentally clicked
        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: modaltitle,
            body: "<p><b>Are you sure you want to "+ modalaction +" course?</b><br />"
                + ((_element.attr('id') == button) ? "<small>Select from the templates below. (Scroll for more options)</small>" : "<small>Student data will not be included in the duplicated course.</small>")
                + template
                + "</p>"
        }).then(function(modal) {

            modal.setSaveButtonText((_element.attr('id') == button) ? 'Create new course' : 'Duplicate this course');
            var root = modal.getRoot();
            root.on(ModalEvents.cancel, function(){
                return;
            });
                
            if((_element.attr('id') == button)){
                root.on(ModalEvents.save, function(e){
                    if(!self.validate()){
                        e.preventDefault();   
                    }else{
                        self.createCourse( );
                    }    
                });
            }else{
                root.on(ModalEvents.save, function(e){
                    if(!self.validate()){
                        e.preventDefault();   
                    }else{
                        self.duplicateCourse( );
                    }
                });
            }
            //remove modal on hide
            root.on(ModalEvents.hidden, function(){
                //remove inputs otherwise duplicates are made causing id problems
                $(SELECTORS.MODALHOLDER).remove();
            });

            modal.show();
        }).done(function() {
            if((_element.attr('id') == button)){
                self.registerSelectorEventListeners(_element, button);
            }else{
                 $(SELECTORS.COURSENAME).val(self._coursename+" (Copy)");
                 $(SELECTORS.SHORTNAME).val(self._shortname+" (Copy)");
            }

            self.populateDateSelects(selectstart,selectend);

            //change category to that of course  
            $(SELECTORS.CATEGORY).val(selectcatergory);
                
            self.registerDateEventListeners(_element);
        });
    }

    /**
     * Switch choosen template based on click in template list
     */
    populateDateSelects(selectstart, selectend) {

          //populate start and end dates
          var currentyear = (new Date()).getFullYear();
          for (var year = 1900; year < currentyear+30; year++) {
              $(SELECTORS.STARTYEAR).append('<option  value="'+year+'" '+ ((year == selectstart.year) ? "selected": " " ) +'>' + year + '</option>');
              $(SELECTORS.ENDYEAR).append('<option value="'+year+'"'+(((year == selectend.year && selectend.year >= selectstart.year) || (year == currentyear && selectend.year <selectstart.year) ) ? "selected": " " )+'>' + year + '</option>');
          }

          var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
          var currentmonth = (new Date()).getMonth();
          for (var month = 0; month < monthNames.length; month++) {
              var mtemp = month;
              mtemp++;
              $(SELECTORS.STARTMONTH).append('<option value="'+mtemp+'" '+((mtemp == selectstart.mon) ? "selected": " " )+'>' + monthNames[month] + '</option>');
              $(SELECTORS.ENDMONTH).append('<option value="'+mtemp+'" '+(((mtemp == selectend.mon && selectend.year >= selectstart.year) || (month == currentmonth && selectend.year <selectstart.year) ) ? "selected": " " )+'>' + monthNames[month] + '</option>');
          }

          var currentday = (new Date()).getDate();
          for (var day = 1; day < 32; day++) {
              $(SELECTORS.STARTDAY).append('<option value="'+day+'" '+((day == selectstart.mday) ? "selected": " " )+'>' + day + '</option>');
              $(SELECTORS.ENDDAY).append('<option value="'+day+'" '+(((day == selectend.mday && selectend.year >= selectstart.year) || (day == currentday && selectend.year <selectstart.year) ) ? "selected": " " )+'>' + day + '</option>');
          }

          for (var hour = 0; hour < 24; hour++) { 
              $(SELECTORS.STARTHOUR).append('<option value="'+hour+'">' + ((hour < 10) ? "0"+hour: hour ) + '</option>');
              $(SELECTORS.ENDHOUR).append('<option value="'+hour+'">' + ((hour < 10) ? "0"+hour: hour ) + '</option>');
          }
          
          for (var minute = 0; minute < 61; minute++) {
              $(SELECTORS.STARTMINUTE).append('<option value="'+minute+'"    >' + ((minute < 10) ? "0"+minute: minute ) + '</option>');
              $(SELECTORS.ENDMINUTE).append('<option  value="'+minute+'"  >' + ((minute < 10) ? "0"+minute: minute ) + '</option>');
          }
    }  
    
    /**
     * Switch choosen template based on click in template list
     */
    setActiveTemplate(e) {

        var templateHolder = $('div[data-role="templateholder"');
        $(templateHolder).find('.active').removeClass("active");
        e.addClass("active");
    }
    /**
     * Switch choosen template based on click in template list
     */
    setEnddate(e) {

        var checked;
        (e.is(":checked")) ? checked=false: checked=true;
        $(SELECTORS.ENDDAY).prop("disabled", checked);
        $(SELECTORS.ENDMONTH).prop("disabled", checked);
        $(SELECTORS.ENDYEAR).prop("disabled", checked);
        $(SELECTORS.ENDHOUR).prop("disabled", checked);
        $(SELECTORS.ENDMINUTE).prop("disabled", checked);
    } 
    
    /**
     * Switch choosen template based on click in template list
     */
    validateDaysInMonth () { 

        var startyear = $(SELECTORS.STARTYEAR).val();
        var endyear = $(SELECTORS.ENDYEAR).val();
        var startmonth = $(SELECTORS.STARTMONTH).val();
        var endmonth = $(SELECTORS.ENDMONTH).val();
        var startday = $(SELECTORS.STARTDAY).val();
        var endday = $(SELECTORS.ENDDAY).val();

        var actualStart = new Date(startyear, startmonth, 0).getDate();
        var actualEnd = new Date(endyear, endmonth, 0).getDate();

        if(actualStart < startday){
            $(SELECTORS.STARTDAY).val(actualStart);
        }
        if(actualEnd < endday){
            $(SELECTORS.ENDDAY).val(actualEnd);
        }
    }

    /**
     * Update the modal body using given template and data.
     *
     * @method render
     * @param {String} template - The name of the template to render.
     * @param {Object} data - Data for template.
     * @param {Object} breadcrumbData - Data required for rending breadcrumbs.
     */
     async render(template,templatenew,templateshort, istemplatelist) {

        var templatelist = [];
        $.each(self._templatelist, function(key,val) {
            templatelist.push({"id":val.id,"fullname":val.fullname,"summary":val.summary, "courseimage": val.courseimage});
        });


        var categories = [];
        $.each(self._categories, function(key,val) {
            categories.push({"id":val.id,"name":val.name});
        });
        const renderData = {
            templatenew: templatenew,
            templateshort: templateshort,
            templatelist:templatelist,
            istemplatelist:istemplatelist,
            categories: categories
        };
        const renderPromise = await Templates.render(template, renderData);
        return renderPromise;
    }

    /**
     * Sets up event listeners.
     * @return void
     */
     registerSelectorEventListeners (_element, button) {

        if(_element.attr('id') == button){
            //set event listners for template options
            $('.tmpl-label').bind('click', function() { self.setActiveTemplate($(this)); } );
            $('button[data-role="info_button"').bind('click', function() { self.showMoreInfo($(this)); } );
        }

    }
    /**
     * Sets up event listeners.
     * @return void
     */
     registerDateEventListeners (_element) {
        //set event listners for template options
        $(SELECTORS.ENDENABLE).bind('click', function() { self.setEnddate($(this)); } );
        $(SELECTORS.ENDMONTH).bind('blur', function() { self.validateDaysInMonth ($(this)); } );
        $(SELECTORS.ENDDAY).bind('blur', function() { self.validateDaysInMonth ($(this)); } );
        $(SELECTORS.STARTMONTH).bind('blur', function() { self.validateDaysInMonth ($(this)); } );
        $(SELECTORS.STARTDAY).bind('blur', function() { self.validateDaysInMonth ($(this)); } );
    }

    /**
     * For Duplicate and Create course.
     * Validate if course name and shortname have been entered
     */
     validate() {

        var coursename = $(SELECTORS.COURSENAME).val();
        var shortname = $(SELECTORS.SHORTNAME).val();
        var startday = $(SELECTORS.STARTDAY).val();
        var startmonth = $(SELECTORS.STARTMONTH).val();
        var startyear = $(SELECTORS.STARTYEAR).val();
        var endyear = $(SELECTORS.ENDYEAR).val();
        var endday = $(SELECTORS.ENDDAY).val();
        var endmonth = $(SELECTORS.ENDMONTH).val();
   
        var test =true; 
        if(coursename.length ==0){
            $(SELECTORS.ERR_COURSENAME).text("Please enter a name for the course");
            $(SELECTORS.ERR_COURSENAME).attr("display", "block");
            $(SELECTORS.ERR_COURSENAME).show();
            test = false;
        }else{
            $(SELECTORS.ERR_COURSENAME).text("");
        }

        if(shortname.length ==0){
            $(SELECTORS.ERR_SHORTNAME).text("Please enter a short name for the course");
            $(SELECTORS.ERR_SHORTNAME).attr("display", "block");
            $(SELECTORS.ERR_SHORTNAME).show();
            test = false;
        }else{
            $(SELECTORS.ERR_SHORTNAME).text("");
        }

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
    showMoreInfo(e) {

        console.log("MAde it");
        var course ="";
        $.each(self._templatelist, function(key,val) {

            console.log(e.attr('id'));
            var id = e.attr('id').split("_");
            
            console.log(id);
            console.log(id[1]);
            if(id[1] == val.id){
                console.log("made it 2");
               course = {"id":val.id,"fullname":val.fullname,"summary":val.summary, "courseimage": val.courseimage};
            }
        });

        console.log(course);
        //adding in confirmation modal in case buttons accidentally clicked
        ModalFactory.create({
            type: ModalFactory.types.DEFAULT,
            title: course.fullname,
            body: course.summary
        }).then(function(modal) {
            modal.show();
        });
    }

    /**
     * After modal info has been entered call ajax request
     */
    createCourse() {
        // return if required values aren't set
        if (!self._courseid) {
            return;
        }
        
        var templateHolder = $('div[data-role="templateholder"');
        var selectedTemplate = $(templateHolder).find('.active')
        var templateid = $(selectedTemplate).attr('id');
        
        var coursename = $(SELECTORS.COURSENAME).val();
        var shortname = $(SELECTORS.SHORTNAME).val();
        var category = $(SELECTORS.CATEGORY).val();

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
        
        if($('#mainspinner').length){
            $('#mainspinner') .show();
            $('#infoholder').addClass("block_urcourserequest_overlay");
        }

        //duplicate course option selected
        // set args
        var args = {
            courseid: self._courseid,
            templateid: templateid,    
            coursename: coursename,
            shortname: shortname,
            categoryid: category,
            startdate: startdate,
            enddate: enddate,
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
            if($('#mainspinner').length){
                $('#mainspinner') .hide();
                $('#infoholder').removeClass("block_urcourserequest_overlay");
            }

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
        }).fail(function(ex) {
            notification.exception;
        });  
    }

     /**
     * After modal info has been entered call ajax request
     */
      duplicateCourse() {
        
        // return if required values aren't set
        if (!self._courseid) {
            return;
        }
        var coursename = $(SELECTORS.COURSENAME).val();
        var shortname = $(SELECTORS.SHORTNAME).val();
        var category = $(SELECTORS.CATEGORY).val();

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

        if($('#mainspinner').length){
            $('#mainspinner') .show();
            $('#infoholder').addClass("block_urcourserequest_overlay");
        }
         
        //duplicate course option selected
        // set args
        var args = {
            courseid: self._courseid,
            coursename: coursename,
            shortname: shortname,
            categoryid: category,
            startdate: startdate,
            enddate: enddate,
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
            if($('#mainspinner').length){
                $('#mainspinner') .hide();
                $('#infoholder').removeClass("block_urcourserequest_overlay");
            }
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
    }
}

