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
 * @package    theme_urcourses_default
 * @author     John Lane
 *
 */

use Symfony\Component\Console\Descriptor\JsonDescriptor;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/course/lib.php");

class theme_urcourses_default_external extends external_api {

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
        return new external_single_structure(array('success' => new external_value(PARAM_BOOL)));
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
            throw new \moodle_exception('error:courseimageexceedsmaxbytes', 'theme_urcourses_default', $CFG->maxbytes);
        }

        // verify filetype
        if ($filetype !== 'jpg' && $filetype !== 'png' && $filetype !== 'gif') {
            throw new \moodle_exception('error:courseimageinvalidfiletype', 'theme_urcourses_default');
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
        $table = 'theme_urcourses_hdrstyle';

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

    /**
     * Checks if the current user is an instructor.
     * If a course id other than 1 is passed, we check if the user is the instructor of that course.
     * Otherwise we check if they are an instructor in any course.
     * @param int $course_id
     * @return bool
     */
    public static function user_is_instructor($course_id) {
        global $USER, $DB;

        // Get instructor role ids.
        $role_query_cond = 'shortname = :a OR shortname = :b OR shortname = :c OR shortname = :d';
        $role_query_arr = ['a' => 'editingteacher', 'b' => 'teacher', 'c' => 'manager', 'd' => 'coursecreator'];
        $instructor_roles = $DB->get_fieldset_select('role', 'id', $role_query_cond, $role_query_arr);
        
        $roleassign_query_cond = 'userid = :uid AND (roleid = :r0 OR roleid = :r1 OR roleid = :r2 OR roleid = :r3)';
        $roleassign_query_arr = [
            'uid' => $USER->id,
            'r0' => $instructor_roles[0],
            'r1' => $instructor_roles[1],
            'r2' => $instructor_roles[2],
            'r3' => $instructor_roles[3]
        ];

        // If we are in a course, add a condition to check if the current user is the instructor of that course.
        // Othewise, we will check to see if the user is an instructor in any course.
        if ($course_id !== 1) {
            $roleassign_query_cond .= 'AND contextid = :cid';
            $roleassign_query_arr['cid'] = \context_course::instance($course_id)->id;
        }

        return $DB->record_exists_select('role_assignments', $roleassign_query_cond, $roleassign_query_arr);
    }

    public static function get_landing_page_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT),
                'currenturl' => new external_value(PARAM_TEXT)
            )
        );
    }

    public static function get_landing_page($courseid, $currenturl) {
        $params = self::validate_parameters(self::get_landing_page_parameters(), array(
            'courseid' => $courseid,
            'currenturl' => $currenturl
        ));
        if (self::user_is_instructor($params['courseid'])) {
            if (strpos($params['currenturl'], '/course/')) {
                $content_url = new moodle_url('/guides/instructor/courseadministration/urcoursescollection.json');
            }
            else {
                $content_url = new moodle_url('/guides/instructor.json');
            }
        }
        else {
            $content_url = new moodle_url('/guides/student.json');
        }
        $output = file_get_contents($content_url);
        $json_output = json_decode($output);
        if ($json_output->content) {
            $html = $json_output->content;
            $links = [];
        }
        else {
            $html = $json_output->jsondata->page_data[0]->content;
            $links = $json_output->jsondata->page_data[0]->contenturls;
        }
        return array('html' => $html, 'links' => $links);
    }

    public static function get_landing_page_returns() {
        return new external_single_structure(array(
            'html' => new external_value(PARAM_RAW),
            'links' => new external_multiple_structure(
                new external_single_structure(array(
                    'name' => new external_value(PARAM_TEXT),
                    'url' => new external_value(PARAM_TEXT)
                )), '', VALUE_OPTIONAL, null
            )
        ));
    }

    public static function get_topic_list_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT)
            )
        );
    }

    public static function get_topic_list($courseid) {
        $params = self::validate_parameters(self::get_topic_list_parameters(), array(
            'courseid' => $courseid
        ));
        $url = new moodle_url('/guides/social/sample-b.json');
        $output = file_get_contents($url);
        $json_output = json_decode($output);
        $topic_list_full = $json_output->jsondata->page_data[0]->all_pages;

        if (self::user_is_instructor($params['courseid'])) {
            $topic_list = array_filter($topic_list_full, function($item) {
                return $item->prefix === 'Instructor';
            });
        } else {
            $topic_list = array_filter($topic_list_full, function($item) {
                return $item->prefix === 'Students';
            });
        }

        usort($topic_list, function($a, $b) {
            return $a->title < $b->title ? -1 : 1;
        });

        return $topic_list;
    }

    public static function get_topic_list_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'prefix' => new external_value(PARAM_TEXT, '', VALUE_OPTIONAL),
                    'title' => new external_value(PARAM_TEXT, '', VALUE_OPTIONAL),
                    'url' => new external_value(PARAM_TEXT, '', VALUE_OPTIONAL),
                    'excerpt' => new external_value(PARAM_TEXT, '', VALUE_OPTIONAL)
                )
            )
        );
    }

    public static function get_guide_page_parameters() {
        return new external_function_parameters (array('url' => new external_value(PARAM_TEXT)));
    }

    public static function get_guide_page($url) {
        $params = self::validate_parameters(self::get_guide_page_parameters(), array('url' => $url));
        $url = (new moodle_url($params['url'] . '.json'))->__toString();
        $output = file_get_contents($url);
        $json_output = json_decode($output);
        return array('html' => $json_output->jsondata->page_data[0]->content);
    }

    public static function get_guide_page_returns() {
        return new external_single_structure(array('html' => new external_value(PARAM_RAW)));
    }

}

