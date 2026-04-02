<?php
require_once 'includes/db.php';

// Drop if exists
$conn->query("DROP TABLE IF EXISTS university_degrees");

// Create table
$sql = "
CREATE TABLE university_degrees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    university VARCHAR(255),
    degree VARCHAR(255),
    stream VARCHAR(100)
)
";
if ($conn->query($sql) === TRUE) {
    echo "Table university_degrees created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Function to guess the stream from subjects
function guessStream($sub1, $sub2, $sub3, $deg) {
    $subs = strtoupper($sub1 . " " . $sub2 . " " . $sub3);
    $degUpper = strtoupper($deg);
    
    if (strpos($subs, 'COMBINED MATHEMATICS') !== false) {
        return 'Maths';
    } elseif (strpos($subs, 'BIOLOGY') !== false) {
        return 'Bio';
    } elseif (strpos($subs, 'ACCOUNTING') !== false || strpos($subs, 'BUSINESS') !== false || strpos($subs, 'ECONOMICS') !== false || strpos($degUpper, 'MANAGEMENT') !== false || strpos($degUpper, 'COMMERCE') !== false) {
        return 'Commerce';
    } elseif (strpos($subs, 'ANY') !== false || strpos($degUpper, 'ARTS') !== false || strpos($degUpper, 'LANGUAGES') !== false) {
        return 'Arts';
    } else {
        // Fallback guess based on degree name
        if (strpos($degUpper, 'MEDICINE') !== false || strpos($degUpper, 'DENTAL') !== false || strpos($degUpper, 'BIOLOGICAL') !== false) {
            return 'Bio';
        } elseif (strpos($degUpper, 'ENGINEERING') !== false || strpos($degUpper, 'PHYSICAL') !== false) {
            return 'Maths';
        } elseif (strpos($degUpper, 'ICT') !== false || strpos($degUpper, 'COMPUTER') !== false) {
            return 'Maths';
        }
        return 'Other Programs';
    }
}

// Populate from flat_zscores
$result = $conn->query("SELECT DISTINCT university_name, degree_name, subject1, subject2, subject3 FROM flat_zscores");

$insertStmt = $conn->prepare("INSERT INTO university_degrees (university, degree, stream) VALUES (?, ?, ?)");

$count = 0;
while ($row = $result->fetch_assoc()) {
    $uni = $row['university_name'];
    $deg = $row['degree_name'];
    $stream = guessStream($row['subject1'], $row['subject2'], $row['subject3'], $deg);
    
    $insertStmt->bind_param("sss", $uni, $deg, $stream);
    $insertStmt->execute();
    $count++;
}

echo "Inserted $count degrees into university_degrees.<br>";
?>