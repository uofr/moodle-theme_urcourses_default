console.log('hello from popup fixer');
//Once the page is loaded, grab all existing glossary popup links by their unique class the 
//auto linker gives them
window.addEventListener("load",addEvents,false);
function addEvents(){
    var popnodes = document.getElementsByClassName("glossary autolink concept glossaryid20");
    length = popnodes.length;
    for(var i = 0; i < length; i++) {
        popnodes[i].addEventListener('click',findPop,false);
    };
}

function findPop() {
    //need to wait for popup node to be created before searching
    //before that node is created, this deletes the first one it finds
    //and its sibling since 
    parent = document.getElementsByClassName('moodle-dialogue-base moodle-dialogue-confirm');
    if (parent[0]){
        sibling = parent[0].previousSibling;
        sibling.remove();
        //need to click the <input> OK button to reset positioning with YUI stuff. 
        //Just deleting the node creaetes a bug where it always spawns in the same spot
        btns = parent[0].getElementsByTagName('input');
        btns[0].click();
    }
    setTimeout (function() {
        //only want to find elements that are in the popup window and set them to start this again after closing 
        //the first popup link was grabbed from
        var parent = document.getElementsByClassName('moodle-dialogue-base moodle-dialogue-confirm');
        //want to set event listeners to popup window links if any exist
        if (parent[0]){
            var poplinks = parent[0].getElementsByClassName('glossary autolink concept glossaryid20');
            lnum = poplinks.length;
            for(var i = 0; i < lnum; i++) {
                poplinks[i].addEventListener('click',closePop,false);
            };
        }
    }, 1000);
}

function closePop(){
    console.log('close popup');
    parent = document.getElementsByClassName('moodle-dialogue-base moodle-dialogue-confirm');
    console.log(parent[0]);
    // if (parent[0]){
    //     sibling = parent[0].previousSibling;
    //     sibling.remove();
    //     //need to click the <input> OK button to reset positioning with YUI stuff. 
    //     //Just deleting the node creaetes a bug where it always spawns in the same spot
    //     //btns = parent[0].getElementsByTagName('input');
    //     //btns[0].click();
    // }

}