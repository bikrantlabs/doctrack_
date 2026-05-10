(function () {
    var modalId = 'add-members-modal';
    var modal = document.getElementById(modalId);
    if (!modal) {
        return;
    }

    var form = document.getElementById('add-members-form');
    var searchInput = document.getElementById('add-members-search-input');
    var searchDropdown = document.getElementById('add-members-search-dropdown');
    var inviteList = document.getElementById('add-members-list');
    var inviteEmptyState = document.getElementById('add-members-empty-state');

    if (!form || !searchInput || !searchDropdown || !inviteList || !inviteEmptyState) {
        return;
    }

    var selectedUsers = new Map();
    var existingMemberIds = new Set();
    var searchTimer = null;
    var searchAbortController = null;

    document.querySelectorAll('[data-member-row]').forEach(function (row) {
        var memberId = Number(row.getAttribute('data-member-row') || 0);
        if (memberId) {
            existingMemberIds.add(memberId);
        }
    });

    function toast(type, message) {
        if (window.DocTrackToast && typeof window.DocTrackToast.show === 'function') {
            window.DocTrackToast.show(type, message);
        }
    }

    function initials(name) {
        if (!name) {
            return '?';
        }

        var parts = name.trim().split(/\s+/).filter(Boolean);
        if (parts.length === 0) {
            return '?';
        }

        if (parts.length === 1) {
            return parts[0].slice(0, 2).toUpperCase();
        }

        return (parts[0][0] + parts[1][0]).toUpperCase();
    }

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('active');
        window.setTimeout(function () {
            searchInput.focus();
        }, 10);
    }

    function hideSearchDropdown() {
        searchDropdown.classList.remove('active');
        searchDropdown.innerHTML = '';
    }

    function closeModal() {
        modal.classList.remove('active');
        modal.classList.add('hidden');
        hideSearchDropdown();
    }

    function renderInvites() {
        var users = Array.from(selectedUsers.values());
        inviteList.innerHTML = '';

        if (users.length === 0) {
            inviteList.classList.add('hidden');
            inviteEmptyState.classList.remove('hidden');
            return;
        }

        inviteList.classList.remove('hidden');
        inviteEmptyState.classList.add('hidden');

        users.forEach(function (user) {
            var item = document.createElement('div');
            item.className = 'invite-item';
            item.innerHTML = [
                '<div class="avatar">' + initials(user.fullname) + '</div>',
                '<div class="invite-info">',
                '  <span class="invite-name"></span>',
                '  <span class="invite-email"></span>',
                '</div>',
                '<select class="role-select" data-user-id="' + String(user.id) + '">',
                '  <option value="viewer">Viewer</option>',
                '  <option value="reviewer">Reviewer</option>',
                '  <option value="editor">Editor</option>',
                '</select>',
                '<button type="button" class="invite-remove" data-user-id="' + String(user.id) + '" aria-label="Remove member">',
                '  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">',
                '      <line x1="18" y1="6" x2="6" y2="18"></line>',
                '      <line x1="6" y1="6" x2="18" y2="18"></line>',
                '  </svg>',
                '</button>'
            ].join('');

            item.querySelector('.invite-name').textContent = user.fullname;
            item.querySelector('.invite-email').textContent = user.email;

            item.querySelector('.role-select').value = user.role;
            item.querySelector('.role-select').addEventListener('change', function (event) {
                var existing = selectedUsers.get(user.id);
                if (!existing) {
                    return;
                }
                existing.role = event.target.value;
                selectedUsers.set(user.id, existing);
            });

            item.querySelector('.invite-remove').addEventListener('click', function () {
                selectedUsers.delete(user.id);
                renderInvites();
            });

            inviteList.appendChild(item);
        });
    }

    function addUser(user) {
        if (!selectedUsers.has(user.id)) {
            selectedUsers.set(user.id, {
                id: user.id,
                fullname: user.fullname,
                email: user.email,
                role: 'viewer'
            });
        }

        renderInvites();
        searchInput.value = '';
        hideSearchDropdown();
    }

    function renderSearchResults(users) {
        searchDropdown.innerHTML = '';

        if (!Array.isArray(users) || users.length === 0) {
            var empty = document.createElement('div');
            empty.className = 'user-search-empty';
            empty.textContent = 'No users found.';
            searchDropdown.appendChild(empty);
            searchDropdown.classList.add('active');
            return;
        }

        users.forEach(function (user) {
            if (selectedUsers.has(user.id) || existingMemberIds.has(user.id)) {
                return;
            }

            var item = document.createElement('button');
            item.type = 'button';
            item.className = 'user-search-item';
            item.innerHTML = [
                '<div class="avatar">' + initials(user.fullname) + '</div>',
                '<div class="user-search-info">',
                '  <span class="user-search-name"></span>',
                '  <span class="user-search-email"></span>',
                '</div>'
            ].join('');
            item.querySelector('.user-search-name').textContent = user.fullname;
            item.querySelector('.user-search-email').textContent = user.email;
            item.addEventListener('click', function () {
                addUser(user);
            });

            searchDropdown.appendChild(item);
        });

        if (searchDropdown.childElementCount === 0) {
            renderSearchResults([]);
            return;
        }

        searchDropdown.classList.add('active');
    }

    function searchUsers(query) {
        var endpoint = form.getAttribute('data-search-url') || '';
        if (!endpoint) {
            return;
        }

        if (searchAbortController) {
            searchAbortController.abort();
        }

        searchAbortController = new AbortController();

        fetch(endpoint + '?q=' + encodeURIComponent(query), {
            headers: {
                'Accept': 'application/json'
            },
            signal: searchAbortController.signal
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (payload) {
                if (!payload || payload.ok !== true) {
                    renderSearchResults([]);
                    return;
                }

                renderSearchResults(payload.users || []);
            })
            .catch(function () {
                renderSearchResults([]);
            });
    }

    function submitInvites(event) {
        event.preventDefault();

        var addUrl = form.getAttribute('data-add-url') || '';
        if (!addUrl) {
            return;
        }

        var payload = {
            members: Array.from(selectedUsers.values()).map(function (user) {
                return {
                    user_id: user.id,
                    role: user.role
                };
            })
        };

        fetch(addUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
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
                    toast('error', result.body.message || 'Could not send invitations.');
                    return;
                }

                toast('success', result.body.message || 'Invitations sent successfully.');
                window.location.reload();
            })
            .catch(function () {
                toast('error', 'Could not send invitations.');
            });
    }

    document.querySelectorAll('[data-modal="' + modalId + '"]').forEach(function (trigger) {
        trigger.addEventListener('click', openModal);
    });

    modal.querySelectorAll('.modal-close').forEach(function (closeButton) {
        closeButton.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });

    searchInput.addEventListener('input', function (event) {
        var query = event.target.value.trim();

        if (searchTimer) {
            window.clearTimeout(searchTimer);
        }

        if (query.length < 2) {
            hideSearchDropdown();
            return;
        }

        searchTimer = window.setTimeout(function () {
            searchUsers(query);
        }, 250);
    });

    document.addEventListener('click', function (event) {
        if (!modal.contains(event.target)) {
            hideSearchDropdown();
        }
    });

    form.addEventListener('submit', submitInvites);
    renderInvites();
})();
