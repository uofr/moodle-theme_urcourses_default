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
 * Contains class \core\output\icon_system
 *
 * @package    core
 * @category   output
 * @copyright  2016 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_boost_campus\output;

defined('MOODLE_INTERNAL') || die();

class theme_boost_campus_icon_system_fontawesome extends \core\output\icon_system_fontawesome {
    /**
     * @var array $map Cached map of moodle icon names to font awesome icon names.
     */
    private $map = [];
    public function get_core_icon_map() {
        $iconmap = parent::get_core_icon_map();
        $iconmap['core:t/editcourse'] = 'fa-cogs';
        $iconmap['core:t/edit'] = 'fa-cogs';
        return $iconmap;
    }
}


