# Phase 2.1 - Quick Reference Guide

## Changes at a Glance

### 1. ProjectRepository.php - Added 3 Methods

**Method 1: Get User's Projects**
```php
public function getAllProjectsByUserId(int $userId): array
```
- Query: SELECT from projects with INNER JOIN on user_projects
- Filter: WHERE user_id = :user_id
- Sort: ORDER BY created_at DESC
- Returns: Array of projects with ID, title, description, role, created_at

**Method 2: Get Document Count**
```php
public function getProjectDocumentCount(int $projectId): int
```
- Query: SELECT COUNT(*) FROM documents
- Filter: WHERE project_id = :project_id
- Returns: Integer count (0 if none)

**Method 3: Get Member Count**
```php
public function getProjectMemberCount(int $projectId): int
```
- Query: SELECT COUNT(*) FROM user_projects
- Filter: WHERE project_id = :project_id
- Returns: Integer count (0 if none)

### 2. ProjectService.php - Added 1 Method

**Method: Fetch Projects with Stats**
```php
public function fetchUserProjectsWithStats(int $userId): array
```
- Calls getAllProjectsByUserId() to get base projects
- For each project:
  - Calls getProjectDocumentCount()
  - Calls getProjectMemberCount()
  - Merges counts into project array
- Returns: Array of enriched project objects

### 3. HomeController.php - Updated appDashboard()

**Before:**
```php
$this->render('app/dashboard/index', ['user' => Auth::user()], 'Projects');
```

**After:**
```php
$user = Auth::user();
$projectService = new ProjectService(new ProjectRepository(), new UserRepository());
$projects = $projectService->fetchUserProjectsWithStats((int) $user['id']);

$this->render('app/dashboard/index', [
    'user' => $user,
    'projects' => $projects,
], 'Projects');
```

**Added imports:**
```php
use App\Repositories\ProjectRepository;
use App\Repositories\UserRepository;
use App\Services\ProjectService;
```

### 4. project-card.php - Complete Refactor

**Signature:**
```php
<?php
/** @var array{id:int, title:string, description:string|null, role:string, created_at:string, documentCount:int, memberCount:int} $project */
?>
```

**Key Changes:**
- Wrapped in `<a href="/app/projects/{id}">` link
- Dynamic title: `<?= e($project['title']) ?>`
- Dynamic description: `<?= e($project['description'] ?? '') ?>`
- Dynamic doc count: `<?= $project['documentCount'] ?> <?= $project['documentCount'] === 1 ? 'doc' : 'docs' ?>`
- Dynamic member count: `<?= $project['memberCount'] ?> <?= $project['memberCount'] === 1 ? 'member' : 'members' ?>`
- Dynamic badge: `<span class="project-badge badge-<?= e($project['role']) ?>"><?= ucfirst($project['role']) ?></span>`

### 5. dashboard/index.php - Project Grid Section

**Before:**
```php
<div class="projects-grid">
    <!-- Hardcoded example card -->
    <div class="project-card">
        <!-- ... hardcoded content ... -->
    </div>
    <?php require BASE_PATH . '/views/app/components/create-new-project-card.php'; ?>
</div>
```

**After:**
```php
<div class="projects-grid">
    <!-- Dynamically render project cards -->
    <?php if (!empty($projects)): ?>
        <?php foreach ($projects as $project): ?>
            <?php require BASE_PATH . '/views/app/components/project-card.php'; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
            </svg>
            <h3>No projects yet</h3>
            <p>Create your first project to get started</p>
        </div>
    <?php endif; ?>
    <?php require BASE_PATH . '/views/app/components/create-new-project-card.php'; ?>
</div>
```

### 6. projects.css - Added Styles

**New CSS Classes:**

```css
/* Link wrapper for entire card */
.project-card-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

/* Hover effect on link hovering over card */
.project-card-link:hover .project-card {
    border-color: var(--color-border-light);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

/* Card container now has flex properties */
.project-card {
    background-color: var(--color-bg-card);
    border: var(--border-width-thin) solid var(--color-border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: all var(--transition-fast);
    height: 100%;
    display: flex;
    flex-direction: column;
}

/* Empty state styles */
.empty-state {
    grid-column: 1 / -1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-3xl);
    text-align: center;
    min-height: 300px;
}

.empty-state svg {
    width: 64px;
    height: 64px;
    color: var(--color-text-muted);
    margin-bottom: var(--spacing-lg);
    opacity: 0.5;
}

.empty-state h3 {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--color-text-primary);
    margin-bottom: var(--spacing-sm);
}

.empty-state p {
    font-size: var(--font-size-base);
    color: var(--color-text-secondary);
}
```

## Data Structure

### Project Object (from service)
```php
[
    'id' => 1,
    'title' => 'Financial Reports',
    'description' => 'Quarterly reports and forecasts',
    'role' => 'owner',  // or 'editor', 'reviewer', 'viewer'
    'created_at' => '2026-03-13 10:30:00',
    'documentCount' => 5,
    'memberCount' => 3
]
```

## Database Queries Generated

### Query 1: Get All Projects for User
```sql
SELECT p.id, p.title, p.description, up.role, p.created_at
FROM projects p
INNER JOIN user_projects up ON p.id = up.project_id
WHERE up.user_id = ?
ORDER BY p.created_at DESC
```

### Query 2: Get Document Count (per project)
```sql
SELECT COUNT(*) as count FROM documents WHERE project_id = ?
```

### Query 3: Get Member Count (per project)
```sql
SELECT COUNT(*) as count FROM user_projects WHERE project_id = ?
```

## Integration Points

### Ready for Phase 2.2
- Routes: Links point to `/app/projects/{id}` (not yet created)
- Member list: `getProjectMemberCount()` data available
- Document list: `getProjectDocumentCount()` data available
- Filtering: Role data available for My Projects vs Shared tabs

### Security Measures
- Type declarations on all functions
- Input escaping with `e()` on all user content
- Parameterized SQL queries (no injection risk)
- Role-based data filtering (users only see their projects)

## Rollback Plan

If needed to revert Phase 2.1:

1. Restore HomeController.php to original (remove 13 lines)
2. Restore project-card.php to original stub
3. Restore dashboard/index.php hardcoded example card
4. Restore projects.css (remove 100 lines of new styles)
5. ProjectRepository and ProjectService methods can remain (unused)

---

**Total Implementation Time**: ~1 hour  
**Testing Time**: ~30 minutes  
**Total Duration**: ~1.5 hours  
**Complexity**: Medium (Repository/Service/View pattern)  
**Risk Level**: Low (no breaking changes)

