<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getPDO();

$users = $pdo->query("SELECT id, full_name AS name FROM users ORDER BY full_name")->fetchAll();
$appts = $pdo->query("SELECT id, CONCAT('#', id, ' — ', appointment_date) AS name FROM appointments ORDER BY id DESC LIMIT 300")->fetchAll();

$config = [
    'table' => 'transactions',
    'title' => 'Transactions',
    'allow_add' => false,
    'fields' => [
        'user_id'         => ['label' => 'User', 'type' => 'select', 'options' => $users, 'required' => true],
        'appointment_id'  => ['label' => 'Appointment', 'type' => 'select', 'options' => $appts, 'nullable' => true],
        'amount'          => ['label' => 'Amount (৳)', 'type' => 'number', 'step' => '0.01', 'required' => true],
        'payment_method'  => ['label' => 'Payment Method', 'type' => 'select', 'enum' => ['bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket', 'bank' => 'Bank Transfer', 'card' => 'Card', 'cash' => 'Cash']],
        'status'          => ['label' => 'Status', 'type' => 'select', 'enum' => ['pending' => 'Pending', 'paid' => 'Paid', 'refunded' => 'Refunded']],
    ],
    'list_columns' => ['user_id', 'amount', 'payment_method', 'status'],
];
require_once __DIR__ . '/../includes/admin_layout_header.php';
require_once __DIR__ . '/../includes/crud_engine.php';
render_crud($pdo, $config);
require_once __DIR__ . '/../includes/admin_layout_footer.php';
