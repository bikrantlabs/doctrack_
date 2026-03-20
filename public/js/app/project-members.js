(function () {
    var root = document.querySelector('[data-members-root="1"]');
    if (!root) {
        return;
    }

    var updateUrlTemplate = root.getAttribute('data-update-url-template') || '';
    var removeUrlTemplate = root.getAttribute('data-remove-url-template') || '';

    function toast(type, message) {
        if (window.DocTrackToast && typeof window.DocTrackToast.show === 'function') {
            window.DocTrackToast.show(type, message);
        }
    }

    function endpoint(template, memberId) {
        return template.replace('__MEMBER_ID__', String(memberId));
    }

    function refreshMemberCount() {
        var countLabel = root.querySelector('.project-members-header p');
        if (!countLabel) {
            return;
        }

        var total = root.querySelectorAll('tbody tr').length;
        countLabel.textContent = total + ' ' + (total === 1 ? 'member' : 'members') + ' in this project';
    }

    function updateRoleBadge(memberId, roleValue) {
        var badge = root.querySelector('[data-role-badge="' + String(memberId) + '"]');
        if (!badge) {
            return;
        }

        badge.classList.remove('badge-editor', 'badge-reviewer', 'badge-viewer');
        badge.classList.add('badge-' + roleValue);
        badge.textContent = roleValue.charAt(0).toUpperCase() + roleValue.slice(1);
    }

    function updateMemberRole(memberId, nextRole, selectElement, previousRole) {
        if (!updateUrlTemplate) {
            return;
        }

        fetch(endpoint(updateUrlTemplate, memberId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ role: nextRole })
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
                    selectElement.value = previousRole;
                    selectElement.setAttribute('data-current-role', previousRole);
                    toast('error', result.body.message || 'Could not update member role.');
                    return;
                }

                updateRoleBadge(memberId, nextRole);
                selectElement.setAttribute('data-current-role', nextRole);
                toast('success', result.body.message || 'Member role updated successfully.');
            })
            .catch(function () {
                selectElement.value = previousRole;
                selectElement.setAttribute('data-current-role', previousRole);
                toast('error', 'Could not update member role.');
            });
    }

    function removeMember(memberId, rowElement) {
        if (!removeUrlTemplate) {
            return;
        }

        fetch(endpoint(removeUrlTemplate, memberId), {
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
                    toast('error', result.body.message || 'Could not remove member.');
                    return;
                }

                rowElement.parentNode.removeChild(rowElement);
                refreshMemberCount();
                toast('success', result.body.message || 'Member removed from project.');
            })
            .catch(function () {
                toast('error', 'Could not remove member.');
            });
    }

    root.querySelectorAll('.member-role-select').forEach(function (selectElement) {
        selectElement.addEventListener('change', function () {
            var memberId = Number(selectElement.getAttribute('data-member-id') || 0);
            if (!memberId) {
                return;
            }

            var nextRole = selectElement.value;
            var previousRole = selectElement.getAttribute('data-current-role') || '';

            if (!previousRole) {
                previousRole = nextRole;
            }

            if (previousRole === nextRole) {
                return;
            }

            updateMemberRole(memberId, nextRole, selectElement, previousRole);
        });

        selectElement.setAttribute('data-current-role', selectElement.value);
    });

    root.querySelectorAll('.member-remove-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            var memberId = Number(button.getAttribute('data-member-id') || 0);
            if (!memberId) {
                return;
            }

            var row = button.closest('tr');
            if (!row) {
                return;
            }

            if (!window.confirm('Remove this member from the project?')) {
                return;
            }

            removeMember(memberId, row);
        });
    });
})();

