<?php


?>
<div class="modal-overlay hidden" id="create-project-modal">
    <div class="modal modal-lg">

        <div class="modal-header">
            <h2 class="modal-title">Create New Project</h2>
            <button class="modal-close" aria-label="Close modal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form
            id="create-project-form"
            class="modal-form"
            method="post"
            action="<?= e(url('/app/projects')) ?>"
            data-search-url="<?= e(url('/app/users/search')) ?>"
            data-create-url="<?= e(url('/app/projects')) ?>"
            novalidate
        >
            <div class="modal-body">

                <!-- ── Section 1: Project Details ─────────────────── -->
                <div class="modal-section">
                    <h3 class="modal-section-title">Project Details</h3>

                    <div class="form-group">
                        <label for="project-title" class="form-label">
                            Project Title <span class="required">*</span>
                        </label>
                        <input type="text"
                               id="project-title"
                               name="title"
                               class="form-field"
                               placeholder="e.g. Q1 Financial Reports"
                               autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="project-description" class="form-label">Description</label>
                        <textarea id="project-description"
                                  name="description"
                                  class="form-textarea"
                                  placeholder="What is this project about?"
                                  rows="3"></textarea>
                    </div>
                </div>

                <!-- ── Section 2: Invite Members ──────────────────── -->
                <div class="modal-section">
                    <h3 class="modal-section-title">
                        Invite Members
                        <span class="optional">(optional)</span>
                    </h3>

                    <div class="form-group">
                        <label for="invite-search-input" class="form-label">Search by name or email</label>
                        <div class="user-search-wrapper">
                            <div class="user-search-input-wrap">
                                <svg class="user-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"/>
                                    <path d="M21 21l-4.35-4.35"/>
                                </svg>
                                <input type="text"
                                       id="invite-search-input"
                                       class="form-field user-search-input"
                                       placeholder="Search users..."
                                       autocomplete="off">
                            </div>
                            <div class="user-search-dropdown" id="invite-search-dropdown"></div>
                        </div>
                    </div>

                    <!-- Invited list -->
                    <div id="invite-empty-state" class="invite-empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <p>No members invited yet. Search above to add people.</p>
                    </div>

                    <div id="invite-list" class="invite-list hidden"></div>

                    <!-- Role reference card -->
                    <div class="role-info-card">
                        <h4 class="role-info-title">Role Permissions</h4>
                        <div class="role-info-list">
                            <div class="role-info-item">
                                <span class="role-badge role-viewer">Viewer</span>
                                <span class="role-description">Can view documents and comments</span>
                            </div>
                            <div class="role-info-item">
                                <span class="role-badge role-reviewer">Reviewer</span>
                                <span class="role-description">Can view, comment, and approve documents</span>
                            </div>
                            <div class="role-info-item">
                                <span class="role-badge role-editor">Editor</span>
                                <span class="role-description">Can view, edit, upload, and manage documents</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /.modal-body -->

            <div class="modal-footer">
                <button type="button" class="btn btn-outline modal-close">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Create Project
                </button>
            </div>
        </form>

    </div>
</div>
