-- ============================================
-- HRMU Employee Clearance Management System
-- Complete database setup (schema + seed data)
-- Run: mysql -u root < database.sql
-- Or use setup.php in the browser
-- ============================================

CREATE DATABASE IF NOT EXISTS hmru_clearance;
USE hmru_clearance;

-- --------------------------------------------
-- Schema
-- --------------------------------------------

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    role_desc VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    gmail_address VARCHAR(100),
    role_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) NOT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dept_name VARCHAR(255) NOT NULL,
    dept_code VARCHAR(50) UNIQUE,
    dept_type ENUM('college', 'office', 'unit') DEFAULT 'office',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    employee_id VARCHAR(50) UNIQUE,
    department_id INT,
    position_title VARCHAR(255),
    employee_type ENUM('regular_instructor', 'visiting_lecturer', 'non_teaching') DEFAULT 'regular_instructor',
    classification VARCHAR(100),
    contact_number VARCHAR(20),
    address TEXT,
    date_hired DATE DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS offices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    office_name VARCHAR(255) NOT NULL,
    office_code VARCHAR(50) UNIQUE,
    department_id INT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS signatory_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    office_id INT NOT NULL,
    department_id INT DEFAULT NULL,
    designation VARCHAR(255),
    signature_image VARCHAR(255) DEFAULT NULL,
    signature_hash VARCHAR(64) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS clearance_periods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    period_name VARCHAR(255) NOT NULL,
    academic_year VARCHAR(20),
    semester VARCHAR(50),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active TINYINT(1) DEFAULT 0,
    status ENUM('pending', 'active', 'closed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Must exist before clearance_requests (FK dependency)
CREATE TABLE IF NOT EXISTS clearance_workflow (
    id INT AUTO_INCREMENT PRIMARY KEY,
    workflow_name VARCHAR(255) NOT NULL,
    period_id INT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (period_id) REFERENCES clearance_periods(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS clearance_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    period_id INT NOT NULL,
    workflow_id INT DEFAULT NULL,
    purpose TEXT DEFAULT NULL,
    clearance_code VARCHAR(50) UNIQUE NOT NULL,
    status ENUM('draft', 'pending', 'in_progress', 'completed', 'rejected', 'revoked') DEFAULT 'draft',
    current_step INT DEFAULT 0,
    total_steps INT DEFAULT 0,
    pdf_path VARCHAR(255) DEFAULT NULL,
    qr_code VARCHAR(255) DEFAULT NULL,
    hash_validation VARCHAR(64) DEFAULT NULL,
    submitted_at DATETIME DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (period_id) REFERENCES clearance_periods(id) ON DELETE CASCADE,
    FOREIGN KEY (workflow_id) REFERENCES clearance_workflow(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS workflow_steps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    workflow_id INT NOT NULL,
    office_id INT DEFAULT NULL,
    step_order INT NOT NULL,
    is_mandatory TINYINT(1) DEFAULT 1,
    is_dynamic TINYINT(1) DEFAULT 0,
    dynamic_field VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workflow_id) REFERENCES clearance_workflow(id) ON DELETE CASCADE,
    FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS clearance_signatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clearance_id INT NOT NULL,
    step_id INT NOT NULL,
    signatory_id INT NOT NULL,
    signature_image VARCHAR(255) DEFAULT NULL,
    signature_hash VARCHAR(64) DEFAULT NULL,
    action ENUM('pending', 'signed', 'rejected', 'returned', 'orphaned') DEFAULT 'pending',
    remarks TEXT,
    ip_address VARCHAR(45),
    browser_info TEXT,
    device_info TEXT,
    signed_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (clearance_id) REFERENCES clearance_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (step_id) REFERENCES workflow_steps(id) ON DELETE CASCADE,
    FOREIGN KEY (signatory_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS clearance_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clearance_id INT NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    document_path VARCHAR(255) NOT NULL,
    document_type VARCHAR(100),
    uploaded_by INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (clearance_id) REFERENCES clearance_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    username VARCHAR(100),
    role_name VARCHAR(50),
    action_type VARCHAR(50) NOT NULL,
    module VARCHAR(100),
    description TEXT,
    ip_address VARCHAR(45),
    browser_info TEXT,
    device_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS verification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clearance_code VARCHAR(50) NOT NULL,
    verifier_ip VARCHAR(45),
    verifier_browser TEXT,
    verification_result ENUM('verified', 'revoked', 'tampered', 'invalid') NOT NULL,
    verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_group VARCHAR(50) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------
-- Seed data
-- --------------------------------------------

INSERT IGNORE INTO roles (role_name, role_desc) VALUES
('admin', 'System Administrator'),
('employee', 'Faculty/Employee'),
('signatory', 'Office Signatory');

INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_group) VALUES
('university_name', 'Zamboanga Peninsula Polytechnic State University', 'general'),
('system_name', 'HRMU Employee Clearance Management System', 'general'),
('smtp_host', '', 'email'),
('smtp_port', '587', 'email'),
('smtp_username', '', 'email'),
('smtp_password', '', 'email'),
('smtp_encryption', 'tls', 'email'),
('from_email', '', 'email'),
('from_name', 'HRMU Clearance System', 'email'),
('dean_workflow_enabled', '1', 'workflow'),
('qr_enabled', '1', 'security'),
('signature_required', '1', 'security');

INSERT IGNORE INTO departments (dept_name, dept_code, dept_type) VALUES
('College of Maritime Engineering', 'CME', 'college'),
('College of Information and Computing Science', 'CICS', 'college'),
('College of Teacher Education', 'CTE', 'college'),
('Department of Technical Education', 'DTE', 'college'),
('College of Arts, Humanities and Social Sciences', 'CAHSS', 'college'),
('Senior High School', 'SHS', 'college'),
('College of Physical Education and Sports', 'CPES', 'college'),
('College of Engineering and Technology', 'CET', 'college'),
('Graduate Studies', 'GS', 'college'),
('School of Business Administration', 'SBA', 'college'),
('National Service Training Program', 'NSTP', 'college');

INSERT IGNORE INTO offices (office_name, office_code, department_id)
SELECT CONCAT(
    CASE
        WHEN d.dept_name LIKE '%Department%' OR d.dept_name LIKE '%NSTP%' OR UPPER(IFNULL(d.dept_code, '')) = 'NSTP' THEN 'Director'
        WHEN d.dept_name LIKE '%Senior High School%' OR UPPER(IFNULL(d.dept_code, '')) = 'SHS' THEN 'Principal'
        WHEN d.dept_name LIKE '%School of Business%' OR UPPER(IFNULL(d.dept_code, '')) = 'SBA' THEN 'OIC-Dean'
        ELSE 'Dean'
    END,
    ', ',
    d.dept_name
),
CASE
    WHEN d.dept_name LIKE '%Department%' OR d.dept_name LIKE '%NSTP%' OR UPPER(IFNULL(d.dept_code, '')) = 'NSTP'
        THEN CONCAT(UPPER(IFNULL(NULLIF(d.dept_code, ''), 'DEPT')), '_HEAD')
    WHEN d.dept_name LIKE '%Senior High School%' OR UPPER(IFNULL(d.dept_code, '')) = 'SHS'
        THEN CONCAT(UPPER(IFNULL(NULLIF(d.dept_code, ''), 'SHS')), '_HEAD')
    WHEN d.dept_name LIKE '%School of Business%' OR UPPER(IFNULL(d.dept_code, '')) = 'SBA'
        THEN CONCAT(UPPER(IFNULL(NULLIF(d.dept_code, ''), 'SBA')), '_HEAD')
    ELSE CONCAT('DEAN_', UPPER(IFNULL(NULLIF(d.dept_code, ''), 'DEPT')))
END,
d.id
FROM departments d
WHERE d.dept_type = 'college'
  AND NOT EXISTS (
      SELECT 1 FROM offices o
      WHERE o.department_id = d.id
        AND (
            o.office_code LIKE 'DEAN_%'
            OR o.office_name LIKE 'Dean, %'
            OR o.office_name LIKE 'Director, %'
            OR o.office_name LIKE 'Principal, %'
            OR o.office_name LIKE 'OIC-Dean, %'
        )
  );

INSERT IGNORE INTO offices (office_name, office_code) VALUES
('Registrar Office', 'REG'),
('Cashier Office', 'CASH'),
('University Accountant', 'ACCT'),
('Learning Commons Center', 'LIB'),
('Supply and Property Management Unit', 'SPMU'),
('Chief Administrative Officer', 'CAO'),
('VP for Admin and Finance', 'VPAF'),
('VP for Academic Affairs', 'VPAA'),
('VP for Research and Extension', 'VPRE'),
('Legal Officer', 'LEGAL'),
('VP for Student Affairs and Services', 'VPSAS'),
('President', 'PRES');

-- --------------------------------------------
-- Upgrades for older databases (safe to re-run)
-- --------------------------------------------

ALTER TABLE users ADD COLUMN IF NOT EXISTS middle_name VARCHAR(100) DEFAULT NULL AFTER first_name;
ALTER TABLE signatory_profiles ADD COLUMN IF NOT EXISTS department_id INT DEFAULT NULL AFTER office_id;
ALTER TABLE clearance_requests ADD COLUMN IF NOT EXISTS workflow_id INT DEFAULT NULL AFTER period_id;
ALTER TABLE clearance_requests ADD COLUMN IF NOT EXISTS purpose TEXT DEFAULT NULL AFTER workflow_id;
ALTER TABLE workflow_steps MODIFY office_id INT NULL;
ALTER TABLE clearance_signatures MODIFY action ENUM('pending', 'signed', 'rejected', 'returned', 'orphaned') DEFAULT 'pending';

UPDATE system_settings SET setting_value = 'Zamboanga Peninsula Polytechnic State University'
WHERE setting_key = 'university_name' AND (setting_value LIKE 'HMRU%' OR setting_value LIKE '%Holy Mary%');

UPDATE notifications SET link = REPLACE(link, '../', '') WHERE link LIKE '../%';
