<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getPDO();

$config = [
    'table' => 'users',
    'title' => 'Patients',
    'where' => ['sql' => "role = 'patient'"],
    'fixed' => ['role' => 'patient'],
    'fields' => [
        'full_name' => ['label' => 'Full Name', 'type' => 'text', 'required' => true],
        'email'     => ['label' => 'Email', 'type' => 'text', 'required' => true],
        'phone'     => ['label' => 'Phone', 'type' => 'text', 'nullable' => true],
        'password'  => ['label' => 'Password', 'type' => 'password'],
    ],
    'list_columns' => ['full_name', 'email', 'phone'],
];
require_once __DIR__ . '/../includes/admin_layout_header.php';
require_once __DIR__ . '/../includes/crud_engine.php';
render_crud($pdo, $config);
require_once __DIR__ . '/../includes/admin_layout_footer.php';
