<?php
/** @var array{id:int, title:string, description:string|null, role:string, created_at:string, documentCount:int, memberCount:int} $project */
/** @var array<int, array{id:int, fullname:string, email:string, role:string, joined_at:string}> $members */
$isOwner = (string) ($project['role'] ?? '') === 'owner';
$roleOptions = ['editor' => 'Editor', 'reviewer' => 'Reviewer', 'viewer' => 'Viewer'];
?>

<section class="project-members-card"
         data-members-root="1"
         data-project-id="<?= (int) $project['id'] ?>"
         data-update-url-template="<?= e(url('/app/projects/' . $project['id'] . '/members/__MEMBER_ID__/role')) ?>"
         data-remove-url-template="<?= e(url('/app/projects/' . $project['id'] . '/members/__MEMBER_ID__/remove')) ?>">
    <div class="project-members-header">
        <h2>Members</h2>
        <p><?= count($members) ?> <?= count($members) === 1 ? 'member' : 'members' ?> in this project</p>
    </div>

    <div class="project-members-table-wrapper">
        <table class="project-members-table">
            <thead>
            <tr>
                <th>Member</th>
                <th>Role</th>
                <th>Joined</th>
                <th class="members-actions-column">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($members as $member): ?>
                <?php
                $memberRole = (string) $member['role'];
                $canManageMember = $isOwner && $memberRole !== 'owner';
                $memberName = trim((string) $member['fullname']);
                $memberInitial = strtoupper(substr($memberName !== '' ? $memberName : '?', 0, 1));
                ?>
                <tr data-member-row="<?= (int) $member['id'] ?>">
                    <td>
                        <div class="member-user-cell">
                            <div class="member-avatar-circle"><?= e($memberInitial) ?></div>
                            <div class="member-user-meta">
                                <span class="member-user-name"><?= e($member['fullname']) ?></span>
                                <span class="member-user-email"><?= e($member['email']) ?></span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if ($canManageMember): ?>
                            <div class="member-role-control">
                                <span class="project-badge badge-<?= e($memberRole) ?>" data-role-badge="<?= (int) $member['id'] ?>">
                                    <?= e(ucfirst($memberRole)) ?>
                                </span>
                                <select class="role-select member-role-select" data-member-id="<?= (int) $member['id'] ?>" aria-label="Change member role">
                                    <?php foreach ($roleOptions as $roleValue => $roleLabel): ?>
                                        <option value="<?= e($roleValue) ?>" <?= $memberRole === $roleValue ? 'selected' : '' ?>>
                                            <?= e($roleLabel) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <span class="project-badge badge-<?= e($memberRole) ?>"><?= e(ucfirst($memberRole)) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="member-joined-at"><?= e(date('M j, Y', strtotime((string) $member['joined_at']))) ?></span>
                    </td>
                    <td class="members-actions-column">
                        <?php if ($canManageMember): ?>
                            <button type="button" class="btn btn-outline btn-sm member-remove-btn" data-member-id="<?= (int) $member['id'] ?>">
                                Remove
                            </button>
                        <?php else: ?>
                            <span class="member-action-placeholder">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

