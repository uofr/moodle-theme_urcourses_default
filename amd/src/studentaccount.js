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
    'core/modal_factory', 'core/modal_events'],
    function($, ajax, notification, str, ModalFactory, ModalEvents) {

    /** Jquery selector strings. */
    var BTN_STUDENT_ACCOUNT= '#action-menu-0-menu a[data-title="studentaccount,theme_urcourses_default"]';

    /**
     * Sets up event listeners.
     * @return void
     */
    var _registerEventListeners = function(link) {
        $(link).on('click', _createStudentAction);
    };

     /**
     * Modal after button click to create student account
     */
    var _createStudentAction =  function() {

        //look if created or not
        var info = $(BTN_STUDENT_ACCOUNT).data("info");
        var infoarray = info.split(',');

        var value =infoarray[1];
        var username =infoarray[0];

        if(value){
            //view student account info
             getStudentAccountInfo(username);
        }else{

            var modaltitle = 'Create a test student for UR Courses';
            var modalaction = '<b>The following test user account will be created: </b><br><br>'
                            + '<b>Email address </b>'
                            +'<a class="btn btn-link p-0" role="button" data-toggle="modal" '
                            +'data-placement="right" data-target="moodle-email-modal-dialogue" data-html="true">'
                                +'<i class="icon fa fa-question-circle text-info fa-fw " '
                                +'title="Help with test student account email" '
                                +'aria-label="Help with test student account email"></i>'
                            +'</a><br>'+username+'+urstudent@uregina.ca'
                            +'<br><br><b>Username </b><br>'+username+'-urstudent'
                            +'<hr/>'
                            +'You can enrol this account to test and experience a course as a student.'
                            +'<br><br>'
                            +'<b>Would you like to create the test student account?</b>'
                            +'<br>';

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
                    $(root).find('button[data-action="save"]').append(
                        ' <span class="spinner-border spinner-border-sm" '+
                        'role="status" aria-hidden="true"></span><span class="sr-only">Loading...</span>');

                    _createStudentAccount(username);
                });

                modal.show();
            });
        }
    };

    /**
     * After modal info has been entered call ajax request
     */
    var getStudentAccountInfo =  function(username) {

        // set args
        var args = {
            username: username
        };

        // set ajax call
        var ajaxCall = {
            methodname: 'theme_urcourses_default_test_account_info',
            args: args,
            fail: notification.exception
        };

        // initiate ajax call
        var promise =  ajax.call([ajaxCall]);
        promise[0].done(function(data) {

            var modaltitle = 'User details for test student account';
            var info ="";
            if(data.userid != 0){
               info = '<b>Email address </b>'
                    +'<a class="btn btn-link p-0" role="button" data-toggle="modal" '
                    +'data-placement="right" data-target="moodle-email-modal-dialogue" data-html="true">'
                    +'<i class="icon fa fa-question-circle text-info fa-fw "'
                    +' title="Help with test student account email" aria-label="Help with test student account email"></i>'
                    +'</a><br>'+data.email
                    +'<br><b>Username </b><br>'+data.username
                    +'<br><b>Account Creation Date </b><br>'+data.datecreated+'<br/>'
                    +'<br><div class="alert alert-warning d-flex justify-content-between bd-highlight " role="alert">'
                        +'<div class="">Reset test account password </div>'
                        +'<button id="test_account_reset" type="button" class="btn btn-primary" >Reset Password</button>'
                    +'</div>';
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
            }).done(function() {
                $("#test_account_reset").bind('click', function() { _resetStudentAccount($(this), username); } );
            });
        });
    };

     /**
     * After modal info has been entered call ajax request
     */
    var _createStudentAccount = function(username) {

          // set args
          var args = {
            username: username
        };

        // set ajax call
        var ajaxCall = {
            methodname: 'theme_urcourses_default_create_test_account',
            args: args,
            fail: notification.exception
        };

        // initiate ajax call
        var promise = ajax.call([ajaxCall]);
        promise[0].done(function(response) {
            _studentAccountResponse(response);
        });
    };
    /**
     * Handles ajax return and response to return data
     * @param {Object} response
     */
    var _studentAccountResponse = function(data) {

        var title ="";
        var info ="";
        if(typeof data.unenroll != "undefined"){

            title = "Success: ";
            info='<div class="alert alert-success" role="alert">'
                    +data.username+" was removed from course. </div>";

            if(!data.unenroll){
                title = "ERROR:";
                info='<div class="alert alert-warning" role="alert">';
                info += data.username+" could not be removed from course. </div>";
            }
        }else if(typeof data.reset != "undefined"){
            title = "Success: ";
            info='<div class="alert alert-success" role="alert">'
                    +data.username+" password was reset. "+
                    "You will recieve an email with new credentials. "+
                    "It may take a few minutes to process.</div>";

            if(!data.reset && data.userid !=0){
                title = "ERROR:";
                info='<div class="alert alert-warning" role="alert">';
                info += data.username+"'s password could not be reset, email failed to send </div>";
            }else if(!data.reset && data.userid ==0){
                title = "ERROR:";
                info='<div class="alert alert-warning" role="alert">';
                info += data.username+"'s account could not be found </div>";
            }
        }else{
            //create new modal
            title = "Success: ";
            info='<div class="alert alert-success" role="alert">';

            //if both actions were done
            if(data.created != false && data.enrolled !=false){
                info +=data.username+" was created. "+
                "New account information has been emailed to you. </div>";
            }else if(data.created != false && data.enrolled ==false ){
                info += data.username+" was created."+
                "New account information has been emailed to you. </div>";
            }else if(data.created == false && data.enrolled !=false ){
                info += data.username+".</div>";
            }else{
                title = "ERROR:";
                info='<div class="alert alert-warning" role="alert">';
                info += data.username+" already exists.</div>";
            }
        }

        //adding in confirmation modal in case buttons accidentally clicked
        ModalFactory.create({
            type: ModalFactory.types.CANCEL,
            title: title,
            body:  info
        }).then(function(modal) {

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
    var _resetStudentAccount = function(e, username) {

        var modaltitle = 'Reset Password for test student account';
        var modalaction = 'reset the password for student account '+username+'+urstudent@uregina.ca.';

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
                $(root).find('button[data-action="save"]').append(
                    '<span class="spinner-border spinner-border-sm"'+
                    ' role="status" aria-hidden="true"></span><span class="sr-only">Loading...</span>');

                // set args
                var args = {
                    username: username
                };

                // set ajax call
                var ajaxCall = {
                    methodname: 'theme_urcourses_default_reset_test_account',
                    args: args,
                    fail: notification.exception
                };

                // initiate ajax call
                var promise = ajax.call([ajaxCall]);
                promise[0].done(function(response) {
                    $(root).find('button[data-action="cancel"]').click();
                    _studentAccountResponse(response);
                    return;
                });
            });
            modal.show();
        });
    };

    /**
     * Entry point to module. Sets globals and registers event listeners.
     * @param {String} root Jquery selector for container.
     * @return void
     */
    var init = function() {

        var salink = $(BTN_STUDENT_ACCOUNT);

        salink.click(function (event) {
            event.preventDefault();
          });

          _registerEventListeners(salink);
    };

    return {
        init: init
    };

});