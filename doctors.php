<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getPDO();
$specs = $pdo->query("SELECT id, name FROM doctor_specializations ORDER BY name")->fetchAll();

$config = [
    'table' => 'doctors',
    'title' => 'Doctors',
    'fields' => [
        'full_name'            => ['label' => 'Full Name', 'type' => 'text', 'required' => true],
        'specialization_id'    => ['label' => 'Specialization', 'type' => 'select', 'options' => $specs, 'required' => true],
        'qualification'        => ['label' => 'Qualification', 'type' => 'text'],
        'experience_years'     => ['label' => 'Experience (years)', 'type' => 'number', 'step' => '1'],
        'consultation_fee'     => ['label' => 'Consultation Fee (৳)', 'type' => 'number', 'step' => '0.01'],
        'rating'               => ['label' => 'Rating', 'type' => 'number', 'step' => '0.1'],
        'available_online'     => ['label' => 'Available Online', 'type' => 'checkbox'],
        'available_home_visit' => ['label' => 'Available for Home Visit', 'type' => 'checkbox'],
    ],
    'list_columns' => ['full_name', 'specialization_id', 'consultation_fee', 'rating'],
];
require_once __DIR__ . '/../includes/admin_layout_header.php';
require_once __DIR__ . '/../includes/crud_engine.php';
render_crud($pdo, $config);
require_once __DIR__ . '/../includes/admin_layout_footer.php';
