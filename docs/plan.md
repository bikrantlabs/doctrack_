# Doctrack Plan

## What We Have Completed

### Phase 1 - Foundation and Authentication

- [x] Front controller routing via `public/index.php`.
- [x] Basic MVC structure (`Controller`, `View`, `Router`, helpers, DB connection).
- [x] Shared layout rendering with content injection in `views/layout.php`.
- [x] Registration and login with PHP sessions.
- [x] Password security using bcrypt (`password_hash` / `password_verify`).
- [x] Logout flow and auth-guarded redirect behavior.

### Phase 1.1 - Clean Architecture Refactor

- [x] Moved auth business rules from controllers into `AuthService`.
- [x] Moved user persistence into `UserRepository`.
- [x] Added duplicate email exception handling at repository layer.
- [x] Kept controllers focused on HTTP orchestration and redirects.

### Phase 1.2 - UX Notification Refactor

- [x] Replaced inline auth errors with toast notifications.
- [x] Added layout-level flash payload transport (`take_flash_messages`).
- [x] Added reusable toast runtime in `public/js/toast.js`.
- [x] Wired toast container globally in `views/layout.php`.

### Phase 2 - App Dashboard + Project Creation Foundation

- [x] Added authenticated app entry route (`/app`) and app dashboard rendering.
- [x] Updated post-login/post-register redirection to `/app`.
- [x] Fixed `SVGRenderer` path resolution to remove `Application` dependency.
- [x] Fixed sidebar active state to follow current URL.
- [x] Added Create Project modal trigger and modal wiring.
- [x] Added user search API for member invite autocomplete (`/app/users/search`).
- [x] Added project creation API (`/app/projects`) with invited members and roles.
- [x] Implemented `ProjectService` and `ProjectRepository` with transactional create flow.

---

## What We Will Build Next

### Phase 2.1 - Project Listing & Display

- [x] Added `getAllProjectsByUserId()` and count methods to `ProjectRepository`.
- [x] Added `fetchUserProjectsWithStats()` service method to enrich projects with document/member counts.
- [x] Updated `HomeController.appDashboard()` to fetch and pass projects to dashboard view.
- [x] Updated `project-card.php` component to dynamically render project data.
- [x] Updated dashboard view to loop through projects and render cards dynamically.
- [x] Added empty state display when no projects exist.
- [x] Added CSS styling for project card links, role badges (owner/editor/reviewer/viewer), and empty state.

### Phase 2.2 - Project Details & Navigation

- [x] Add project detail page (`/app/projects/{id}`).
- [x] Build `My Projects` view (projects owned or with edit role).
- [x] Build `Shared with Me` view (projects with viewer/reviewer role).
- [x] Implement project filtering and navigation between views.

### Phase 2.3 - Project Members Management

- [x] Fetch and display project member list with roles from `user_projects`.
- [x] Add owner-only member role change actions.
- [x] Add owner-only member removal functionality.
- [x] Implement member list UI with role badges and action buttons.

### Phase 2.4 - Project Invitations & Notifications

- [x] Implement invite by email workflow with `project_invitations` table.
- [x] Track invitation states (pending, accepted, declined).
- [x] Display notification regarding incoming invitations on dashboard.
- [x] The notification should be displayed under the `header-icon-btn` in `dashboard/index.php` as a dropdown.
- [x] Don't implement the `notification` table and related logic for now, just show pending invitations in the dropdown.
- [x] The notification dropdown should reside in its own file `views/app/components/notifications-dropdown.php` and be
  accompanied by JavaScript.
- [x] Add accept/decline invitation actions and flows.

Got it. Here's the rewritten Phase 3:

---

### Phase 3 - Documents, Versioning, and Review Workflow(`/app/projects/{projectId}/` page)

#### Phase 3.1 - Document Upload

Pre-Note: The headless page prototype for `/app/project/{projectId}` page is kept under `docs/project_id page.png`.
Again, this design is only the prototype of how page should look like, align with our current sidebar/and app theme to
implement the page design. The `app/projects/show.php` is responsible for specific project page. Extract the
`project-members-card` into separate component.

- [x] Add document upload button within `/app/project/{projectId}`.
- [x] A modal should allow uploading a file (PDF/DOCX) along with a title edit input.
- [x] Avoid using external library for dropzone; dropzone should be implemented natively with html, css and js.
- [x] Submitting the modal creates the document and version 1 automatically.
- [x] Uploaded files should be stored securely outside the public directory.
- [x] Create separate files for different components of the document upload feature.

File Storage Strategy:
> Create a directory outside your public/ folder, something like storage/documents/, and save files there. Since it's
> outside webroot, files aren't directly accessible via URL, which is the right behavior — you want every file download
> to
> go through PHP so you can auth-check it first.
> When saving a file, don't use the original filename. Generate something like {documentId}_{versionNumber}_
> {timestamp}.pdf or just a UUID, and store the original filename separately in the database if you want to show it to
> the
> user. This avoids collisions and path traversal issues.
> For serving the file back, you make a route like /app/documents/file/{versionId}, PHP reads the file from
> storage/documents/, sets the right Content-Type header (application/pdf or
> application/vnd.openxmlformats-officedocument.wordprocessingml.document), and streams it with readfile(). The browser
> either renders it inline or downloads it depending on whether you set Content-Disposition: inline or attachment.

#### Phase 3.2 - Document Listing and Detail Page

- [ ] List all documents within a project in `/app/project/{projectId}`, each showing its current version status and
  file type(already done).
- [x] Clicking a document opens its detail page `/app/project/{projectId}/{documentId}`.
- [x] Left side of the page should show include document preview/viewer.
- [x] Use any open-source JavaScript library of your choice for PDF/DOCX preview.
- [x] Build document viewer interface in such way that later if new document type comes in, we can easily use any viewer
  for that file type.
- [x] Don't use sidebar for this page, left side->document viewer, right side->document details, versions, threads,
  comments, and review actions.
- [x] The detail page shows the current version by default, with a way to navigate to previous versions.
- [x] Files should be servable/downloadable from the detail page after an auth check.

#### Phase 3.3 - Review Threads

Pre-Note: The headless prototype is under `docs/project_id_document_id page.png`. The "Review Threads" section on the
right side of the document detail page should list all threads related to the document, regardless of version. Each
thread should indicate which version(s) it is open on and allow filtering by version.

- [x] Add "Create new thread" button on the document detail page in "Review Threads" section, visible to reviewers and
  owners.
- [x] Reviewer and Owner can open review threads on a document, each thread representing a distinct issue or concern.
- [x] Threads belong to the document, not a specific version, and persist across all versions.
- [x] All threads are visible on the document detail page regardless of which version is being viewed.
-

#### Phase 3.4 - Review Comments

Pre-Note: The headless prototype is under `docs/document_id review_comments section.png`. When clicked on each thread,
the "Review Threads" section should be changed to show the comments inside the thread. Each comment should indicate
which version it was made on, and when viewing a specific version, comments from other versions should be visually
de-emphasized but still accessible. Each comment should indicate page number, author, timestamp, content and version as
in the image. Everyone can add comments in threads, again as the prototype there should be "Mark as resolved" button
only for reviewers and owners. For other users, the button should be hidden. For adding new comment, there should be a
text area for content and a option to choose page number. Commenter can either choose "Current Page", or specify page
number.

- [ ] Anyone can post comments inside a thread, tied to the specific version they are currently viewing and a page
  number.
- [ ] When viewing a thread, comments should be grouped or labeled by which version they were made on.
- [ ] Only users with reviewer, editor, or owner role in the project can comment.
- [ ] There should be "Back" button as shown in prototype to go back to thread list from comment view.
- [ ] Each comment should indicate page number, author, timestamp, content and version as in the image.
- [ ] When viewing a specific version, comments from other versions should be visually de-emphasized but still
  accessible.
- [ ] There should be a text area for adding new comment, and an option to choose page number (either "Current Page" or
  specify page number).
- [ ] Only reviewers and owners can see the "Mark as resolved" button for each thread, and can mark a thread as
  resolved. Other users should not see this button.
- [ ] Marking a thread as resolved should record the user and timestamp of resolution, and visually indicate the
  thread's resolved status in the thread list and comment view.

#### Phase 3.5 - New Version Upload

- [ ] From the document detail page, allow uploading a new version of the document.
- [ ] A modal should accept the new file and an optional change summary.
- [ ] Only `owner` and `editor` roles can upload new versions.
- [ ] Uploading automatically increments the version number and updates the document's current version.
- [ ] A version that is locked cannot be overwritten — uploading a new version always creates a new entry.

#### Phase 3.6 - Version Submission and Approval

- [ ] A document owner or editor can submit a version for review, which locks it from further changes.
- [ ] A project owner can approve a locked version.
- [ ] Approval should only be possible if the version is currently under review.

#### Phase 3.7 - Review Status Across Versions

- [ ] Each thread tracks whether it is open or resolved per version.
- [ ] When a new version is uploaded, all unresolved threads from the previous version carry forward as open on the new
  version.
- [ ] A thread can be marked resolved on the current version by the thread creator, editor, or owner.

#### Phase 3.8 - Review Completeness and Approval Gate

- [ ] A version cannot be approved while it has open unresolved threads.
- [ ] The document detail page should surface how many open threads remain on the current version.
- [ ] A project owner should have the option to force-approve, bypassing the open thread check.

### Phase 4 - Review Workflow Core

- [ ] Submit version for review (`under_review`, `is_locked = true`).
- [ ] Enforce lock behavior (no overwrite of locked versions).
- [ ] Add owner approval flow (`approved`) with gate checks.
- [ ] Enforce role-based permissions for owner/editor/reviewer/viewer actions.

### Phase 5 - Threads, Comments, and Review Status

- [ ] Create review threads per document.
- [ ] Add comments tied to `document_version_id` and `page_number`.
- [ ] Track per-version thread state in `review_status`.
- [ ] Carry unresolved threads forward automatically to new versions.
- [ ] Allow reviewer/owner resolution with `resolved_by` and `resolved_at`.

### Phase 6 - Cumulative Review View

- [ ] Build single source-of-truth timeline on document page.
- [ ] Group comments by thread across versions.
- [ ] Show version labels and per-version resolution state clearly.
- [ ] Add quick filters (open/resolved, by version, by reviewer).

### Phase 7 - Quality, Security, and Hardening

- [ ] Add CSRF protection for forms and APIs.
- [ ] Add input normalization and stricter validation.
- [ ] Add file upload hardening and safe storage strategy.
- [ ] Add integration tests for auth/project/document/review flows.
- [ ] Add error logging, environment config strategy, and deployment checklist.

---

## Notes

- Database schema remains unchanged and is used as provided in `schema.sql`.
- Architecture direction: controller -> service -> repository, with thin views and reusable UI components.
- You don't have to create multiple .md files for each phase, one summary file with clear sections for each phase is
  sufficient.
- Don't build the UI part in single `.php` file, break into smaller chunks and components.