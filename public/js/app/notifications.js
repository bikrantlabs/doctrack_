(function () {
    var trigger = document.querySelector('[data-notifications-trigger="1"]');
    var dropdown = document.querySelector('[data-notifications-root="1"]');

    if (!trigger || !dropdown) {
        return;
    }

    function toast(type, message) {
        if (window.DocTrackToast && typeof window.DocTrackToast.show === 'function') {
            window.DocTrackToast.show(type, message);
        }
    }

    function toggleDropdown(force) {
        var shouldOpen = typeof force === 'boolean' ? force : !dropdown.classList.contains('active');

        dropdown.hidden = !shouldOpen;
        dropdown.setAttribute('aria-hidden', shouldOpen ? 'false' : 'true');
        trigger.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');

        if (typeof force === 'boolean') {
            if (force) {
                dropdown.classList.add('active');
            } else {
                dropdown.classList.remove('active');
            }
        } else {
            dropdown.classList.toggle('active');
        }
    }

    function closeDropdown() {
        toggleDropdown(false);
    }

    function updateBadge(count) {
        var badge = trigger.querySelector('.notification-badge');
        if (count > 0) {
            if (!badge) {
                var newBadge = document.createElement('span');
                newBadge.className = 'notification-badge';
                trigger.appendChild(newBadge);
                badge = newBadge;
            }
            badge.textContent = Math.min(count, 9) + (count > 9 ? '+' : '');
        } else {
            if (badge && badge.parentNode) {
                badge.parentNode.removeChild(badge);
            }
        }
    }

    // --- Invitation Accept/Decline ---

    function removeInvitation(invitationId) {
        var item = dropdown.querySelector('[data-invitation-id="' + String(invitationId) + '"]');
        if (item && item.parentNode) {
            item.parentNode.removeChild(item);
        }

        var items = dropdown.querySelectorAll('.invitation-item');
        var unreadCount = dropdown.querySelectorAll('.notification-item:not(.invitation-item).notification-unread').length;
        var badgeCount = items.length + unreadCount;

        if (items.length === 0) {
            var sectionLabels = dropdown.querySelectorAll('.notifications-section-label');
            sectionLabels.forEach(function (label) {
                if (label.textContent.trim() === 'Invitations') {
                    label.parentNode.removeChild(label);
                }
            });
            var divider = dropdown.querySelector('.notifications-divider');
            if (divider) {
                divider.parentNode.removeChild(divider);
            }
        }

        var allItems = dropdown.querySelectorAll('.notification-item');
        if (allItems.length === 0) {
            var list = dropdown.querySelector('.notifications-list');
            if (list) {
                list.innerHTML = '<div class="notifications-empty"><p>No notifications yet</p></div>';
            }
            var footer = dropdown.querySelector('.notifications-footer');
            if (footer) {
                footer.parentNode.removeChild(footer);
            }
            updateBadge(0);
            return;
        }

        updateBadge(badgeCount);
    }

    function acceptInvitation(invitationId, button) {
        var endpoint = button.getAttribute('data-accept-url');
        if (!endpoint) {
            endpoint = '/app/invitations/' + String(invitationId) + '/accept';
        }

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(function (response) {
                return response.json().then(function (body) {
                    return {
                        ok: response.ok,
                        body: body || {}
                    };
                });
            })
            .then(function (result) {
                if (!result.ok || result.body.ok !== true) {
                    toast('error', result.body.message || 'Could not accept invitation.');
                    return;
                }

                removeInvitation(invitationId);
                toast('success', result.body.message || 'Invitation accepted!');
            })
            .catch(function () {
                toast('error', 'Could not accept invitation.');
            });
    }

    function declineInvitation(invitationId, button) {
        var endpoint = button.getAttribute('data-decline-url');
        if (!endpoint) {
            endpoint = '/app/invitations/' + String(invitationId) + '/decline';
        }

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(function (response) {
                return response.json().then(function (body) {
                    return {
                        ok: response.ok,
                        body: body || {}
                    };
                });
            })
            .then(function (result) {
                if (!result.ok || result.body.ok !== true) {
                    toast('error', result.body.message || 'Could not decline invitation.');
                    return;
                }

                removeInvitation(invitationId);
                toast('success', result.body.message || 'Invitation declined.');
            })
            .catch(function () {
                toast('error', 'Could not decline invitation.');
            });
    }

    // --- General Notification Click ---

    function markNotificationRead(notificationId, callback) {
        fetch('/app/notifications/' + String(notificationId) + '/read', {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (result) {
                if (result.ok) {
                    if (typeof callback === 'function') {
                        callback();
                    }
                }
            })
            .catch(function () {
                // Silently fail — navigation still proceeds
            });
    }

    // --- Mark All as Read ---

    function markAllAsRead() {
        fetch('/app/notifications/read-all', {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (result) {
                if (result.ok) {
                    dropdown.querySelectorAll('.notification-item.notification-unread').forEach(function (item) {
                        item.classList.remove('notification-unread');
                        item.style.borderLeft = '';
                        item.setAttribute('data-is-read', '1');
                    });
                    var dots = dropdown.querySelectorAll('.notification-unread-dot');
                    dots.forEach(function (dot) {
                        dot.parentNode.removeChild(dot);
                    });
                    var footer = dropdown.querySelector('.notifications-footer');
                    if (footer) {
                        footer.parentNode.removeChild(footer);
                    }
                    updateBadge(0);
                    toast('success', 'All notifications marked as read.');
                }
            })
            .catch(function () {
                toast('error', 'Could not mark notifications as read.');
            });
    }

    // --- Event Bindings ---

    trigger.addEventListener('click', function (event) {
        event.stopPropagation();
        toggleDropdown();
    });

    // Force a consistent closed state on initial render.
    toggleDropdown(false);

    dropdown.addEventListener('click', function (event) {
        event.stopPropagation();
    });

    // Invitation Accept buttons
    dropdown.querySelectorAll('.invitation-accept-btn').forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.stopPropagation();
            var invitationId = Number(button.getAttribute('data-invitation-id') || 0);
            if (!invitationId) {
                return;
            }

            acceptInvitation(invitationId, button);
        });
    });

    // Invitation Decline buttons
    dropdown.querySelectorAll('.invitation-decline-btn').forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.stopPropagation();
            var invitationId = Number(button.getAttribute('data-invitation-id') || 0);
            if (!invitationId) {
                return;
            }

            declineInvitation(invitationId, button);
        });
    });

    // General notification items — mark as read on click and navigate
    dropdown.querySelectorAll('.notification-item:not(.invitation-item)').forEach(function (item) {
        item.addEventListener('click', function (event) {
            var notificationId = Number(item.getAttribute('data-notification-id') || 0);
            var isRead = item.getAttribute('data-is-read') === '1';

            if (notificationId && !isRead) {
                markNotificationRead(notificationId, function () {
                    item.classList.remove('notification-unread');
                    item.style.borderLeft = '';
                    item.setAttribute('data-is-read', '1');
                    var dot = item.querySelector('.notification-unread-dot');
                    if (dot) {
                        dot.parentNode.removeChild(dot);
                    }
                    var invitationCount = dropdown.querySelectorAll('.invitation-item').length;
                    var remainingUnread = dropdown.querySelectorAll('.notification-item:not(.invitation-item).notification-unread').length;
                    updateBadge(invitationCount + remainingUnread);
                    if (remainingUnread === 0) {
                        var footer = dropdown.querySelector('.notifications-footer');
                        if (footer) {
                            footer.parentNode.removeChild(footer);
                        }
                    }
                });
            }
        });
    });

    // Mark all as read button
    var markAllBtn = dropdown.querySelector('[data-mark-all-read="1"]');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function (event) {
            event.stopPropagation();
            markAllAsRead();
        });
    }

    document.addEventListener('click', function (event) {
        var target = event.target;
        if (!trigger.contains(target) && !dropdown.contains(target)) {
            closeDropdown();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeDropdown();
        }
    });
})();
