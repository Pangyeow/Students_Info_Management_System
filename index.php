<?php
include "db_connect.php";

$currentTab = $_GET['tab'] ?? $_GET['view'] ?? 'students';
if (!in_array($currentTab, ['dashboard', 'students', 'courses', 'grades', 'reports'])) {
  $currentTab = 'students';
}

// Handle Grade Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_grade') {
  $gradeId = (int) ($_POST['id'] ?? 0);
  $gpa = trim($_POST['gpa'] ?? '');
  $remarks = trim($_POST['remarks'] ?? '');

  if ($gradeId > 0) {
    $stmtGrade = $conn->prepare("UPDATE students SET gpa = ?, remarks = ? WHERE id = ?");
    $stmtGrade->bind_param("ssi", $gpa, $remarks, $gradeId);
    $stmtGrade->execute();
    header("Location: index.php?tab=grades&msg=" . urlencode("Grade and academic standing updated successfully!"));
    exit;
  }
}

// Metrics
$totalStudents = (int) ($conn->query("SELECT COUNT(*) AS c FROM students")->fetch_assoc()['c'] ?? 0);
$enrolledCount = (int) ($conn->query("SELECT COUNT(*) AS c FROM students WHERE status = 'Enrolled'")->fetch_assoc()['c'] ?? 0);
$coursesCount = (int) ($conn->query("SELECT COUNT(DISTINCT course) AS c FROM students WHERE course != ''")->fetch_assoc()['c'] ?? 0);
$newThisMonth = (int) ($conn->query("SELECT COUNT(*) AS c FROM students WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')")->fetch_assoc()['c'] ?? 0);

// Distinct courses for filter
$courseListResult = $conn->query("SELECT DISTINCT course FROM students WHERE course != '' ORDER BY course ASC");
$coursesList = [];
if ($courseListResult) {
  while ($cRow = $courseListResult->fetch_assoc()) {
    $coursesList[] = $cRow['course'];
  }
}

// Filter and Search parameters
$search = trim($_GET['search'] ?? '');
$courseFilter = trim($_GET['course'] ?? '');
$yearFilter = trim($_GET['year'] ?? '');

$where = [];
$params = [];
$types = "";

if ($search !== '') {
  $where[] = "(student_id LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR course LIKE ? OR major LIKE ? OR email LIKE ?)";
  $like = "%$search%";
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
  $types .= "ssssss";
}

if ($courseFilter !== '') {
  $where[] = "course = ?";
  $params[] = $courseFilter;
  $types .= "s";
}

if ($yearFilter !== '') {
  $where[] = "year_level = ?";
  $params[] = (int) $yearFilter;
  $types .= "i";
}

$whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Count for pagination
$countSql = "SELECT COUNT(*) AS c FROM students $whereSql";
if (!empty($params)) {
  $countStmt = $conn->prepare($countSql);
  $countStmt->bind_param($types, ...$params);
  $countStmt->execute();
  $totalFiltered = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);
} else {
  $totalFiltered = (int) ($conn->query($countSql)->fetch_assoc()['c'] ?? 0);
}

// Pagination
$limit = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));
$totalPages = max(1, ceil($totalFiltered / $limit));
if ($page > $totalPages)
  $page = $totalPages;
$offset = ($page - 1) * $limit;

// Fetch students for table
$querySql = "SELECT * FROM students $whereSql ORDER BY id DESC LIMIT ? OFFSET ?";
$fetchParams = $params;
$fetchParams[] = $limit;
$fetchParams[] = $offset;
$fetchTypes = $types . "ii";

$stmt = $conn->prepare($querySql);
$stmt->bind_param($fetchTypes, ...$fetchParams);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $students[] = $row;
  }
}

// Helper to build filter query string
function buildQuery($overrides = [])
{
  $current = $_GET;
  foreach ($overrides as $k => $v) {
    if ($v === null || $v === '') {
      unset($current[$k]);
    } else {
      $current[$k] = $v;
    }
  }
  return '?' . http_build_query($current);
}

include "includes/header.php";
?>

<?php if (isset($_GET['msg'])): ?>
  <div
    class="alert alert-success alert-dismissible fade show bg-dark text-success border border-success border-opacity-25 mb-4"
    role="alert">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_GET['msg']) ?>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- ========================================== -->
<!-- TAB: DASHBOARD -->
<!-- ========================================== -->
<?php if ($currentTab === 'dashboard'): ?>
  <!-- 4 Metrics Cards -->
  <div class="metrics-grid">
    <div class="metric-card">
      <div class="metric-label">Total students</div>
      <div class="metric-value"><?= number_format($totalStudents) ?></div>
    </div>
    <div class="metric-card">
      <div class="metric-label">Enrolled</div>
      <div class="metric-value"><?= number_format($enrolledCount) ?></div>
    </div>
    <div class="metric-card">
      <div class="metric-label">Courses</div>
      <div class="metric-value"><?= number_format($coursesCount) ?></div>
    </div>
    <div class="metric-card">
      <div class="metric-label">New this month</div>
      <div class="metric-value"><?= number_format($newThisMonth) ?></div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Recent Students Table -->
    <div class="col-lg-7">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="section-title fs-5">Recent Students</h3>
        <a href="index.php?tab=students" class="text-primary text-decoration-none small fw-semibold">
          View all students <i class="bi bi-arrow-right"></i>
        </a>
      </div>
      <div class="table-card">
        <div class="table-responsive">
          <table class="custom-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Course</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $recent = $conn->query("SELECT * FROM students ORDER BY id DESC LIMIT 5");
              if ($recent && $recent->num_rows > 0):
                while ($r = $recent->fetch_assoc()):
                  ?>
                  <tr>
                    <td class="text-secondary fw-semibold"><?= htmlspecialchars($r['student_id']) ?></td>
                    <td class="text-white fw-medium"><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                    <td><?= htmlspecialchars($r['course']) ?></td>
                    <td><?= renderStatusBadge($r['status']) ?></td>
                  </tr>
                <?php endwhile; else: ?>
                <tr>
                  <td colspan="4" class="text-center py-4 text-secondary">No records found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Quick Stats & Course Distribution -->
    <div class="col-lg-5">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="section-title fs-5">Enrollment Distribution</h3>
        <a href="index.php?tab=courses" class="text-primary text-decoration-none small fw-semibold">
          All courses <i class="bi bi-arrow-right"></i>
        </a>
      </div>
      <div class="metric-card p-4">
        <?php
        $dist = $conn->query("SELECT course, COUNT(*) as c FROM students WHERE course != '' GROUP BY course ORDER BY c DESC LIMIT 4");
        if ($dist && $dist->num_rows > 0):
          while ($d = $dist->fetch_assoc()):
            $pct = ($totalStudents > 0) ? round(($d['c'] / $totalStudents) * 100) : 0;
            ?>
            <div class="mb-3">
              <div class="d-flex justify-content-between small mb-1">
                <span class="text-white fw-semibold"><?= htmlspecialchars($d['course']) ?></span>
                <span class="text-secondary"><?= $d['c'] ?> students (<?= $pct ?>%)</span>
              </div>
              <div class="progress" style="height: 6px; background-color: #242838;">
                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct ?>%;"></div>
              </div>
            </div>
          <?php endwhile; else: ?>
          <p class="text-secondary small mb-0">No course statistics available yet.</p>
        <?php endif; ?>

        <hr style="border-color: #242838;" class="my-3">
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-secondary small">Active Retention Rate:</span>
          <span class="badge-enrolled status-badge">
            <?= ($totalStudents > 0) ? round(($enrolledCount / $totalStudents) * 100) : 0 ?>% Active
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- TAB: COURSES -->
  <!-- ========================================== -->
<?php elseif ($currentTab === 'courses'): ?>
  <div class="section-header-bar">
    <div>
      <h2 class="section-title">Courses Directory</h2>
      <p class="text-secondary small mb-0">Explore student counts and distribution across academic programs</p>
    </div>
    <a href="index.php?tab=students" class="btn btn-secondary btn-sm">
      <i class="bi bi-people me-1"></i> View Students List
    </a>
  </div>

  <?php
  $courseSummary = $conn->query("SELECT course, COUNT(*) as total, 
                                  SUM(CASE WHEN status = 'Enrolled' THEN 1 ELSE 0 END) as enrolled,
                                  SUM(CASE WHEN status = 'Inactive' THEN 1 ELSE 0 END) as inactive
                                 FROM students WHERE course != '' GROUP BY course ORDER BY total DESC");
  ?>
  <div class="table-card mb-4">
    <div class="table-responsive">
      <table class="custom-table">
        <thead>
          <tr>
            <th>Course Code / Name</th>
            <th>Total Students</th>
            <th>Enrolled</th>
            <th>Inactive</th>
            <th style="text-align: right; padding-right: 24px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($courseSummary && $courseSummary->num_rows > 0):
            while ($c = $courseSummary->fetch_assoc()): ?>
              <tr>
                <td class="fw-semibold text-white fs-6">
                  <i class="bi bi-journal-text text-primary me-2"></i><?= htmlspecialchars($c['course']) ?>
                </td>
                <td class="fw-bold text-white"><?= (int) $c['total'] ?></td>
                <td><span class="status-badge badge-enrolled"><?= (int) $c['enrolled'] ?> Enrolled</span></td>
                <td><span class="status-badge badge-inactive"><?= (int) $c['inactive'] ?> Inactive</span></td>
                <td style="text-align: right; padding-right: 24px;">
                  <a href="index.php?tab=students&course=<?= urlencode($c['course']) ?>"
                    class="btn btn-sm btn-outline-primary" style="font-size: 0.8rem;">
                    <i class="bi bi-people me-1"></i> Filter Students
                  </a>
                </td>
              </tr>
            <?php endwhile; else: ?>
            <tr>
              <td colspan="5" class="text-center py-5 text-secondary">No courses found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- TAB: GRADES -->
  <!-- ========================================== -->
<?php elseif ($currentTab === 'grades'): ?>
  <div class="section-header-bar">
    <div>
      <h2 class="section-title">Academic Grades & Standing</h2>
      <p class="text-secondary small mb-0">Manage, evaluate, and update student grades and academic performance</p>
    </div>
    <div class="section-actions">
      <!-- Course & Year Filter Form -->
      <form id="gradeFilterForm" method="get" class="d-flex align-items-center gap-2 m-0">
        <input type="hidden" name="tab" value="grades">
        <?php if (!empty($search)): ?>
          <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
        <?php endif; ?>

        <select name="course" class="custom-select-dark" onchange="document.getElementById('gradeFilterForm').submit()">
          <option value="">All courses</option>
          <?php foreach ($coursesList as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>" <?= ($courseFilter === $c) ? 'selected' : '' ?>>
              <?= htmlspecialchars($c) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <select name="year" class="custom-select-dark" onchange="document.getElementById('gradeFilterForm').submit()">
          <option value="">All years</option>
          <option value="1" <?= ($yearFilter === '1') ? 'selected' : '' ?>>1st Year</option>
          <option value="2" <?= ($yearFilter === '2') ? 'selected' : '' ?>>2nd Year</option>
          <option value="3" <?= ($yearFilter === '3') ? 'selected' : '' ?>>3rd Year</option>
          <option value="4" <?= ($yearFilter === '4') ? 'selected' : '' ?>>4th Year</option>
        </select>
      </form>
    </div>
  </div>

  <div class="table-card">
    <div class="table-responsive">
      <table class="custom-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Student Name</th>
            <th>Course</th>
            <th>Year</th>
            <th>Grade / GPA</th>
            <th>Academic Standing</th>
            <th>Status</th>
            <th style="text-align: right; padding-right: 24px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($students)):
            foreach ($students as $row):
              $studentGpa = !empty($row['gpa']) ? htmlspecialchars($row['gpa']) : '1.75';
              $studentRemarks = !empty($row['remarks']) ? htmlspecialchars($row['remarks']) : 'Good Standing';
              ?>
              <tr>
                <td class="text-secondary fw-semibold"><?= htmlspecialchars($row['student_id']) ?></td>
                <td class="text-white fw-medium"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                <td><?= htmlspecialchars($row['course']) ?></td>
                <td><?= formatYearLevel($row['year_level']) ?></td>
                <td>
                  <span class="badge bg-dark text-white border border-secondary px-2 py-1 fs-6 fw-bold">
                    <?= $studentGpa ?>
                  </span>
                </td>
                <td><?= renderRemarksBadge($studentRemarks) ?></td>
                <td><?= renderStatusBadge($row['status'] ?? 'Enrolled') ?></td>
                <td style="text-align: right; padding-right: 24px;">
                  <button type="button" class="btn btn-sm btn-outline-primary" style="font-size: 0.825rem;" onclick='openGradeModal(<?= json_encode([
                    "id" => $row["id"],
                    "student_id" => $row["student_id"],
                    "name" => $row["first_name"] . " " . $row["last_name"],
                    "course" => $row["course"],
                    "year" => formatYearLevel($row["year_level"]),
                    "gpa" => $studentGpa,
                    "remarks" => $studentRemarks
                  ]) ?>)'>
                    <i class="bi bi-pencil-square me-1"></i> Edit Grade
                  </button>
                </td>
              </tr>
            <?php endforeach; else: ?>
            <tr>
              <td colspan="8" class="text-center py-5 text-secondary">No records found matching your criteria.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination for Grades -->
    <div class="table-footer">
      <p class="showing-text">
        <?php if ($totalFiltered > 0): ?>
          Showing <?= ($offset + 1) ?> to <?= min($offset + count($students), $totalFiltered) ?> of
          <?= number_format($totalFiltered) ?>
        <?php else: ?>
          Showing 0 students
        <?php endif; ?>
      </p>

      <div class="pagination-group">
        <a href="<?= ($page > 1) ? buildQuery(['page' => $page - 1]) : '#' ?>"
          class="page-btn <?= ($page <= 1) ? 'disabled' : '' ?>">Prev</a>

        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <?php if ($p == 1 || $p == $totalPages || abs($p - $page) <= 2): ?>
            <a href="<?= buildQuery(['page' => $p]) ?>" class="page-btn <?= ($p === $page) ? 'active' : '' ?>">
              <?= $p ?>
            </a>
          <?php elseif ($p == 2 || $p == $totalPages - 1): ?>
            <span class="text-secondary px-1">...</span>
          <?php endif; ?>
        <?php endfor; ?>

        <a href="<?= ($page < $totalPages) ? buildQuery(['page' => $page + 1]) : '#' ?>"
          class="page-btn <?= ($page >= $totalPages) ? 'disabled' : '' ?>">Next</a>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- TAB: REPORTS -->
  <!-- ========================================== -->
<?php elseif ($currentTab === 'reports'): ?>
  <div class="section-header-bar">
    <div>
      <h2 class="section-title">System Reports & Analytics</h2>
      <p class="text-secondary small mb-0">Comprehensive institutional metrics and student demographics</p>
    </div>
    <button onclick="window.print()" class="btn btn-primary btn-sm">
      <i class="bi bi-printer me-1"></i> Print / Export Report
    </button>
  </div>

  <div class="row g-4">
    <div class="col-md-4">
      <div class="metric-card">
        <div class="metric-label">Total Registered Students</div>
        <div class="metric-value"><?= number_format($totalStudents) ?></div>
        <div class="text-secondary small mt-2">Active database entries</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="metric-card">
        <div class="metric-label">Enrollment Rate</div>
        <div class="metric-value"><?= ($totalStudents > 0) ? round(($enrolledCount / $totalStudents) * 100) : 0 ?>%</div>
        <div class="text-success small mt-2"><i class="bi bi-check-circle me-1"></i><?= $enrolledCount ?> officially
          enrolled</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="metric-card">
        <div class="metric-label">Active Degree Programs</div>
        <div class="metric-value"><?= $coursesCount ?></div>
        <div class="text-secondary small mt-2">Programs with registered students</div>
      </div>
    </div>
  </div>

  <div class="table-card mt-4">
    <div class="p-3 border-bottom" style="border-color: #202330 !important;">
      <h5 class="text-white fs-6 mb-0">Year Level Distribution</h5>
    </div>
    <div class="table-responsive">
      <table class="custom-table">
        <thead>
          <tr>
            <th>Year Level</th>
            <th>Total Students</th>
            <th>Enrolled</th>
            <th>Inactive</th>
          </tr>
        </thead>
        <tbody>
          <?php for ($y = 1; $y <= 4; $y++):
            $yCount = (int) ($conn->query("SELECT COUNT(*) AS c FROM students WHERE year_level = $y")->fetch_assoc()['c'] ?? 0);
            $yEnrolled = (int) ($conn->query("SELECT COUNT(*) AS c FROM students WHERE year_level = $y AND status = 'Enrolled'")->fetch_assoc()['c'] ?? 0);
            $yInactive = (int) ($conn->query("SELECT COUNT(*) AS c FROM students WHERE year_level = $y AND status = 'Inactive'")->fetch_assoc()['c'] ?? 0);
            ?>
            <tr>
              <td class="text-white fw-semibold"><?= formatYearLevel($y) ?> Year</td>
              <td><?= $yCount ?></td>
              <td><span class="status-badge badge-enrolled"><?= $yEnrolled ?> Enrolled</span></td>
              <td><span class="status-badge badge-inactive"><?= $yInactive ?> Inactive</span></td>
            </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- TAB: STUDENTS (Default / Reference Photo) -->
  <!-- ========================================== -->
<?php else: ?>
  <!-- 4 Metrics Cards matching the reference photo -->

  <!-- Section Header with Title, Filters and Add Button -->
  <div class="section-header-bar">
    <h2 class="section-title">Students</h2>

    <div class="section-actions">
      <!-- Course & Year Filter Form -->
      <form id="filterForm" method="get" class="d-flex align-items-center gap-2 m-0">
        <input type="hidden" name="tab" value="students">
        <?php if (!empty($search)): ?>
          <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
        <?php endif; ?>

        <select name="course" class="custom-select-dark" onchange="document.getElementById('filterForm').submit()">
          <option value="">All courses</option>
          <?php foreach ($coursesList as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>" <?= ($courseFilter === $c) ? 'selected' : '' ?>>
              <?= htmlspecialchars($c) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <!-- Year Filter -->
        <select name="year" class="custom-select-dark" onchange="document.getElementById('filterForm').submit()">
          <option value="">All years</option>
          <option value="1" <?= ($yearFilter === '1') ? 'selected' : '' ?>>1st Year</option>
          <option value="2" <?= ($yearFilter === '2') ? 'selected' : '' ?>>2nd Year</option>
          <option value="3" <?= ($yearFilter === '3') ? 'selected' : '' ?>>3rd Year</option>
          <option value="4" <?= ($yearFilter === '4') ? 'selected' : '' ?>>4th Year</option>
        </select>
      </form>

      <!-- Add Student Button -->
      <a href="add.php" class="btn-add-student" data-bs-toggle="modal" data-bs-target="#addStudentModal">
        <i class="bi bi-person-plus-fill"></i> Add student
      </a>
    </div>
  </div>

  <!-- Students Table Card -->
  <div class="table-card">
    <div class="table-responsive">
      <table class="custom-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Course</th>
            <th>Year</th>
            <th>Status</th>
            <th style="text-align: right; padding-right: 24px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($students)): ?>
            <?php foreach ($students as $row): ?>
              <tr>
                <td class="fw-semibold text-secondary"><?= htmlspecialchars($row['student_id']) ?></td>
                <td class="fw-medium text-white">
                  <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                </td>
                <td><?= htmlspecialchars($row['course']) ?></td>
                <td><?= formatYearLevel($row['year_level']) ?></td>
                <td><?= renderStatusBadge($row['status'] ?? 'Enrolled') ?></td>
                <td style="text-align: right; padding-right: 24px;">
                  <div class="action-btns-group justify-content-end">
                    <!-- View Icon -->
                    <button type="button" class="action-btn view-btn" title="View Details" onclick='showStudentModal(<?= json_encode([
                      "id" => $row["id"],
                      "student_id" => $row["student_id"],
                      "first_name" => $row["first_name"],
                      "last_name" => $row["last_name"],
                      "email" => $row["email"],
                      "course" => $row["course"],
                      "major" => $row["major"] ?? "",
                      "year_level" => $row["year_level"],
                      "year_formatted" => formatYearLevel($row["year_level"]),
                      "status" => $row["status"] ?? "Enrolled",
                      "gpa" => $row["gpa"] ?? "1.75",
                      "remarks" => $row["remarks"] ?? "Good Standing"
                    ]) ?>)'>
                      <i class="bi bi-eye"></i>
                    </button>
                    <!-- Edit Icon -->
                    <a href="edit.php?id=<?= $row['id'] ?>" class="action-btn edit-btn" title="Edit Student">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <!-- Delete Icon -->
                    <a href="delete.php?id=<?= $row['id'] ?>" class="action-btn delete-btn" title="Delete Student"
                      onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars(addslashes($row['first_name'] . ' ' . $row['last_name'])) ?>?');">
                      <i class="bi bi-trash3"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center py-5 text-secondary">
                <i class="bi bi-inbox fs-3 d-block mb-2 text-muted"></i>
                No students found matching your criteria.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination & Showing count footer -->
    <div class="table-footer">
      <p class="showing-text">
        <?php if ($totalFiltered > 0): ?>
          Showing <?= ($offset + 1) ?> to <?= min($offset + count($students), $totalFiltered) ?> of
          <?= number_format($totalFiltered) ?>
        <?php else: ?>
          Showing 0 students
        <?php endif; ?>
      </p>

      <div class="pagination-group">
        <!-- Prev -->
        <a href="<?= ($page > 1) ? buildQuery(['page' => $page - 1]) : '#' ?>"
          class="page-btn <?= ($page <= 1) ? 'disabled' : '' ?>">Prev</a>

        <!-- Page Numbers -->
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <?php if ($p == 1 || $p == $totalPages || abs($p - $page) <= 2): ?>
            <a href="<?= buildQuery(['page' => $p]) ?>" class="page-btn <?= ($p === $page) ? 'active' : '' ?>">
              <?= $p ?>
            </a>
          <?php elseif ($p == 2 || $p == $totalPages - 1): ?>
            <span class="text-secondary px-1">...</span>
          <?php endif; ?>
        <?php endfor; ?>

        <!-- Next -->
        <a href="<?= ($page < $totalPages) ? buildQuery(['page' => $page + 1]) : '#' ?>"
          class="page-btn <?= ($page >= $totalPages) ? 'disabled' : '' ?>">Next</a>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- Modal: Quick Add Student -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content dark-modal">
      <div class="modal-header dark-modal-header">
        <h5 class="modal-title text-white fs-6">
          <i class="bi bi-person-plus-fill me-2 text-primary"></i>Add New Student
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="add.php" method="post">
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="dark-label">Student ID <span class="text-danger">*</span></label>
              <input type="text" name="student_id" class="form-control dark-input" placeholder="e.g. 2026-0105"
                required>
            </div>
            <div class="col-md-6">
              <label class="dark-label">Status <span class="text-danger">*</span></label>
              <select name="status" class="form-select dark-select" required>
                <option value="Enrolled" selected>Enrolled</option>
                <option value="Inactive">Inactive</option>
                <option value="Graduated">Graduated</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="dark-label">First Name <span class="text-danger">*</span></label>
              <input type="text" name="first_name" class="form-control dark-input" placeholder="Juan" required>
            </div>
            <div class="col-md-6">
              <label class="dark-label">Last Name <span class="text-danger">*</span></label>
              <input type="text" name="last_name" class="form-control dark-input" placeholder="Dela Cruz" required>
            </div>
            <div class="col-md-12">
              <label class="dark-label">Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control dark-input" placeholder="juan.delacruz@example.com"
                required>
            </div>
            <div class="col-md-6">
              <label class="dark-label">Course <span class="text-danger">*</span></label>
              <input type="text" name="course" class="form-control dark-input" placeholder="e.g. BSIT, BSCS, BSBA"
                required>
            </div>
            <div class="col-md-6">
              <label class="dark-label">Major</label>
              <input type="text" name="major" class="form-control dark-input" placeholder="e.g. Web Development">
            </div>
            <div class="col-md-12">
              <label class="dark-label">Year Level <span class="text-danger">*</span></label>
              <select name="year_level" class="form-select dark-select" required>
                <option value="">Select year level...</option>
                <option value="1">1st Year</option>
                <option value="2">2nd Year</option>
                <option value="3">3rd Year</option>
                <option value="4">4th Year</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer dark-modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm px-3">
            <i class="bi bi-check2 me-1"></i> Save Student
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Edit Grade -->
<div class="modal fade" id="editGradeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content dark-modal">
      <div class="modal-header dark-modal-header">
        <h5 class="modal-title text-white fs-6">
          <i class="bi bi-award-fill me-2 text-primary"></i>Edit Student Grade & Standing
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="index.php" method="post">
        <input type="hidden" name="action" value="update_grade">
        <input type="hidden" name="id" id="editGradeStudentId">
        <div class="modal-body p-4">
          <div class="p-3 rounded mb-3" style="background: #111319; border: 1px solid #202330;">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-white fw-bold fs-6" id="editGradeStudentName">Student Name</span>
              <span class="badge bg-primary text-white" id="editGradeStudentCourse">Course</span>
            </div>
            <div class="text-secondary small">
              ID: <span id="editGradeStudentIdText" class="text-light">2026-0000</span> | Year: <span
                id="editGradeStudentYear" class="text-light">1st</span>
            </div>
          </div>

          <div class="mb-3">
            <label class="dark-label">Grade / GPA <span class="text-danger">*</span></label>
            <input type="text" name="gpa" id="editGradeGpaInput" class="form-control dark-input"
              placeholder="e.g. 1.25, 1.50, 88.5" required>
            <div class="form-text text-secondary small">Enter GPA (e.g. 1.00 - 3.00) or Numerical Grade.</div>
          </div>

          <div class="mb-3">
            <label class="dark-label">Academic Standing / Remarks <span class="text-danger">*</span></label>
            <select name="remarks" id="editGradeRemarksSelect" class="form-select dark-select" required>
              <option value="Dean's Lister">Dean's Lister</option>
              <option value="Good Standing">Good Standing</option>
              <option value="Passed">Passed</option>
              <option value="No Credits">No Credits</option>
              <option value="Incomplete">Incomplete</option>
              <option value="Failed">Failed</option>
            </select>
          </div>
        </div>
        <div class="modal-footer dark-modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm px-3">
            <i class="bi bi-check2 me-1"></i> Save Grade
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include "includes/footer.php"; ?>