<?php
/**
 * CinePulse - Authentication & User Session Manager
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('USERS_FILE', __DIR__ . '/data/users.json');

function getDefaultUsers() {
    return [
        [
            'id' => 1,
            'username' => 'joshuagwapo',
            'email' => 'joshua@cinepulse.com',
            'password' => '$2y$10$gGqNsveiOo6Cdu1Y53q/D.lOXbcTBePqet6B9SuWL6MT3bbyUeQeS',
            'created_at' => '2026-10-09 19:03:25',
            'avatar' => '😎'
        ]
    ];
}

function getUsers() {
    if (!file_exists(USERS_FILE)) {
        return getDefaultUsers();
    }
    $data = @file_get_contents(USERS_FILE);
    $users = json_decode($data, true);
    return !empty($users) ? $users : getDefaultUsers();
}

function isLoggedIn() {
    return !empty($_SESSION['logged_in_user']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $target = urlencode($_SERVER['REQUEST_URI'] ?? 'index.php');
        header("Location: login.php?msg=login_required&redirect={$target}");
        exit;
    }
}

function getCurrentUser() {
    return $_SESSION['logged_in_user'] ?? null;
}

function loginUser($usernameOrEmail, $password) {
    $users = getUsers();
    $input = strtolower(trim($usernameOrEmail));

    foreach ($users as $user) {
        if (strtolower($user['username']) === $input || strtolower($user['email']) === $input) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['logged_in_user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'avatar' => $user['avatar'] ?? '😎',
                    'created_at' => $user['created_at'] ?? date('Y-m-d')
                ];
                return ['success' => true, 'user' => $_SESSION['logged_in_user']];
            }
        }
    }

    return ['success' => false, 'message' => 'Invalid username or password'];
}

function logoutUser() {
    unset($_SESSION['logged_in_user']);
    session_destroy();
}
