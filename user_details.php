<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getPDO();

$users = $pdo->query("SELECT id, full_name AS name FROM users ORDER BY full_name")->fetchAll();

$config = [
    'table' => 'user_details',
    'title' => 'User Details',
    'fields' => [
        'user_id'       => ['label' => 'User', 'type' => 'select', 'options' => $users, 'required' => true],
        'address'       => ['label' => 'Address', 'type' => 'text', 'nullable' => true],
        'city'          => ['label' => 'City', 'type' => 'text', 'nullable' => true],
        'date_of_birth' => ['label' => 'Date of Birth', 'type' => 'date', 'nullable' => true],
        'gender'        => ['label' => 'Gender', 'type' => 'select', 'enum' => ['' => 'Not set', 'male' => 'Male', 'female' => 'Female', 'other' => 'Other']],
        'blood_group'   => ['label' => 'Blood Group', 'type' => 'text', 'nullable' => true],
    ],
    'list_columns' => ['user_id', 'city', 'gender', 'blood_group'],
];
require_once __DIR__ . '/../includes/admin_layout_header.php';
require_once __DIR__ . '/../includes/crud_engine.php';
render_crud($pdo, $config);
require_once __DIR__ . '/../includes/admin_layout_footer.php';
