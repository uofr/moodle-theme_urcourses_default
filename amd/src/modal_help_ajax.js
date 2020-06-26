import Ajax from 'core/ajax';
import Templates from 'core/templates';

export default class ModalHelpAjax {

    static async getLandingPage(courseid, currenturl) {
        const ajaxConfig = {
            methodname: 'theme_urcourses_default_get_landing_page',
            args: {
                courseid: courseid,
                currenturl: currenturl
            }
        };
        return  Ajax.call([ajaxConfig])[0];
    }

    static getTopicList(courseid) {
        const ajaxConfig = {
            methodname: 'theme_urcourses_default_get_topic_list',
            args: {
                courseid: courseid
            }
        };
        return Ajax.call([ajaxConfig])[0];
    }

    static getGuidePage(url) {
        const ajaxConfig = {
            methodname: 'theme_urcourses_default_get_guide_page',
            args: {
                url: url
            }
        };
        return Ajax.call([ajaxConfig])[0];
    }

}