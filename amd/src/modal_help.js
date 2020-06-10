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
            this.topicsList = countries;
        }.bind(this));

        this.getModal().on('input', SELECTORS.SEARCH, function(e, data) {
            var searchValue = this.searchBox.val();
            var regex = RegExp(searchValue.split('').join('.*'), 'i');
            var suggestions = [];
            this.suggestionBox.html('');
            if (!searchValue.length) {
                return;
            }
            for (var topic of this.topicsList) {
                var match = regex.exec(topic);
                if (match) {
                    suggestions.push({
                        text: topic,
                        length: match[0].length,
                        start: match.index
                    });
                }
            }
            suggestions.sort(function(a, b) {
                if (a.length < b.length) return -1;
                if (a.length > b.length) return 1;
                if (a.start < b.start) return -1;
                if (a.start > b.start) return 1;
            });
            for (var suggestion of suggestions) {
                var html = `<a href="#" class="dropdown-item suggestion-item" data-value="${suggestion.text}">${suggestion.text}</a>`;
                this.suggestionBox.append(html);
            }
        }.bind(this));

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

    // Weird setup code. Don't touch!
    if (!registered) {
        ModalRegistry.register(ModalHelp.TYPE, ModalHelp, 'theme_urcourses_default/modal_help');
        registered = true;
    }

    return ModalHelp;
});
