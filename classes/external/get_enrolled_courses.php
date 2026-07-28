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
 * Get enrolled courses.
 *
 * @module  theme_urcourses_default
 * @author  2024 John Lane <john.lane@uregina.ca>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_urcourses_default\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use \theme_urcourses_default\external\course_summary_exporter;
use core\exception\invalid_parameter_exception;
use context_user;
use context_helper;
use context_course;
use Generator;
use core_course_category;
use context;
use coding_exception;

require_once($CFG->dirroot . '/theme/urcourses_default/locallib.php');

defined('MOODLE_INTERNAL') || die();

class get_enrolled_courses extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_enrolled_courses_by_timeline_classification_parameters() {
        return new external_function_parameters(
            array(
                'classification' => new external_value(PARAM_ALPHA, 'future, inprogress, or past'),
                'limit' => new external_value(PARAM_INT, 'Result set limit', VALUE_DEFAULT, 0),
                'offset' => new external_value(PARAM_INT, 'Result set offset', VALUE_DEFAULT, 0),
                'sort' => new external_value(PARAM_TEXT, 'Sort string', VALUE_DEFAULT, null),
                'customfieldname' => new external_value(PARAM_ALPHANUMEXT, 'Used when classification = customfield',
                    VALUE_DEFAULT, null),
                'customfieldvalue' => new external_value(PARAM_RAW, 'Used when classification = customfield',
                    VALUE_DEFAULT, null),
                'searchvalue' => new external_value(PARAM_RAW, 'The value a user wishes to search against',
                    VALUE_DEFAULT, null),
                'requiredfields' => new external_multiple_structure(
                    new external_value(PARAM_ALPHANUMEXT, 'Field name to be included from the results', VALUE_DEFAULT),
                    'Array of the only field names that need to be returned. If empty, all fields will be returned.',
                    VALUE_DEFAULT, []
                ),
            )
        );
    }

    /**
     * Returns description of method result value
     *
     * @return \core_external\external_description
     */
    public static function get_enrolled_courses_by_timeline_classification_returns() {
        return new external_single_structure(
            array(
                'courses' => new external_multiple_structure(course_summary_exporter::get_read_structure(), 'Course'),
                'nextoffset' => new external_value(PARAM_INT, 'Offset for the next request')
            )
        );
    }

    /**
     * Get courses matching the given timeline classification.
     *
     * NOTE: The offset applies to the unfiltered full set of courses before the classification
     * filtering is done.
     * E.g.
     * If the user is enrolled in 5 courses:
     * c1, c2, c3, c4, and c5
     * And c4 and c5 are 'future' courses
     *
     * If a request comes in for future courses with an offset of 1 it will mean that
     * c1 is skipped (because the offset applies *before* the classification filtering)
     * and c4 and c5 will be return.
     *
     * @param string $classification past, inprogress, or future
     * @param int $limit Result set limit
     * @param int $offset Offset the full course set before timeline classification is applied
     * @param string|null $sort SQL sort string for results
     * @param string|null $customfieldname
     * @param string|null $customfieldvalue
     * @param string|null $searchvalue
     * @param array $requiredfields Array of the only field names that need to be returned. If empty, all fields will be returned.
     * @return array list of courses and warnings
     */
    public static function get_enrolled_courses_by_timeline_classification(
        string $classification,
        int $limit = 0,
        int $offset = 0,
        ?string $sort = null,
        ?string $customfieldname = null,
        ?string $customfieldvalue = null,
        ?string $searchvalue = null,
        array $requiredfields = []
    ) {
        global $CFG, $PAGE, $USER;
        require_once($CFG->dirroot . '/course/lib.php');

        $params = self::validate_parameters(self::get_enrolled_courses_by_timeline_classification_parameters(),
            array(
                'classification' => $classification,
                'limit' => $limit,
                'offset' => $offset,
                'sort' => $sort,
                'customfieldvalue' => $customfieldvalue,
                'searchvalue' => $searchvalue,
                'requiredfields' => $requiredfields,
            )
        );

        $classification = $params['classification'];
        $limit = $params['limit'];
        $offset = $params['offset'];
        $sort = $params['sort'];
        $customfieldvalue = $params['customfieldvalue'];
        $searchvalue = clean_param($params['searchvalue'], PARAM_TEXT);
        $requiredfields = $params['requiredfields'];

        switch($classification) {
            case COURSE_TIMELINE_ALLINCLUDINGHIDDEN:
                break;
            case COURSE_TIMELINE_ALL:
                break;
            case COURSE_TIMELINE_PAST:
                break;
            case COURSE_TIMELINE_INPROGRESS:
                break;
            case COURSE_TIMELINE_FUTURE:
                break;
            case COURSE_FAVOURITES:
                break;
            case COURSE_TIMELINE_HIDDEN:
                break;
            case COURSE_TIMELINE_SEARCH:
                break;
            case COURSE_CUSTOMFIELD:
                break;
            default:
                throw new invalid_parameter_exception('Invalid classification');
        }

        self::validate_context(context_user::instance($USER->id));
        $exporterfields = array_keys(course_summary_exporter::define_properties());
        // Get the required properties from the exporter fields based on the required fields.
        $requiredproperties = array_intersect($exporterfields, $requiredfields);
        // If the resulting required properties is empty, fall back to the exporter fields.
        if (empty($requiredproperties)) {
            $requiredproperties = $exporterfields;
        }

        $fields = join(',', $requiredproperties);
        $hiddencourses = get_hidden_courses_on_timeline();

        // If the timeline requires really all courses, get really all courses.
        if ($classification == COURSE_TIMELINE_ALLINCLUDINGHIDDEN) {
            $courses = self::course_get_enrolled_courses_for_logged_in_user(0, $offset, $sort, $fields, COURSE_DB_QUERY_LIMIT);

            // Otherwise if the timeline requires the hidden courses then restrict the result to only $hiddencourses.
        } else if ($classification == COURSE_TIMELINE_HIDDEN) {
            $courses = self::course_get_enrolled_courses_for_logged_in_user(0, $offset, $sort, $fields,
                COURSE_DB_QUERY_LIMIT, $hiddencourses);

            // Otherwise get the requested courses and exclude the hidden courses.
        } else if ($classification == COURSE_TIMELINE_SEARCH) {
            // Prepare the search API options.
            $searchcriteria['search'] = $searchvalue;
            $options = ['idonly' => true];
            $courses = self::course_get_enrolled_courses_for_logged_in_user_from_search(
                0,
                $offset,
                $sort,
                $fields,
                COURSE_DB_QUERY_LIMIT,
                $searchcriteria,
                $options
            );
        } else {
            $courses = self::course_get_enrolled_courses_for_logged_in_user(0, $offset, $sort, $fields,
                COURSE_DB_QUERY_LIMIT, [], $hiddencourses);
        }

        $favouritecourseids = [];
        $ufservice = \core_favourites\service_factory::get_service_for_user_context(context_user::instance($USER->id));
        $favourites = $ufservice->find_favourites_by_type('core_course', 'courses');

        if ($favourites) {
            $favouritecourseids = array_map(
                function($favourite) {
                    return $favourite->itemid;
                }, $favourites);
        }

        if ($classification == COURSE_FAVOURITES) {
            list($filteredcourses, $processedcount) = course_filter_courses_by_favourites(
                $courses,
                $favouritecourseids,
                $limit
            );
        } else if ($classification == COURSE_CUSTOMFIELD) {
            list($filteredcourses, $processedcount) = course_filter_courses_by_customfield(
                $courses,
                $customfieldname,
                $customfieldvalue,
                $limit
            );
        } else {
            list($filteredcourses, $processedcount) = course_filter_courses_by_timeline_classification(
                $courses,
                $classification,
                $limit
            );
        }

        $renderer = $PAGE->get_renderer('core');
        $formattedcourses = array_map(function($course) use ($renderer, $favouritecourseids) {
            if ($course == null) {
                return;
            }
            context_helper::preload_from_record($course);
            $context = context_course::instance($course->id);
            $isfavourite = false;
            if (in_array($course->id, $favouritecourseids)) {
                $isfavourite = true;
            }
            $exporter = new course_summary_exporter($course, ['context' => $context, 'isfavourite' => $isfavourite]);
            return $exporter->export($renderer);
        }, $filteredcourses);

        $formattedcourses = array_filter($formattedcourses, function($course) {
            if ($course != null) {
                return $course;
            }
        });

        return [
            'courses' => $formattedcourses,
            'nextoffset' => $offset + $processedcount
        ];
    }

    /**
     * Get the list of enrolled courses for the current user.
     *
     * This function returns a Generator. The courses will be loaded from the database
     * in chunks rather than a single query.
     *
     * @param int $limit Restrict result set to this amount
     * @param int $offset Skip this number of records from the start of the result set
     * @param string|null $sort SQL string for sorting
     * @param string|null $fields SQL string for fields to be returned
     * @param int $dbquerylimit The number of records to load per DB request
     * @param array $includecourses courses ids to be restricted
     * @param array $hiddencourses courses ids to be excluded
     * @return Generator
     */
    private static function course_get_enrolled_courses_for_logged_in_user(
        int $limit = 0,
        int $offset = 0,
        ?string $sort = null,
        ?string $fields = null,
        int $dbquerylimit = COURSE_DB_QUERY_LIMIT,
        array $includecourses = [],
        array $hiddencourses = []
    ): Generator {

        $haslimit = !empty($limit);
        $recordsloaded = 0;
        $querylimit = (!$haslimit || $limit > $dbquerylimit) ? $dbquerylimit : $limit;

        while ($courses = self::enrol_get_my_courses($fields, $sort, $querylimit, $includecourses, false, $offset, $hiddencourses)) {
            yield from $courses;

            $recordsloaded += $querylimit;

            if (count($courses) < $querylimit) {
                break;
            }
            if ($haslimit && $recordsloaded >= $limit) {
                break;
            }

            $offset += $querylimit;
        }
    }

    /**
     * Get the list of enrolled courses the current user searched for.
     *
     * This function returns a Generator. The courses will be loaded from the database
     * in chunks rather than a single query.
     *
     * @param int $limit Restrict result set to this amount
     * @param int $offset Skip this number of records from the start of the result set
     * @param string|null $sort SQL string for sorting
     * @param string|null $fields SQL string for fields to be returned
     * @param int $dbquerylimit The number of records to load per DB request
     * @param array $searchcriteria contains search criteria
     * @param array $options display options, same as in get_courses() except 'recursive' is ignored -
     *                       search is always category-independent
     * @return Generator
     */
    private static function course_get_enrolled_courses_for_logged_in_user_from_search(
        int $limit = 0,
        int $offset = 0,
        ?string $sort = null,
        ?string $fields = null,
        int $dbquerylimit = COURSE_DB_QUERY_LIMIT,
        array $searchcriteria = [],
        array $options = []
    ): Generator {

        $haslimit = !empty($limit);
        $recordsloaded = 0;
        $querylimit = (!$haslimit || $limit > $dbquerylimit) ? $dbquerylimit : $limit;
        $ids = core_course_category::search_courses($searchcriteria, $options);

        // If no courses were found matching the criteria return back.
        if (empty($ids)) {
            return;
        }

        while ($courses = self::enrol_get_my_courses($fields, $sort, $querylimit, $ids, false, $offset)) {
            yield from $courses;

            $recordsloaded += $querylimit;

            if (count($courses) < $querylimit) {
                break;
            }
            if ($haslimit && $recordsloaded >= $limit) {
                break;
            }

            $offset += $querylimit;
        }
    }

    /**
     * Returns list of courses current $USER is enrolled in and can access
     *
     * The $fields param is a list of field names to ADD so name just the fields you really need,
     * which will be added and uniq'd.
     *
     * If $allaccessible is true, this will additionally return courses that the current user is not
     * enrolled in, but can access because they are open to the user for other reasons (course view
     * permission, currently viewing course as a guest, or course allows guest access without
     * password).
     *
     * @param string|array $fields Extra fields to be returned (array or comma-separated list).
     * @param string|null $sort Comma separated list of fields to sort by, defaults to respecting navsortmycoursessort.
     * Allowed prefixes for sort fields are: "ul" for the user_lastaccess table, "c" for the courses table,
     * "ue" for the user_enrolments table.
     * @param int $limit max number of courses
     * @param array $courseids the list of course ids to filter by
     * @param bool $allaccessible Include courses user is not enrolled in, but can access
     * @param int $offset Offset the result set by this number
     * @param array $excludecourses IDs of hidden courses to exclude from search
     * @return array
     */
    private static function enrol_get_my_courses($fields = null, $sort = null, $limit = 0, $courseids = [], $allaccessible = false,
        $offset = 0, $excludecourses = []) {
        global $DB, $USER, $CFG;

        // Allowed prefixes and field names.
        $allowedprefixesandfields = ['c' => array_keys($DB->get_columns('course')),
                                    'ul' => array_keys($DB->get_columns('user_lastaccess')),
                                    'ue' => array_keys($DB->get_columns('user_enrolments'))];

        // Re-Arrange the course sorting according to the admin settings.
        $sort = enrol_get_courses_sortingsql($sort);

        // Guest account does not have any enrolled courses.
        if (!$allaccessible && (isguestuser() or !isloggedin())) {
            return array();
        }

        $basefields = [
            'id', 'category', 'sortorder',
            'shortname', 'fullname', 'idnumber',
            'startdate', 'visible',
            'groupmode', 'groupmodeforce', 'cacherev',
            'showactivitydates', 'showcompletionconditions',
        ];

        if (empty($fields)) {
            $fields = $basefields;
        } else if (is_string($fields)) {
            // turn the fields from a string to an array
            $fields = explode(',', $fields);
            $fields = array_map('trim', $fields);
            $fields = array_unique(array_merge($basefields, $fields));
        } else if (is_array($fields)) {
            $fields = array_unique(array_merge($basefields, $fields));
        } else {
            throw new coding_exception('Invalid $fields parameter in enrol_get_my_courses()');
        }
        if (in_array('*', $fields)) {
            $fields = array('*');
        }

        $orderby = "";
        $sort    = trim($sort);
        $sorttimeaccess = false;
        if (!empty($sort)) {
            $rawsorts = explode(',', $sort);
            $sorts = array();
            foreach ($rawsorts as $rawsort) {
                $rawsort = trim($rawsort);
                // Make sure that there are no more white spaces in sortparams after explode.
                $sortparams = array_values(array_filter(explode(' ', $rawsort)));
                // If more than 2 values present then throw coding_exception.
                if (isset($sortparams[2])) {
                    throw new coding_exception('Invalid $sort parameter in enrol_get_my_courses()');
                }
                // Check the sort ordering if present, at the beginning.
                if (isset($sortparams[1]) && (preg_match("/^(asc|desc)$/i", $sortparams[1]) === 0)) {
                    throw new coding_exception('Invalid sort direction in $sort parameter in enrol_get_my_courses()');
                }

                $sortfield = $sortparams[0];
                $sortdirection = $sortparams[1] ?? 'asc';
                if (strpos($sortfield, '.') !== false) {
                    $sortfieldparams = explode('.', $sortfield);
                    // Check if more than one dots present in the prefix field.
                    if (isset($sortfieldparams[2])) {
                        throw new coding_exception('Invalid $sort parameter in enrol_get_my_courses()');
                    }
                    list($prefix, $fieldname) = [$sortfieldparams[0], $sortfieldparams[1]];
                    // Check if the field name matches with the allowed prefix.
                    if (array_key_exists($prefix, $allowedprefixesandfields) &&
                        (in_array($fieldname, $allowedprefixesandfields[$prefix]))) {
                        if ($prefix === 'ul') {
                            $sorts[] = "COALESCE({$prefix}.{$fieldname}, 0) {$sortdirection}";
                            $sorttimeaccess = true;
                        } else {
                            // Check if the field name that matches with the prefix and just append to sorts.
                            $sorts[] = $rawsort;
                        }
                    } else {
                        throw new coding_exception('Invalid $sort parameter in enrol_get_my_courses()');
                    }
                } else {
                    // Check if the field name matches with $allowedprefixesandfields.
                    $found = false;
                    foreach (array_keys($allowedprefixesandfields) as $prefix) {
                        if (in_array($sortfield, $allowedprefixesandfields[$prefix])) {
                            if ($prefix === 'ul') {
                                $sorts[] = "COALESCE({$prefix}.{$sortfield}, 0) {$sortdirection}";
                                $sorttimeaccess = true;
                            } else {
                                $sorts[] = "{$prefix}.{$sortfield} {$sortdirection}";
                            }
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        // The param is not found in $allowedprefixesandfields.
                        throw new coding_exception('Invalid $sort parameter in enrol_get_my_courses()');
                    }
                }
            }
            $sort = implode(',', $sorts);
            $orderby = "ORDER BY $sort";
        }

        $wheres = ['c.id <> ' . SITEID];
        $params = [];

        if (isset($USER->loginascontext) and $USER->loginascontext->contextlevel == CONTEXT_COURSE) {
            // list _only_ this course - anything else is asking for trouble...
            $wheres[] = "courseid = :loginas";
            $params['loginas'] = $USER->loginascontext->instanceid;
        }

        $coursefields = 'c.' .join(',c.', $fields);
        $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
        $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
        $params['contextlevel'] = CONTEXT_COURSE;
        $wheres = implode(" AND ", $wheres);

        $timeaccessselect = "";
        $timeaccessjoin = "";

        if (!empty($courseids)) {
            list($courseidssql, $courseidsparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
            $wheres = sprintf("%s AND c.id %s", $wheres, $courseidssql);
            $params = array_merge($params, $courseidsparams);
        }

        if (!empty($excludecourses)) {
            list($courseidssql, $courseidsparams) = $DB->get_in_or_equal($excludecourses, SQL_PARAMS_NAMED, 'param', false);
            $wheres = sprintf("%s AND c.id %s", $wheres, $courseidssql);
            $params = array_merge($params, $courseidsparams);
        }

        $courseidsql = "";
        // Logged-in, non-guest users get their enrolled courses.
        if (!isguestuser() && isloggedin()) {
            $courseidsql .= "
                    SELECT DISTINCT e.courseid
                    FROM {enrol} e
                    JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid1)
                    WHERE ue.status = :active AND e.status = :enabled AND ue.timestart <= :now1
                        AND (ue.timeend = 0 OR ue.timeend > :now2)";
            $params['userid1'] = $USER->id;
            $params['active'] = ENROL_USER_ACTIVE;
            $params['enabled'] = ENROL_INSTANCE_ENABLED;
            $params['now1'] = $params['now2'] = time();

            if ($sorttimeaccess) {
                $params['userid2'] = $USER->id;
                $timeaccessselect = ', ul.timeaccess as lastaccessed';
                $timeaccessjoin = "LEFT JOIN {user_lastaccess} ul ON (ul.courseid = c.id AND ul.userid = :userid2)";
            }
        }

        // When including non-enrolled but accessible courses...
        if ($allaccessible) {
            if (is_siteadmin()) {
                // Site admins can access all courses.
                $courseidsql = "SELECT DISTINCT c2.id AS courseid FROM {course} c2";
            } else {
                // If we used the enrolment as well, then this will be UNIONed.
                if ($courseidsql) {
                    $courseidsql .= " UNION ";
                }

                // Include courses with guest access and no password.
                $courseidsql .= "
                        SELECT DISTINCT e.courseid
                        FROM {enrol} e
                        WHERE e.enrol = 'guest' AND e.password = :emptypass AND e.status = :enabled2";
                $params['emptypass'] = '';
                $params['enabled2'] = ENROL_INSTANCE_ENABLED;

                // Include courses where the current user is currently using guest access (may include
                // those which require a password).
                $courseids = [];
                $accessdata = get_user_accessdata($USER->id);
                foreach ($accessdata['ra'] as $contextpath => $roles) {
                    if (array_key_exists($CFG->guestroleid, $roles)) {
                        // Work out the course id from context path.
                        $context = context::instance_by_id(preg_replace('~^.*/~', '', $contextpath));
                        if ($context instanceof context_course) {
                            $courseids[$context->instanceid] = true;
                        }
                    }
                }

                // Include courses where the current user has moodle/course:view capability.
                $courses = get_user_capability_course('moodle/course:view', null, false);
                if (!$courses) {
                    $courses = [];
                }
                foreach ($courses as $course) {
                    $courseids[$course->id] = true;
                }

                // If there are any in either category, list them individually.
                if ($courseids) {
                    list ($allowedsql, $allowedparams) = $DB->get_in_or_equal(
                            array_keys($courseids), SQL_PARAMS_NAMED);
                    $courseidsql .= "
                            UNION
                        SELECT DISTINCT c3.id AS courseid
                            FROM {course} c3
                            WHERE c3.id $allowedsql";
                    $params = array_merge($params, $allowedparams);
                }
            }
        }

        // Note: we can not use DISTINCT + text fields due to MS limitations, that is why
        // we have the subselect there.
        $sql = "SELECT $coursefields $ccselect $timeaccessselect
                FROM {course} c
                JOIN ($courseidsql) en ON (en.courseid = c.id)
            $timeaccessjoin
            $ccjoin
                WHERE $wheres
            $orderby";

        $courses = $DB->get_records_sql($sql, $params, $offset, $limit);

        // preload contexts and check visibility
        foreach ($courses as $id=>$course) {
            context_helper::preload_from_record($course);
            if (!$course->visible) {
                if (!$context = context_course::instance($id, IGNORE_MISSING)) {
                    unset($courses[$id]);
                    continue;
                }
                // if (!has_capability('moodle/course:viewhiddencourses', $context)) {
                //     unset($courses[$id]);
                //     continue;
                // }
            }
            $courses[$id] = $course;
        }

        //wow! Is that really all? :-D

        return $courses;
    }
}