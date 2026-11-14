<?php

$host = "localhost";
$user = "root";
$pass = "";
$db = "sims_db";

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

$conn->query("CREATE TABLE IF NOT EXISTS `courses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `course_code` VARCHAR(20) NOT NULL UNIQUE,
  `course_name` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$courseCountCheck = $conn->query("SELECT COUNT(*) AS c FROM `courses`");
if ($courseCountCheck && ((int) ($courseCountCheck->fetch_assoc()['c'] ?? 0)) === 0) {
    $conn->query("INSERT IGNORE INTO `courses` (`id`, `course_code`, `course_name`) VALUES
        (1, 'BSIT', 'Bachelor of Science in Information Technology'),
        (2, 'BSCS', 'Bachelor of Science in Computer Science'),
        (3, 'BSBA', 'Bachelor of Science in Business Administration')");
}

$tableCheck = $conn->query("SHOW TABLES LIKE 'students'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    $colCourseId = $conn->query("SHOW COLUMNS FROM `students` LIKE 'course_id'");
    if ($colCourseId && $colCourseId->num_rows === 0) {
        $colOldCourse = $conn->query("SHOW COLUMNS FROM `students` LIKE 'course'");
        if ($colOldCourse && $colOldCourse->num_rows > 0) {
            $conn->query("ALTER TABLE `students` ADD COLUMN `course_id` INT NULL AFTER `email`");
            $conn->query("UPDATE `students` s JOIN `courses` c ON UPPER(TRIM(s.course)) = UPPER(TRIM(c.course_code)) SET s.course_id = c.id");
            $conn->query("UPDATE `students` SET `course_id` = (SELECT `id` FROM `courses` ORDER BY `id` ASC LIMIT 1) WHERE `course_id` IS NULL");
            $conn->query("ALTER TABLE `students` MODIFY COLUMN `course_id` INT NOT NULL");
            $conn->query("ALTER TABLE `students` DROP COLUMN `course`");
        } else {
            $conn->query("ALTER TABLE `students` ADD COLUMN `course_id` INT NOT NULL DEFAULT 1 AFTER `email`");
        }
    }

    $colMajor = $conn->query("SHOW COLUMNS FROM `students` LIKE 'major'");
    if ($colMajor && $colMajor->num_rows === 0) {
        $conn->query("ALTER TABLE `students` ADD COLUMN `major` VARCHAR(100) NULL AFTER `course_id`");
    }

    $colStatus = $conn->query("SHOW COLUMNS FROM `students` LIKE 'status'");
    if ($colStatus && $colStatus->num_rows === 0) {
        $conn->query("ALTER TABLE `students` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'Enrolled' AFTER `year_level`");
    }

    $colGpa = $conn->query("SHOW COLUMNS FROM `students` LIKE 'gpa'");
    if ($colGpa && $colGpa->num_rows === 0) {
        $conn->query("ALTER TABLE `students` ADD COLUMN `gpa` VARCHAR(10) NULL DEFAULT '1.75' AFTER `status`");
    }

    $colRemarks = $conn->query("SHOW COLUMNS FROM `students` LIKE 'remarks'");
    if ($colRemarks && $colRemarks->num_rows === 0) {
        $conn->query("ALTER TABLE `students` ADD COLUMN `remarks` VARCHAR(100) NULL DEFAULT 'Good Standing' AFTER `gpa`");
    }

    $countCheck = $conn->query("SELECT COUNT(*) AS c FROM `students`");
    if ($countCheck) {
        $cnt = (int) ($countCheck->fetch_assoc()['c'] ?? 0);
        if ($cnt === 0) {
            $conn->query("INSERT IGNORE INTO `students` (student_id, first_name, last_name, email, course_id, major, year_level, status) VALUES
                ('2026-0101', 'Juan', 'Dela Cruz', 'juan.delacruz@example.com', 1, 'Web Development', 3, 'Enrolled'),
                ('2026-0102', 'Maria', 'Santos', 'maria.santos@example.com', 2, 'Data Science', 2, 'Enrolled'),
                ('2026-0103', 'Carlo', 'Reyes', 'carlo.reyes@example.com', 1, 'Network & Security', 1, 'Inactive'),
                ('2026-0104', 'Ana', 'Lopez', 'ana.lopez@example.com', 3, 'Financial Management', 4, 'Enrolled')");
        }
    }
}

if (!function_exists('formatYearLevel')) {
    function formatYearLevel($level)
    {
        $suffixes = [1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th', 5 => '5th'];
        return $suffixes[(int) $level] ?? ($level . 'th');
    }
}

if (!function_exists('renderStatusBadge')) {
    function renderStatusBadge($status)
    {
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
    function renderRemarksBadge($remarks)
    {
        $r = trim($remarks ?? 'Good Standing');
        if (empty($r)) {
            $r = 'Good Standing';
        }
        $decoded = html_entity_decode($r, ENT_QUOTES, 'UTF-8');
        if (stripos($decoded, "Dean") !== false || stripos($decoded, "Honor") !== false) {
            return '<span class="status-badge" style="background: rgba(234, 179, 8, 0.15); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.3);"><i class="bi bi-award-fill me-1"></i>' . htmlspecialchars($decoded) . '</span>';
        } elseif (stripos($decoded, 'Good') !== false) {
            return '<span class="status-badge badge-enrolled"><i class="bi bi-check-circle-fill me-1"></i>' . htmlspecialchars($decoded) . '</span>';
        } elseif (stripos($decoded, 'Pass') !== false) {
            return '<span class="status-badge" style="background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3);"><i class="bi bi-check2-circle me-1"></i>' . htmlspecialchars($decoded) . '</span>';
        } elseif (stripos($decoded, 'Fail') !== false || stripos($decoded, 'Dropped') !== false) {
            return '<span class="status-badge" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);"><i class="bi bi-x-circle-fill me-1"></i>' . htmlspecialchars($decoded) . '</span>';
        } elseif (stripos($decoded, 'Incomplete') !== false || stripos($decoded, 'Credit') !== false) {
            return '<span class="status-badge" style="background: rgba(249, 115, 22, 0.15); color: #fb923c; border: 1px solid rgba(249, 115, 22, 0.3);"><i class="bi bi-exclamation-circle-fill me-1"></i>' . htmlspecialchars($decoded) . '</span>';
        } else {
            return '<span class="status-badge badge-inactive"><i class="bi bi-info-circle-fill me-1"></i>' . htmlspecialchars($decoded) . '</span>';
        }
    }
}
