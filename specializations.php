<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getPDO();
$config = [
    'table' => 'doctor_specializations',
    'title' => 'Doctor Specializations',
    'fields' => [
        'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
        'icon' => ['label' => 'Icon key', 'type' => 'text', 'default' => 'stethoscope'],
    ],
    'list_columns' => ['name', 'icon'],
];
require_once __DIR__ . '/../includes/admin_layout_header.php';
require_once __DIR__ . '/../includes/crud_engine.php';
render_crud($pdo, $config);
require_once __DIR__ . '/../includes/admin_layout_footer.php';
