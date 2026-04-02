<?php
/**
 * UI Helper functions for University-DMS
 */

/**
 * Returns the image path for a university or a placeholder if not found.
 * 
 * @param array $university The university data array
 * @return string The image path or placeholder URL
 */
function getUniversityImagePath($university) {
    if (!empty($university['image_url'])) {
        return htmlspecialchars($university['image_url']);
    }
    
    // Check if an image exists in the uploads folder based on ID
    $id = isset($university['id']) ? $university['id'] : 0;
    $localImagePath = "uploads/universities/uni_{$id}.jpg";
    
    if (file_exists($localImagePath)) {
        return $localImagePath;
    }
    
    // Default placeholder
    return "assets/images/uni-placeholder.webp";
}

/**
 * Returns the university location or a default string.
 * 
 * @param array $university The university data array
 * @return string The location
 */
function getUniversityLocation($university) {
    return !empty($university['location']) ? $university['location'] : 'Sri Lanka';
}

/**
 * Returns a truncated description of the university.
 * 
 * @param array $university The university data array
 * @param int $limit The character limit
 * @return string The truncated description
 */
function getUniversityDescription($university, $limit = 200) {
    $desc = !empty($university['description']) ? $university['description'] : 'Discover programs and opportunities at this institution.';
    
    if (strlen($desc) > $limit) {
        $desc = substr($desc, 0, $limit) . '...';
    }
    
    return $desc;
}

/**
 * Returns the university type (Public/Private/etc).
 * 
 * @param array $university The university data array
 * @return string The type
 */
function getUniversityType($university) {
    return !empty($university['type']) ? $university['type'] : 'University';
}
