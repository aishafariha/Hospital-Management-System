<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getPDO();

$config = [
    'table' => 'users',
    'title' => 'Users',
    'fields' => [
        'full_name' => ['label' => 'Full Name', 'type' => 'text', 'required' => true],
        'email'     => ['label' => 'Email', 'type' => 'text', 'required' => true],
        'phone'     => ['label' => 'Phone', 'type' => 'text', 'nullable' => true],
        'role'      => ['label' => 'Role', 'type' => 'select', 'enum' => ['admin' => 'Admin', 'patient' => 'Patient', 'physician' => 'Physician', 'vendor' => 'Vendor']],
        'password'  => ['label' => 'Password', 'type' => 'password'],
    ],
    'list_columns' => ['full_name', 'email', 'phone', 'role'],
];
require_once __DIR__ . '/../includes/admin_layout_header.php';
require_once __DIR__ . '/../includes/crud_engine.php';
render_crud($pdo, $config);
require_once __DIR__ . '/../includes/admin_layout_footer.php';
