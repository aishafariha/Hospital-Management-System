<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getPDO();

$config = [
    'table' => 'rooms',
    'title' => 'Room Management',
    'fields' => [
        'room_number'   => ['label' => 'Room Number', 'type' => 'text', 'required' => true],
        'room_type'     => ['label' => 'Room Type', 'type' => 'select', 'enum' => ['general' => 'General', 'cabin' => 'Cabin', 'ICU' => 'ICU', 'deluxe' => 'Deluxe']],
        'price_per_day' => ['label' => 'Price per Day (৳)', 'type' => 'number', 'step' => '0.01', 'required' => true],
        'is_available'  => ['label' => 'Available', 'type' => 'checkbox'],
    ],
    'list_columns' => ['room_number', 'room_type', 'price_per_day', 'is_available'],
];
require_once __DIR__ . '/../includes/admin_layout_header.php';
require_once __DIR__ . '/../includes/crud_engine.php';
render_crud($pdo, $config);
require_once __DIR__ . '/../includes/admin_layout_footer.php';
