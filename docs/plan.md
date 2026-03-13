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

- [ ] Fetch and display project member list with roles from `user_projects`.
- [ ] Add owner-only member role change actions.
- [ ] Add owner-only member removal functionality.
- [ ] Implement member list UI with role badges and action buttons.

### Phase 2.4 - Project Invitations & Notifications

- [ ] Implement invite by email workflow with `project_invitations` table.
- [ ] Track invitation states (pending, accepted, declined).
- [ ] Display notification regarding incoming invitations on dashboard.
- [ ] Add accept/decline invitation actions and flows.
-

### Phase 3 - Documents and Versioning

- [ ] Upload PDF/DOCX documents within a project.
- [ ] Create first and subsequent `document_versions` correctly.
- [ ] Keep `documents.current_version_id` in sync.
- [ ] Show document list per project and document detail pages.
- [ ] Implement version history and current version metadata UI.

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
- Immediate next focus: complete project listing/details so newly created projects are fully navigable.
- You don't have to create multiple .md files for each phase, one summary file with clear sections for each phase is
  sufficient.