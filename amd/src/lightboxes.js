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
 * Theme UR Courses - Display images linked with rel="lightbox" in a modal.
 */

import $ from 'jquery';
import LightboxModal from 'theme_urcourses_default/lightbox_modal';
import Templates from 'core/templates';

const SELECTORS = {
    PAGE_CONTENT: '#page-content',
    LIGHTBOX_IMAGE: 'a[rel="lightbox"]'
};

const TEMPLATES = {
    LIGHTBOX_MODAL_BODY: 'theme_urcourses_default/lightbox_modal_body'
};

export const init = () => {
    if ($(SELECTORS.LIGHTBOX_IMAGE).length === 0) {
        return;
    }

    registerEventListeners($(SELECTORS.PAGE_CONTENT));
};

const registerEventListeners = (root) => {
    root.on('click', SELECTORS.LIGHTBOX_IMAGE, (e) => {
        e.preventDefault();
        const image = $(e.target);
        showLightboxModal(image.attr('src'), image.attr('title'));
    });
};

const showLightboxModal = (imgsrc, caption) => {
    LightboxModal.create({
        body: Templates.render(TEMPLATES.LIGHTBOX_MODAL_BODY, {
            imgsrc: imgsrc,
            caption: caption
        }),
        removeOnClose: true,
        show: true,
        large: true
    });
};