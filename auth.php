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
    if (!empty($_SESSION['logged_in_user'])) {
        return true;
    }
    if (!empty($_COOKIE['cinepulse_user'])) {
        $userData = json_decode($_COOKIE['cinepulse_user'], true);
        if (!empty($userData) && is_array($userData)) {
            $_SESSION['logged_in_user'] = $userData;
            return true;
        }
    }
    return false;
}

function requireLogin() {
    if (!isLoggedIn()) {
        $currentPage = basename($_SERVER['PHP_SELF'] ?? '');
        if ($currentPage === 'login.php') {
            return;
        }
        $target = urlencode($_SERVER['REQUEST_URI'] ?? 'index.php');
        header("Location: login.php?msg=login_required&redirect={$target}");
        exit;
    }
}

function getCurrentUser() {
    if (isLoggedIn()) {
        return $_SESSION['logged_in_user'] ?? null;
    }
    return null;
}

function loginUser($usernameOrEmail, $password) {
    $users = getUsers();
    $input = strtolower(trim($usernameOrEmail));

    foreach ($users as $user) {
        if (strtolower($user['username']) === $input || strtolower($user['email']) === $input) {
            if (password_verify($password, $user['password'])) {
                $userData = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'avatar' => $user['avatar'] ?? '😎',
                    'created_at' => $user['created_at'] ?? date('Y-m-d')
                ];
                $_SESSION['logged_in_user'] = $userData;
                // Persistent cookie for serverless compatibility across lambdas
                setcookie('cinepulse_user', json_encode($userData), time() + (86400 * 30), "/");
                return ['success' => true, 'user' => $userData];
            }
        }
    }

    return ['success' => false, 'message' => 'Invalid username or password'];
}

function logoutUser() {
    unset($_SESSION['logged_in_user']);
    setcookie('cinepulse_user', '', time() - 3600, "/");
    session_destroy();
}
