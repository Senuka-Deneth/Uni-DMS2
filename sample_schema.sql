-- sample_schema.sql - Uni-DMS Database Schema

CREATE DATABASE IF NOT EXISTS uni_dms;
USE uni_dms;

-- Universities
CREATE TABLE universities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    description TEXT,
    image VARCHAR(255)
);

-- Faculties
CREATE TABLE faculties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    university_id INT,
    name VARCHAR(255) NOT NULL,
    FOREIGN KEY (university_id) REFERENCES universities(id) ON DELETE CASCADE
);

-- Departments
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT,
    name VARCHAR(255) NOT NULL,
    FOREIGN KEY (faculty_id) REFERENCES faculties(id) ON DELETE CASCADE
);

-- Degrees
CREATE TABLE degrees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT,
    name VARCHAR(255) NOT NULL,
    duration VARCHAR(50),
    description TEXT,
    medium VARCHAR(50),
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

-- Subjects
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    degree_id INT,
    name VARCHAR(255) NOT NULL,
    FOREIGN KEY (degree_id) REFERENCES degrees(id) ON DELETE CASCADE
);

-- Z-score Cutoffs
CREATE TABLE zscore_cutoffs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    degree_id INT,
    stream ENUM('Maths','Bio','Commerce','Arts'),
    cutoff DECIMAL(4,3),
    year YEAR,
    FOREIGN KEY (degree_id) REFERENCES degrees(id) ON DELETE CASCADE
);

-- Sample data for universities
INSERT INTO universities (name, location, description, image) VALUES
('University of Colombo', 'Colombo', 'Leading university in Sri Lanka', 'colombo.jpg'),
('University of Peradeniya', 'Peradeniya', 'Beautiful campus with diverse faculties', 'peradeniya.jpg');

-- Site users for login
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    fullname VARCHAR(255),
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (username, password_hash, fullname, email) VALUES
('student', '$2y$10$Vfvn5My7za1jGVGxa1Vje.3Xks77FNyNGWaSjERHWDQ6A9Rwwss/e%', 'Sample Student', 'student@example.com');