<?php
/**
 * CinePulse - Authentication & User Session Manager
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('USERS_FILE', __DIR__ . '/data/users.json');

// Ensure data directory exists
if (!is_dir(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0777, true);
}

// Ensure default user joshuagwapo is saved
$defaultUsers = [
    [
        'id' => 1,
        'username' => 'joshuagwapo',
        'email' => 'joshua@cinepulse.com',
        'password' => password_hash('mamagwapa12', PASSWORD_BCRYPT),
        'created_at' => date('Y-m-d H:i:s'),
        'avatar' => '😎'
    ]
];
file_put_contents(USERS_FILE, json_encode($defaultUsers, JSON_PRETTY_PRINT));

function getUsers() {
    if (!file_exists(USERS_FILE)) return [];
    $data = file_get_contents(USERS_FILE);
    return json_decode($data, true) ?: [];
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
