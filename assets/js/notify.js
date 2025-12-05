// Global notification utility
// This script defines a single function, showNotification, to display
// transient messages in the center of the page. The notification
// contains an icon (checkmark for success, cross for error) and
// disappears automatically after a few seconds. Include this
// script in your pages via a <script src="assets/js/notify.js"></script>

function showNotification(message, type = 'success') {
    // Create the notification element if it doesn't already exist
    let notification = document.getElementById('global-notification');
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'global-notification';
        notification.className = 'notification hidden';

        // Icon element
        const iconEl = document.createElement('span');
        iconEl.className = 'notification-icon';
        notification.appendChild(iconEl);

        // Text element
        const textEl = document.createElement('span');
        textEl.className = 'notification-text';
        notification.appendChild(textEl);

        document.body.appendChild(notification);
    }
    // Set message text. Use whitespace preserving style defined in CSS.
    const textNode = notification.querySelector('.notification-text');
    if (textNode) {
        textNode.textContent = message;
    }
    // Set icon based on type
    const iconNode = notification.querySelector('.notification-icon');
    if (iconNode) {
        if (type === 'error') {
            iconNode.textContent = '✖️';
        } else {
            // default success icon
            iconNode.textContent = '✅';
        }
    }
    // Remove previous classes and apply current type
    notification.classList.remove('success', 'error', 'hidden');
    notification.classList.add(type);
    // Trigger visibility animation
    // Use a small timeout to ensure class change triggers CSS transition
    setTimeout(() => {
        notification.classList.add('visible');
    }, 10);
    // Hide after 3 seconds
    setTimeout(() => {
        notification.classList.remove('visible');
        // Delay hiding completely to allow fade-out to finish
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 500);
    }, 3000);
}