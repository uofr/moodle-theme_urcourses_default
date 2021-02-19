import Ajax from 'core/ajax';

export default class ModalHelpAjax {

    static async getLandingPageUrl(contextid, localUrl) {
        console.log(localUrl);
        const ajaxConfig = {
            methodname: 'theme_urcourses_default_get_landing_page',
            args: {
                contextid: contextid,
                localurl: localUrl
            }
        };
        return Ajax.call([ajaxConfig])[0];
    }

    static getTopicListUrl(contextid) {
        const ajaxConfig = {
            methodname: 'theme_urcourses_default_get_topic_list',
            args: {
                contextid: contextid
            } 
        };
        return Ajax.call([ajaxConfig])[0];
    }

    static getSearchUrl(contextid, query) {
        const ajaxConfig = {
            methodname: 'theme_urcourses_default_modal_help_search',
            args: {
                contextid: contextid,
                query: query
            }
        };
        return Ajax.call([ajaxConfig])[0];
    }

    static getGuideUrl(url, contextid) {
        const ajaxConfig = {
            methodname: 'theme_urcourses_default_get_guide_page',
            args: {
                url: url,
                contextid: contextid
            }
        };
        return Ajax.call([ajaxConfig])[0];
    }

    static userIsInstructor() {
        const ajaxConfig = {
            methodname: 'theme_urcourses_default_user_is_instructor',
            args: {}
        };
        return Ajax.call([ajaxConfig])[0];
    }

}
