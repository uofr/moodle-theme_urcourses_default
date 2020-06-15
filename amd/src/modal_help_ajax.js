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

    return {
        getTopicList: getTopicList,
        getRemtlHelp: getRemtlHelp
    };
});