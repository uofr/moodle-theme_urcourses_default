define(
[
    'jquery',
    'core/modal',
    'core/modal_registry',
    'core/modal_events',
    'core/key_codes',
    'core/custom_interaction_events'
],
function(
    $,
    Modal,
    ModalRegistry,
    ModalEvents,
    KeyCodes,
    CustomEvents
) {
    var registered = false;
    var countries = ["Afghanistan","Albania","Algeria","Andorra","Angola","Anguilla","Antigua &amp; Barbuda","Argentina","Armenia","Aruba","Australia","Austria","Azerbaijan","Bahamas","Bahrain","Bangladesh","Barbados","Belarus","Belgium","Belize","Benin","Bermuda","Bhutan","Bolivia","Bosnia &amp; Herzegovina","Botswana","Brazil","British Virgin Islands","Brunei","Bulgaria","Burkina Faso","Burundi","Cambodia","Cameroon","Canada","Cape Verde","Cayman Islands","Central Arfrican Republic","Chad","Chile","China","Colombia","Congo","Cook Islands","Costa Rica","Cote D Ivoire","Croatia","Cuba","Curacao","Cyprus","Czech Republic","Denmark","Djibouti","Dominica","Dominican Republic","Ecuador","Egypt","El Salvador","Equatorial Guinea","Eritrea","Estonia","Ethiopia","Falkland Islands","Faroe Islands","Fiji","Finland","France","French Polynesia","French West Indies","Gabon","Gambia","Georgia","Germany","Ghana","Gibraltar","Greece","Greenland","Grenada","Guam","Guatemala","Guernsey","Guinea","Guinea Bissau","Guyana","Haiti","Honduras","Hong Kong","Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland","Isle of Man","Israel","Italy","Jamaica","Japan","Jersey","Jordan","Kazakhstan","Kenya","Kiribati","Kosovo","Kuwait","Kyrgyzstan","Laos","Latvia","Lebanon","Lesotho","Liberia","Libya","Liechtenstein","Lithuania","Luxembourg","Macau","Macedonia","Madagascar","Malawi","Malaysia","Maldives","Mali","Malta","Marshall Islands","Mauritania","Mauritius","Mexico","Micronesia","Moldova","Monaco","Mongolia","Montenegro","Montserrat","Morocco","Mozambique","Myanmar","Namibia","Nauro","Nepal","Netherlands","Netherlands Antilles","New Caledonia","New Zealand","Nicaragua","Niger","Nigeria","North Korea","Norway","Oman","Pakistan","Palau","Palestine","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Poland","Portugal","Puerto Rico","Qatar","Reunion","Romania","Russia","Rwanda","Saint Pierre &amp; Miquelon","Samoa","San Marino","Sao Tome and Principe","Saudi Arabia","Senegal","Serbia","Seychelles","Sierra Leone","Singapore","Slovakia","Slovenia","Solomon Islands","Somalia","South Africa","South Korea","South Sudan","Spain","Sri Lanka","St Kitts &amp; Nevis","St Lucia","St Vincent","Sudan","Suriname","Swaziland","Sweden","Switzerland","Syria","Taiwan","Tajikistan","Tanzania","Thailand","Timor L'Este","Togo","Tonga","Trinidad &amp; Tobago","Tunisia","Turkey","Turkmenistan","Turks &amp; Caicos","Tuvalu","Uganda","Ukraine","United Arab Emirates","United Kingdom","United States of America","Uruguay","Uzbekistan","Vanuatu","Vatican City","Venezuela","Vietnam","Virgin Islands (US)","Yemen","Zambia","Zimbabwe"];
    var SELECTORS = {
        SEARCH: '#modal_help_search',
        SEARCH_BUTTON: '#modal_help_search_btn',
        SUGGESTIONS: '#modal_help_search_suggestions',
        SUGGESTION_ITEM: '.suggestion-item'
    };

    /**
     * Modal constructor.
     *
     * @param {object} root The root jQuery element for the modal.
     */
    var ModalHelp = function(root) {
        Modal.call(this, root);
        this.searchBox = this.root.find(SELECTORS.SEARCH);
        this.searchButton = this.root.find(SELECTORS.SEARCH_BUTTON);
        this.suggestionBox = this.root.find(SELECTORS.SUGGESTIONS);
        this.suggestionItemIndex = 0;
    };

    ModalHelp.prototype = Object.create(Modal.prototype);
    ModalHelp.prototype.constructor = ModalHelp;
    ModalHelp.TYPE = 'theme_urcourses_default-help';

    /**
     * Set up event handling.
     *
     * @method registerEventListeners
     */
    ModalHelp.prototype.registerEventListeners = function() {
        Modal.prototype.registerEventListeners.call(this);

        this.getRoot().on(ModalEvents.shown, function() {
            console.log('shown');
        }.bind(this));

        this.getModal().on('input', SELECTORS.SEARCH, function(e, data) {
            $(SELECTORS.SUGGESTIONS).html('');
            if (!this.value.length) {
                return;
            }
            for (var i = 0; i < countries.length; i++) {
                if (countries[i].substr(0, this.value.length).toUpperCase() === this.value.toUpperCase()) {
                    var suggestionItem = $('<a href="#"></a>');
                    suggestionItem.addClass('dropdown-item suggestion-item');
                    suggestionItem.attr('data-value', countries[i]);
                    suggestionItem.html(`<strong>${countries[i].substr(0, this.value.length)}</strong>${countries[i].substr(this.value.length)}`);
                    $(SELECTORS.SUGGESTIONS).append(suggestionItem);
                }
            }
        });

        this.getModal().on('keydown', SELECTORS.SEARCH, function(e, data) {     
            switch (e.keyCode) {
                case KeyCodes.arrowUp:
                    e.preventDefault();
                    this.suggestionItemIndex = this.getModal().find(SELECTORS.SUGGESTION_ITEM).length - 1;
                    this.getModal().find(SELECTORS.SUGGESTION_ITEM).eq(this.suggestionItemIndex).focus();
                    break;
                case KeyCodes.arrowDown:
                    e.preventDefault();
                    this.suggestionItemIndex = 0;
                    this.getModal().find(SELECTORS.SUGGESTION_ITEM).eq(this.suggestionItemIndex).focus();
                    break;
                case KeyCodes.arrowLeft:
                     e.preventDefault();
                    this.suggestionItemIndex = this.getModal().find(SELECTORS.SUGGESTION_ITEM).length - 1;
                    this.getModal().find(SELECTORS.SUGGESTION_ITEM).eq(this.suggestionItemIndex).focus();
                    break;
                case KeyCodes.arrowRight:
                    e.preventDefault();
                    this.suggestionItemIndex = 0;
                    this.getModal().find(SELECTORS.SUGGESTION_ITEM).eq(this.suggestionItemIndex).focus();
                    break;
            }
        }.bind(this));

        this.getModal().on('keydown', SELECTORS.SUGGESTIONS, function(e, data) {
            switch (e.keyCode) {
                case KeyCodes.arrowUp:
                    e.preventDefault();
                    this.suggestionItemIndex--;
                    if (this.suggestionItemIndex == -1) {
                        this.suggestionItemIndex = this.getModal().find(SELECTORS.SUGGESTION_ITEM).length - 1;
                    }
                    this.getModal().find(SELECTORS.SUGGESTION_ITEM).eq(this.suggestionItemIndex).focus();
                    break;
                case KeyCodes.arrowDown:
                    e.preventDefault();
                    this.suggestionItemIndex++;
                    if (this.suggestionItemIndex == this.getModal().find(SELECTORS.SUGGESTION_ITEM).length) {
                        this.suggestionItemIndex = 0;
                    }
                    this.getModal().find(SELECTORS.SUGGESTION_ITEM).eq(this.suggestionItemIndex).focus();
                    break;
                case KeyCodes.arrowLeft:
                    e.preventDefault();
                    this.suggestionItemIndex--;
                    if (this.suggestionItemIndex == -1) {
                        this.suggestionItemIndex = this.getModal().find(SELECTORS.SUGGESTION_ITEM).length - 1;
                    }
                    this.getModal().find(SELECTORS.SUGGESTION_ITEM).eq(this.suggestionItemIndex).focus();
                    break;
                case KeyCodes.arrowRight:
                    e.preventDefault();
                    this.suggestionItemIndex++;
                    if (this.suggestionItemIndex == this.getModal().find(SELECTORS.SUGGESTION_ITEM).length) {
                        this.suggestionItemIndex = 0;
                    }
                    this.getModal().find(SELECTORS.SUGGESTION_ITEM).eq(this.suggestionItemIndex).focus();
                    break;
                case KeyCodes.escape:
                    e.preventDefault();
                    this.getModal().find(SELECTORS.SUGGESTIONS).html('');
                    break;
            }
        }.bind(this));

        CustomEvents.define(this.getModal().find(SELECTORS.SUGGESTION_ITEM, [CustomEvents.events.activate]));
        this.getModal().on(`${CustomEvents.events.activate} ${CustomEvents.events.keyboardActivate}`, SELECTORS.SUGGESTION_ITEM, function() {
            $(SELECTORS.SEARCH).val($(this).attr('data-value'));
            $(SELECTORS.SUGGESTIONS).html('');
        });
    };

    if (!registered) {
        ModalRegistry.register(ModalHelp.TYPE, ModalHelp, 'theme_urcourses_default/modal_help');
        registered = true;
    }

    return ModalHelp;
});
