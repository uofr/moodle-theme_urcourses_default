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
 * @package    theme_urcourses_default
 * @author     John Lane
 *
 */

import $ from 'jquery';
import Notification from 'core/notification';
import ModalFactory from 'core/modal_factory';
import ModalHelp from 'theme_urcourses_default/modal_help';

const SELECTORS = {
    MODAL_HELP_TRIGGER: '#modal_help_trigger'
};

export const init = async (contextId) => {
    const modalHelpConfig = {
        type: ModalHelp.getType()
    };
    const modalHelpTrigger = $(SELECTORS.MODAL_HELP_TRIGGER);
    try {
        const helpModal = await ModalFactory.create(modalHelpConfig, modalHelpTrigger);
        helpModal.init(contextId);
    }
    catch(error) {
        Notification.exception(error);
    }
};
