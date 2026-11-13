<?php

// Database configuration for local XAMPP / phpMyAdmin
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sims_db";

// InfinityFree configuration (uncomment if deploying to live host)
// $host = "sql203.infinityfree.com";
// $user = "if0_43425374";
// $pass = "pangyeow22";
// $db   = "if0_43425374_maniella";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Auto-create / migrate table schema to match reference UI
$tableCheck = $conn->query("SHOW TABLES LIKE 'students'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    // Check 'major' column
    $colMajor = $conn->query("SHOW COLUMNS FROM `students` LIKE 'major'");
    if ($colMajor && $colMajor->num_rows === 0) {
        $conn->query("ALTER TABLE `students` ADD COLUMN `major` VARCHAR(100) NULL AFTER `course`");
    }

    // Check 'status' column
    $colStatus = $conn->query("SHOW COLUMNS FROM `students` LIKE 'status'");
    if ($colStatus && $colStatus->num_rows === 0) {
        $conn->query("ALTER TABLE `students` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'Enrolled' AFTER `year_level`");
    }

    // Check 'gpa' column
    $colGpa = $conn->query("SHOW COLUMNS FROM `students` LIKE 'gpa'");
    if ($colGpa && $colGpa->num_rows === 0) {
        $conn->query("ALTER TABLE `students` ADD COLUMN `gpa` VARCHAR(10) NULL DEFAULT '1.75' AFTER `status`");
    }

    // Check 'remarks' column
    $colRemarks = $conn->query("SHOW COLUMNS FROM `students` LIKE 'remarks'");
    if ($colRemarks && $colRemarks->num_rows === 0) {
        $conn->query("ALTER TABLE `students` ADD COLUMN `remarks` VARCHAR(100) NULL DEFAULT 'Good Standing' AFTER `gpa`");
    }

    // Seed sample records matching the reference photo if table is empty
    $countCheck = $conn->query("SELECT COUNT(*) AS c FROM `students`");
    if ($countCheck) {
        $cnt = (int)($countCheck->fetch_assoc()['c'] ?? 0);
        if ($cnt === 0) {
            $conn->query("INSERT IGNORE INTO `students` (student_id, first_name, last_name, email, course, major, year_level, status) VALUES
                ('2026-0101', 'Juan', 'Dela Cruz', 'juan.delacruz@example.com', 'BSIT', 'Web Development', 3, 'Enrolled'),
                ('2026-0102', 'Maria', 'Santos', 'maria.santos@example.com', 'BSCS', 'Data Science', 2, 'Enrolled'),
                ('2026-0103', 'Carlo', 'Reyes', 'carlo.reyes@example.com', 'BSIT', 'Network & Security', 1, 'Inactive'),
                ('2026-0104', 'Ana', 'Lopez', 'ana.lopez@example.com', 'BSBA', 'Financial Management', 4, 'Enrolled')");
        }
    }
}

// Helpers
if (!function_exists('formatYearLevel')) {
    function formatYearLevel($level) {
        $suffixes = [1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th', 5 => '5th'];
        return $suffixes[(int)$level] ?? ($level . 'th');
    }
}

if (!function_exists('renderStatusBadge')) {
    function renderStatusBadge($status) {
        $s = trim($status ?? 'Enrolled');
        if (strcasecmp($s, 'Enrolled') === 0) {
            return '<span class="status-badge badge-enrolled">Enrolled</span>';
        } elseif (strcasecmp($s, 'Inactive') === 0) {
            return '<span class="status-badge badge-inactive">Inactive</span>';
        } elseif (strcasecmp($s, 'Graduated') === 0) {
            return '<span class="status-badge badge-graduated">Graduated</span>';
        } else {
            return '<span class="status-badge badge-inactive">' . htmlspecialchars($s) . '</span>';
        }
    }
}

if (!function_exists('renderRemarksBadge')) {
    function renderRemarksBadge($remarks) {
        $r = trim($remarks ?? 'Good Standing');
        if (empty($r)) $r = 'Good Standing';
        if (stripos($r, "Dean's") !== false || stripos($r, "Honor") !== false) {
            return '<span class="status-badge" style="background: rgba(234, 179, 8, 0.15); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.3);"><i class="bi bi-award-fill me-1"></i>' . htmlspecialchars($r) . '</span>';
        } elseif (stripos($r, 'Passed') !== false || stripos($r, 'Good') !== false) {
            return '<span class="status-badge badge-enrolled"><i class="bi bi-check-circle-fill me-1"></i>' . htmlspecialchars($r) . '</span>';
        } elseif (stripos($r, 'Fail') !== false || stripos($r, 'Dropped') !== false) {
            return '<span class="status-badge" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);"><i class="bi bi-x-circle-fill me-1"></i>' . htmlspecialchars($r) . '</span>';
        } else {
            return '<span class="status-badge badge-inactive"><i class="bi bi-info-circle-fill me-1"></i>' . htmlspecialchars($r) . '</span>';
        }
    }
}
