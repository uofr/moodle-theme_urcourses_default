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
 * Theme Boost Campus - Script to setup the help modal.
 *
 * @package    theme_uofr_conservatory
 * @author     John Lane
 *
 */

import $ from 'jquery';
import Notification from 'core/notification';
import ModalFactory from 'core/modal_factory';
import ModalHelp from 'theme_uofr_conservatory/modal_help';

const SELECTORS = {
    MODAL_HELP_TRIGGER: '#modal_help_trigger'
};

export const init = async (contextid, localUrl) => {

    try {
        const modalHelp = await ModalFactory.create({type: ModalHelp.getType()});
        await modalHelp.init(contextid, localUrl);
        $(SELECTORS.MODAL_HELP_TRIGGER).on('click', () => {
            console.log("SEtup");
            modalHelp.show();
        });
    } catch(error) {
         Notification.exception(error);
    }
};