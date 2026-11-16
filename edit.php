<?php
/**
 * edit.php — Edit Student form.
 * Loads a student by ?id=, shows the form pre-filled and saves changes on submit.
 */
include "db_connect.php";
$error = "";
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
  header("Location: index.php");
  exit;
}

$coursesList = [];
$cRes = $conn->query("SELECT id, course_code, course_name FROM courses ORDER BY course_code ASC");
if ($cRes) {
  $coursesList = $cRes->fetch_all(MYSQLI_ASSOC);
}

/* ---------- CRUD: update a student ---------- */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $student_id = trim($_POST['student_id'] ?? '');
  $first_name = trim($_POST['first_name'] ?? '');
  $last_name = trim($_POST['last_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $course_id = (int) ($_POST['course_id'] ?? 0);
  $major = trim($_POST['major'] ?? '');
  $year_level = (int) ($_POST['year_level'] ?? 0);
  $status = trim($_POST['status'] ?? 'Enrolled');
  $gpa = normalizeGpa($_POST['gpa'] ?? '1.75');
  $remarks = trim($_POST['remarks'] ?? 'Good Standing');
  $retTab = trim($_POST['ret_tab'] ?? $_GET['tab'] ?? '');

  if ($student_id === '' || $first_name === '' || $last_name === '' || $email === '' || $course_id <= 0 || $year_level <= 0) {
    $error = "Please fill in all required fields.";
  } elseif ($gpa === false) {
    $error = "Grade / GPA must be a number from 0 to 100 (e.g. 1.75 or 92).";
  } else {
    $chkStmt = $conn->prepare("SELECT id FROM courses WHERE id = ?");
    $chkStmt->bind_param("i", $course_id);
    $chkStmt->execute();
    $courseExists = $chkStmt->get_result()->num_rows > 0;

    if (!$courseExists) {
      $error = "Selected course is invalid or does not exist.";
    } else {
      $stmt = $conn->prepare("UPDATE students SET student_id=?, first_name=?, last_name=?, email=?, course_id=?, major=?, year_level=?, status=?, gpa=?, remarks=? WHERE id=?");
      $stmt->bind_param("ssssisisssi", $student_id, $first_name, $last_name, $email, $course_id, $major, $year_level, $status, $gpa, $remarks, $id);

      try {
        $stmt->execute();
        $targetUrl = "index.php?" . ($retTab ? "tab=" . urlencode($retTab) . "&" : "") . "msg=" . urlencode("Student updated successfully!");
        header("Location: " . $targetUrl);
        exit;
      } catch (mysqli_sql_exception $e) {
        $error = "Could not update. Student ID may already exist or input violates database constraints.";
      }
    }
  }
}

$stmt = $conn->prepare("SELECT s.*, c.course_code, c.course_name FROM students s LEFT JOIN courses c ON s.course_id = c.id WHERE s.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$s = $stmt->get_result()->fetch_assoc();

if (!$s) {
  header("Location: index.php");
  exit;
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
        <h3 class="fw-bold mt-1">Edit Student</h3>
      </div>
    </div>

    <div class="metric-card p-4">
      <?php if ($error): ?>
        <div class="alert alert-danger text-danger border border-danger border-opacity-25 mb-4">
          <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
        </div>
      <?php endif; ?>

      <form method="post">
        <input type="hidden" name="id" value="<?= $s['id'] ?>">
        <input type="hidden" name="ret_tab" value="<?= htmlspecialchars($_GET['tab'] ?? '') ?>">

        <div class="row g-3">
          <div class="col-md-6">
            <label class="dark-label">Student ID <span class="text-danger">*</span></label>
            <input type="text" name="student_id" class="form-control dark-input"
              value="<?= htmlspecialchars($s['student_id']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="dark-label">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-select dark-select" required>
              <option value="Enrolled" <?= (($s['status'] ?? 'Enrolled') === 'Enrolled') ? 'selected' : '' ?>>Enrolled
              </option>
              <option value="Inactive" <?= (($s['status'] ?? '') === 'Inactive') ? 'selected' : '' ?>>Inactive</option>
              <option value="Graduated" <?= (($s['status'] ?? '') === 'Graduated') ? 'selected' : '' ?>>Graduated</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="dark-label">First Name <span class="text-danger">*</span></label>
            <input type="text" name="first_name" class="form-control dark-input"
              value="<?= htmlspecialchars($s['first_name']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="dark-label">Last Name <span class="text-danger">*</span></label>
            <input type="text" name="last_name" class="form-control dark-input"
              value="<?= htmlspecialchars($s['last_name']) ?>" required>
          </div>
          <div class="col-md-12">
            <label class="dark-label">Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control dark-input"
              value="<?= htmlspecialchars($s['email']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="dark-label">Course <span class="text-danger">*</span></label>
            <select name="course_id" class="form-select dark-select" required>
              <option value="">Select course...</option>
              <?php foreach ($coursesList as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ((int) $s['course_id'] === (int) $c['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['course_code']) ?> - <?= htmlspecialchars($c['course_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="dark-label">Major</label>
            <input type="text" name="major" class="form-control dark-input"
              value="<?= htmlspecialchars($s['major'] ?? '') ?>" placeholder="e.g. Web Development">
          </div>
          <div class="col-md-12">
            <label class="dark-label">Year Level <span class="text-danger">*</span></label>
            <select name="year_level" class="form-select dark-select" required>
              <?php for ($i = 1; $i <= 4; $i++): ?>
                <option value="<?= $i ?>" <?= ((int) $s['year_level'] === $i) ? 'selected' : '' ?>>
                  <?= formatYearLevel($i) ?> Year
                </option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="dark-label">Grade / GPA</label>
            <input type="text" name="gpa" id="editStudentGpa" class="form-control dark-input"
              value="<?= htmlspecialchars($s['gpa'] ?? '') ?>" placeholder="e.g. 1.25"
              oninput="autoUpdateRemarks('editStudentGpa', 'editStudentRemarks')"
              onchange="autoUpdateRemarks('editStudentGpa', 'editStudentRemarks')">
            <div class="form-text text-secondary small">Changing GPA updates remarks automatically, or select below.
            </div>
          </div>
          <div class="col-md-6">
            <label class="dark-label">Academic Remarks / Standing <span class="text-danger">*</span></label>
            <select name="remarks" id="editStudentRemarks" class="form-select dark-select" required>
              <?php
              $opts = ["Dean's Lister", "Good Standing", "Passed", "No Credits", "Incomplete", "Failed"];
              $currR = trim(html_entity_decode($s['remarks'] ?? 'Good Standing', ENT_QUOTES, 'UTF-8'));
              if (!empty($currR) && !in_array($currR, $opts)) {
                $opts[] = $currR;
              }
              foreach ($opts as $opt):
                ?>
                <option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>" <?= (strcasecmp($currR, $opt) === 0) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top theme-border-top">
          <a href="index.php" class="btn btn-secondary btn-sm px-3">Cancel</a>
          <button type="submit" class="btn btn-primary btn-sm px-4">
            <i class="bi bi-check2 me-1"></i> Update Student
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include "includes/footer.php"; ?>