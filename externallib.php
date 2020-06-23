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

    public static function user_is_instructor() {
        global $USER, $DB, $COURSE;
        $teacher_role_id = $DB->get_field('role', 'id', array('shortname' => 'editingteacher'));
        $editing_teacher_role_id = $DB->get_field('role', 'id', array('shortname' => 'teacher'));

        if ($DB->record_exists('course', array('id' => $COURSE->id)) && $COURSE->id !== 1) {
            $course_context = \context_course::instance($COURSE->id);
            $is_teacher = $DB->record_exists('role_assignments',
                array('roleid' => $teacher_role_id, 'userid' => $USER->id, 'contextid' => $course_context->id));
            $is_editing_teacher = $DB->record_exists('role_assignments', 
                array('roleid' => $editing_teacher_role_id, 'userid' => $USER->id, 'contextid' => $course_context->id));
        } else {
            $is_teacher = $DB->record_exists('role_assignments',
                array('userid' => $USER->id, 'roleid' => $teacher_role_id));
            $is_editing_teacher = $DB->record_exists('role_assignments',
                array('userid' => $USER->id, 'roleid' => $editing_teacher_role_id));
        }

        return $is_teacher || $is_editing_teacher;
    }

    public static function get_remtl_help_parameters() {
        return new external_function_parameters(array());
    }

    public static function get_remtl_help() {
        $url = self::user_is_instructor() ? new moodle_url('/guides/instructor.json') : new moodle_url('/guides/student.json');
        $output = file_get_contents($url);
        $json_output = json_decode($output);
        return array('html' => $json_output->content);
    }

    public static function get_remtl_help_returns() {
        return new external_single_structure(array('html' => new external_value(PARAM_RAW)));
    }

    public static function get_topic_list_parameters() {
        return new external_function_parameters(array());
    }

    public static function get_topic_list() {
        global $PAGE;
        $url = new moodle_url('/guides/social/sample-b.json');
        $output = file_get_contents($url);
        $json_output = json_decode($output);
        $topic_list_full = $json_output->jsondata->page_data[0]->all_pages;
        $topic_list = array_filter($topic_list_full, function($item) {
            return self::user_is_instructor() ? $item->prefix === 'Instructor' : $item->prefix === 'Students';
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

