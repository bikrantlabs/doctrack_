(function () {
    var modalId = 'approve-version-modal';
    var modal = document.getElementById(modalId);
    if (!modal) {
        return;
    }

    var form = document.getElementById('approve-version-form');
    var submitButton = document.getElementById('approve-version-submit-btn');
    if (!form || !submitButton) {
        return;
    }

    function showToast(type, message) {
        if (window.DocTrackToast && typeof window.DocTrackToast.show === 'function') {
            window.DocTrackToast.show(type, message);
        }
    }

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('active');
        window.setTimeout(function () {
            submitButton.focus();
        }, 10);
    }

    function closeModal() {
        modal.classList.remove('active');
        modal.classList.add('hidden');
    }

    function setSubmitState(isSubmitting) {
        submitButton.disabled = isSubmitting;
        submitButton.textContent = isSubmitting ? 'Approving...' : 'Confirm Approval';
    }

    function submitApproval(event) {
        event.preventDefault();

        var approveUrl = form.getAttribute('data-approve-url') || form.getAttribute('action');
        var documentUrl = form.getAttribute('data-document-url') || window.location.pathname;
        var versionField = form.querySelector('input[name="version_id"]');
        if (!approveUrl || !versionField || !versionField.value) {
            showToast('error', 'Approval endpoint is missing.');
            return;
        }

        setSubmitState(true);

        fetch(approveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                version_id: Number(versionField.value)
            })
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
                    showToast('error', result.body.message || 'Could not approve this version.');
                    return;
                }

                showToast('success', result.body.message || 'Document version approved.');
                window.location.href = documentUrl;
            })
            .catch(function () {
                showToast('error', 'Could not approve this version.');
            })
            .finally(function () {
                setSubmitState(false);
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

    form.addEventListener('submit', submitApproval);
})();
