define(
[
    'jquery',
    'core/modal',
    'core/modal_registry',
    'core/modal_events',
    'core/key_codes',
    'core/custom_interaction_events',
    'core/notification',
    'core/templates',
    'theme_urcourses_default/modal_help_ajax'
],
function(
    $,
    Modal,
    ModalRegistry,
    ModalEvents,
    KeyCodes,
    CustomEvents,
    Notification,
    Templates,
    ModalHelpAjax
) {
    var registered = false;

    var SELECTORS = {
        SEARCH: '#modal_help_search',
        SEARCH_BUTTON: '#modal_help_search_btn',
        SUGGESTIONS: '#modal_help_search_suggestions',
        SUGGESTION_ITEM: '.suggestion-item'
    };

    var TEMPLATES = {
        MODAL_HELP_CONTENT: 'theme_urcourses_default/modal_help_content'
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

        this.getRoot().on(ModalEvents.shown, () => {
			var promises = ModalHelpAjax.getModalContent();
			promises[0].then((response) => {
                var renderPromise = Templates.render(TEMPLATES.MODAL_HELP_CONTENT, {html: response.html});
                this.setBody(renderPromise);
            }).catch(Notification.exception);
            promises[1].then((response) => {
                this.topicsList = response;
            }).catch(Notification.exception);
        });

        this.getRoot().on(ModalEvents.hidden, () => {
            this.setBody('');
            this.searchBox.val('');
            this.suggestionBox.html('');
        });

        this.getModal().on('input', SELECTORS.SEARCH, (e, data) => {
            var searchValue = this.searchBox.val();
            var regex = RegExp(searchValue.split('').join('.*'), 'i');
            var suggestions = [];
            this.suggestionBox.html('');
            if (!searchValue.length) {
                return;
            }
            for (var topic of this.topicsList) {
                var match = regex.exec(topic.title);
                if (match) {
                    suggestions.push({
                        text: topic.title,
                        length: match[0].length,
                        start: match.index
                    });
                }
            }
            suggestions.sort(function(a, b) {
                if (a.length < b.length) {
                    return -1;
                }
                if (a.length > b.length) {
                    return 1;
                }
                if (a.start < b.start) {
                    return -1;
                }
                if (a.start > b.start) {
                    return 1;
                }
            });
            for (var suggestion of suggestions) {
                var html = `<a href="#" class="dropdown-item suggestion-item" data-value="${suggestion.text}">${suggestion.text}</a>`;
                this.suggestionBox.append(html);
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
