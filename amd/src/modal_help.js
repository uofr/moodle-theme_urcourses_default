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
    };

    var TEMPLATES = {
        MODAL_HELP_CONTENT: 'theme_urcourses_default/modal_help_content',
        MODAL_HELP_TOPICS: 'theme_urcourses_default/modal_help_topics'
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
        this.topicList = null;
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
                this.setBody(renderPromise);
            }).catch(Notification.exception);
            if (this.topicList === null) {
                ModalHelpAjax.getTopicList()
                .then((response) => {
                    this.topicList = response;
                    return Templates.render(TEMPLATES.MODAL_HELP_TOPICS, {topics: response});
                })
                .then((html, js) => {
                    Templates.replaceNodeContents(SELECTORS.SUGGESTIONS, html, js);
                }).catch(Notification.exception);
            }
        });

        this.getRoot().on(ModalEvents.hidden, () => {
            this.setBody('');
            this.searchBox.val('');
            this.suggestionBox.html('');
        });

        this.getModal().on('focus', SELECTORS.SEARCH, (e) => {
            this.suggestionBox.removeClass('d-none');
        });

        this.getModal().on('input', SELECTORS.SEARCH, (e, data) => {
            var searchValue = this.searchBox.val();
            var regex = RegExp(searchValue.split('').join('.*'), 'i');
            var suggestions = [];
            this.suggestionBox.html('');
            if (!searchValue.length) {
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
                var html = `<span class="modal-help-suggestion-item" data-url="${suggestion.url}" data-value="${suggestion.text}">${suggestion.text}</span>`;
                this.suggestionBox.append(html);
            }
        });

        this.getModal().on(CustomEvents.events.activate, SELECTORS.SUGGESTION_ITEM, (e) => {
            $(SELECTORS.SEARCH).val($(e.target).attr('data-value'));
            $(SELECTORS.SUGGESTIONS).html('');
            ModalHelpAjax.getGuidePage($(e.target).attr('data-url'))
            .then((response) => {
                var renderPromise = Templates.render(TEMPLATES.MODAL_HELP_CONTENT, {html: response.html});
                this.setBody(renderPromise);
            }).catch(Notification.exception);
        });
    };

    if (!registered) {
        ModalRegistry.register(ModalHelp.TYPE, ModalHelp, 'theme_urcourses_default/modal_help');
        registered = true;
    }

    return ModalHelp;
});
