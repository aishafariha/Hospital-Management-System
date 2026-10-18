<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getPDO();

$patients = $pdo->query("SELECT id, full_name AS name FROM users WHERE role='patient' ORDER BY full_name")->fetchAll();
$doctors  = $pdo->query("SELECT id, full_name AS name FROM doctors ORDER BY full_name")->fetchAll();
$services = $pdo->query("SELECT id, name FROM hospital_services ORDER BY name")->fetchAll();
$rooms    = $pdo->query("SELECT id, CONCAT(room_number, ' (', room_type, ')') AS name FROM rooms ORDER BY room_number")->fetchAll();

$config = [
    'table' => 'appointments',
    'title' => 'Appointments',
    'fields' => [
        'patient_id'        => ['label' => 'Patient', 'type' => 'select', 'options' => $patients, 'required' => true],
        'doctor_id'         => ['label' => 'Doctor', 'type' => 'select', 'options' => $doctors, 'required' => true],
        'service_id'        => ['label' => 'Related Service', 'type' => 'select', 'options' => $services, 'nullable' => true],
        'room_id'           => ['label' => 'Reserved Room', 'type' => 'select', 'options' => $rooms, 'nullable' => true],
        'appointment_date'  => ['label' => 'Date', 'type' => 'date', 'required' => true],
        'appointment_time'  => ['label' => 'Time', 'type' => 'time', 'required' => true],
        'mode'              => ['label' => 'Visit Type', 'type' => 'select', 'enum' => ['online' => 'Online', 'home_visit' => 'Home Visit', 'in_hospital' => 'In-Hospital']],
        'status'            => ['label' => 'Status', 'type' => 'select', 'enum' => ['pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled']],
        'notes'             => ['label' => 'Notes', 'type' => 'textarea'],
    ],
    'list_columns' => ['patient_id', 'doctor_id', 'room_id', 'appointment_date', 'mode', 'status'],
];
require_once __DIR__ . '/../includes/admin_layout_header.php';
require_once __DIR__ . '/../includes/crud_engine.php';
render_crud($pdo, $config);
require_once __DIR__ . '/../includes/admin_layout_footer.php';
