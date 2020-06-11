define(
[
    'core/ajax',
],
function(
    Ajax
){
    var getRemtlHelp = function() {
        var args = {};
        var ajaxCall = {
            methodname: 'theme_urcourses_default_get_remtl_help',
            args: args
        };
        return Ajax.call([ajaxCall])[0];
    };

    var getTopicList = function() {
        var args = {};
        var ajaxCall = {
            methodname: 'theme_urcourses_default_get_topic_list',
            args: args
        };
        return Ajax.call([ajaxCall])[0];
    };

    return {
        getRemtlHelp: getRemtlHelp,
        getTopicList: getTopicList
    };
});