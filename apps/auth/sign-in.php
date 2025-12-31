<?php
// apps/auth/sign-in.php

session_start();

// Placeholder only: DB validation will be added after DB config + tables exist.
// For now, just redirect back with a flag.

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: /?error=empty');
    exit;
}

// TODO: validate against emr_users table (password_verify)
// TODO: load roles/permissions into session

$_SESSION['emr_user'] = [
    'username' => $username,
];

header('Location: /apps/simrs/home/index.php');
exit;
