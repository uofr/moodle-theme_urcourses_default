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
        SUGGESTION_ITEM: '.modal-help-suggestion-item'
    };

    var TEMPLATES = {
        MODAL_HELP_CONTENT: 'theme_urcourses_default/modal_help_content',
        MODAL_HELP_TOPICS: 'theme_urcourses_default/modal_help_topics'
    };

    /*
     * Modal constructor.
     *
     * @param {object} root The root jQuery element for the modal.
     */
    var ModalHelp = function(root) {
        Modal.call(this, root);
        this.searchBox = this.root.find(SELECTORS.SEARCH);
        this.searchButton = this.root.find(SELECTORS.SEARCH_BUTTON);
        this.suggestionBox = this.root.find(SELECTORS.SUGGESTIONS);
        this.topicList = null;
        this.suggestionIndex = 0;
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
            ModalHelpAjax.getRemtlHelp()
            .then((response) => {
                var renderPromise = Templates.render(TEMPLATES.MODAL_HELP_CONTENT, {html: response.html});
                return this.setBody(renderPromise);
            }).catch(Notification.exception);

            if (this.topicList === null) {
                ModalHelpAjax.getTopicList()
                .then((response) => {
                    this.topicList = response;
                    return Templates.render(TEMPLATES.MODAL_HELP_TOPICS, {topics: response});
                })
                .then((html, js) => {
                    return Templates.replaceNodeContents(SELECTORS.SUGGESTIONS, html, js);
                }).catch(Notification.exception);
            }
        });

        this.getRoot().on(ModalEvents.hidden, () => {
            this.setBody('');
            this.searchBox.val('');
            this.suggestionBox.addClass('d-none');
        });

        this.getRoot().on('click', (e) => {
            var target = e.target;
            var searchBox = this.searchBox[0];
            var suggestionBox = this.suggestionBox[0];
            if (!(target === searchBox) && !(target === suggestionBox)) {
                this.suggestionBox.addClass('d-none');
            }
        });

        this.getModal().on('focus', SELECTORS.SEARCH, () => {
            this.suggestionBox.removeClass('d-none');
        });

        this.getModal().on('input', SELECTORS.SEARCH, () => {
            var searchValue = this.searchBox.val();
            var regex = RegExp(searchValue.split('').join('.*'), 'i');
            var suggestionItems = $(SELECTORS.SUGGESTION_ITEM);
            var suggestions = [];
            suggestionItems.addClass('d-none');
            suggestionItems.removeClass('selected');
            if (!searchValue.length) {
                suggestionItems.eq(0).addClass('selected');
                suggestionItems.removeClass('d-none');
                return;
            }
            for (var topic of this.topicList) {
                var match = regex.exec(topic.title);
                if (match) {
                    suggestions.push({
                        text: topic.title,
                        url: topic.url,
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
                var matchingSuggestion = suggestionItems.filter(`[data-value="${suggestion.text}"]`);
                matchingSuggestion.removeClass('d-none');
            }
            suggestionItems.filter(':not(.d-none)').eq(0).addClass('selected');
        });

        this.getModal().on('keydown', SELECTORS.SEARCH, (e) => {
            var visibleSuggestions = $(SELECTORS.SUGGESTION_ITEM).filter(':not(.d-none)');
            if (e.keyCode === KeyCodes.arrowDown || e.keyCode === KeyCodes.arrowRight) {
                this.suggestionIndex++;
                if (this.suggestionIndex >= visibleSuggestions.length) {
                    this.suggestionIndex = 0;
                }
                visibleSuggestions.removeClass('selected');
                visibleSuggestions.eq(this.suggestionIndex).addClass('selected');
            } else if (e.keyCode === KeyCodes.arrowUp || e.keyCode === KeyCodes.arrowLeft) {
                this.suggestionIndex--;
                if (this.suggestionIndex < 0) {
                    this.suggestionIndex = visibleSuggestions.length - 1;
                }
                visibleSuggestions.removeClass('selected');
                visibleSuggestions.eq(this.suggestionIndex).addClass('selected');
            }
        });

        this.getModal().on('mouseover', SELECTORS.SUGGESTION_ITEM, (e) => {
            $(SELECTORS.SUGGESTION_ITEM).removeClass('selected');
            $(e.currentTarget).addClass('selected');
        });
    };

    if (!registered) {
        ModalRegistry.register(ModalHelp.TYPE, ModalHelp, 'theme_urcourses_default/modal_help');
        registered = true;
    }

    return ModalHelp;
});
