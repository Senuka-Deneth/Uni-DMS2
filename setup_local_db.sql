-- ============================================
-- Uni-DMS Local Database Setup (XAMPP)
-- ============================================
-- How to run (choose one method):
--
-- Method 1 — phpMyAdmin:
--   Open http://localhost/phpmyadmin
--   Click "Import" tab → select this file → click "Go"
--
-- Method 2 — XAMPP Shell:
--   Open XAMPP Control Panel → Shell button
--   Run: mysql -u root uni_dms < setup_local_db.sql
--
-- Method 3 — Command Prompt (Windows):
--   cd C:\xampp\mysql\bin
--   mysql.exe -u root uni_dms < C:\path\to\setup_local_db.sql
-- ============================================

CREATE DATABASE IF NOT EXISTS `uni_dms`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `uni_dms`;

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS universities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255) DEFAULT NULL,
    type ENUM('Government','Private','Foreign') DEFAULT 'Government',
    website VARCHAR(255) DEFAULT NULL,
    contact VARCHAR(100) DEFAULT NULL,
    established_year YEAR DEFAULT NULL,
    description TEXT,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS faculties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    university_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (university_id) REFERENCES universities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES faculties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS degrees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    faculty VARCHAR(255) DEFAULT NULL,
    duration VARCHAR(50) DEFAULT NULL,
    degree_type VARCHAR(100) DEFAULT NULL,
    stream_requirement VARCHAR(100) DEFAULT NULL,
    min_zscore DECIMAL(4,3) DEFAULT NULL,
    medium VARCHAR(50) DEFAULT NULL,
    description TEXT,
    career_paths TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    degree_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    FOREIGN KEY (degree_id) REFERENCES degrees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS zscore_cutoffs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    degree_id INT NOT NULL,
    stream ENUM('Maths','Bio','Commerce','Arts','Physical Science','Biological Science') NOT NULL,
    cutoff DECIMAL(4,3) NOT NULL,
    year YEAR DEFAULT NULL,
    FOREIGN KEY (degree_id) REFERENCES degrees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS extracurricular_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    university_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) DEFAULT 'General',
    description TEXT,
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (university_id) REFERENCES universities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    fullname VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    fullname VARCHAR(255),
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- Sample seed data:
INSERT INTO universities (name, location, type, website, contact, established_year, description, image) VALUES
('University of Colombo', 'Colombo', 'Government', 'https://www.cmb.ac.lk', '+94 11 250 2400', 1921, 'Leading research university located in the heart of Colombo.', 'colombo.jpg'),
('University of Peradeniya', 'Peradeniya', 'Government', 'https://www.pdn.ac.lk', '+94 81 238 9000', 1942, 'Historic campus nestled among hills with strong STEM and arts faculties.', 'peradeniya.jpg'),
('University of Moratuwa', 'Moratuwa', 'Government', 'https://www.mrt.ac.lk', '+94 11 265 0505', 1972, 'Engineering-focused university with innovation and tech hubs.', 'moratuwa.jpg');

INSERT INTO faculties (university_id, name) VALUES
(1, 'Faculty of Science'),
(2, 'Faculty of Engineering'),
(3, 'Faculty of Information Technology');

INSERT INTO departments (faculty_id, name) VALUES
(1, 'Department of Mathematics'),
(1, 'Department of Statistics'),
(2, 'Department of Civil Engineering'),
(3, 'Department of Computer Science'),
(3, 'Department of Information Systems');

INSERT INTO degrees (department_id, name, faculty, duration, degree_type, stream_requirement, min_zscore, medium, description, career_paths) VALUES
(1, 'Bachelor of Science in Mathematics', 'Faculty of Science', '4 years', 'BSc', 'Maths', 1.95, 'English', 'Theory-driven mathematics degree with research opportunities.', 'Academia, Finance, Data Science'),
(3, 'Bachelor of Science in Civil Engineering', 'Faculty of Engineering', '4 years', 'BScEng', 'Physical Science', 1.85, 'English', 'Civil engineering degree preparing students for infrastructure design.', 'Construction, Project Management, Consulting'),
(4, 'Bachelor of Science in Information Technology', 'Faculty of Information Technology', '4 years', 'BSc', 'Physical Science', 2.05, 'English', 'Practical IT degree with industry-grade labs.', 'Software Development, Cybersecurity, Product Management'),
(5, 'Bachelor of Science in Data Science', 'Faculty of Information Technology', '4 years', 'BSc', 'Maths', 2.20, 'English', 'Data science program blending statistics and engineering.', 'Data Analytics, AI, Research'),
(2, 'Bachelor of Science in Applied Statistics', 'Faculty of Science', '4 years', 'BSc', 'Maths', 1.90, 'English', 'Statistics degree for research and analytics careers.', 'Research, Finance, Public Sector');

INSERT INTO zscore_cutoffs (degree_id, stream, cutoff, year) VALUES
(1, 'Maths', 1.98, 2025),
(2, 'Maths', 1.90, 2025),
(3, 'Physical Science', 2.10, 2025),
(4, 'Maths', 2.25, 2025),
(5, 'Maths', 1.92, 2025);

INSERT INTO extracurricular_activities (university_id, name, category, description, is_available) VALUES
(1, 'Colombo Debate Union', 'Clubs', 'Competitive debating and public speaking academy.', 1),
(2, 'Peradeniya Adventure Squad', 'Sports', 'Outdoor adventure club organizing hiking and camping.', 1),
(3, 'Moratuwa Robotics Lab', 'Community', 'Student-led robotics research and competitions.', 1);

INSERT INTO subjects (degree_id, name) VALUES
(1, 'Calculus I'),
(3, 'Structural Analysis'),
(4, 'Data Engineering Foundations');

INSERT INTO users (username, password_hash, fullname, email) VALUES
('student', '$2y$10$Vfvn5My7za1jGVGxa1Vje.3Xks77FNyNGWaSjERHWDQ6A9Rwwss/e%', 'Sample Student', 'student@example.com');

INSERT INTO admin_users (username, password_hash, fullname) VALUES
('admin', '$2y$12$LTLX0c6VE/8.s2HveoT5POZvBuUXWQWTk39UqXd0YTL6bIbwCcP9y%', 'System Administrator');
