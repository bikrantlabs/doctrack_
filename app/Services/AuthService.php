<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DuplicateEmailException;
use App\Repositories\UserRepository;

final class AuthService
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    /** @return array{ok:bool,message:string,user?:array{id:int,email:string,fullname:string}} */
    public function login(string $email, string $password): array
    {
        if ($email === '' || $password === '') {
            return ['ok' => false, 'message' => 'Email and password are required.'];
        }

        $user = $this->users->findByEmail($email);
        if ($user === null || !password_verify($password, $user['password'])) {
            return ['ok' => false, 'message' => 'Invalid credentials.'];
        }

        return [
            'ok' => true,
            'message' => 'Welcome back, ' . $user['fullname'] . '!',
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'fullname' => $user['fullname'],
            ],
        ];
    }

    /**
     * @param array{fullname:string,firstName:string,lastName:string,email:string,password:string} $input
     * @return array{ok:bool,message:string,user?:array{id:int,email:string,fullname:string}}
     */
    public function register(array $input): array
    {
        $fullname = trim($input['fullname']);
        if ($fullname === '') {
            $fullname = trim($input['firstName'] . ' ' . $input['lastName']);
        }

        $email = trim($input['email']);
        $password = $input['password'];

        if ($fullname === '' || $email === '' || $password === '') {
            return ['ok' => false, 'message' => 'Full name, email, and password are required.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'message' => 'Please provide a valid email address.'];
        }

        if (strlen($password) < 8) {
            return ['ok' => false, 'message' => 'Password must be at least 8 characters.'];
        }

        try {
            $id = $this->users->create($email, $fullname, password_hash($password, PASSWORD_BCRYPT));
        } catch (DuplicateEmailException) {
            return ['ok' => false, 'message' => 'That email is already registered.'];
        }

        return [
            'ok' => true,
            'message' => 'Your account is ready. Welcome to DocTrack.',
            'user' => [
                'id' => $id,
                'email' => $email,
                'fullname' => $fullname,
            ],
        ];
    }
}

