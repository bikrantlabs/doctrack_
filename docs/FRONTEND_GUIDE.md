# DocTrack — Frontend Guide (HTML, CSS, JavaScript)

## 1. What is the Frontend?

The frontend is everything the user **sees and interacts with** in the browser. It consists of three technologies working together:

- **HTML** — The structure and content of each page (headings, forms, buttons, text)
- **CSS** — The visual design (colors, fonts, spacing, layout, animations)
- **JavaScript** — The behavior and interactivity (opening modals, submitting forms without reloading, rendering PDFs)

In DocTrack, all three are written from scratch — no framework like React or Bootstrap was used.

---

## 2. Frontend Architecture (How the Parts Connect)

```
┌──────────────────────────────────────────────────────────┐
│                    layout.php                             │
│          (The master HTML shell — every page              │
│           is wrapped inside this)                         │
│                                                           │
│  <head>                                                   │
│    <link rel="stylesheet" href="css/main.css">            │
│    <!-- main.css imports ALL other CSS files →             │
│          one HTTP request loads everything -->            │
│  </head>                                                  │
│  <body>                                                   │
│    <?= $content ?>   ← Page-specific view injected here  │
│    <div id="toast-container" data-toasts="..."></div>     │
│    <script src="js/toast.js" defer></script>              │
│  </body>                                                  │
└──────────────────────┬───────────────────────────────────┘
                       │
        ┌──────────────┼──────────────┐
        ▼              ▼              ▼
┌──────────────┐ ┌──────────┐ ┌──────────────┐
│  Page View   │ │  CSS     │ │  JavaScript   │
│  (HTML)      │ │  Files   │ │  Files        │
│              │ │          │ │              │
│ index.php    │ │ theme.css│ │ toast.js     │
│ login.php    │ │ common   │ │ projects.js  │
│ dashboard/   │ │   .css   │ │ document-    │
│   index.php  │ │ app/     │ │   viewer.js  │
│ projects/    │ │   *.css  │ │ notifications│
│   show.php   │ │ auth/    │ │   .js        │
│ documents/   │ │   *.css  │ │ ...          │
│   show.php   │ │          │ │              │
│ components/  │ │          │ │ pdfjs.js     │
│   *.php      │ │          │ │ mammoth.js   │
│ modals/*.php │ │          │ │              │
└──────────────┘ └──────────┘ └──────────────┘
```

---

## 3. Breaking Down Each Layer

### A. HTML — The View System

Every page in DocTrack is built using a **layout + content** pattern:

#### The Layout Shell — `views/layout.php`

This is the "wrapper" that surrounds every page. Think of it like a picture frame — the frame stays the same, but the picture inside changes. It contains:

- The `<html>`, `<head>`, and `<body>` tags
- The `<title>` of the page (changes per page)
- The single CSS link (`main.css`)
- Google Fonts link for the "Inter" font family
- The `$content` area — this is where page-specific HTML gets injected
- A toast notification container (`<div id="toast-container">`) that holds flash messages
- The `toast.js` script (loaded on every page)

#### Page Views

Each page is a separate PHP file that fills in the `$content` area. They are organized by feature:

| View | What it shows |
|---|---|
| `index.php` | Landing page — the first thing visitors see. Has a hero section, features list, and call-to-action buttons |
| `auth/login.php` | Login form with email and password fields |
| `auth/register.php` | Registration form with name, email, and password |
| `app/dashboard/index.php` | Main dashboard after login — shows all projects in a card grid |
| `app/projects/show.php` | Project detail — shows documents inside the project and team members |
| `app/documents/show.php` | Document viewer — shows the actual file (PDF/DOCX) with review threads sidebar |
| `not-found.php` | 404 error page — shows when someone visits a non-existent URL |

#### Components — `views/app/components/`

These are **reusable pieces of HTML** that multiple pages can use. Instead of writing the same code twice, components are shared:

| Component | Used on | What it contains |
|---|---|---|
| `app-header.php` | Dashboard, Project page, Document page | Top bar with logo, search bar, notification bell, user avatar |
| `app-sidebar.php` | Dashboard, Project page | Left navigation menu (Projects, Documents, Reviews, Settings) |
| `project-card.php` | Dashboard | A single card showing one project's title, description, stats |
| `create-new-project-card.php` | Dashboard | A special dashed-border card that opens the "create project" form |
| `project-documents-card.php` | Project page | A section showing all documents inside a project |
| `project-members-card.php` | Project page | A table showing team members and their roles |
| `document-viewer-pane.php` | Document page | The left panel showing the actual PDF or DOCX file |
| `document-detail-sidebar.php` | Document page | The right panel with version history and action buttons |
| `document-review-threads-card.php` | Document page | The review discussion section — threads, comments, and forms |
| `notifications-dropdown.php` | Header (on all app pages) | The dropdown panel showing notifications and invitations |

#### Modals — `views/app/modals/`

Modals are **popup windows** that appear on top of the page. All modals follow the same structure:

```
<div class="modal-overlay hidden" id="modal-id">
  <div class="modal">
    <div class="modal-header">Title + Close button</div>
    <form class="modal-form">
      <div class="modal-body">Form fields go here</div>
      <div class="modal-footer">Cancel + Submit buttons</div>
    </form>
  </div>
</div>
```

| Modal | Purpose |
|---|---|
| `create-new-project.php` | Form to create a new project (title, description, invite members) |
| `upload-document.php` | Form to upload a new document to a project |
| `upload-document-version.php` | Form to upload a new version of an existing document |
| `approve-document-version.php` | Confirmation dialog to approve a document version |
| `add-members.php` | Form to search for users and invite them to a project |

---

### B. CSS — The Visual Design

DocTrack's CSS follows a **single-entry, multi-file** architecture. One master file (`main.css`) imports all other CSS files, so the browser only makes **one request** for all styles.

#### File Organization

```
main.css  (orchestrator — imports everything below)
  │
  ├── theme.css       (design tokens — colors, fonts, spacing, shadows)
  ├── minimal.css     (overrides for a "plain college project" look)
  ├── common.css      (shared styles — buttons, forms, cards, typography)
  ├── home.css        (landing page — hero section, features, CTA)
  ├── toast.css       (notification popup styles)
  ├── not_found.css   (404 page styles)
  ├── auth/
  │   ├── login.css   (login page layout)
  │   └── register.css (registration page layout)
  └── app/
      ├── app.css           (dashboard layout — sidebar, header, main area)
      ├── projects.css      (project cards, tables, modals)
      ├── documents.css     (document viewer, version list, data tables)
      ├── create-project-modal.css  (create project popup)
      ├── notifications.css (notification bell and dropdown)
      ├── invitations.css   (invitation display)
      ├── reviews.css       (review threads styling)
      └── team.css          (team member cards)
```

#### The Theme System — `theme.css`

This file defines **design tokens** — named values that control the entire visual appearance. Think of them as "settings" for the design:

- **Colors** — Primary blue (`#0d6efd`), background shades, text colors (light gray to dark), status colors (green for success, red for error, yellow for warning)
- **Font** — "Inter" font family from Google Fonts, with sizes ranging from extra-small (12px) to 6xl (60px)
- **Spacing** — A 10-step scale from 4px to 96px
- **Borders** — Widths (thin/medium/thick) and radius sizes (small to full circle)
- **Shadows** — Currently all set to `none` (intentionally disabled for a minimal look)
- **Z-index layers** — A numbering system that controls which elements sit on top of others (dropdowns = 100, modals = 500)
- **Layout constants** — Header height (72px), sidebar width (280px), container max-width (1200px)

#### The Minimal Override — `minimal.css`

This file deliberately **removes all visual polish** — no shadows, no animations, no gradients, no rounded corners, no hover effects. This was done to give the project a simple, straightforward "college project" appearance.

#### Common Styles — `common.css`

Contains styles shared across all pages:
- **Reset** — Removes browser default margins and paddings
- **Typography** — Default text styles for headings, paragraphs, links
- **Buttons** — Primary (blue), secondary (gray), danger (red), ghost (transparent) variants
- **Forms** — Input fields, textareas, select dropdowns, labels
- **Cards** — Box containers with padding and borders
- **Utility classes** — Small helpers for margins, text alignment, display types

#### How Responsive Design Works

The layout **adapts to different screen sizes** using media queries:
- **Mobile (< 768px)** — Single column layout, sidebar hidden, search bar hidden, cards stack vertically
- **Tablet (768px - 1024px)** — Two columns, some elements collapse
- **Desktop (> 1024px)** — Full layout with sidebar, multi-column grids

Example: The dashboard shows 4 stat cards in a row on desktop, 2 on tablet, 1 on mobile.

---

### C. JavaScript — The Interactivity

JavaScript handles everything that makes the page **feel alive** — opening modals, submitting forms without page reload, rendering PDFs, showing notification popups.

#### File Organization

```
js/
  ├── toast.js                    (notification popup system — loaded on EVERY page)
  ├── app/
  │   ├── projects.js             (create project modal logic)
  │   ├── project-members.js      (manage member roles and removal)
  │   ├── project-document-upload.js  (upload document form with drag-and-drop)
  │   ├── add-members.js          (search users and invite them)
  │   ├── document-viewer.js      (render PDF/DOCX in the browser)
  │   ├── document-version-upload.js  (upload new version with drag-and-drop)
  │   ├── document-version-approval.js (approve document version)
  │   ├── document-review-threads.js   (create threads, add comments, resolve)
  │   └── notifications.js        (notification bell, invitations)
  └── libraries/
      ├── pdfjs.js                (PDF.js library — renders PDF files)
      ├── pdf.worker.min.mjs      (PDF.js helper — runs in background)
      └── mammoth.js              (Mammoth.js — converts DOCX to HTML)
```

#### How Scripts are Loaded

- Each page only loads the JavaScript files it needs (no unnecessary code)
- Scripts use the `defer` attribute — this means they run **after** the HTML is fully loaded
- The `toast.js` file is on every page (it handles flash messages)
- The dashboard loads `projects.js` + `notifications.js`
- The project page loads `project-members.js` + `project-document-upload.js` + optionally `add-members.js`
- The document page loads `document-viewer.js` + `document-review-threads.js` + `document-version-upload.js` + optionally `document-version-approval.js` + the PDF.js and Mammoth.js libraries

#### How JavaScript Talks to the Server

Every interactive action uses the **`fetch()` API** — a built-in browser feature for sending HTTP requests without reloading the page. The flow is always:

```
User clicks button → JavaScript captures the click
  → JavaScript sends a fetch() request to a URL
    → Server processes it and sends back JSON
      → JavaScript reads the JSON response
        → Shows a success/error toast notification
        → Updates the page (adds/removes elements, reloads if needed)
```

Example — Creating a project:
1. User fills in the form and clicks "Create"
2. `projects.js` collects the form data
3. Sends a `fetch()` POST request to `/app/projects`
4. Server responds with `{ok: true, message: "Project created"}`
5. JavaScript shows a green success toast and reloads the page

#### How Each JavaScript File Works

**`toast.js`** — The notification system:
- Reads flash messages from a `data-toasts` attribute on the page's HTML
- Creates small colored popup boxes in the bottom-right corner
- Types: success (green), error (red), warning (yellow), info (blue)
- Automatically disappears after 3.5 seconds with a progress bar animation
- Hovering over a toast pauses the timer

**`projects.js`** — Creating projects:
- Opens the "Create Project" modal when clicking the new project button
- Handles user search — when the owner types a name, it searches for users via AJAX
- Shows matching users in a dropdown list
- Lets the owner select users and assign them roles (reviewer, editor, viewer)
- Submits the form via `fetch()` and shows the result

**`project-members.js`** — Managing team members:
- When the owner changes a member's role in the dropdown, it sends the update
- When the owner clicks "Remove," it asks for confirmation, then removes
- Shows toast messages for success or failure

**`project-document-upload.js`** — Uploading documents:
- Supports drag-and-drop — user can drag a file onto the upload area
- Shows a visual highlight when dragging over the drop zone
- Only accepts PDF and DOCX files
- Submits the file and title via `fetch()` with `FormData`

**`document-viewer.js`** — Viewing PDFs and DOCX files:
- Detects the file type (PDF vs DOCX)
- For PDF: Uses the PDF.js library to render each page onto a `<canvas>` element
  - Shows page navigation (prev/next buttons, page number input)
  - Can jump to a specific page
  - Listens for a custom event `doctrack:viewer:goto-page` (triggered when clicking a review comment that references a page)
- For DOCX: Uses Mammoth.js to convert the document to HTML and display it
- Shows a loading spinner while the file loads
- Shows an error message if the file is unsupported

**`document-review-threads.js`** — The review system:
- Has three "modes" which switch based on what the user is doing:
  - **List mode** — Shows all review threads with their status (open/resolved)
  - **Create mode** — Form to create a new thread (title, comment, page number)
  - **Detail mode** — Shows a single thread with all its comments
- When a comment is added, the page reloads to show the new comment
- When a page chip is clicked in a comment, it dispatches a custom event that tells the document viewer to jump to that page
- Saves state in `sessionStorage` — if you reload the page, it reopens the thread you were viewing

**`document-version-upload.js`** — Uploading new versions:
- Similar to document upload with drag-and-drop
- Shows a checkbox list of existing review threads — user can select which ones to carry over to the new version
- Submits the file, change summary, and selected thread IDs

**`document-version-approval.js`** — Approving a version:
- Simple confirmation modal — "Are you sure you want to approve this version?"
- Submits the version ID and shows the result

**`notifications.js`** — Notifications and invitations:
- When the bell icon is clicked, toggles the notification dropdown open/closed
- Shows two sections: Invitations (pending invites to projects) and Activity (notifications about project events)
- Invitations have Accept and Decline buttons
- Clicking a notification marks it as read and navigates to the relevant page
- "Mark all as read" button clears all unread notifications
- The badge count updates dynamically (shows "9+" if more than 9)

#### How JavaScript Finds HTML Elements

Instead of using CSS classes (which might change), the JavaScript uses **`data-*` attributes** — custom attributes that are specifically meant for JavaScript to find elements:

| HTML Attribute | Used by |
|---|---|
| `data-document-viewer="1"` | `document-viewer.js` finds the viewer container |
| `data-notifications-trigger="1"` | `notifications.js` finds the bell icon |
| `data-review-threads-root="1"` | `document-review-threads.js` finds the threads section |
| `data-modal="modal-id"` | Any script that opens a modal |
| `data-pdf-page-input="1"` | `document-viewer.js` finds the page number input |
| `data-members-root="1"` | `project-members.js` finds the members table |

---

## 4. Hidden/Hard Things You Should Know

### Hard Thing #1: Role-Based UI Rendering

The JavaScript does NOT decide what buttons to show — the **HTML is already rendered correctly by PHP on the server**. For example, the "Approve" button only exists in the HTML if the current user is a reviewer. The JavaScript only handles the interaction after the button is visible. This means a user cannot hack the frontend to see buttons they shouldn't have access to — if the server decided they can't approve, the button was never in the HTML in the first place.

### Hard Thing #2: Communication Between JavaScript Files

Different JavaScript files need to talk to each other. For example, when a user clicks a page number in a review comment, `document-review-threads.js` needs to tell `document-viewer.js` to scroll to that page. Since there is no framework, this is done using **custom DOM events**:

```javascript
// document-review-threads.js dispatches a custom event
document.dispatchEvent(new CustomEvent('doctrack:viewer:goto-page', {
  detail: { pageNumber: 5 }
}));

// document-viewer.js listens for it
document.addEventListener('doctrack:viewer:goto-page', function(event) {
  scrollToPage(event.detail.pageNumber);
});
```

This is a simple pub/sub (publish/subscribe) pattern — one script "publishes" an event, and other scripts "subscribe" and react.

### Hard Thing #3: No Build Tools

Most modern web projects use tools like Webpack or Vite to bundle JavaScript files together, minify them, and handle dependencies. DocTrack has **none of that**. Every JavaScript file is loaded individually via `<script>` tags. The PDF.js and Mammoth.js libraries are manually downloaded and placed in the `js/libraries/` folder. There is no `npm install` or `package.json`.

### Hard Thing #4: Search with AbortController

The user search feature (for inviting members) uses a pattern called **debouncing with cancellation**:

1. When the user types, the search waits 250ms before sending the request (debouncing) — this prevents sending a request for EVERY keystroke
2. If the user types again before the 250ms is up, the timer resets
3. If a previous search request is still in flight, it gets **cancelled** using `AbortController` — this ensures we don't get results from an old search showing up after a newer search

```javascript
// Pseudocode for the pattern
let searchTimeout;
let abortController;

input.addEventListener('input', function() {
  clearTimeout(searchTimeout);
  if (abortController) abortController.abort();
  
  searchTimeout = setTimeout(() => {
    abortController = new AbortController();
    fetch('/search?q=' + query, { signal: abortController.signal });
  }, 250);
});
```

### Hard Thing #5: PDF Rendering Performance

PDF.js renders PDF pages onto `<canvas>` elements, which can be slow for documents with many pages. The current implementation renders all pages sequentially. For a 50-page document, this means 50 separate render operations happen one after another. Each page is rendered at a scale of 1.5x for sharp display. The page navigation works by scrolling the `<canvas>` elements into view.

### Hard Thing #6: Drag-and-Drop File Upload

The document upload feature supports drag-and-drop, which requires handling four events: `dragenter`, `dragover`, `dragleave`, and `drop`. The implementation:

1. When a file is dragged over the drop zone, a visual highlight class (`.drag-over`) is added
2. `dragover` must call `preventDefault()` to allow the drop (a common gotcha)
3. When the file is dropped, the file data is extracted and attached to the hidden file input
4. The file type is validated client-side (only PDF and DOCX) before submission

### Hard Thing #7: Session Storage for UI State

The review threads feature saves its state in `sessionStorage` — a browser storage that persists only for the current tab session. If you're viewing a thread's details and refresh the page, the JavaScript reads `sessionStorage` and reopens that same thread. This creates a smooth user experience without needing to store UI state on the server.

---

## 5. Potential Defense Questions

### General Frontend

**Q: Why didn't you use a frontend framework like React or Vue?**
> The project scope didn't require complex client-side state management or a virtual DOM. Vanilla JavaScript is simpler to understand, has no build step, and keeps the project dependency-free. Each feature has its own dedicated JavaScript file, keeping the code organized without a framework.

**Q: How do the HTML pages connect to each other?**
> Every page is wrapped in a master layout file called `layout.php`. The layout provides the HTML shell, CSS link, and basic JavaScript. Page-specific content is injected into the `$content` area of the layout. Links between pages are standard `<a href="...">` tags that trigger full page navigation.

**Q: How did you organize your CSS?**
> We used a modular approach where each page or feature has its own CSS file. A master file called `main.css` imports all other files using `@import`. This keeps code organized during development while still loading as a single file in the browser. We also defined all design values (colors, spacing, fonts) as CSS custom properties in a theme file, making the design consistent.

### CSS

**Q: Explain how the theme system works.**
> All visual properties — colors, font sizes, spacing, border radii — are defined as CSS custom properties (variables) in a `theme.css` file. For example, `--color-primary: #0d6efd` defines the primary blue color. Throughout the rest of the CSS, we reference `var(--color-primary)` instead of hardcoding the value. This makes it easy to change the entire look by editing just the theme file.

**Q: How does the page adapt to different screen sizes?**
> We used CSS media queries — rules that apply only when the screen is above or below certain widths. For example, at widths below 768px, the sidebar hides, the grid changes to single column, and the search bar is removed. This makes the site usable on phones, tablets, and desktops.

### JavaScript

**Q: How do you communicate with the server without reloading the page?**
> We use the browser's built-in `fetch()` function. When the user performs an action like creating a project or adding a comment, JavaScript sends an HTTP request to the server in the background. The server responds with JSON data (a lightweight text format). JavaScript then updates the page or shows a toast notification based on the response.

**Q: Why did you use `defer` on script tags?**
> The `defer` attribute tells the browser to download the script immediately but wait until the HTML is fully parsed before executing it. This ensures our JavaScript code can find and interact with all HTML elements on the page, since they exist in the DOM by the time the script runs.

**Q: How does the PDF viewer work?**
> We used a library called PDF.js created by Mozilla (the Firefox team). It takes a PDF file and renders each page onto a `<canvas>` element — which is like an HTML drawing board. The user can navigate pages using prev/next buttons or type a page number directly. When someone clicks a review comment that references a page, the viewer scrolls to that page.

**Q: What happens when JavaScript is disabled?**
> The core functionality (viewing pages, reading documents) still works because links trigger full page reloads. However, interactive features like creating projects, uploading documents, and adding comments depend on JavaScript. The server-side code always validates permissions regardless of what the frontend sends.

### UX & Design

**Q: How did you handle form validation?**
> Forms use the `novalidate` attribute to disable browser-default validation popups. Instead, JavaScript validates the input and shows user-friendly toast messages. For example, if a required field is empty, a red toast says "Please fill in this field" instead of the browser's default popup.

**Q: How does the drag-and-drop upload work?**
> When a user drags a file over the upload zone, JavaScript adds a visual highlight by adding a CSS class. When the file is dropped, JavaScript reads the file data from the browser's drop event, verifies it's a PDF or DOCX, and attaches it to a hidden file input field. The form is then submitted normally via fetch().

**Q: What makes this frontend different from using a template or framework?**
> Every line of code was written manually — no Bootstrap, no Tailwind, no React, no jQuery. The CSS theme system, the JavaScript event handling, the modal system, and the component reuse pattern were all designed from scratch. This gives complete control over the code but required more effort to build.
