define(
[
    'core/modal',
    'core/modal_registry'
],
function(
    Modal,
    ModalRegistry
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
    };

    if (!registered) {
        ModalRegistry.register(ModalHelp.TYPE, ModalHelp, 'theme_urcourses_default/modal_help');
        registered = true;
    }

    return ModalHelp;
});