define(
[
    'core/ajax',
],
function(
    Ajax
){
	var getModalContent = function() {
		return Ajax.call([
			{
				methodname: 'theme_urcourses_default_get_remtl_help',
				args: {}
			},
			{
				methodname: 'theme_urcourses_default_get_topic_list',
				args: {}
			}
		]);
	};

    return {
		getModalContent: getModalContent
    };
});
