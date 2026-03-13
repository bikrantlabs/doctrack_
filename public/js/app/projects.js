(function () {
    const modalId = 'create-project-modal';
    const modal = document.getElementById(modalId);
    if (!modal) {
        return;
    }

    const form = document.getElementById('create-project-form');
    const searchInput = document.getElementById('invite-search-input');
    const searchDropdown = document.getElementById('invite-search-dropdown');
    const inviteList = document.getElementById('invite-list');
    const inviteEmptyState = document.getElementById('invite-empty-state');

    if (!form || !searchInput || !searchDropdown || !inviteList || !inviteEmptyState) {
        return;
    }

    const selectedUsers = new Map();
    let searchTimer = null;
    let searchAbortController = null;

    function initials(name) {
        if (!name) {
            return '?';
        }

        const parts = name.trim().split(/\s+/).filter(Boolean);
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
            const titleInput = document.getElementById('project-title');
            if (titleInput) {
                titleInput.focus();
            }
        }, 10);
    }

    function closeModal() {
        modal.classList.remove('active');
        modal.classList.add('hidden');
        hideSearchDropdown();
    }

    function hideSearchDropdown() {
        searchDropdown.classList.remove('active');
        searchDropdown.innerHTML = '';
    }

    function renderInvites() {
        const users = Array.from(selectedUsers.values());
        inviteList.innerHTML = '';

        if (users.length === 0) {
            inviteList.classList.add('hidden');
            inviteEmptyState.classList.remove('hidden');
            return;
        }

        inviteList.classList.remove('hidden');
        inviteEmptyState.classList.add('hidden');

        users.forEach(function (user) {
            const item = document.createElement('div');
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

            const roleSelect = item.querySelector('.role-select');
            roleSelect.value = user.role;
            roleSelect.addEventListener('change', function (event) {
                const nextRole = event.target.value;
                const existing = selectedUsers.get(user.id);
                if (!existing) {
                    return;
                }
                existing.role = nextRole;
                selectedUsers.set(user.id, existing);
            });

            const removeButton = item.querySelector('.invite-remove');
            removeButton.addEventListener('click', function () {
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
            const empty = document.createElement('div');
            empty.className = 'user-search-empty';
            empty.textContent = 'No users found.';
            searchDropdown.appendChild(empty);
            searchDropdown.classList.add('active');
            return;
        }

        users.forEach(function (user) {
            if (selectedUsers.has(user.id)) {
                return;
            }

            const item = document.createElement('button');
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
        const endpoint = form.dataset.searchUrl;
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

    function submitProject(event) {
        event.preventDefault();

        const createUrl = form.dataset.createUrl;
        if (!createUrl) {
            return;
        }

        const payload = {
            title: (document.getElementById('project-title') || {value: ''}).value,
            description: (document.getElementById('project-description') || {value: ''}).value,
            members: Array.from(selectedUsers.values()).map(function (user) {
                return {
                    user_id: user.id,
                    role: user.role
                };
            })
        };

        fetch(createUrl, {
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
                        status: response.status,
                        body: body
                    };
                });
            })
            .then(function (result) {
                const body = result.body || {};
                if (result.status >= 200 && result.status < 300 && body.ok === true) {
                    if (window.DocTrackToast && typeof window.DocTrackToast.show === 'function') {
                        window.DocTrackToast.show('success', body.message || 'Project created successfully.');
                    }
                    window.location.reload();
                    return;
                }

                if (window.DocTrackToast && typeof window.DocTrackToast.show === 'function') {
                    window.DocTrackToast.show('error', body.message || 'Could not create project.');
                }
            })
            .catch(function () {
                if (window.DocTrackToast && typeof window.DocTrackToast.show === 'function') {
                    window.DocTrackToast.show('error', 'Could not create project.');
                }
            });
    }

    document.querySelectorAll('[data-modal="' + modalId + '"]').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            openModal();
        });
    });

    modal.querySelectorAll('.modal-close').forEach(function (closeButton) {
        closeButton.addEventListener('click', function () {
            closeModal();
        });
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
        const query = event.target.value.trim();

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

    form.addEventListener('submit', submitProject);
    renderInvites();
})();

