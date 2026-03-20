(function () {
    var trigger = document.querySelector('[data-notifications-trigger="1"]');
    var dropdown = document.querySelector('[data-invitations-root="1"]');

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

    function removeInvitation(invitationId) {
        var item = dropdown.querySelector('[data-invitation-id="' + String(invitationId) + '"]');
        if (item && item.parentNode) {
            item.parentNode.removeChild(item);
        }

        var items = dropdown.querySelectorAll('.invitation-item');
        if (items.length === 0) {
            var empty = document.querySelector('.notifications-empty');
            if (!empty) {
                var list = dropdown.querySelector('.notifications-list');
                if (list) {
                    list.innerHTML = '<div class="notifications-empty"><p>No pending invitations</p></div>';
                }
            }
        }

        var badge = trigger.querySelector('.notification-badge');
        var count = items.length;
        if (badge && count > 0) {
            badge.textContent = Math.min(count, 9) + (count > 9 ? '+' : '');
        } else if (badge) {
            badge.parentNode.removeChild(badge);
        }
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

    trigger.addEventListener('click', function (event) {
        event.stopPropagation();
        toggleDropdown();
    });

    // Force a consistent closed state on initial render.
    toggleDropdown(false);

    dropdown.addEventListener('click', function (event) {
        event.stopPropagation();
    });

    dropdown.querySelectorAll('.invitation-accept-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            var invitationId = Number(button.getAttribute('data-invitation-id') || 0);
            if (!invitationId) {
                return;
            }

            acceptInvitation(invitationId, button);
        });
    });

    dropdown.querySelectorAll('.invitation-decline-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            var invitationId = Number(button.getAttribute('data-invitation-id') || 0);
            if (!invitationId) {
                return;
            }

            declineInvitation(invitationId, button);
        });
    });

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

