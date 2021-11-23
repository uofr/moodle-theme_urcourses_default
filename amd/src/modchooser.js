define(['jquery'], function($){
    /**
    * Snap modchooser listener to add current section to urls.
     */
    var modchooserjump = function() {
        $("[name='submitbutton']").trigger('click');
    };

    var setmodjump = function() {
        setTimeout(function(){
            $('li.modchooser_resources a').tab('show');
            $("[type='radio']").change(modchooserjump);
        }, 500);
    };

    var init = function() {
        //create an event listener on the hidden input named "jump"
        $('.section-modchooser-link').click(setmodjump);
    };

    return {
        init: init
    };
});