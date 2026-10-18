-- =============================================================
-- Fariha Healthcare Module — Database Import Script
-- Matches the schema auto-created by config/database.php exactly.
-- Import this in phpMyAdmin (or `mysql -u root -p < fariha_healthcare.sql`)
-- if you'd rather set the database up manually than let the app
-- auto-create it on first run.
-- =============================================================

CREATE DATABASE IF NOT EXISTS `fariha_healthcare`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `fariha_healthcare`;

-- -------------------------------------------------------------
-- Marker table (app uses this to know seed data already ran)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS setup_marker (
    id INT PRIMARY KEY
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- users
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','patient','physician','vendor') NOT NULL DEFAULT 'patient',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- user_details
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS user_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    address VARCHAR(255) DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    date_of_birth DATE DEFAULT NULL,
    gender ENUM('male','female','other') DEFAULT NULL,
    blood_group VARCHAR(5) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- doctor_specializations
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS doctor_specializations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT 'stethoscope'
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- doctors
-- -------------------------------------------------------------
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
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- hospital_services
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS hospital_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category ENUM('online','in_hospital') NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    icon VARCHAR(50) DEFAULT 'heart-pulse',
    base_price DECIMAL(10,2) DEFAULT 0.00
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- rooms
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(20) NOT NULL,
    room_type ENUM('general','cabin','ICU','deluxe') NOT NULL,
    price_per_day DECIMAL(10,2) NOT NULL,
    is_available TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- discounts
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS discounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    percentage DECIMAL(5,2) NOT NULL,
    valid_until DATE DEFAULT NULL,
    active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- appointments
-- -------------------------------------------------------------
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
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- transactions
-- -------------------------------------------------------------
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
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- comments
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    display_name VARCHAR(100) NOT NULL,
    message VARCHAR(500) NOT NULL,
    rating TINYINT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =============================================================
-- Seed data (only inserted if the marker row isn't already there,
-- so re-running this file is safe and won't duplicate rows)
-- =============================================================

INSERT INTO setup_marker (id)
SELECT 1 WHERE NOT EXISTS (SELECT 1 FROM setup_marker WHERE id = 1);

-- Admin account: admin@fariha.com / Admin@123
-- (bcrypt hash below is valid for PHP's password_verify())
INSERT INTO users (full_name, email, password_hash, role)
SELECT 'Fariha Admin', 'admin@fariha.com',
       '$2b$10$CJH9yNQdcGhVTWXnATfBzeNjwE5tpW1s5p93wUKMs4EED7mSiCNjC', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@fariha.com');

INSERT INTO doctor_specializations (name)
SELECT * FROM (SELECT 'General Physician' AS name UNION ALL
               SELECT 'Cardiologist'      UNION ALL
               SELECT 'Dermatologist'     UNION ALL
               SELECT 'Pediatrician'      UNION ALL
               SELECT 'Gynecologist'      UNION ALL
               SELECT 'Orthopedic'        UNION ALL
               SELECT 'Neurologist'       UNION ALL
               SELECT 'Dentist') AS seed
WHERE NOT EXISTS (SELECT 1 FROM doctor_specializations LIMIT 1);

INSERT INTO doctors (full_name, specialization_id, qualification, experience_years, consultation_fee, rating, available_online, available_home_visit)
SELECT * FROM (
    SELECT 'Dr. Anika Rahman'   AS full_name, 1 AS specialization_id, 'MBBS, FCPS'            AS qualification, 8  AS experience_years, 600.00  AS consultation_fee, 4.5 AS rating, 1 AS available_online, 1 AS available_home_visit UNION ALL
    SELECT 'Dr. Kamal Hossain',      2, 'MBBS, MD (Cardiology)', 15, 1200.00, 4.5, 1, 0 UNION ALL
    SELECT 'Dr. Nusrat Jahan',       3, 'MBBS, DDV',              6,  700.00, 4.5, 1, 0 UNION ALL
    SELECT 'Dr. Rafiq Islam',        4, 'MBBS, DCH',             10,  500.00, 4.5, 1, 1 UNION ALL
    SELECT 'Dr. Farzana Akter',      5, 'MBBS, FCPS (Gynae)',    12,  900.00, 4.5, 1, 0 UNION ALL
    SELECT 'Dr. Shakil Ahmed',       6, 'MBBS, MS (Ortho)',       9, 1000.00, 4.5, 1, 0
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM doctors LIMIT 1);

INSERT INTO hospital_services (name, category, description, icon, base_price)
SELECT * FROM (
    SELECT 'Medicine Delivery at Home' AS name, 'online' AS category, 'Get prescribed medicines delivered to your doorstep' AS description, 'pill' AS icon, 0.00 AS base_price UNION ALL
    SELECT 'Doctor Appointment',        'online', 'Book a consultation with certified doctors',            'calendar-heart', 500.00  UNION ALL
    SELECT 'Home Doctor Service',       'online', 'A doctor visits you at home',                            'house-medical',  1500.00 UNION ALL
    SELECT 'Home Nursing Service',      'online', 'Professional nursing care at home — general, post-surgery, elderly, pediatric, palliative, wound dressing, and medication support', 'nurse', 1200.00 UNION ALL
    SELECT 'Ambulance Service',         'online', '24/7 emergency ambulance dispatch',                     'ambulance',      800.00  UNION ALL
    SELECT 'Laboratory Test Booking',   'online', 'Book diagnostic lab tests online',                       'flask',          300.00  UNION ALL
    SELECT 'Prescription Upload',       'online', 'Upload a prescription for quick ordering',               'file-upload',    0.00    UNION ALL
    SELECT 'Health Checkup',            'online', 'Comprehensive full-body checkup packages',               'heart-pulse',    2500.00 UNION ALL
    SELECT 'Vaccination Service',       'online', 'Book vaccination appointments at home or clinic',        'syringe',        600.00
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM hospital_services LIMIT 1);

INSERT INTO rooms (room_number, room_type, price_per_day)
SELECT * FROM (
    SELECT 'G-101'   AS room_number, 'general' AS room_type, 1500.00 AS price_per_day UNION ALL
    SELECT 'C-201',  'cabin',   3500.00 UNION ALL
    SELECT 'ICU-301','ICU',     8000.00 UNION ALL
    SELECT 'D-401',  'deluxe',  6000.00
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM rooms LIMIT 1);

INSERT INTO discounts (code, percentage, valid_until)
SELECT 'WELCOME10', 10.00, DATE_ADD(CURDATE(), INTERVAL 90 DAY)
WHERE NOT EXISTS (SELECT 1 FROM discounts WHERE code = 'WELCOME10');

INSERT INTO comments (display_name, message, rating)
SELECT * FROM (
    SELECT 'Tanvir Hasan' AS display_name, 'Booked a home doctor visit within an hour. Genuinely impressed by how smooth it was.' AS message, 5 AS rating UNION ALL
    SELECT 'Mim Akter',   'The medicine delivery service saved me a trip during monsoon season. Highly recommend.', 5 UNION ALL
    SELECT 'Imran Kabir', 'Lab test booking was simple and the reports arrived digitally the next day.', 4
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM comments LIMIT 1);

-- =============================================================
-- Done. Database `fariha_healthcare` is ready to use.
-- Demo admin login: admin@fariha.com / Admin@123
-- =============================================================
