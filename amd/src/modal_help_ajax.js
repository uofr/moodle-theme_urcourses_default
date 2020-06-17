define(
[
    'core/ajax'
],
function(
    Ajax
){
    var getTopicList = function() {
        return Ajax.call([
            {
                methodname: 'theme_urcourses_default_get_topic_list',
                args: {}
            }
        ])[0];
    };

    var getRemtlHelp = function() {
        return Ajax.call([
            {
                methodname: 'theme_urcourses_default_get_remtl_help',
                args: {}
            }
        ])[0];
    };

	var getGuidePage = function(url) {
		return Ajax.call([
			{
				methodname: 'theme_urcourses_default_get_guide_page',
				args: {url: url}
			}
		])[0];
	};

    return {
        getTopicList: getTopicList,
        getRemtlHelp: getRemtlHelp,
		getGuidePage: getGuidePage
    };
});
