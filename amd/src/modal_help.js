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
        SUGGESTION_ITEM: '.modal-help-suggestion-item',
        LOADING_OVERLAY: '[data-region="overlay-icon-container"]'
    };

    var TEMPLATES = {
        MODAL_HELP_CONTENT: 'theme_urcourses_default/modal_help_content'
    };

    var CSS = {
        SUGGESTION_ACTIVE: 'suggestion-active'
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
        this.suggestionItemIndex = -1;
        this.topicsList = null;
    };

    ModalHelp.prototype = Object.create(Modal.prototype);
    ModalHelp.prototype.constructor = ModalHelp;
    ModalHelp.TYPE = 'theme_urcourses_default-help';

    ModalHelp.prototype.getNextSuggestion = function() {
        this.getModal().find(SELECTORS.SUGGESTION_ITEM).removeClass(CSS.SUGGESTION_ACTIVE);
        this.suggestionItemIndex++;
        if (this.suggestionItemIndex > this.getModal().find(SELECTORS.SUGGESTION_ITEM).length - 1) {
            this.suggestionItemIndex = 0;
        }
        this.getModal().find(SELECTORS.SUGGESTION_ITEM).eq(this.suggestionItemIndex).addClass(CSS.SUGGESTION_ACTIVE);
    };

    ModalHelp.prototype.getPrevSuggestion = function() {
        this.getModal().find(SELECTORS.SUGGESTION_ITEM).removeClass(CSS.SUGGESTION_ACTIVE);
        this.suggestionItemIndex--;
        if (this.suggestionItemIndex < 0) {
            this.suggestionItemIndex = this.getModal().find(SELECTORS.SUGGESTION_ITEM).length - 1;
        }
        this.getModal().find(SELECTORS.SUGGESTION_ITEM).eq(this.suggestionItemIndex).addClass(CSS.SUGGESTION_ACTIVE);
    };

    /**
     * Set up event handling.
     *
     * @method registerEventListeners
     */
    ModalHelp.prototype.registerEventListeners = function() {
        Modal.prototype.registerEventListeners.call(this);

        this.getRoot().on(ModalEvents.shown, () => {
            // get default content
            ModalHelpAjax.getRemtlHelp()
            .then((response) => {
                var renderPromise = Templates.render(TEMPLATES.MODAL_HELP_CONTENT, {html: response.html});
                this.setBody(renderPromise);
            }).catch(Notification.exception);
            // if topic list is null, get the list
            // otherwise, it's been set already and we don't need to do an ajax request
            if (this.topicsList === null) {
                ModalHelpAjax.getTopicList()
                .then((response) => {
                    this.topicsList = response;
                });
            }
        });

        this.getRoot().on(ModalEvents.hidden, () => {
            this.setBody('');
            this.searchBox.val('');
            this.suggestionBox.html('');
        });

        this.getModal().on('input', SELECTORS.SEARCH, (e, data) => {
            // this function should do nothing until the list is loaded
            if (this.topicsList === null) {
                return;
            }
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
            suggestions.sort((a, b) => {
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
                var html = `<span class="modal-help-suggestion-item" data-value="${suggestion.text}">${suggestion.text}</span>`;
                this.suggestionBox.append(html);
            }
        });

        this.getModal().on('keydown', SELECTORS.SEARCH, (e, data) => {
            if (e.keyCode === KeyCodes.arrowUp || e.keyCode === KeyCodes.arrowLeft) {
                e.preventDefault();
                this.getPrevSuggestion();
            }
            else if (e.keyCode === KeyCodes.arrowDown || e.keyCode === KeyCodes.arrowRight) {
                e.preventDefault();
                this.getNextSuggestion();
            }
        });

        this.getModal().on('keydown', SELECTORS.SUGGESTIONS, (e, data) => {
            if (e.keyCode === KeyCodes.arrowUp || e.keyCode === KeyCodes.arrowLeft) {
                e.preventDefault();
                this.getPrevSuggestion();
            }
            else if (e.keyCode === KeyCodes.arrowDown || e.keyCode === KeyCodes.arrowRight) {
                e.preventDefault();
                this.getNextSuggestion();
            }
            else if (e.keyCode === KeyCodes.escape) {
                e.preventDefault();
                this.getModal().find(SELECTORS.SUGGESTIONS).html('');
            }
        });

        CustomEvents.define(this.getModal().find(SELECTORS.SUGGESTION_ITEM, [CustomEvents.events.activate]));
        this.getModal().on(`${CustomEvents.events.activate} ${CustomEvents.events.keyboardActivate}`, SELECTORS.SUGGESTION_ITEM, function() {
            $(SELECTORS.SEARCH).val($(this).attr('data-value'));
            $(SELECTORS.SUGGESTIONS).html('');
        });

        this.getModal().on('mouseover', SELECTORS.SUGGESTIONS, () => {
            this.suggestionItemIndex = -1;
            this.getModal().find(SELECTORS.SUGGESTION_ITEM).removeClass(CSS.SUGGESTION_ACTIVE);
        });
    };

    if (!registered) {
        ModalRegistry.register(ModalHelp.TYPE, ModalHelp, 'theme_urcourses_default/modal_help');
        registered = true;
    }

    return ModalHelp;
});
