<?php

include "db_connect.php";
$error = "";

$coursesList = [];
$cRes = $conn->query("SELECT id, course_code, course_name FROM courses ORDER BY course_code ASC");
if ($cRes) {
  $coursesList = $cRes->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $student_id = trim($_POST['student_id'] ?? '');
  $first_name = trim($_POST['first_name'] ?? '');
  $last_name = trim($_POST['last_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $course_id = (int) ($_POST['course_id'] ?? 0);
  $major = trim($_POST['major'] ?? '');
  $year_level = (int) ($_POST['year_level'] ?? 0);
  $status = trim($_POST['status'] ?? 'Enrolled');
  $gpa = trim($_POST['gpa'] ?? '1.75');
  $remarks = trim($_POST['remarks'] ?? 'Good Standing');

  if ($student_id === '' || $first_name === '' || $last_name === '' || $email === '' || $course_id <= 0 || $year_level <= 0) {
    $error = "Please fill in all required fields.";
  } else {
    $chkStmt = $conn->prepare("SELECT id FROM courses WHERE id = ?");
    $chkStmt->bind_param("i", $course_id);
    $chkStmt->execute();
    $courseExists = $chkStmt->get_result()->num_rows > 0;

    if (!$courseExists) {
      $error = "Selected course is invalid or does not exist.";
    } else {
      $stmt = $conn->prepare("INSERT INTO students (student_id, first_name, last_name, email, course_id, major, year_level, status, gpa, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("ssssisisss", $student_id, $first_name, $last_name, $email, $course_id, $major, $year_level, $status, $gpa, $remarks);

      try {
        $stmt->execute();
        header("Location: index.php?msg=" . urlencode("Student added successfully!"));
        exit;
      } catch (mysqli_sql_exception $e) {
        $error = "Could not save. Student ID may already exist or input violates database constraints.";
      }
    }
  }
}

include "includes/header.php";
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <a href="index.php" class="text-secondary text-decoration-none small">
          <i class="bi bi-arrow-left me-1"></i> Back to Students
        </a>
        <h3 class="text-white fw-bold mt-1">Add Student</h3>
      </div>
    </div>

    <div class="metric-card p-4">
      <?php if ($error): ?>
        <div class="alert alert-danger bg-dark text-danger border border-danger border-opacity-25 mb-4">
          <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
        </div>
      <?php endif; ?>

      <form method="post">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="dark-label">Student ID <span class="text-danger">*</span></label>
            <input type="text" name="student_id" class="form-control dark-input" placeholder="e.g. 2026-0105"
              value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="dark-label">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-select dark-select" required>
              <option value="Enrolled" <?= (($_POST['status'] ?? 'Enrolled') === 'Enrolled') ? 'selected' : '' ?>>Enrolled
              </option>
              <option value="Inactive" <?= (($_POST['status'] ?? '') === 'Inactive') ? 'selected' : '' ?>>Inactive</option>
              <option value="Graduated" <?= (($_POST['status'] ?? '') === 'Graduated') ? 'selected' : '' ?>>Graduated
              </option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="dark-label">First Name <span class="text-danger">*</span></label>
            <input type="text" name="first_name" class="form-control dark-input" placeholder="Juan"
              value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="dark-label">Last Name <span class="text-danger">*</span></label>
            <input type="text" name="last_name" class="form-control dark-input" placeholder="Dela Cruz"
              value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
          </div>
          <div class="col-md-12">
            <label class="dark-label">Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control dark-input" placeholder="juan.delacruz@example.com"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="dark-label">Course <span class="text-danger">*</span></label>
            <select name="course_id" class="form-select dark-select" required>
              <option value="">Select course...</option>
              <?php foreach ($coursesList as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ((int) ($_POST['course_id'] ?? 0) === (int) $c['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['course_code']) ?> - <?= htmlspecialchars($c['course_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="dark-label">Major</label>
            <input type="text" name="major" class="form-control dark-input" placeholder="e.g. Web Development"
              value="<?= htmlspecialchars($_POST['major'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="dark-label">Year Level <span class="text-danger">*</span></label>
            <select name="year_level" class="form-select dark-select" required>
              <option value="">Select year level...</option>
              <?php for ($i = 1; $i <= 4; $i++): ?>
                <option value="<?= $i ?>" <?= (isset($_POST['year_level']) && (int) $_POST['year_level'] === $i) ? 'selected' : '' ?>>
                  <?= formatYearLevel($i) ?> Year
                </option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="dark-label">Grade / GPA</label>
            <input type="text" name="gpa" id="addPageGpa" class="form-control dark-input" placeholder="e.g. 1.75"
              value="<?= htmlspecialchars($_POST['gpa'] ?? '1.75') ?>"
              oninput="autoUpdateRemarks('addPageGpa', 'addPageRemarks')"
              onchange="autoUpdateRemarks('addPageGpa', 'addPageRemarks')">
            <div class="form-text text-secondary small">Changing GPA updates standing automatically, or select below.
            </div>
          </div>
          <div class="col-md-12">
            <label class="dark-label">Academic Remarks / Standing</label>
            <select name="remarks" id="addPageRemarks" class="form-select dark-select">
              <option value="Dean's Lister" <?= (($_POST['remarks'] ?? '') === "Dean's Lister") ? 'selected' : '' ?>>Dean's
                Lister</option>
              <option value="Good Standing" <?= (($_POST['remarks'] ?? 'Good Standing') === 'Good Standing') ? 'selected' : '' ?>>Good Standing</option>
              <option value="Passed" <?= (($_POST['remarks'] ?? '') === 'Passed') ? 'selected' : '' ?>>Passed</option>
              <option value="No Credits" <?= (($_POST['remarks'] ?? '') === 'No Credits') ? 'selected' : '' ?>>No Credits
              </option>
              <option value="Incomplete" <?= (($_POST['remarks'] ?? '') === 'Incomplete') ? 'selected' : '' ?>>Incomplete
              </option>
              <option value="Failed" <?= (($_POST['remarks'] ?? '') === 'Failed') ? 'selected' : '' ?>>Failed</option>
            </select>
          </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top"
          style="border-color: #202330 !important;">
          <a href="index.php" class="btn btn-secondary btn-sm px-3">Cancel</a>
          <button type="submit" class="btn btn-primary btn-sm px-4">
            <i class="bi bi-check2 me-1"></i> Save Student
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include "includes/footer.php"; ?>