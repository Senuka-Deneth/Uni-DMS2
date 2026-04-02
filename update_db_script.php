<?php
require_once 'includes/db.php';

// Allow large transactions
$conn->query("SET FOREIGN_KEY_CHECKS = 0;");

// Update degrees table to include specific fields if necessary or map using zscore_cutoffs.
// Best approach: Add columns to zscore_cutoffs
$conn->query("ALTER TABLE zscore_cutoffs ADD COLUMN subject1 VARCHAR(100) DEFAULT NULL");
$conn->query("ALTER TABLE zscore_cutoffs ADD COLUMN subject2 VARCHAR(100) DEFAULT NULL");
$conn->query("ALTER TABLE zscore_cutoffs ADD COLUMN subject3 VARCHAR(100) DEFAULT NULL");
$conn->query("ALTER TABLE zscore_cutoffs ADD COLUMN district VARCHAR(100) DEFAULT NULL");

// Clear existing to avoid duplicate issues on re-run (or just append, but we might truncate)
$conn->query("TRUNCATE TABLE zscore_cutoffs");

// Universities mapped internally or just insert new ones if missing.
// Let's add the data provided by the user.
$data = [
    // Moratuwa (id: 3)
    ['University of Moratuwa', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Colombo', 2.07],
    ['University of Moratuwa', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Gampaha', 2.08],
    ['University of Moratuwa', 'BSc Engineering Honours - Earth Resources Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Colombo', 1.61],
    ['University of Moratuwa', 'BSc Engineering Honours - Earth Resources Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Gampaha', 1.67],
    ['University of Moratuwa', 'BSc Engineering Honours - Textile & Apparel Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Colombo', 1.62],
    ['University of Moratuwa', 'BSc Engineering Honours - Textile & Apparel Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Gampaha', 1.70],
    ['University of Moratuwa', 'Transport Management & Logistics Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Colombo', 1.45],
    ['University of Moratuwa', 'Transport Management & Logistics Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Gampaha', 1.55],
    ['University of Moratuwa', 'BSc Honours in Artificial Intelligence', 'Combined Mathematics', 'Physics', 'ICT', 'Colombo', 2.25],
    ['University of Moratuwa', 'BSc Honours in Artificial Intelligence', 'Combined Mathematics', 'Physics', 'ICT', 'Gampaha', 1.67],
    ['University of Moratuwa', 'BSc Honours in Information Technology', 'Combined Mathematics', 'Physics', 'ICT', 'Colombo', 1.93],
    ['University of Moratuwa', 'BSc Honours in Information Technology', 'Combined Mathematics', 'Physics', 'ICT', 'Gampaha', 1.42],
    ['University of Moratuwa', 'BSc Honours in Information Technology & Management', 'Combined Mathematics', 'Physics', 'ICT', 'Colombo', 1.70],
    ['University of Moratuwa', 'BSc Honours in Information Technology & Management', 'Combined Mathematics', 'Physics', 'ICT', 'Gampaha', 1.45],
    ['University of Moratuwa', 'Bachelor of Architecture', 'Combined Mathematics', 'Physics', 'Chemistry', 'All', 1.35],
    
    // Peradeniya
    ['University of Peradeniya', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Colombo', 1.90],
    ['University of Peradeniya', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Gampaha', 1.90],
    ['University of Peradeniya', 'BSc Physical Science', 'Combined Mathematics', 'Physics', 'Chemistry', 'Colombo', 1.52],
    ['University of Peradeniya', 'BSc Physical Science', 'Combined Mathematics', 'Physics', 'Chemistry', 'Gampaha', 1.47],
    
    // Ruhuna
    ['University of Ruhuna', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Colombo', 1.90],
    ['University of Ruhuna', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Galle', 1.91],
    ['University of Ruhuna', 'BSc Physical Science', 'Combined Mathematics', 'Physics', 'ICT', 'All', 1.30],
    
    // Jaffna
    ['University of Jaffna', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'All', 1.90],
    ['University of Jaffna', 'BSc Computer Science', 'Combined Mathematics', 'Physics', 'ICT', 'All', 1.63],
    
    // Sri Jayewardenepura
    ['University of Sri Jayewardenepura', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'All', 1.88],
    ['University of Sri Jayewardenepura', 'BSc Computing / Computer Science', 'Combined Mathematics', 'Physics', 'ICT', 'All', 1.58],
    
    // South Eastern
    ['South Eastern University of Sri Lanka', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'All', 1.83]
];

// Helper to get or create univ and degree
foreach ($data as $row) {
    list($uni_name, $deg_name, $sub1, $sub2, $sub3, $district, $cutoff) = $row;
    
    // Get/create university
    $stmt = $conn->prepare("SELECT id FROM universities WHERE name = ?");
    $stmt->bind_param("s", $uni_name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $uni_id = $res->fetch_assoc()['id'];
    } else {
        $stmt2 = $conn->prepare("INSERT INTO universities (name) VALUES (?)");
        $stmt2->bind_param("s", $uni_name);
        $stmt2->execute();
        $uni_id = $conn->insert_id;
    }
    
    // Create dummy faculty/department if missing for this uni
    $conn->query("INSERT IGNORE INTO faculties (id, university_id, name) VALUES ($uni_id, $uni_id, 'Faculty of General')");
    $conn->query("INSERT IGNORE INTO departments (id, faculty_id, name) VALUES ($uni_id, $uni_id, 'Department of General')");
    $dept_id = $uni_id;
    
    // Get/create degree
    $stmt = $conn->prepare("SELECT id FROM degrees WHERE name = ? AND department_id IN (SELECT id FROM departments WHERE faculty_id IN (SELECT id FROM faculties WHERE university_id = ?))");
    $stmt->bind_param("si", $deg_name, $uni_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $deg_id = $res->fetch_assoc()['id'];
    } else {
        $stmt2 = $conn->prepare("INSERT INTO degrees (department_id, name, duration, medium) VALUES (?, ?, '4 years', 'English')");
        $stmt2->bind_param("is", $dept_id, $deg_name);
        $stmt2->execute();
        $deg_id = $conn->insert_id;
    }
    
    // Insert cutoff
    $stream = 'Physical Science'; // Dummy
    $stmt = $conn->prepare("INSERT INTO zscore_cutoffs (degree_id, stream, cutoff, subject1, subject2, subject3, district) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdssss", $deg_id, $stream, $cutoff, $sub1, $sub2, $sub3, $district);
    $stmt->execute();
}

echo "Database updated successfully.";
$conn->query("SET FOREIGN_KEY_CHECKS = 1;");
?>