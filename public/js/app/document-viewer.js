(function () {
    var root = document.querySelector('[data-document-viewer="1"]');
    if (!root) return;

    var fileUrl = root.getAttribute('data-file-url') || '';
    var fileType = (root.getAttribute('data-file-type') || '').toLowerCase();

    var loading = root.querySelector('[data-document-viewer-loading="1"]');
    var pdfTarget = root.querySelector('[data-document-viewer-pdf="1"]');
    var docxTarget = root.querySelector('[data-document-viewer-docx="1"]');
    var error = root.querySelector('[data-document-viewer-error="1"]');

    function setHidden(node, hidden) {
        if (!node) return;
        node.classList.toggle('hidden', hidden);
    }

    function showError(message) {
        if (error) error.textContent = message;
        setHidden(loading, true);
        setHidden(pdfTarget, true);
        setHidden(docxTarget, true);
        setHidden(error, false);
    }

    function finalize(target) {
        setHidden(loading, true);
        setHidden(pdfTarget, target !== 'pdf');
        setHidden(docxTarget, target !== 'docx');
        setHidden(error, true);
    }

    function renderPdf() {
        if (!pdfTarget || !window.pdfjsLib) {
            showError('PDF viewer failed to load.');
            return;
        }

        pdfjsLib.GlobalWorkerOptions.workerSrc =
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.4.168/pdf.worker.min.mjs';

        pdfjsLib.getDocument(fileUrl).promise
            .then(function (pdfDoc) {
                finalize('pdf');
                var totalPages = pdfDoc.numPages;
                var currentPage = 1;
                var renderedPages = {};
                var isProgrammaticScroll = false;
                var scrollSyncFrame = null;

                // Update page count display
                var pageCountEl = root.querySelector('[data-pdf-page-count="1"]');
                if (pageCountEl) pageCountEl.textContent = '/ ' + totalPages;

                function renderPage(pageNum) {
                    if (renderedPages[pageNum]) {
                        return Promise.resolve();
                    }

                    return pdfDoc.getPage(pageNum).then(function (page) {
                        var viewport = page.getViewport({scale: 1.5});

                        var canvas = document.createElement('canvas');
                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        canvas.style.display = 'block';
                        canvas.style.marginBottom = '8px';
                        canvas.style.width = '100%';
                        canvas.setAttribute('data-page-num', pageNum);
                        pdfTarget.appendChild(canvas);

                        renderedPages[pageNum] = canvas;

                        return page.render({
                            canvasContext: canvas.getContext('2d'),
                            viewport: viewport
                        }).promise;
                    });
                }

                function updatePageDisplay() {
                    var pageInput = root.querySelector('[data-pdf-page-input="1"]');
                    if (pageInput) pageInput.value = currentPage;

                    // Scroll to current page
                    var pageCanvas = pdfTarget.querySelector('[data-page-num="' + currentPage + '"]');
                    if (pageCanvas) {
                        pageCanvas.scrollIntoView({behavior: 'smooth', block: 'start'});
                    }
                }

                function syncPageFromScroll() {
                    if (isProgrammaticScroll) {
                        return;
                    }

                    var body = root.querySelector('.document-viewer-body');
                    if (!body) {
                        return;
                    }

                    var canvases = pdfTarget.querySelectorAll('canvas[data-page-num]');
                    if (!canvases.length) {
                        return;
                    }

                    var bodyRect = body.getBoundingClientRect();
                    var bestPage = currentPage;
                    var bestVisibleArea = -1;

                    for (var idx = 0; idx < canvases.length; idx++) {
                        var canvas = canvases[idx];
                        var rect = canvas.getBoundingClientRect();

                        var visibleTop = Math.max(rect.top, bodyRect.top);
                        var visibleBottom = Math.min(rect.bottom, bodyRect.bottom);
                        var visibleHeight = Math.max(0, visibleBottom - visibleTop);
                        var visibleArea = visibleHeight * rect.width;

                        if (visibleArea > bestVisibleArea) {
                            bestVisibleArea = visibleArea;
                            bestPage = parseInt(canvas.getAttribute('data-page-num') || '1', 10);
                        }
                    }

                    if (!isNaN(bestPage) && bestPage !== currentPage) {
                        currentPage = bestPage;
                        var pageInput = root.querySelector('[data-pdf-page-input="1"]');
                        if (pageInput) pageInput.value = currentPage;
                    }
                }

                function queueScrollSync() {
                    if (scrollSyncFrame !== null) {
                        return;
                    }

                    scrollSyncFrame = window.requestAnimationFrame(function () {
                        scrollSyncFrame = null;
                        syncPageFromScroll();
                    });
                }

                function goToPage(pageNum) {
                    pageNum = Math.max(1, Math.min(pageNum, totalPages));
                    currentPage = pageNum;
                    isProgrammaticScroll = true;
                    updatePageDisplay();

                    // Re-enable scroll sync after the smooth scroll settles.
                    window.setTimeout(function () {
                        isProgrammaticScroll = false;
                        syncPageFromScroll();
                    }, 300);
                }

                root.addEventListener('doctrack:viewer:goto-page', function (event) {
                    var requestedPage = event && event.detail ? Number(event.detail.page) : 0;
                    if (!Number.isInteger(requestedPage) || requestedPage <= 0) {
                        return;
                    }

                    goToPage(requestedPage);
                });

                // Render all pages initially
                var chain = Promise.resolve();
                for (var i = 1; i <= totalPages; i++) {
                    (function (pageNum) {
                        chain = chain.then(function () {
                            return renderPage(pageNum);
                        });
                    })(i);
                }

                // Setup control event listeners
                var prevBtn = root.querySelector('[data-pdf-prev-btn="1"]');
                var nextBtn = root.querySelector('[data-pdf-next-btn="1"]');
                var pageInput = root.querySelector('[data-pdf-page-input="1"]');
                var viewerBody = root.querySelector('.document-viewer-body');

                if (viewerBody) {
                    viewerBody.addEventListener('scroll', queueScrollSync, {passive: true});
                }

                if (prevBtn) {
                    prevBtn.addEventListener('click', function () {
                        goToPage(currentPage - 1);
                    });
                }

                if (nextBtn) {
                    nextBtn.addEventListener('click', function () {
                        goToPage(currentPage + 1);
                    });
                }

                if (pageInput) {
                    pageInput.addEventListener('change', function () {
                        var pageNum = parseInt(this.value, 10);
                        if (!isNaN(pageNum)) {
                            goToPage(pageNum);
                        }
                    });

                    pageInput.addEventListener('keypress', function (e) {
                        if (e.key === 'Enter') {
                            var pageNum = parseInt(this.value, 10);
                            if (!isNaN(pageNum)) {
                                goToPage(pageNum);
                            }
                        }
                    });
                }

                return chain.then(function () {
                    syncPageFromScroll();
                });
            })
            .catch(function () {
                showError('Unable to render PDF.');
            });
    }

    function renderDocx() {
        if (!docxTarget || !window.mammoth) {
            showError('DOCX viewer failed to load.');
            return;
        }

        fetch(fileUrl)
            .then(function (r) {
                return r.arrayBuffer();
            })
            .then(function (buf) {
                return window.mammoth.convertToHtml({arrayBuffer: buf});
            })
            .then(function (result) {
                docxTarget.innerHTML = result.value || '<p>No preview content.</p>';
                finalize('docx');
            })
            .catch(function () {
                showError('Unable to render DOCX preview.');
            });
    }

    if (!fileUrl) {
        showError('No file available for preview.');
        return;
    }
    if (fileType === 'pdf') {
        renderPdf();
        return;
    }
    if (fileType === 'docx') {
        renderDocx();
        return;
    }
    showError('Unsupported file type for preview.');
})();