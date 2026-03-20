(function () {
    var root = document.querySelector('[data-review-threads-root="1"]');
    if (!root) {
        return;
    }

    var createUrl = root.getAttribute('data-create-thread-url') || '';
    var commentUrlTemplate = root.getAttribute('data-comment-url-template') || '';
    var resolveUrlTemplate = root.getAttribute('data-resolve-url-template') || '';
    var createToggle = root.querySelector('[data-thread-create-toggle="1"]');
    var createForm = root.querySelector('[data-thread-create-form="1"]');
    var createCancel = root.querySelector('[data-thread-create-cancel="1"]');
    var createSubmit = root.querySelector('[data-thread-create-submit="1"]');
    var titleInput = root.querySelector('#thread-title-input');
    var commentInput = root.querySelector('#thread-comment-input');
    var pageNumberInput = root.querySelector('#thread-page-number-input');
    var selectedVersionId = (root.getAttribute('data-selected-version-id') || '').trim();

    var statusSelect = root.querySelector('[data-thread-status-select="1"]');
    var threadItems = root.querySelectorAll('[data-thread-item="1"]');
    var emptyState = root.querySelector('[data-thread-empty-state="1"]');
    var filterEmptyState = root.querySelector('[data-thread-filter-empty="1"]');
    var listHeader = root.querySelector('[data-thread-list-header="1"]');
    var modeHeader = root.querySelector('[data-thread-mode-header="1"]');
    var modeTitle = root.querySelector('[data-thread-mode-title="1"]');
    var modeStatus = root.querySelector('[data-thread-mode-status="1"]');
    var modeMeta = root.querySelector('[data-thread-mode-meta="1"]');
    var modeBack = root.querySelector('[data-thread-mode-back="1"]');
    var listView = root.querySelector('[data-thread-list-view="1"]');
    var detailView = root.querySelector('[data-thread-detail-view="1"]');
    var uiStateKey = 'doctrack.reviewThreads.state:' + window.location.pathname + window.location.search;

    var activeStatusFilter = statusSelect ? (statusSelect.value || 'all') : 'all';

    function showToast(type, message) {
        if (window.DocTrackToast && typeof window.DocTrackToast.show === 'function') {
            window.DocTrackToast.show(type, message);
        }
    }

    function setCreateFormVisible(visible) {
        if (!createForm) {
            return;
        }

        createForm.classList.toggle('hidden', !visible);
        if (visible && titleInput) {
            titleInput.focus();
        }
    }

    function setSubmitState(isSubmitting) {
        if (!createSubmit) {
            return;
        }

        createSubmit.disabled = isSubmitting;
        createSubmit.textContent = isSubmitting ? 'Creating...' : 'Create';
    }

    function saveUiState(state) {
        try {
            sessionStorage.setItem(uiStateKey, JSON.stringify(state));
        } catch (error) {
            // Ignore storage failures (private mode / blocked storage).
        }
    }

    function readUiState() {
        try {
            var raw = sessionStorage.getItem(uiStateKey);
            if (!raw) {
                return null;
            }

            var parsed = JSON.parse(raw);
            return parsed && typeof parsed === 'object' ? parsed : null;
        } catch (error) {
            return null;
        }
    }

    function clearUiState() {
        try {
            sessionStorage.removeItem(uiStateKey);
        } catch (error) {
            // Ignore storage failures.
        }
    }

    function setModeHeaderDetail(status, creator) {
        if (modeStatus) {
            var normalized = (status || 'open').toLowerCase();
            modeStatus.textContent = normalized;
            modeStatus.classList.remove('hidden', 'status-open', 'status-resolved', 'status-draft');
            modeStatus.classList.add('status-' + normalized);
        }

        if (modeMeta) {
            modeMeta.textContent = creator ? ('By ' + creator) : '';
            modeMeta.classList.toggle('hidden', !creator);
        }
    }

    function clearModeHeaderDetail() {
        if (modeStatus) {
            modeStatus.classList.add('hidden');
            modeStatus.classList.remove('status-open', 'status-resolved', 'status-draft');
        }

        if (modeMeta) {
            modeMeta.textContent = '';
            modeMeta.classList.add('hidden');
        }
    }

    function animatePanel(node) {
        if (!node) {
            return;
        }

        node.classList.remove('thread-animate-in');
        void node.offsetWidth;
        node.classList.add('thread-animate-in');
    }

    function showOnly(node) {
        var panels = [listView, createForm, detailView];
        panels.forEach(function (panel) {
            if (!panel) {
                return;
            }

            panel.classList.toggle('hidden', panel !== node);
        });

        if (node) {
            animatePanel(node);
        }
    }

    function showThreadDetailPanel(threadId) {
        root.querySelectorAll('[data-thread-detail-panel]').forEach(function (panel) {
            var isTarget = panel.getAttribute('data-thread-detail-panel') === String(threadId);
            panel.classList.toggle('hidden', !isTarget);
        });
    }

    function setMode(mode, options) {
        options = options || {};

        var isList = mode === 'list';
        var isCreate = mode === 'create';
        var isDetail = mode === 'detail';

        if (listHeader) {
            listHeader.classList.toggle('hidden', !isList);
        }

        if (modeHeader) {
            modeHeader.classList.toggle('hidden', isList);
        }

        if (modeTitle && !isList) {
            modeTitle.textContent = options.title || (isCreate ? 'Create New Thread' : 'Review Thread');
        }

        if (isDetail) {
            setModeHeaderDetail(options.status || 'open', options.creator || '');
        } else {
            clearModeHeaderDetail();
        }

        if (!options.skipPersist) {
            if (isDetail && options.threadId) {
                saveUiState({
                    mode: 'detail',
                    threadId: String(options.threadId),
                    title: options.title || 'Review Thread',
                    status: options.status || 'open',
                    creator: options.creator || ''
                });
            } else if (isCreate) {
                saveUiState({mode: 'create'});
            } else {
                clearUiState();
            }
        }

        if (isList) {
            showOnly(listView);
            showThreadDetailPanel('');
            return;
        }

        if (isCreate) {
            showOnly(createForm);
            showThreadDetailPanel('');
            if (titleInput) {
                titleInput.focus();
            }
            return;
        }

        if (isDetail) {
            showOnly(detailView);
            showThreadDetailPanel(options.threadId || '');
        }
    }

    function buildEndpoint(template, threadId) {
        return template.replace('__THREAD_ID__', String(threadId));
    }

    function getCurrentViewerPage() {
        var pageInput = document.querySelector('[data-pdf-page-input="1"]');
        if (!pageInput) {
            return 1;
        }

        var page = Number(pageInput.value);
        return Number.isInteger(page) && page > 0 ? page : 1;
    }

    function jumpViewerToPage(pageNumber) {
        var viewerRoot = document.querySelector('[data-document-viewer="1"]');
        if (!viewerRoot) {
            showToast('error', 'Document viewer is not available on this page.');
            return;
        }

        viewerRoot.dispatchEvent(new CustomEvent('doctrack:viewer:goto-page', {
            detail: {page: pageNumber}
        }));
    }

    function setCommentSubmitState(threadId, isSubmitting) {
        var submit = root.querySelector('[data-thread-comment-submit="' + String(threadId) + '"]');
        if (!submit) {
            return;
        }

        submit.disabled = isSubmitting;
        submit.textContent = isSubmitting ? 'Adding...' : 'Add Comment';
    }

    function setResolveState(threadId, isSubmitting) {
        var resolveButton = root.querySelector('[data-thread-resolve-btn="' + String(threadId) + '"]');
        if (!resolveButton) {
            return;
        }

        resolveButton.disabled = isSubmitting;
        resolveButton.textContent = isSubmitting ? 'Resolving...' : 'Mark as Resolved';
    }

    function applyFilters() {
        var visibleCount = 0;

        threadItems.forEach(function (item) {
            var status = item.getAttribute('data-thread-status') || 'resolved';
            var visible = activeStatusFilter === 'all' || status === activeStatusFilter;

            item.classList.toggle('hidden', !visible);
            if (visible) {
                visibleCount++;
            }
        });

        if (filterEmptyState) {
            filterEmptyState.classList.toggle('hidden', visibleCount > 0 || threadItems.length === 0);
        }

        if (emptyState && threadItems.length > 0) {
            emptyState.classList.add('hidden');
        }
    }

    if (statusSelect) {
        statusSelect.addEventListener('change', function () {
            activeStatusFilter = statusSelect.value || 'all';
            applyFilters();
        });
    }

    if (createToggle && createForm) {
        createToggle.addEventListener('click', function () {
            setCreateFormVisible(true);
            setMode('create', {title: 'Create New Thread'});
        });
    }

    if (createCancel && createForm) {
        createCancel.addEventListener('click', function () {
            setCreateFormVisible(false);
            setMode('list');
        });
    }

    if (createForm) {
        createForm.addEventListener('submit', function (event) {
            event.preventDefault();

            if (!createUrl) {
                showToast('error', 'Create thread endpoint is missing.');
                return;
            }

            var title = titleInput ? titleInput.value.trim() : '';
            var comment = commentInput ? commentInput.value.trim() : '';
            var pageNumber = pageNumberInput ? Number(pageNumberInput.value) : 0;

            if (!title) {
                showToast('error', 'Thread title is required.');
                return;
            }

            if (!comment) {
                showToast('error', 'First comment is required.');
                return;
            }

            if (!Number.isInteger(pageNumber) || pageNumber <= 0) {
                showToast('error', 'Page number must be a positive whole number.');
                return;
            }

            if (!selectedVersionId) {
                showToast('error', 'Selected version is missing. Reload and try again.');
                return;
            }

            setSubmitState(true);

            fetch(createUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    title: title,
                    comment: comment,
                    page_number: pageNumber,
                    version_id: selectedVersionId
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
                        showToast('error', result.body.message || 'Could not create review thread.');
                        return;
                    }

                    showToast('success', result.body.message || 'Review thread created.');
                    window.location.reload();
                })
                .catch(function () {
                    showToast('error', 'Could not create review thread.');
                })
                .finally(function () {
                    setSubmitState(false);
                });
        });
    }

    root.querySelectorAll('[data-thread-open-btn]').forEach(function (button) {
        button.addEventListener('click', function () {
            var threadId = button.getAttribute('data-thread-open-btn') || '';
            var threadTitle = button.getAttribute('data-thread-title') || 'Review Thread';
            var threadStatus = button.getAttribute('data-thread-selected-status') || 'open';
            var threadCreator = button.getAttribute('data-thread-created-by') || '';
            if (!threadId) {
                return;
            }

            setCreateFormVisible(false);
            setMode('detail', {
                threadId: threadId,
                title: threadTitle,
                status: threadStatus,
                creator: threadCreator
            });
        });
    });

    if (modeBack) {
        modeBack.addEventListener('click', function () {
            setCreateFormVisible(false);
            setMode('list');
        });
    }

    root.querySelectorAll('[data-thread-page-jump]').forEach(function (button) {
        button.addEventListener('click', function () {
            var page = Number(button.getAttribute('data-thread-page-jump') || '0');
            if (!Number.isInteger(page) || page <= 0) {
                return;
            }

            jumpViewerToPage(page);
        });
    });

    root.querySelectorAll('[data-thread-page-mode]').forEach(function (select) {
        select.addEventListener('change', function () {
            var threadId = select.getAttribute('data-thread-page-mode') || '';
            var customWrap = root.querySelector('[data-thread-custom-page-wrap="' + threadId + '"]');
            if (!customWrap) {
                return;
            }

            customWrap.classList.toggle('hidden', select.value !== 'custom');
        });
    });

    root.querySelectorAll('[data-thread-comment-form]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            var threadId = form.getAttribute('data-thread-comment-form') || '';
            if (!threadId) {
                return;
            }

            if (!commentUrlTemplate) {
                showToast('error', 'Comment endpoint is missing.');
                return;
            }

            var commentField = form.querySelector('textarea[name="comment"]');
            var modeField = form.querySelector('[data-thread-page-mode="' + threadId + '"]');
            var customPageField = form.querySelector('[data-thread-custom-page="' + threadId + '"]');
            var commentValue = commentField ? commentField.value.trim() : '';

            if (!commentValue) {
                showToast('error', 'Comment is required.');
                return;
            }

            var pageNumber = 0;
            if (modeField && modeField.value === 'custom') {
                pageNumber = customPageField ? Number(customPageField.value) : 0;
            } else {
                pageNumber = getCurrentViewerPage();
            }

            if (!Number.isInteger(pageNumber) || pageNumber <= 0) {
                showToast('error', 'Page number must be a positive whole number.');
                return;
            }

            if (!selectedVersionId) {
                showToast('error', 'Selected version is missing. Reload and try again.');
                return;
            }

            setCommentSubmitState(threadId, true);

            fetch(buildEndpoint(commentUrlTemplate, threadId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    comment: commentValue,
                    page_number: pageNumber,
                    version_id: selectedVersionId
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
                        showToast('error', result.body.message || 'Could not add comment.');
                        return;
                    }

                    showToast('success', result.body.message || 'Comment added.');
                    saveUiState({
                        mode: 'detail',
                        threadId: String(threadId),
                        title: modeTitle ? modeTitle.textContent : 'Review Thread',
                        status: modeStatus ? modeStatus.textContent : 'open',
                        creator: modeMeta ? (modeMeta.textContent || '').replace(/^By\s+/, '') : ''
                    });
                    window.location.reload();
                })
                .catch(function () {
                    showToast('error', 'Could not add comment.');
                })
                .finally(function () {
                    setCommentSubmitState(threadId, false);
                });
        });
    });

    root.querySelectorAll('[data-thread-resolve-btn]').forEach(function (button) {
        button.addEventListener('click', function () {
            var threadId = button.getAttribute('data-thread-resolve-btn') || '';
            if (!threadId) {
                return;
            }

            if (!resolveUrlTemplate) {
                showToast('error', 'Resolve endpoint is missing.');
                return;
            }

            if (!selectedVersionId) {
                showToast('error', 'Selected version is missing. Reload and try again.');
                return;
            }

            setResolveState(threadId, true);

            fetch(buildEndpoint(resolveUrlTemplate, threadId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    version_id: selectedVersionId
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
                        showToast('error', result.body.message || 'Could not resolve thread.');
                        return;
                    }

                    showToast('success', result.body.message || 'Thread marked as resolved.');
                    saveUiState({
                        mode: 'detail',
                        threadId: String(threadId),
                        title: modeTitle ? modeTitle.textContent : 'Review Thread',
                        status: 'resolved',
                        creator: modeMeta ? (modeMeta.textContent || '').replace(/^By\s+/, '') : ''
                    });
                    window.location.reload();
                })
                .catch(function () {
                    showToast('error', 'Could not resolve thread.');
                })
                .finally(function () {
                    setResolveState(threadId, false);
                });
        });
    });

    applyFilters();

    var initialUiState = readUiState();
    if (initialUiState && initialUiState.mode === 'detail' && initialUiState.threadId) {
        var detailPanel = root.querySelector('[data-thread-detail-panel="' + initialUiState.threadId + '"]');
        if (detailPanel) {
            setMode('detail', {
                threadId: initialUiState.threadId,
                title: initialUiState.title || 'Review Thread',
                status: initialUiState.status || 'open',
                creator: initialUiState.creator || '',
                skipPersist: true
            });
            return;
        }
    }

    setMode('list', {skipPersist: true});
})();

