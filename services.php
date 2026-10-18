<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getPDO();

$config = [
    'table' => 'hospital_services',
    'title' => 'Hospital Services',
    'fields' => [
        'name'        => ['label' => 'Name', 'type' => 'text', 'required' => true],
        'category'    => ['label' => 'Category', 'type' => 'select', 'enum' => ['online' => 'Online', 'in_hospital' => 'In-Hospital']],
        'description' => ['label' => 'Description', 'type' => 'textarea'],
        'icon'        => ['label' => 'Icon key', 'type' => 'text', 'default' => 'heart-pulse'],
        'base_price'  => ['label' => 'Base Price (৳)', 'type' => 'number', 'step' => '0.01'],
    ],
    'list_columns' => ['name', 'category', 'base_price'],
];
require_once __DIR__ . '/../includes/admin_layout_header.php';
require_once __DIR__ . '/../includes/crud_engine.php';
render_crud($pdo, $config);
require_once __DIR__ . '/../includes/admin_layout_footer.php';
