<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Repositories\ProjectRepository;
use App\Repositories\UserRepository;
use App\Services\ProjectService;

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
        $projectService = new ProjectService(new ProjectRepository(), new UserRepository());
        $activeScope = $projectService->normalizeScope($rawScope);
        $projects = $projectService->fetchProjectsByScope((int) $user['id'], $activeScope);

        $this->render('app/dashboard/index', [
            'user' => $user,
            'activeScope' => $activeScope,
            'projects' => $projects,
        ], 'Projects');
    }
}

