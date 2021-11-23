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
 * Webservices for Boost Campus.
 *
 * @package    theme_uofr_conservatory
 * @author     John Lane
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/course/lib.php");
require_once($CFG->dirroot.'/theme/uofr_conservatory/locallib.php');

class theme_uofr_conservatory_external extends external_api {

    /**
     * Describes upload_course_image parameters.
     * @return external_function_parameters
     */
    public static function upload_course_image_parameters() {
        return new external_function_parameters(
            array(
            'courseid' => new external_value(PARAM_INT),
            'imagedata' => new external_value(PARAM_TEXT),
            'imagename' => new external_value(PARAM_TEXT),
            )
        );
    }

	public static function choose_header_style_parameters() {
        return new external_function_parameters(
            array(
            'courseid' => new external_value(PARAM_INT),
            'headerstyle' => new external_value(PARAM_INT),
            )
        );
    }

	public static function toggle_course_availability_parameters() {
        return new external_function_parameters(
            array(
            'courseid' => new external_value(PARAM_INT),
            'availability' => new external_value(PARAM_INT),
            )
        );
    }

    /**
     * Describes upload_couse_image return value.
     * @return external_single_structure
     */
    public static function upload_course_image_returns() {
        return new external_single_structure(array('success' => new external_value(PARAM_BOOL)));
    }
	
    /**
     * Describes choose_header_style return value.
     * @return external_single_structure
     */
    public static function choose_header_style_returns() {
        return new external_single_structure(array('success' => new external_value(PARAM_INT)));
    }
	
    /**
     * Describes choose_header_style return value.
     * @return external_single_structure
     */
    public static function toggle_course_availability_returns() {
        return new external_single_structure(array('success' => new external_value(PARAM_BOOL)));
    }

    /**
     * Changes course header image.
     * @param int $courseid - ID of course.
     * @param string $imagedata - Raw image data.
     * @param string $imagename - Name of image.
     * @return bool $success - Whether or not the image was uploaded properly.
     */
    public static function upload_course_image($courseid, $imagedata, $imagename) {
        global $CFG;

        // get params
        $params = self::validate_parameters(
            self::upload_course_image_parameters(),
            array(
            'courseid' => $courseid,
            'imagedata' => $imagedata,
            'imagename' => $imagename,
            )
        );

        // ensure user has permissions to change image
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('moodle/course:changesummary', $context);

        $filestorage = get_file_storage();
        $filetype = strtolower(pathinfo($params['imagename'], PATHINFO_EXTENSION));
        $filetype === 'jpeg' ? 'jpg' : $filetype;
        $new_filename = 'courseimage_'.time().'.' . $filetype;

        $binary_data = base64_decode($params['imagedata']);

        // verify size
        if (strlen($binary_data) > get_max_upload_file_size($CFG->maxbytes)) {
            throw new \moodle_exception('error:courseimageexceedsmaxbytes', 'theme_uofr_conservatory', $CFG->maxbytes);
        }

        // verify filetype
        if ($filetype !== 'jpg' && $filetype !== 'png' && $filetype !== 'gif') {
            throw new \moodle_exception('error:courseimageinvalidfiletype', 'theme_uofr_conservatory');
        }

        if ($context->contextlevel === CONTEXT_COURSE) {
            $fileinfo = array(
                'contextid' => $context->id,
                'component' => 'course',
                'filearea' => 'overviewfiles',
                'itemid' => 0,
                'filepath' => '/',
                'filename' => $new_filename
            );

            // Remove any old course summary image files for this context.
            $filestorage->delete_area_files($context->id, $fileinfo['component'], $fileinfo['filearea']);

            // Create and set new image.
            $storedfile = $filestorage->create_file_from_string($fileinfo, $binary_data);
            $success = $storedfile instanceof \stored_file;
        }

        // return
        return array('success' => $success);
    }
	
	
	public static function choose_header_style($courseid, $headerstyle) {
        global $CFG, $DB;

        // get params
        $params = self::validate_parameters(
            self::choose_header_style_parameters(),
            array(
            'courseid' => $courseid,
            'headerstyle' => $headerstyle,
            )
        );

        // ensure user has permissions to change image
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('moodle/course:changesummary', $context);
		
		//update the db
		$table = 'theme_conservatory_hdrstyle';
		
	    $newrecord = new stdClass();
	    $newrecord->courseid = $courseid;
	    $newrecord->hdrstyle = $headerstyle;

	    //database check if user has a record, insert if not
	    if ($record = $DB->get_record($table, array('courseid'=>$courseid))) {
	     	//if has a record, update record to $setdarkmode
			
			$newrecord->id = $record->id;
     	 	$success = $DB->update_record($table, $newrecord);
	    } else {
	        //create a record
	        $success = $DB->insert_record($table, $newrecord);
	    } 
		
		return array('success' => $success);
		
	}
	
	public static function toggle_course_availability($courseid, $availability) {
        global $CFG, $DB;
		
        // get params
        $params = self::validate_parameters(
            self::toggle_course_availability_parameters(),
            array(
            'courseid' => $courseid,
            'availability' => $availability,
            )
        );

        // ensure user has permissions to change image
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('moodle/course:changesummary', $context);
		
		$table = 'course';
		
	    $newrecord = new stdClass();
	    $newrecord->courseid = $courseid;
	    $newrecord->visible = $availability==1?0:1;

		if ($record = $DB->get_record($table, array('id'=>$courseid))) {
			$newrecord->id = $record->id;
     		$success = $DB->update_record($table, $newrecord);

			return array('success' => $success);
		} else {
			return array('error' => 'Record not found');
        }
    }

    public static function user_is_instructor_parameters() {
        return new external_function_parameters([]);
    }

    public static function user_is_instructor() {
        return theme_uofr_conservatory_user_is_instructor();
    }

    public static function user_is_instructor_returns() {
        return new external_value(PARAM_BOOL);
    }

    /**
     * Returns description of get_landing_page's parameters.
     *
     * @return external_function_parameters
     */
    public static function get_landing_page_parameters() {
        return new external_function_parameters(array(
            'contextid' => new external_value(PARAM_INT),
            'localurl' => new external_value(PARAM_TEXT)
        ));
    }
        /**
     * Returns landing page data for help modal.
     *
     * @param int $courseid
     * @param int $currenturl
     * @return array
     */
    public static function get_landing_page($contextid, $localurl) {
        global $PAGE;

        $params = self::validate_parameters(self::get_landing_page_parameters(), array(
            'contextid' => $contextid,
            'localurl' => $localurl
        ));

        $context = context::instance_by_id($params['contextid']);
        self::validate_context($context);

        $is_instructor = theme_uofr_conservatory_user_is_instructor();
        $url = $is_instructor ? new moodle_url('/guides/instructor.json') : new moodle_url('/guides/student.json');
        $target = '';
        if ($is_instructor) {
            if (strpos($params['localurl'], '/course/') === 0) {
                $url = new moodle_url('/guides/instructor/courseadministration.json');
            }
            else if (strpos($params['localurl'], '/mod/assign/') === 0) {
                $url = new moodle_url('/guides/instructor/assignments.json');
                $target = '#assignment_activity';
            }
            else if (strpos($params['localurl'], '/mod/turnitintooltwo/') === 0) {
                $url = new moodle_url('/guides/instructor/assignments.json');
                $target = '#turnitin';
            }
            else if (strpos($params['localurl'], '/mod/kalvidassign/') === 0) {
                $url = new moodle_url('/guides/instructor/assignments.json');
                $target = '#media';
            }
            else if (strpos($params['localurl'], '/mod/attendance/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#attendance';
            }
            else if (strpos($params['localurl'], '/mod/oublog/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#blog';
            }
            else if (strpos($params['localurl'], '/mod/chat/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#chat';
            }
            else if (strpos($params['localurl'], '/mod/choice/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#choice';
            }
            else if (strpos($params['localurl'], '/mod/mail/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#course_email';
            }
            else if (strpos($params['localurl'], '/mod/data/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#database';
            }
            else if (strpos($params['localurl'], '/mod/feedback/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#feedback';
            }
            else if (strpos($params['localurl'], '/mod/glossary/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#glossary';
            }
            else if (strpos($params['localurl'], '/mod/lesson/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#lesson';
            }
            else if (strpos($params['localurl'], '/mod/questionnaire/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#questionnaire';
            }
            else if (strpos($params['localurl'], '/mod/scorm/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#scorm';
            }
            else if (strpos($params['localurl'], '/mod/survey/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#survey';
            }
            else if (strpos($params['localurl'], '/mod/wiki/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#wiki';
            }
            else if (strpos($params['localurl'], '/mod/workshop/') === 0) {
                $url = new moodle_url('/guides/instructor/activities.json');
                $target = '#workshop';
            }
            else if (strpos($params['localurl'], '/mod/lti/') === 0) {
                $url = new moodle_url('/guides/instructor/more.json');
                $target = '#external_tool';
            }
            else if (strpos($params['localurl'], '/mod/imscp/') === 0) {
                $url = new moodle_url('/guides/instructor/more.json');
                $target = '#ims';
            }
            else if (strpos($params['localurl'], '/mod/lightboxgallery/') === 0) {
                $url = new moodle_url('/guides/instructor/more.json');
                $target = '#lightbox_gallery';
            }
            else if (strpos($params['localurl'], '/mod/scheduler/') === 0) {
                $url = new moodle_url('/guides/instructor/more.json');
                $target = '#scheduler';
            }
            else if (strpos($params['localurl'], '/mod/skype/') === 0) {
                $url = new moodle_url('/guides/instructor/more.json');
                $target = '#skype';
            }
            else if (strpos($params['localurl'], '/mod/book/') === 0) {
                $url = new moodle_url('/guides/instructor/resources.json');
                $target = '#book';
            }
            else if (strpos($params['localurl'], '/mod/folder/') === 0) {
                $url = new moodle_url('/guides/instructor/resources.json');
                $target = '#folder';
            }
            else if (strpos($params['localurl'], '/mod/kalvidres/') === 0) {
                $url = new moodle_url('/guides/instructor/resources.json');
                $target = '#media';
            }
            else if (strpos($params['localurl'], '/mod/page/') === 0) {
                $url = new moodle_url('/guides/instructor/resources.json');
                $target = '#page';
            }
            else if (strpos($params['localurl'], '/local/mymedia/') === 0) {
                $url = new moodle_url('/guides/instructor/kaltura.json');
                $target = '#my_media';
            }
            else if (strpos($params['localurl'], '/mod/quiz/') === 0) {
                $url = new moodle_url('/guides/instructor/quizzes.json');
            }
            else if (strpos($params['localurl'], '/mod/forum/') === 0) {
                $url = new moodle_url('/guides/instructor/forums.json');
            }
            else if (strpos($params['localurl'], '/mod/zoom/') === 0) {
                $url = new moodle_url('/guides/instructor/zoom.json');
            }
        } else {
            if (strpos($params['localurl'], '/mod/mail/') === 0) {
                $url = new moodle_url('/guides/student/courseemail.json');
            }
            else if (strpos($params['localurl'], '/mod/forum/') === 0) {
                $url = new moodle_url('/guides/student/forums.json');
            }
            else if (strpos($params['localurl'], '/mod/chat/') === 0) {
                $url = new moodle_url('/guides/student/chat.json');
            }
            else if (strpos($params['localurl'], '/mod/zoom/') === 0) {
                $url = new moodle_url('/guides/student/zoom.json');
            }
            else if (strpos($params['localurl'], '/mod/assign/') === 0) {
                $url = new moodle_url('/guides/student/assignments.json');
            }
            else if (strpos($params['localurl'], '/mod/turnitintooltwo/') === 0) {
                $url = new moodle_url('/guides/student/turnitin.json');
            }
            else if (strpos($params['localurl'], '/mod/quiz/') === 0) {
                $url = new moodle_url('/guides/student/quizzesexams.json');
            }
            else if (strpos($params['localurl'], '/mod/oublog/') === 0) {
                $url = new moodle_url('/guides/student/blogs.json');
            }
            else if (strpos($params['localurl'], '/mod/wiki/') === 0) {
                $url = new moodle_url('/guides/student/wikis.json');
            }
            else if (strpos($params['localurl'], '/mod/book/') === 0) {
                $url = new moodle_url('/guides/student/bookshelf.json');
            }
        }

        return [
            'url' => $url->get_path(),
            'target' => $target
        ];

    }

	/**
     * Returns description of get_landing_page return value.
     *
     * @return external_single_structure
    */
    public static function get_landing_page_returns() {
        return new external_single_structure([
            'url' => new external_value(PARAM_TEXT),
            'target' => new external_value(PARAM_TEXT, '', VALUE_OPTIONAL, '')
        ]);
    }

	    /**
     * Returns description of get_topic_list's parameters.
     *
     * @return external_function_parameters
     */
    public static function get_topic_list_parameters() {
        return new external_function_parameters(array(
            'contextid' => new external_value(PARAM_INT)
        ));
    }
  
    /**
     * Gets list of help topics from the guides.
     *
     * @return array
     */
    public static function get_topic_list($contextid) {
        $params = self::validate_parameters(self::get_topic_list_parameters(), array(
            'contextid' => $contextid
        ));

        $context = context::instance_by_id($params['contextid']);
        self::validate_context($context);

        $content_url_absolute = new moodle_url('/guides/social/sample-b.json');

        return [
            'url' => $content_url_absolute->get_path(),
            'instructor' => theme_uofr_conservatory_user_is_instructor($context)
        ];
    }

 /**
     * Returns description of get_topic_list return value.
     *
     * @return external_multiple_structure
     */
    public static function get_topic_list_returns() {
        return new external_single_structure([
            'url' => new external_value(PARAM_TEXT),
            'instructor' => new external_value(PARAM_BOOL)
        ]);
    }

    /**
     * Returns description of params passed to get_guide_page.
     *
     * @return external_function_parameters
     */
    public static function get_guide_page_parameters() {
        return new external_function_parameters(array(
            'url' => new external_value(PARAM_TEXT),
            'contextid' => new external_value(PARAM_INT)
        ));
    }

    /**
     * Returns guide page data.
     *
     * @param string $url 
     */
    public static function get_guide_page($url, $contextid) {
        $params = self::validate_parameters(self::get_guide_page_parameters(), array(
            'url' => $url,
            'contextid' => $contextid
        ));

        $context = context::instance_by_id($params['contextid']);
        self::validate_context($context);

        // get url of format /guides/.../page.json
        $relative_path = substr($params['url'], strpos($params['url'], '/guides/'));
        $url_trimmed = (strpos($relative_path, '/index.html') !== false)
            ? str_replace('/index.html', '', $relative_path)
            : rtrim($relative_path, '/');
        list($content_url, $target) = explode('#', $url_trimmed);
        $target = $target ? "#$target" : null;
        
        $content_url = new moodle_url($content_url . '.json');
        $json_output = json_decode(file_get_contents($content_url));
        $html = ($json_output->content) ? $json_output->content : $json_output->jsondata->page_data[0]->content;
        $title = $json_output->jsondata ? $json_output->jsondata->page_data[0]->title : '';

        // convert links in the html
        //$base = $context->contextlevel === CONTEXT_MODULE ? '../../guides/' : '../guides/';

        //$reg1 = '/\.\/|\.\.\//';
        //$reg2 = '/href="\b(?!https|http\b)/';
        //$reg3 = '/src="\b(?!https|http\b)/';
 
        //$html = preg_replace($reg1, $base, $html);
        //$html = preg_replace($reg2, 'href="' . $base, $html);
        //$html = preg_replace($reg3, 'src="' . $base, $html);
        return array(
            'html' => $html,
            'title' => $title,
            'target' => $target
        );
    }

    /**
     * Returns description of get_guide_page return value.
     *
     * @return external_single_structure
     */
    public static function get_guide_page_returns() {
        return new external_single_structure(array(
            'html' => new external_value(PARAM_RAW),
            'title' => new external_value(PARAM_TEXT),
            'target' => new external_value(PARAM_TEXT, '', VALUE_OPTIONAL, null)
        ));
    }

    /**
     * Returns description of modal_help_search's parameters.
     *
     * @return external_function_parameters
     */
    public static function modal_help_search_parameters() {
        return new external_function_parameters(array(
            'contextid' => new external_value(PARAM_INT),
            'query' => new external_value(PARAM_TEXT)
        ));
    }

    /**
     * Searches the guides.
     *
     * @param int $contextid
     * @param string $query
     * @return array
     */
    public static function modal_help_search($contextid, $query) {
        $params = self::validate_parameters(self::modal_help_search_parameters(), array(
            'contextid' => $contextid,
            'query' => $query
        ));

        $context = context::instance_by_id($params['contextid']);
        self::validate_context($context);

        $query = urlencode($params['query']);
        $search_url = new moodle_url("/guides/search.json/query:$query");
        return $search_url->get_path();
    }  
      
    /**
     * Returns description of modal_help_serach's return value.
     *
     * @return external_single_structure
     */
    public static function modal_help_search_returns() {
        return new external_value(PARAM_TEXT);
    }
}

