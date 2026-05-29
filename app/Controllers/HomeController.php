<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\NotificationModel;
use App\Models\ProjectModel;
use App\Repositories\NotificationRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\UserRepository;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->render('index', ['user' => Auth::user()], 'Home');
    }

    public function dashboard(): void
    {
        if (!Auth::check()) {
            $this->flash('error', 'Please sign in to continue.');
            $this->redirect('/login');
        }

        $this->redirect('/app');
    }

    public function appDashboard(): void
    {
        if (!Auth::check()) {
            $this->flash('error', 'Please sign in to continue.');
            $this->redirect('/login');
        }

        $user = Auth::user();
        if (!is_array($user) || !isset($user['id'])) {
            $this->flash('error', 'Please sign in to continue.');
            $this->redirect('/login');
        }

        $rawScope = (string) ($_GET['scope'] ?? 'all');
        $projectModel = new ProjectModel(new ProjectRepository(), new UserRepository(), new NotificationRepository());
        $activeScope = $projectModel->normalizeScope($rawScope);
        $projects = $projectModel->fetchProjectsByScope((int) $user['id'], $activeScope);
        $pendingInvitations = $projectModel->fetchPendingInvitations((int) $user['id']);
        $pendingInvitationCount = $projectModel->getPendingInvitationCount((int) $user['id']);

        $notificationModel = new NotificationModel(new NotificationRepository(), new ProjectRepository());
        $notifications = $notificationModel->fetchNotifications((int) $user['id']);
        $notificationUnreadCount = $notificationModel->getUnreadCount((int) $user['id']);

        $this->render('app/dashboard/index', [
            'user' => $user,
            'activeScope' => $activeScope,
            'projects' => $projects,
            'pendingInvitations' => $pendingInvitations,
            'pendingInvitationCount' => $pendingInvitationCount,
            'notifications' => $notifications,
            'notificationUnreadCount' => $notificationUnreadCount,
        ], 'Projects');
    }
}

