define(
[
    'jquery',
    'core/modal',
    'core/modal_registry',
    'core/modal_events',
    'theme_urcourses_default/tail.select'
],
function(
    $,
    Modal,
    ModalRegistry,
    ModalEvents,
    TailSelect
) {
    var registered = false;

    /**
     * Modal constructor.
     *
     * @param {object} root The root jQuery element for the modal.
     */
    var ModalHelp = function(root) {
        Modal.call(this, root);
    };

    ModalHelp.TYPE = 'theme_urcourses_default-help';
    ModalHelp.prototype = Object.create(Modal.prototype);
    ModalHelp.prototype.constructor = ModalHelp;

    /**
     * Set up event handling.
     *
     * @method registerEventListeners
     */
    ModalHelp.prototype.registerEventListeners = function() {
        Modal.prototype.registerEventListeners.call(this);

        this.getRoot().on(ModalEvents.shown, function() {
            TailSelect('#test_select', {
                search: true
            });
        }.bind(this));
    };

    if (!registered) {
        ModalRegistry.register(ModalHelp.TYPE, ModalHelp, 'theme_urcourses_default/modal_help');
        registered = true;
    }

    return ModalHelp;
});
