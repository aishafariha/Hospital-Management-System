<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getPDO();

$config = [
    'table' => 'discounts',
    'title' => 'Discounts',
    'fields' => [
        'code'        => ['label' => 'Code', 'type' => 'text', 'required' => true],
        'percentage'  => ['label' => 'Percentage (%)', 'type' => 'number', 'step' => '0.01', 'required' => true],
        'valid_until' => ['label' => 'Valid Until', 'type' => 'date', 'nullable' => true],
        'active'      => ['label' => 'Active', 'type' => 'checkbox'],
    ],
    'list_columns' => ['code', 'percentage', 'valid_until', 'active'],
];
require_once __DIR__ . '/../includes/admin_layout_header.php';
require_once __DIR__ . '/../includes/crud_engine.php';
render_crud($pdo, $config);
require_once __DIR__ . '/../includes/admin_layout_footer.php';
