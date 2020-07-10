import Ajax from 'core/ajax';

export default class ModalHelpAjax {

    static async getLandingPage(contextid) {
        const ajaxConfig = {
            methodname: 'theme_urcourses_default_get_landing_page',
            args: {
                contextid: contextid
            }
        };
        return Ajax.call([ajaxConfig])[0];
    }

    static getTopicList(contextid) {
        const ajaxConfig = {
            methodname: 'theme_urcourses_default_get_topic_list',
            args: {
                contextid: contextid
            }
        };
        return Ajax.call([ajaxConfig])[0];
    }

    static searchGuides(contextid, query) {
        const ajaxConfig = {
            methodname: 'theme_urcourses_default_modal_help_search',
            args: {
                contextid: contextid,
                query: query
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
