# DocTrack — Pre-Final Defense Guide

## 1. What is DocTrack?

DocTrack is a **web-based document review and approval system**. Think of it like Google Docs meets Trello — teams can upload documents (PDF/DOCX), invite members to review them, leave comments on specific pages, create new versions, and finally approve the document.

It is built using **PHP** (a server-side programming language) and **MySQL** (a database). No frameworks like Laravel or React were used — everything is written from scratch using pure PHP, vanilla JavaScript, and plain CSS.

---

## 2. System Architecture (How Parts Connect)

The system follows a pattern called **MVC** — Model-View-Controller — which is a standard way of organizing web applications:

```
┌──────────────────────────────────────────────────┐
│                   BROWSER                        │
│        (Chrome, Edge, etc.)                      │
└────────────────────┬─────────────────────────────┘
                     │  HTTP Request (URL)
                     ▼
┌──────────────────────────────────────────────────┐
│              public/index.php                    │
│           (Front Controller / Entry Point)       │
└────────────────────┬─────────────────────────────┘
                     │
                     ▼
┌──────────────────────────────────────────────────┐
│              app/Core/Router.php                 │
│           (Looks at URL, decides which           │
│            Controller to call)                   │
└────────────────────┬─────────────────────────────┘
                     │
                     ▼
┌──────────────────────────────────────────────────┐
│           app/Controllers/*.php                  │
│           (Receives request, calls               │
│            the right Service)                    │
└────────────────────┬─────────────────────────────┘
                     │
                     ▼
┌──────────────────────────────────────────────────┐
│            app/Services/*.php                    │
│           (Business logic — rules,               │
│            validations, permissions)             │
└────┬──────────────────────────────┬──────────────┘
     │                              │
     ▼                              ▼
┌──────────────┐          ┌──────────────────────┐
│ Repositories │          │  NotificationService │
│ (SQL queries)│          │  (Sends alerts)       │
└──────┬───────┘          └──────────────────────┘
       │
       ▼
┌──────────────────────────────────────────────────┐
│            app/Core/Database.php                 │
│           (PDO — connects to MySQL)              │
└──────────────────────────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────────────────┐
│              MySQL Database                      │
│            (8 tables of data)                    │
└──────────────────────────────────────────────────┘

Then back up:
Service → Controller → View (HTML page) → Browser
```

---

## 3. Breaking Down Each Layer

### A. Entry Point — `public/index.php`

This is the **front door** of the application. Every single request goes through this file first. It does four things:

1. **Starts a session** — PHP sessions let us keep the user logged in across pages using cookies
2. **Auto-loads classes** — Whenever we use a class like `ProjectController`, PHP automatically finds and loads the correct file without us manually including it
3. **Defines routes** — It tells the Router: "If someone visits `/login`, call `AuthController::showLogin`. If they visit `/app/projects/5`, call `ProjectController::show` with id=5"
4. **Dispatches the request** — Hands over control to the Router

### B. Router — `app/Core/Router.php`

The Router is like a **switchboard operator**. Its job is simple:

- Look at the URL (e.g., `/app/projects/42`)
- Look at the HTTP method (GET = viewing a page, POST = submitting a form, DELETE = deleting something)
- Find a matching route pattern
- Extract parameters from the URL (like `42` from `/app/projects/42`)
- Call the right Controller method with those parameters
- If no route matches, show a 404 page

**Hidden detail:** The Router uses **regex pattern matching** to extract `{id}` from URL patterns like `/app/projects/{id}`. It converts `{id}` into a regex capture group `(?P<id>[^/]+)`.

### C. Controllers — `app/Controllers/`

Controllers are **thin middlemen**. They:

1. Check if the user is logged in (via `Auth::user()`)
2. Read input from the request (form data, JSON body, URL parameters)
3. Call the appropriate Service method
4. Send back a response — either an HTML page (via `render()`) or JSON data (for AJAX calls)

There are three controllers:
- **`AuthController`** — Handles login, registration, logout
- **`HomeController`** — Shows landing page and main dashboard
- **`ProjectController`** — The biggest one. Handles everything related to projects, documents, reviews, members, invitations, notifications. This is the **heart of the application**.

**Hidden detail:** `ProjectController` has a private `readPayload()` method that can read data from both regular forms (`$_POST`) AND JSON requests (`php://input`). This dual-support is important because the frontend JavaScript sends data as JSON.

### D. Services — `app/Services/`

Services contain **business logic** — the actual rules of the application. This is the "brain" layer. Controllers are thin, Services are "fat" (they contain the real work).

Four services:
- **`AuthService`** — Validates registration (checks for duplicate emails), hashes passwords using bcrypt, verifies login credentials
- **`ProjectService`** — Creates projects, manages members, handles invitations (invite/accept/decline), changes roles, removes members
- **`DocumentService`** — Handles document uploads, versioning, review threads, comments, approvals, file streaming
- **`NotificationService`** — Creates and retrieves notifications for users

**Hidden detail #1:** Services check permissions on EVERY operation. Before letting someone delete a project or approve a document, the service calls a method to check what role the user has in that project (`getUserRoleInProject`). Only `owner` level can do certain things, `editor` level can do others, and `viewer` can barely do anything.

**Hidden detail #2:** When uploading a new document version, the service can carry over existing review threads from the previous version. The frontend lets you select which threads to continue, and the service marks them as "marked_for_review" in the new version.

### E. Repositories — `app/Repositories/`

Repositories are **SQL query writers**. They contain no business logic — only raw database queries using PHP's PDO (PHP Data Objects) with prepared statements (which prevent SQL injection attacks).

Five repositories:
- **`UserRepository`** — Find users by email, search users by name
- **`ProjectRepository`** — Create/find/delete projects, manage members and invitations
- **`DocumentRepository`** — Document CRUD, version management, thread/comment queries
- **`InvitationRepository`** — Invitation queries
- **`NotificationRepository`** — Notification queries

**Hidden detail:** The repositories use **transactions** — `beginTransaction()`, `commit()`, `rollBack()`. When creating a project, for example, the system needs to insert into BOTH the `projects` table AND the `user_projects` table. If one insert fails, the transaction rolls back both so the database is never left in an inconsistent state.

### F. Views — `views/` folder

Views are **HTML templates** mixed with PHP. They don't contain logic — they just display data that was passed to them. The `View::render()` method:

1. Takes the view file and variables
2. Extracts variables so they become available as `$user`, `$project`, etc.
3. Captures the HTML output into a buffer
4. Loads a `layout.php` that wraps the content with headers, navigation, CSS, and JavaScript

Views are organized:
- `layout.php` — The master page shell (HTML shell with CSS/JS)
- `index.php` — Landing page
- `auth/` — Login and register forms
- `app/dashboard/` — Main dashboard showing all projects
- `app/projects/` — Project detail page
- `app/documents/` — Document viewer with review interface
- `app/components/` — Reusable pieces (sidebar, header, project card, etc.)
- `app/modals/` — Popup modals for creating projects, uploading documents, etc.

**Hidden detail:** The `layout.php` injects flash messages (success/error notifications) into a JSON `data-toasts` attribute on the HTML body. A JavaScript file (`toast.js`) reads this attribute and shows floating toast notifications. This is how success/error messages are displayed after page redirects.

### G. Frontend JavaScript — `public/js/app/`

Each major feature has its own JavaScript file. They are loaded only on the pages that need them:

- **`projects.js`** — Creates projects, handles the "create project" modal
- **`project-members.js`** — Adding members, changing roles, removing members
- **`add-members.js`** — Search for users to invite
- **`project-document-upload.js`** — Uploading documents to a project
- **`document-viewer.js`** — Renders PDF (using PDF.js library) or DOCX (using Mammoth.js library) in the browser, handles page navigation
- **`document-version-upload.js`** — Uploading new versions of a document, selecting review threads to carry over
- **`document-review-threads.js`** — Creating threads, adding comments, resolving threads
- **`document-version-approval.js`** — Approving a document version
- **`notifications.js`** — Fetching and displaying notifications

**Hidden detail:** All JavaScript uses `fetch()` API to make AJAX requests (no page reloads). For example, when you add a comment, JavaScript sends a POST request with JSON data, the server responds with JSON, and JavaScript updates the page. No page refresh needed.

### H. File Storage

Uploaded documents are stored in `storage/documents/` — which is **outside the web root** (`public/`). This means you cannot access them by typing a URL directly. They are served through a special route (`/app/documents/file/{versionId}`) that:
1. Checks the user is logged in
2. Verifies they are a member of the project
3. Resolves the file path safely (prevents path traversal attacks)
4. Streams the file with proper headers

---

## 4. Database Structure (8 Tables)

Think of the database as a filing cabinet with 8 drawers:

| Table | What it stores |
|---|---|
| **users** | Each person who has an account (email, name, password) |
| **projects** | A project — a container for documents (title, description) |
| **user_projects** | Links users to projects, WITH a role (owner/reviewer/editor/viewer) |
| **documents** | A document inside a project (title, who created it) |
| **document_versions** | Each version of a document (file path, status, locked or not) |
| **review_threads** | A discussion thread about a document (like a forum topic) |
| **review_comments** | Individual comments within a thread (page number, text content) |
| **review_status** | Whether a thread is open/resolved for a specific version |
| **project_invitations** | Records of people invited to join a project (pending/accepted/rejected) |
| **notifications** | Alerts for users (someone invited you, a new version was uploaded, etc.) |

**Key relationships:**
- A **User** can be in many **Projects** (many-to-many), and the role they have depends on the project
- A **Project** has many **Documents**
- A **Document** has many **Versions** (version history)
- A **Document** has many **Review Threads**
- A **Review Thread** has many **Comments**
- A **Review Thread** has a **Status** for each Version (open → resolved)

---

## 5. Hidden/Hard Things You Should Know

### Hard Thing #1: Document Versioning Workflow

This is the most complex part of the system. A document goes through this lifecycle:

```
Upload v1 (draft) → Upload v2 (draft) → Upload v3 (under_review)
  → Reviewers comment → Upload v4 addressing comments → Approve
```

Each version has a `status`: `draft` → `under_review` → `approved`. The `current_version_id` in the `documents` table always points to the latest version.

When a new version is uploaded:
- The old version can be **locked** (no more editing)
- Review threads from the old version can be **carried over** to the new version
- The thread's status for the NEW version starts as `marked_for_review`

### Hard Thing #2: Role-Based Access

There is NO separate "admin" role for the whole system. Roles only exist **within a project**:

- **Owner** — Full control. Can delete the project, change anyone's role, remove members. Created automatically when you create a project.
- **Editor** — Can upload documents and new versions, but cannot delete the project or change roles.
- **Reviewer** — Can create threads, comment, resolve threads, approve versions. Cannot upload.
- **Viewer** — Read-only. Can only see what's in the project.

Permissions are checked in the Service layer (not Controller, not View). This is done so that even if someone sends a malicious request, the Service layer will block it.

### Hard Thing #3: Invitation System

Members are added through an **invitation** system, not directly. When the owner adds a member:
1. A record is created in `project_invitations` with status `pending`
2. A notification is sent to the invited user
3. The user sees the invitation and must **accept** or **decline**
4. Only after accepting does a record appear in `user_projects`

This means: just inviting someone doesn't give them access — they have to accept first.

### Hard Thing #4: Flash Messages with AJAX

Flash messages work differently depending on how the request was made:
- **Non-AJAX (page navigation):** The controller stores a message in `$_SESSION['_flash']`, redirects, and the layout page reads it and shows it as a toast notification
- **AJAX (JavaScript requests):** The server returns `{ok: true, message: "..."}` as JSON, and the JavaScript code optionally shows a toast

The `toast.js` script creates and shows these toast messages with a timer that auto-dismisses them.

### Hard Thing #5: Method Spoofing

HTML forms can only do GET and POST. But some operations need DELETE. The system solves this by using a hidden field `_method=DELETE` in a POST form. The `index.php` reads this and treats it as a DELETE request for routing purposes.

### Hard Thing #6: File Security

Uploaded documents are NOT stored in the `public/` folder. They are in `storage/documents/` which is NOT accessible via URL. To view a file, the browser must go through the `/app/documents/file/{versionId}` route which:
1. Authenticates the user
2. Checks project membership using `realpath()` to prevent path traversal
3. Streams the file

This means even if someone knows the file path, they cannot access it directly.

### Hard Thing #7: JavaScript Libraries Without npm

PDF.js and Mammoth.js (for viewing PDF and DOCX files) are manually downloaded and placed in `public/js/libraries/`. There is no package manager (npm) involved. The libraries are loaded via `<script>` tags in the view templates. This is unusual in modern web development but was done intentionally to avoid dependency complexity.

---

## 6. Potential Defense Questions

### Architecture & Design

**Q: Why did you build your own framework instead of using Laravel or Symfony?**
> Building a custom framework gave us complete control over the architecture. It also helped us learn exactly how MVC frameworks work under the hood — from routing to database connections. For a project of this scope, a full framework would have been overkill.

**Q: What is MVC and how did you implement it?**
> MVC stands for Model-View-Controller. Our Controllers handle HTTP requests, Services (acting as Models) contain business logic, and Views display HTML. The Router connects the URL to the right Controller. We didn't use formal Model classes — instead, Repositories handle database queries and Services handle rules.

**Q: How does the Router work?**
> The Router maintains a list of registered routes (URL patterns paired with Controller methods). When a request comes in, it first tries to match the URL exactly. If no exact match is found, it uses regex pattern matching to extract parameters from URL patterns that contain `{id}` or `{projectId}` placeholders.

### Database

**Q: Explain the database relationships.**
> Users and Projects have a many-to-many relationship connected through the `user_projects` table, which also stores the user's role. Projects have many Documents, which have many Versions and Review Threads. Threads have many Comments. The `review_status` table tracks whether a thread is open or resolved for each specific version.

**Q: Why use prepared statements?**
> Prepared statements separate SQL code from data. This prevents SQL injection attacks because user input is never directly inserted into SQL queries — it's always treated as parameterized data.

### Security

**Q: How do you prevent unauthorized access?**
> Every protected controller method first calls `Auth::user()` to check if the user is logged in. Then, in the Service layer, every operation checks `getUserRoleInProject()` to verify the user has the required permission level for that specific project.

**Q: How are passwords stored?**
> Passwords are hashed using PHP's `password_hash()` with the bcrypt algorithm, which is a one-way encryption. We never store plain-text passwords. When logging in, we use `password_verify()` to check if the entered password matches the stored hash.

**Q: How are uploaded files secured?**
> Files are stored outside the web root directory, so they cannot be accessed by guessing URLs. They are served through a controller method that authenticates the user, verifies project membership, and uses `realpath()` to prevent path traversal attacks.

### Features

**Q: Explain the document versioning workflow.**
> Documents can have multiple versions. Each version has a status: draft, under_review, or approved. When a new version is uploaded, it starts as draft. The creator can change it to under_review when ready for feedback. Reviewers can comment. Finally, someone with appropriate permissions can approve it, which locks the version.

**Q: How does the review system work?**
> Reviewers create "threads" on a document — each thread is like a discussion topic with a title. Within a thread, reviewers can add comments that are tied to specific page numbers. Threads can be resolved when the feedback has been addressed. When a new version is uploaded, unresolved threads can be carried over.

**Q: How do invitations work?**
> When a project owner invites someone, a pending invitation is created. The invited user sees their pending invitations in the dashboard. They must explicitly accept before gaining access to the project. This ensures users cannot be forcibly added to projects.

### Technical

**Q: How does the file viewer work?**
> For PDF files, we use the PDF.js library (from Mozilla) to render pages in the browser. For DOCX files, we use Mammoth.js which converts the document to HTML. Both libraries run entirely in the browser and do not require server-side rendering.

**Q: What is a singleton and where did you use it?**
> A singleton ensures only one instance of a class exists. We used it for the Database class — only one PDO connection is created per request, and it's reused everywhere. This prevents creating multiple database connections unnecessarily.

**Q: How do notifications work?**
> When certain actions happen (invitation sent, new version uploaded, etc.), the NotificationService creates a record in the notifications table. The frontend periodically fetches unread notifications via AJAX and displays them in a dropdown. Users can mark individual notifications as read or mark all as read.

**Q: What challenges did you face?**
> One challenge was the document versioning system — ensuring review threads from old versions can carry over to new versions while maintaining proper status tracking. Another was implementing role-based access within projects without a full-fledged authorization framework.
