(function () {
    var modalId = 'upload-document-modal';
    var modal = document.getElementById(modalId);
    if (!modal) {
        return;
    }

    var form = document.getElementById('upload-document-form');
    var fileInput = document.getElementById('upload-document-file');
    var dropzone = document.getElementById('native-dropzone');
    var selectedFileLabel = document.getElementById('dropzone-selected-file');
    var submitButton = document.getElementById('upload-document-submit-btn');

    if (!form || !fileInput || !dropzone || !selectedFileLabel || !submitButton) {
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
            var titleInput = document.getElementById('upload-document-title');
            if (titleInput) {
                titleInput.focus();
            }
        }, 10);
    }

    function closeModal() {
        modal.classList.remove('active');
        modal.classList.add('hidden');
    }

    function setSubmitState(isLoading) {
        submitButton.disabled = isLoading;
        submitButton.textContent = isLoading ? 'Uploading...' : 'Upload Document';
    }

    function isAllowedFile(file) {
        if (!file || !file.name) {
            return false;
        }

        var extension = file.name.split('.').pop();
        if (!extension) {
            return false;
        }

        extension = extension.toLowerCase();
        return extension === 'pdf' || extension === 'docx';
    }

    function setSelectedFile(file) {
        if (!file) {
            selectedFileLabel.classList.add('hidden');
            selectedFileLabel.textContent = '';
            dropzone.classList.remove('has-file');
            return;
        }

        selectedFileLabel.classList.remove('hidden');
        selectedFileLabel.textContent = file.name;
        dropzone.classList.add('has-file');
    }

    function useFile(file) {
        if (!isAllowedFile(file)) {
            showToast('error', 'Only PDF and DOCX files are allowed.');
            fileInput.value = '';
            setSelectedFile(null);
            return;
        }

        setSelectedFile(file);
    }

    function submitUpload(event) {
        event.preventDefault();

        if (!fileInput.files || fileInput.files.length === 0) {
            showToast('error', 'Please choose a file to upload.');
            return;
        }

        var selected = fileInput.files[0];
        if (!isAllowedFile(selected)) {
            showToast('error', 'Only PDF and DOCX files are allowed.');
            return;
        }

        var uploadUrl = form.getAttribute('data-upload-url') || form.getAttribute('action');
        if (!uploadUrl) {
            showToast('error', 'Upload endpoint is missing.');
            return;
        }

        setSubmitState(true);

        var payload = new FormData(form);

        fetch(uploadUrl, {
            method: 'POST',
            body: payload,
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
                    showToast('error', result.body.message || 'Could not upload document.');
                    return;
                }

                showToast('success', result.body.message || 'Document uploaded successfully.');
                window.location.reload();
            })
            .catch(function () {
                showToast('error', 'Could not upload document.');
            })
            .finally(function () {
                setSubmitState(false);
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

    dropzone.addEventListener('click', function () {
        fileInput.click();
    });

    dropzone.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            fileInput.click();
        }
    });

    fileInput.addEventListener('change', function () {
        var file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
        useFile(file);
    });

    ['dragenter', 'dragover'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (event) {
            event.preventDefault();
            event.stopPropagation();
            dropzone.classList.add('drag-over');
        });
    });

    ['dragleave', 'dragend'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (event) {
            event.preventDefault();
            event.stopPropagation();
            dropzone.classList.remove('drag-over');
        });
    });

    dropzone.addEventListener('drop', function (event) {
        event.preventDefault();
        event.stopPropagation();
        dropzone.classList.remove('drag-over');

        var files = event.dataTransfer ? event.dataTransfer.files : null;
        if (!files || files.length === 0) {
            return;
        }

        var file = files[0];
        var transfer = new DataTransfer();
        transfer.items.add(file);
        fileInput.files = transfer.files;
        useFile(file);
    });

    form.addEventListener('submit', submitUpload);
})();

