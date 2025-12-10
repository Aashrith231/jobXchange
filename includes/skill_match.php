<?php
/**
 * Skill Match Score Utility Functions
 * Calculates how well a candidate's skills match a job's required skills
 */

/**
 * Calculate skill match percentage between candidate skills and job skills
 * 
 * @param string $candidateSkills Comma-separated skills from candidate profile
 * @param string $jobSkills Comma-separated required skills from job posting
 * @return array Array with 'percentage' and 'badge_class' keys
 */
function calculateSkillMatch($candidateSkills, $jobSkills) {
    // Handle empty or null values
    if (empty($candidateSkills) || empty($jobSkills)) {
        return [
            'percentage' => 0,
            'matched' => 0,
            'total' => 0,
            'badge_class' => 'match-none'
        ];
    }
    
    // Convert to lowercase arrays and trim whitespace
    $candidateSkillsArray = array_map('trim', array_map('strtolower', explode(',', $candidateSkills)));
    $jobSkillsArray = array_map('trim', array_map('strtolower', explode(',', $jobSkills)));
    
    // Remove empty values
    $candidateSkillsArray = array_filter($candidateSkillsArray);
    $jobSkillsArray = array_filter($jobSkillsArray);
    
    // Calculate matches
    $matchedSkills = array_intersect($candidateSkillsArray, $jobSkillsArray);
    $matchCount = count($matchedSkills);
    $totalRequired = count($jobSkillsArray);
    
    // Calculate percentage
    $percentage = $totalRequired > 0 ? round(($matchCount / $totalRequired) * 100) : 0;
    
    // Determine badge class based on percentage
    $badgeClass = getBadgeClass($percentage);
    
    return [
        'percentage' => $percentage,
        'matched' => $matchCount,
        'total' => $totalRequired,
        'badge_class' => $badgeClass,
        'matched_skills' => $matchedSkills
    ];
}

/**
 * Get CSS badge class based on match percentage
 * 
 * @param int $percentage Match percentage
 * @return string CSS class name
 */
function getBadgeClass($percentage) {
    if ($percentage >= 80) {
        return 'match-excellent';
    } elseif ($percentage >= 60) {
        return 'match-good';
    } elseif ($percentage >= 40) {
        return 'match-fair';
    } elseif ($percentage >= 20) {
        return 'match-low';
    } else {
        return 'match-none';
    }
}

/**
 * Display skill match badge HTML
 * 
 * @param array $matchData Result from calculateSkillMatch()
 * @return string HTML for skill match badge
 */
function displaySkillMatchBadge($matchData) {
    $percentage = $matchData['percentage'];
    $badgeClass = $matchData['badge_class'];
    
    $html = '<span class="skill-match-badge ' . $badgeClass . '" title="' . $matchData['matched'] . ' out of ' . $matchData['total'] . ' skills matched">';
    $html .= '🎯 Match: ' . $percentage . '%';
    $html .= '</span>';
    
    return $html;
}

/**
 * Display detailed skill match info
 * 
 * @param array $matchData Result from calculateSkillMatch()
 * @return string HTML for detailed match info
 */
function displayDetailedMatch($matchData) {
    $html = '<div class="skill-match-details">';
    $html .= '<div class="match-score">';
    $html .= displaySkillMatchBadge($matchData);
    $html .= '</div>';
    $html .= '<div class="match-info">';
    $html .= '<small>' . $matchData['matched'] . ' of ' . $matchData['total'] . ' required skills</small>';
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}
?>

