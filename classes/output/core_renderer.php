<?php
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
 * Theme Boost Union - Core renderer
 *
 * @package    theme_boost_union
 * @copyright  2022 Moodle an Hochschulen e.V. <kontakt@moodle-an-hochschulen.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_urcourses_default\output;
/*
use stdClass;
use context_course;
use html_writer;
use moodle_url;
*/

use coding_exception;
use core\plugininfo\enrol;
use html_writer;
use tabobject;
use tabtree;
use context_system;
use custom_menu_item;
use custom_menu;
use block_contents;
use navigation_node;
use action_link;
use stdClass;
use moodle_url;
use preferences_groups;
use action_menu;
use help_icon;
use single_button;
use single_select;
use paging_bar;
use url_select;
use context_course;
use pix_icon;
use user_picture;
use action_menu_filler;
use action_menu_link_secondary;
use core_text;



use \core_course\external\course_summary_exporter;



/**
 * Extending the core_renderer interface.
 *
 * @package    theme_boost_union
 * @copyright  2022 Moodle an Hochschulen e.V. <kontakt@moodle-an-hochschulen.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_boost_union\output\core_renderer {
	
    public function favicon() {
		global $CFG;
        $favicon = $CFG->wwwroot . '/theme/urcourses_default/pix/favicon.ico';
		return $favicon;
    }
    /**
     * Get the compact logo URL.
     *
     * @return string
     */
    public function get_compact_logo_url($maxwidth = 100, $maxheight = 100) {
        global $OUTPUT;
        return $OUTPUT->image_url('logo', 'theme_urcourses_default');
    }
    public function get_compact_logosmall_url($maxwidth = 100, $maxheight = 100) {
        global $OUTPUT;
        return $OUTPUT->image_url('logo', 'theme_urcourses_default');
    }
	
    /**
     * Returns the services and support link for the help pop-up.
     *
     * @return string
     */
    public function services_support_link(): string {
        global $CFG;

        if (during_initial_install() ||
            (isset($CFG->showservicesandsupportcontent) && $CFG->showservicesandsupportcontent == false) ||
            !is_siteadmin()) {
            return '';
        }

        $liferingicon = $this->pix_icon('t/life-ring', '', 'moodle', ['class' => 'fa fa-life-ring']);
        $newwindowicon = $this->pix_icon('i/externallink', get_string('opensinnewwindow'), 'moodle', ['class' => 'ml-1']);
        $link = 'https://urcourses.uregina.ca/guides';
        $content = $liferingicon . get_string('moodleservicesandsupport') . $newwindowicon;

        return html_writer::tag('a', $content, ['target' => '_blank', 'href' => $link]);
    }
	
    /**
     * Renders the "breadcrumb" for all pages in boost.
     *
     * @return string the HTML for the navbar.
     */
    public function navbar(): string {
        $newnav = new \theme_urcourses_default\boostnavbar($this->page);
        return $this->render_from_template('core/navbar', $newnav);
    }
	
    /**
     * Renders the header bar.
     *
     * @param context_header $contextheader Header bar object.
     * @return string HTML for the header bar.
     */
   protected function render_context_header(\context_header $contextheader) {

       // Generate the heading first and before everything else as we might have to do an early return.
       if (!isset($contextheader->heading)) {
           $heading = $this->heading($this->page->heading, $contextheader->headinglevel, 'h2');
       } else {
           $heading = $this->heading($contextheader->heading, $contextheader->headinglevel, 'h2');
       }

       // All the html stuff goes here.
       $html = html_writer::start_div('page-context-header');

       // Image data.
       if (isset($contextheader->imagedata)) {
           // Header specific image.
           $html .= html_writer::div($contextheader->imagedata, 'page-header-image mr-2');
       }

       // Headings.
       if (isset($contextheader->prefix)) {
           $prefix = html_writer::div($contextheader->prefix, 'text-muted text-uppercase small line-height-3');
           //$heading = $prefix . $heading;
		   // Change order of activity type
           $heading = $heading . $prefix;
       }
       $html .= html_writer::tag('div', $heading, array('class' => 'page-header-headings'));

       // Buttons.
       if (isset($contextheader->additionalbuttons)) {
           $html .= html_writer::start_div('btn-group header-button-group');
           foreach ($contextheader->additionalbuttons as $button) {
               if (!isset($button->page)) {
                   // Include js for messaging.
                   if ($button['buttontype'] === 'togglecontact') {
                       \core_message\helper::togglecontact_requirejs();
                   }
                   if ($button['buttontype'] === 'message') {
                       \core_message\helper::messageuser_requirejs();
                   }
                   $image = $this->pix_icon($button['formattedimage'], $button['title'], 'moodle', array(
                       'class' => 'iconsmall',
                       'role' => 'presentation'
                   ));
                   $image .= html_writer::span($button['title'], 'header-button-title');
               } else {
                   $image = html_writer::empty_tag('img', array(
                       'src' => $button['formattedimage'],
                       'role' => 'presentation'
                   ));
               }
               $html .= html_writer::link($button['url'], html_writer::tag('span', $image), $button['linkattributes']);
           }
           $html .= html_writer::end_div();
       }
       $html .= html_writer::end_div();

       return $html;
   }
   
   public function full_header() {
       // MODIFICATION START.
       global $USER, $COURSE, $CFG, $DB, $OUTPUT, $PAGE;
       $header = new stdClass();
       
       $sitecontextheader = '<div class="page-context-header"><div class="page-header-headings"><h1>'.$COURSE->fullname.'</h1></div></div>';
       
       $headertext = (!empty($this->context_header())) ? $this->context_header() : $sitecontextheader;

       
       //Little hack to add back missing header for dashboard
       //The context header the comes through is not formated properly
       if($this->page->pagelayout=="mydashboard"&&strpos($PAGE->url,'my/indexsys.php')===false){
           $headertext = $sitecontextheader;
       }
       /*
        if (!empty($this->page->activityname)) {
        $header->contextheader = '<a href="'.$CFG->wwwroot.'/mod/'.$this->page->activityname.'/view.php?id='.$this->page->context->instanceid.'">'.$headertext.'</a>';
        } else {
        $header->contextheader = '<h2><a href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'">'.$COURSE->fullname.'</a></h2>';
        }
        */
	//error_log('pg url:'.$PAGE->url);
	
	$activityheader = false;
	
	//error_log('$PAGE->url:'.$PAGE->url);
	//error_log('component:'.$PAGE->url->get_param('component'));
	
	if (strpos($PAGE->url,'grade/')==true&&$PAGE->url->get_param('component')!==null) {
		// advanced grading page for forum, etc
		$activityheader = true;
	} else if (strpos($PAGE->url,'grade/')==true&&$PAGE->url->get_param('areaid')!==null) {
		// advanced grading page for forum, etc
		$activityheader = true;
	} else if (strpos($PAGE->url,'mod/')==true&&strpos($PAGE->url,'report/')!==false) {
		// forum report, for example
		$activityheader = true;
	} else if (strpos($PAGE->url,'admin/')==true&&strpos($PAGE->url,'permissions.php')==true&&$PAGE->url->get_param('contextid')!==null) {
		// permissions within activity
		$activityheader = true;
	} else if (strpos($PAGE->url,'grade/')===false&&strpos($PAGE->url,'backup/')===false&&strpos($PAGE->url,'reset.php')===false&&strpos($PAGE->url,'coursecompetencies.php')===false&&strpos($PAGE->url,'unenrolself.php')===false&&strpos($PAGE->url,'newbadge.php')===false&&strpos($PAGE->url,'report/')===false&&$PAGE->url->get_param('bui_editid')===null&&strpos($PAGE->url,'my/courses.php')===false&&strpos($PAGE->url,'admin/')===false&&strpos($PAGE->url,'my/indexsys.php')===false) {
		$activityheader = true;
	}
	
	
	
	if ($activityheader === true) {
        $header->contextheader = '<a href="'.$CFG->wwwroot.'/mod/'.$this->page->activityname.'/view.php?id='.$this->page->context->instanceid.'" class="activity-header-link">'.$headertext.'</a>';
       	
	} else {
		
		$headertext = $COURSE->fullname;
        $header->contextheader = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'" class="course-header-link">'.$headertext.'</a>';
       
	}
	
       
       $header->mycourseheader = '<h2><a href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'" title="'.$PAGE->url.'" class="mycourse-header-link">'.$COURSE->fullname.'</a></h2>';
       
       $header->iscoursepage = false;
       
	//$isgrader = ($PAGE->get_url()) ? : false;
	
       if (strip_tags($headertext) == $COURSE->fullname) {
           $header->contextheader = '<!-- hello -->';//'<h2><a href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'">'.$COURSE->fullname.'</a></h2>';
           $header->iscoursepage = true;
       }
       
       //error_log('Header actions:'.print_r($PAGE->get_header_actions(),1));
       
       $header->headeractions = $PAGE->get_header_actions();
       
       /*
        if (strip_tags($this->context_header()) != $COURSE->fullname) {
        $header->mycourseheader = '<h2><a href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'">'.$COURSE->fullname.'</a></h2>';
        } else {
        $header->mycourseheader = $header->contextheader;
        $header->contextheader = '<!-- hello -->';
        }
        */
       $header->instructors = $this->course_authornames();
       
       $instnum = substr_count($this->course_authornames(), 'href');
       if ($instnum > 2) {
           $header->instructnum = "largelist";
       }
       else  $header->instructnum = "smalllist";
       
       $header->navbar = $this->navbar();
       
       
       //$preheader = $header->courseimage = theme_boost_union_get_course_image($COURSE);
       $preheader = $header->courseimage = theme_urcourses_default_get_course_image();
       //$preheader = $header->courseimage = theme_urcourses_default_get_course_image_old($COURSE);
		   
	$header->courselink = $CFG->wwwroot.'/course/view.php?id='.$COURSE->id;
	
       if (!$header->courseimage) {
           $header->courseimage = $OUTPUT->get_generated_image_for_id($COURSE->id);
       }
       if ($COURSE->id == 1) {
       	$header->courseimage = $CFG->wwwroot.'/theme/urcourses_default/pix/siteheader.jpg';
       }	
       
       
       $header->coursenavicon = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'"><img class="instr-avatar img-rounded" style="border-radius: 0.25em" src="'.$header->courseimage.'" height="18" width="18" title="'.$COURSE->fullname.'" alt="'.$COURSE->fullname.'" /></a>';
       
	//error_log('navbar'.$header->navbar);
       
       $html = $this->render_from_template('theme_urcourses_default/full_header', $header);
       
       return $html;
       //.'<pre><code>'.$preheader.' // '.$header->courseimage.'</code></pre>';
       //'<pre><code>'.print_r($this->page,1).'</code></pre>';
       //.'<pre><code>'.print_r($this->context_header(),1).'</code></pre>';
       //.'<pre><code>'.print_r($header,1).'</code></pre>';
       
   }
   
   public function course_authornames() {
       
       global $CFG, $USER, $DB, $OUTPUT, $COURSE;
       
       // expecting $course
       
       //$context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
       $context = context_course::instance($COURSE->id);
       
       
       /// first find all roles that are supposed to be displayed
       if (!empty($CFG->coursecontact)) {
           $managerroles = explode(',', $CFG->coursecontact);
           $namesarray = array();
           $rusers = array();
           
           if (!isset($COURSE->managers)) {
               $rusers = get_role_users($managerroles, $context, true,
                                        'ra.id AS raid, u.id, u.username, u.firstname, u.lastname,
                                        u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename,
                                        r.name AS rolename, r.sortorder, r.id AS roleid',
                                        'r.sortorder ASC, u.lastname ASC');
           } else {
               //  use the managers array if we have it for perf reasosn
               //  populate the datastructure like output of get_role_users();
               foreach ($COURSE->managers as $manager) {
                   $u = new stdClass();
                   $u = $manager->user;
                   $u->roleid = $manager->roleid;
                   $u->rolename = $manager->rolename;
                   
                   $rusers[] = $u;
               }
           }
           
           /// Rename some of the role names if needed
           if (isset($context)) {
               $aliasnames = $DB->get_records('role_names', array('contextid'=>$context->id), '', 'roleid,contextid,name');
           }
           
           $namesarray = array();
           $canviewfullnames = has_capability('moodle/site:viewfullnames', $context);
           foreach ($rusers as $ra) {
               if (isset($namesarray[$ra->id])) {
                   //  only display a user once with the higest sortorder role
                   continue;
               }
               
               if (isset($aliasnames[$ra->roleid])) {
                   $ra->rolename = $aliasnames[$ra->roleid]->name;
               }
               
               $fullname = fullname($ra, $canviewfullnames);
               $usr_img = '<img class="instr-avatar img-rounded" style="border-radius: 0.25em" src="'.$CFG->wwwroot.'/user/pix.php/'.$ra->id.'/f2.jpg" height="24" width="24" title="Profile picture of '.$fullname.'" alt="Profile picture of '.$fullname.'" />';
               $namesarray[$ra->id] = html_writer::link(new moodle_url('/user/view.php', array('id'=>$ra->id, 'course'=>$COURSE->id)), $usr_img.' <span>'.$fullname.'</span>');
           }
           
           if (!empty($namesarray)) {
               $course_authornames = html_writer::start_tag('div', array('class'=>'teacherlist'));
               $course_authornames .= implode('', $namesarray);
               $course_authornames .= html_writer::end_tag('div');
               
               return $course_authornames;
           } else return '';
       }
   }

  
/**
 * Construct a user menu, returning HTML that can be echoed out by a
 * layout file.
 *
 * @param stdClass $user A user object, usually $USER.
 * @param bool $withlinks true if a dropdown should be built.
 * @return string HTML fragment.
 */
public function user_menu($user = null, $withlinks = null) {
    global $USER, $CFG, $DB;
    require_once($CFG->dirroot . '/user/lib.php');
	//error_log('ahoy, the user menu');
    if (is_null($user)) {
        $user = $USER;
    }

    // Note: this behaviour is intended to match that of core_renderer::login_info,
    // but should not be considered to be good practice; layout options are
    // intended to be theme-specific. Please don't copy this snippet anywhere else.
    if (is_null($withlinks)) {
        $withlinks = empty($this->page->layout_options['nologinlinks']);
    }

    // Add a class for when $withlinks is false.
    $usermenuclasses = 'usermenu';
    if (!$withlinks) {
        $usermenuclasses .= ' withoutlinks';
    }

    $returnstr = "";

    // If during initial install, return the empty return string.
    if (during_initial_install()) {
        return $returnstr;
    }

    $loginpage = $this->is_login_page();
    $loginurl = get_login_url();
    // If not logged in, show the typical not-logged-in string.
    if (!isloggedin()) {
        $returnstr = get_string('loggedinnot', 'moodle');
        if (!$loginpage) {
            $returnstr .= " (<a href=\"$loginurl\">" . get_string('login') . '</a>)';
        }
        return html_writer::div(
            html_writer::span(
                $returnstr,
                'login'
            ),
            $usermenuclasses
        );

    }

    // If logged in as a guest user, show a string to that effect.
    if (isguestuser()) {
        $returnstr = get_string('loggedinasguest');
        if (!$loginpage && $withlinks) {
            $returnstr .= " (<a href=\"$loginurl\">".get_string('login').'</a>)';
        }

        return html_writer::div(
            html_writer::span(
                $returnstr,
                'login'
            ),
            $usermenuclasses
        );
    }

    // Get some navigation opts.
    $opts = user_get_user_navigation_info($user, $this->page);
	//error_log('opts:'.print_r($opts,1));
    if ($usedarkmode = $DB->get_record('theme_urcourses_darkmode', array('userid'=>$USER->id, 'darkmode'=>1))) {
        //changes url to opposite of whatever the toggle currently is to set dark mode in db under columns2.php
        $darkchk = $usedarkmode->darkmode;
    } else {
        $darkchk = 0;
    }
    $usedarkmodeurl = ($darkchk == 1) ? 0 : 1;
    //dark mode variable for if on/off to swap icon
    $mynodelabel = ($darkchk == 1) ? "i/item" : "i/marker";
    $darkstate = ($darkchk == 1) ? "on" : "off";

    //creating dark mode object 
    $mynode = new stdClass();
    $mynode->itemtype = "link";
    $mynode->url = new moodle_url($this->page->url,array("darkmode"=>$usedarkmodeurl));
    $mynode->title = "Darkmode is " . $darkstate;
    $mynode->titleidentifier = "darkmode, theme_urcourses_default";
	$mynode->pix = $mynodelabel;
	

	//For Test student user account
	//check if not student user
	$troleid = $DB->get_field('role', 'id', ['shortname' => 'editingteacher']);
	$mroleid = $DB->get_field('role', 'id', ['shortname' => 'manager']);
	$iroleid = $DB->get_field('role', 'id', ['shortname' => 'instdesigner']);
	$isteacher = $DB->record_exists('role_assignments', ['userid' => $USER->id, 'roleid' => $troleid]);
	$ismanager = $DB->record_exists('role_assignments', ['userid' => $USER->id, 'roleid' => $mroleid]);
	$isdesigner = $DB->record_exists('role_assignments', ['userid' => $USER->id, 'roleid' => $iroleid]);


	if($isteacher|| $ismanager || $isdesigner || is_siteadmin() ){
		$saccountnode = new stdClass();
		$saccountnode->itemtype = "link";
		$saccountnode->url = new moodle_url($this->page->url);
		$saccountnode->pix =     "i/user";
		$saccountnode->titleidentifier = "studentaccount,theme_urcourses_default";
		$saccountnode->useridentifier = $USER->username;
		
		$isaccount= theme_urcourses_default_check_test_account($USER->username);
		$saccountnode->account = $isaccount;
		
		if($isaccount){
			$saccountnode->title = "Modify test student";
		}else{
			$saccountnode->title = "Create test student";
		}	
	}


    //$lnode = $opts->navitems[count($opts->navitems)]; //get logout node
    
	$allnodes = $opts->navitems; //get logout node
	
	//if (has_capability('moodle/role:switchroles', $PAGE->context)) {
	    // Do or display something.
		//} else {
		
	//}
	
	//error_log('navitems:'.print_r($opts->navitems,1));
	
	
	//$lastnode = array_pop($opts->navitems);
	
	//$opts->navitems[] = $mynode; //dark node placed in 5
	//$opts->navitems[] = $lastnode;
	//error_log('COUNT: '.count($allnodes));
	
	$menukey = count($opts->navitems);
	for ($i=0; $i < count($allnodes); $i++) {
		if (isset($opts->navitems[$i]->title) && $opts->navitems[$i]->title == 'Preferences') {
			$menukey = $i+1;
			$i = count($allnodes);
		}
	}
	$opts->navitems[$menukey] = $mynode;
	$opts->navitems[$menukey+1] = $saccountnode;
	for ($i=$menukey+2; $i<count($allnodes)+2; $i++) {
		$opts->navitems[$i] = $allnodes[$i-2];
	}
	
    //$opts->navitems[] = $lnode; //placing log out back in at the end
	
	
    $avatarclasses = "avatars";
    $avatarcontents = html_writer::span($opts->metadata['useravatar'], 'avatar current');
    $usertextcontents = $opts->metadata['userfullname'];

    // Other user.
    if (!empty($opts->metadata['asotheruser'])) {
        $avatarcontents .= html_writer::span(
            $opts->metadata['realuseravatar'],
            'avatar realuser'
        );
        $usertextcontents = $opts->metadata['realuserfullname'];
        $usertextcontents .= html_writer::tag(
            'span',
            get_string(
                'loggedinas',
                'moodle',
                html_writer::span(
                    $opts->metadata['userfullname'],
                    'value'
                )
            ),
            array('class' => 'meta viewingas')
        );
    }

    // Role.
    if (!empty($opts->metadata['asotherrole'])) {
        $role = core_text::strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['rolename'])));
        $usertextcontents .= html_writer::span(
            $opts->metadata['rolename'],
            'meta role role-' . $role
        );
    }

    // User login failures.
    if (!empty($opts->metadata['userloginfail'])) {
        $usertextcontents .= html_writer::span(
            $opts->metadata['userloginfail'],
            'meta loginfailures'
        );
    }

    // MNet.
    if (!empty($opts->metadata['asmnetuser'])) {
        $mnet = strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['mnetidprovidername'])));
        $usertextcontents .= html_writer::span(
            $opts->metadata['mnetidprovidername'],
            'meta mnet mnet-' . $mnet
        );
    }
	/*
    $returnstr .= html_writer::span(
        html_writer::span($usertextcontents, 'usertext mr-1') .
        html_writer::span($avatarcontents, $avatarclasses),
        'userbutton'
    );*/
	// just display the avatar
    $returnstr .= html_writer::span(
        html_writer::span($avatarcontents, $avatarclasses),
        'userbutton'
    );

    // Create a divider (well, a filler).
    $divider = new action_menu_filler();
    $divider->primary = false;

    $am = new action_menu();
    $am->set_menu_trigger(
        $returnstr
    );
    $am->set_action_label(get_string('usermenu'));
	//$am->set_menu_left();
    //$am->set_alignment(action_menu::TR, action_menu::BR);
    $am->set_nowrap_on_items();
    if ($withlinks) {
        $navitemcount = count($opts->navitems);
        $idx = 0;
        foreach ($opts->navitems as $key => $value) {

            switch ($value->itemtype) {
                case 'divider':
                    // If the nav item is a divider, add one and skip link processing.
                    $am->add($divider);
                    break;

                case 'invalid':
                    // Silently skip invalid entries (should we post a notification?).
                    break;

                case 'link':
                    // Process this as a link item.
                    $pix = null;
                    if (isset($value->pix) && !empty($value->pix)) {
                        $pix = new pix_icon($value->pix, '', null, array('class' => 'iconsmall'));
                    } else if (isset($value->imgsrc) && !empty($value->imgsrc)) {
                        $value->title = html_writer::img(
                            $value->imgsrc,
                            $value->title,
                            array('class' => 'iconsmall')
                        ) . $value->title;
                    }

                    $al = new action_menu_link_secondary(
                        $value->url,
                        $pix,
                        $value->title,
                        array('class' => 'icon')
                    );
                    if (!empty($value->titleidentifier)) {
                        $al->attributes['data-title'] = $value->titleidentifier;
					}
					//UR HACK
					if (!empty($value->useridentifier)) {
                        $al->attributes['data-info'] = $value->useridentifier.",".$value->account;
					}
					
                    $am->add($al);
                    break;
            }

            $idx++;

            // Add dividers after the first item and before the last item.
            if ($idx == $navitemcount - 1) {
                $am->add($divider);
            }
        }
    }
	
	
	$am->attributes['id'] = 'user-menu-toggle';
	//error_log('action_menu'.print_r($am,1));
	
    return html_writer::div(
        $this->render($am),
        $usermenuclasses
    );
}

/**
 * Renders the login form.
 *
 * @param \core_auth\output\login $form The renderable.
 * @return string
 */
public function render_login(\core_auth\output\login $form) {
    global $CFG, $SITE;

    $context = $form->export_for_template($this);

    $context->errorformatted = $this->error_text($context->error);
    $url = $this->get_logo_url();
    if ($url) {
        $url = $url->out(false);
    }
    $context->logourl = $url;
    $context->sitename = format_string($SITE->fullname, true,
            ['context' => context_course::instance(SITEID), "escape" => false]);

    return $this->render_from_template('theme_urcourses_default/loginform', $context);
}

   
	
}

