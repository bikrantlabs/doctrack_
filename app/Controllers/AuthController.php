<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\AuthModel;
use App\Repositories\UserRepository;

final class AuthController extends Controller
{
    private AuthModel $authModel;

    public function __construct()
    {
        $this->authModel = new AuthModel(new UserRepository());
    }

    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/app');
        }

        $this->render('auth/login', [], 'Sign In');
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            $this->redirect('/app');
        }

        $this->render('auth/register', [], 'Create Account');
    }

    public function login(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $this->keepOld(['email' => $email]);

        $result = $this->authModel->login($email, $password);
        if (!$result['ok']) {
            $this->flash('error', $result['message']);
            $this->redirect('/login');
        }

        Auth::login($result['user']);

        unset($_SESSION['_old']);

        $this->flash('success', $result['message']);
        $this->redirect('/app');
    }

    public function register(): void
    {
        $firstName = trim((string) ($_POST['firstName'] ?? ''));
        $lastName = trim((string) ($_POST['lastName'] ?? ''));
        $fullname = trim((string) ($_POST['fullname'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $this->keepOld([
            'fullname' => $fullname,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
        ]);

        $result = $this->authModel->register([
            'fullname' => $fullname,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'password' => $password,
        ]);
        if (!$result['ok']) {
            $this->flash('error', $result['message']);
            $this->redirect('/register');
        }

        Auth::login($result['user']);

        unset($_SESSION['_old']);

        $this->flash('success', $result['message']);
        $this->redirect('/app');
    }

    public function logout(): void
    {
        if (Auth::check()) {
            Auth::logout();
        }

        $this->flash('success', 'You have been signed out.');
        $this->redirect('/');
    }
}

