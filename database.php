<?php
/**
 * Fariha Healthcare Module — Auto-Configuring Database Layer
 * -----------------------------------------------------------
 * No manual phpMyAdmin steps required. On first load this file will:
 *   1. Connect to MySQL (no DB selected)
 *   2. Create the `fariha_healthcare` database if it doesn't exist
 *   3. Create every required table if it doesn't exist
 *   4. Seed demo data (specializations, doctors, services, an admin user)
 * Edit the constants below only if your MySQL credentials differ.
 */

// ---- Connection settings (defaults match XAMPP / Laragon out of the box) ----
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'fariha_healthcare');

function getPDO(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        // Step 1: connect without a database selected
        $root = new PDO(
            "mysql:host=" . DB_HOST . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Step 2: create the database if missing
        $root->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Step 3: connect to the actual database
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // real prepared statements
            ]
        );

        autoSetupSchema($pdo);

        return $pdo;
    } catch (PDOException $e) {
        die('<div style="font-family:sans-serif;padding:40px;color:#b00">
                <h2>Database connection failed</h2>
                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                <p>Check that MySQL is running (XAMPP / Laragon) and that DB_HOST / DB_USER / DB_PASS in
                <code>config/database.php</code> match your local setup.</p>
             </div>');
    }
}

function autoSetupSchema(PDO $pdo): void {
    // Guard: only run full setup once per app lifetime using a marker table
    $pdo->exec("CREATE TABLE IF NOT EXISTS setup_marker (id INT PRIMARY KEY)");
    $already = $pdo->query("SELECT COUNT(*) c FROM setup_marker")->fetch()['c'];

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            phone VARCHAR(30) DEFAULT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('admin','patient','physician','vendor') NOT NULL DEFAULT 'patient',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_details (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            address VARCHAR(255) DEFAULT NULL,
            city VARCHAR(100) DEFAULT NULL,
            date_of_birth DATE DEFAULT NULL,
            gender ENUM('male','female','other') DEFAULT NULL,
            blood_group VARCHAR(5) DEFAULT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS doctor_specializations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            icon VARCHAR(50) DEFAULT 'stethoscope'
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS doctors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT DEFAULT NULL,
            full_name VARCHAR(150) NOT NULL,
            specialization_id INT NOT NULL,
            qualification VARCHAR(150) DEFAULT NULL,
            experience_years INT DEFAULT 0,
            consultation_fee DECIMAL(10,2) DEFAULT 500.00,
            rating DECIMAL(2,1) DEFAULT 4.5,
            available_online TINYINT(1) DEFAULT 1,
            available_home_visit TINYINT(1) DEFAULT 0,
            photo VARCHAR(255) DEFAULT NULL,
            FOREIGN KEY (specialization_id) REFERENCES doctor_specializations(id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS hospital_services (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            category ENUM('online','in_hospital') NOT NULL,
            description VARCHAR(255) DEFAULT NULL,
            icon VARCHAR(50) DEFAULT 'heart-pulse',
            base_price DECIMAL(10,2) DEFAULT 0.00
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS rooms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            room_number VARCHAR(20) NOT NULL,
            room_type ENUM('general','cabin','ICU','deluxe') NOT NULL,
            price_per_day DECIMAL(10,2) NOT NULL,
            is_available TINYINT(1) DEFAULT 1
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS discounts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(30) NOT NULL UNIQUE,
            percentage DECIMAL(5,2) NOT NULL,
            valid_until DATE DEFAULT NULL,
            active TINYINT(1) DEFAULT 1
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS appointments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            doctor_id INT NOT NULL,
            service_id INT DEFAULT NULL,
            room_id INT DEFAULT NULL,
            appointment_date DATE NOT NULL,
            appointment_time TIME NOT NULL,
            mode ENUM('online','home_visit','in_hospital') DEFAULT 'online',
            status ENUM('pending','confirmed','completed','cancelled') DEFAULT 'pending',
            notes VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
            FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL
        ) ENGINE=InnoDB
    ");

    // Migration: add room_id to appointments if this table already existed from an earlier version
    $col = $pdo->query("
        SELECT COUNT(*) c FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'appointments' AND COLUMN_NAME = 'room_id'
    ")->fetch()['c'];
    if ((int) $col === 0) {
        $pdo->exec("ALTER TABLE appointments ADD COLUMN room_id INT DEFAULT NULL AFTER service_id");
        $pdo->exec("ALTER TABLE appointments ADD FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL");
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            appointment_id INT DEFAULT NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_method ENUM('bkash','nagad','rocket','bank','card','cash') DEFAULT 'bkash',
            status ENUM('pending','paid','refunded') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL
        ) ENGINE=InnoDB
    ");

    // Migration: widen payment_method to the new mobile-money options for databases created before this update
    $pmCol = $pdo->query("
        SELECT COLUMN_TYPE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions' AND COLUMN_NAME = 'payment_method'
    ")->fetch();
    if ($pmCol && strpos($pmCol['COLUMN_TYPE'], 'bkash') === false) {
        $pdo->exec("UPDATE transactions SET payment_method = 'bkash' WHERE payment_method = 'mobile_banking'");
        $pdo->exec("ALTER TABLE transactions MODIFY COLUMN payment_method ENUM('bkash','nagad','rocket','bank','card','cash') DEFAULT 'bkash'");
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT DEFAULT NULL,
            display_name VARCHAR(100) NOT NULL,
            message VARCHAR(500) NOT NULL,
            rating TINYINT DEFAULT 5,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB
    ");

    if ($already > 0) {
        return; // already seeded
    }

    // ---- Seed data (runs once) ----
    $pdo->exec("INSERT INTO setup_marker (id) VALUES (1)");

    // Admin account: email admin@fariha.com / password Admin@123
    $adminHash = password_hash('Admin@123', PASSWORD_BCRYPT);
    $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (?,?,?, 'admin')")
        ->execute(['Fariha Admin', 'admin@fariha.com', $adminHash]);

    $specializations = ['General Physician','Cardiologist','Dermatologist','Pediatrician','Gynecologist','Orthopedic','Neurologist','Dentist'];
    $stmt = $pdo->prepare("INSERT INTO doctor_specializations (name) VALUES (?)");
    foreach ($specializations as $s) { $stmt->execute([$s]); }

    $doctors = [
        ['Dr. Anika Rahman', 1, 'MBBS, FCPS', 8, 600, 1, 1],
        ['Dr. Kamal Hossain', 2, 'MBBS, MD (Cardiology)', 15, 1200, 1, 0],
        ['Dr. Nusrat Jahan', 3, 'MBBS, DDV', 6, 700, 1, 0],
        ['Dr. Rafiq Islam', 4, 'MBBS, DCH', 10, 500, 1, 1],
        ['Dr. Farzana Akter', 5, 'MBBS, FCPS (Gynae)', 12, 900, 1, 0],
        ['Dr. Shakil Ahmed', 6, 'MBBS, MS (Ortho)', 9, 1000, 1, 0],
    ];
    $stmt = $pdo->prepare("INSERT INTO doctors (full_name, specialization_id, qualification, experience_years, consultation_fee, available_online, available_home_visit) VALUES (?,?,?,?,?,?,?)");
    foreach ($doctors as $d) { $stmt->execute($d); }

    $onlineServices = [
        ['Medicine Delivery at Home','online','Get prescribed medicines delivered to your doorstep','pill', 0],
        ['Doctor Appointment','online','Book a consultation with certified doctors','calendar-heart', 500],
        ['Home Doctor Service','online','A doctor visits you at home','house-medical', 1500],
        ['Home Nursing Service','online','Professional nursing care at home — general, post-surgery, elderly, pediatric, palliative, wound dressing, and medication support','nurse', 1200],
        ['Ambulance Service','online','24/7 emergency ambulance dispatch','ambulance', 800],
        ['Laboratory Test Booking','online','Book diagnostic lab tests online','flask', 300],
        ['Prescription Upload','online','Upload a prescription for quick ordering','file-upload', 0],
        ['Health Checkup','online','Comprehensive full-body checkup packages','heart-pulse', 2500],
        ['Vaccination Service','online','Book vaccination appointments at home or clinic','syringe', 600],
    ];
    $stmt = $pdo->prepare("INSERT INTO hospital_services (name, category, description, icon, base_price) VALUES (?,?,?,?,?)");
    foreach ($onlineServices as $s) { $stmt->execute($s); }

    $rooms = [
        ['G-101','general', 1500],
        ['C-201','cabin', 3500],
        ['ICU-301','ICU', 8000],
        ['D-401','deluxe', 6000],
    ];
    $stmt = $pdo->prepare("INSERT INTO rooms (room_number, room_type, price_per_day) VALUES (?,?,?)");
    foreach ($rooms as $r) { $stmt->execute($r); }

    $pdo->exec("INSERT INTO discounts (code, percentage, valid_until) VALUES ('WELCOME10', 10.00, DATE_ADD(CURDATE(), INTERVAL 90 DAY))");

    $comments = [
        ['Tanvir Hasan', 'Booked a home doctor visit within an hour. Genuinely impressed by how smooth it was.', 5],
        ['Mim Akter', 'The medicine delivery service saved me a trip during monsoon season. Highly recommend.', 5],
        ['Imran Kabir', 'Lab test booking was simple and the reports arrived digitally the next day.', 4],
    ];
    $stmt = $pdo->prepare("INSERT INTO comments (display_name, message, rating) VALUES (?,?,?)");
    foreach ($comments as $c) { $stmt->execute($c); }
}
