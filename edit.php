<?php
include "db_connect.php";
$error = "";
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
  header("Location: index.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $student_id = trim($_POST['student_id'] ?? '');
  $first_name = trim($_POST['first_name'] ?? '');
  $last_name = trim($_POST['last_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $course = trim($_POST['course'] ?? '');
  $major = trim($_POST['major'] ?? '');
  $year_level = (int) ($_POST['year_level'] ?? 0);
  $status = trim($_POST['status'] ?? 'Enrolled');
  $gpa = trim($_POST['gpa'] ?? '1.75');
  $remarks = trim($_POST['remarks'] ?? 'Good Standing');

  if ($student_id === '' || $first_name === '' || $last_name === '' || $email === '' || $course === '' || $year_level <= 0) {
    $error = "Please fill in all required fields.";
  } else {
    $stmt = $conn->prepare("UPDATE students SET student_id=?, first_name=?, last_name=?, email=?, course=?, major=?, year_level=?, status=?, gpa=?, remarks=? WHERE id=?");
    $stmt->bind_param("ssssssisssi", $student_id, $first_name, $last_name, $email, $course, $major, $year_level, $status, $gpa, $remarks, $id);

    try {
      $stmt->execute();
      header("Location: index.php?msg=" . urlencode("Student updated successfully!"));
      exit;
    } catch (mysqli_sql_exception $e) {
      $error = "Could not update. Student ID may already exist.";
    }
  }
}

// Fetch existing student
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
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
        <h3 class="text-white fw-bold mt-1">Edit Student</h3>
      </div>
    </div>

    <div class="metric-card p-4">
      <?php if ($error): ?>
        <div class="alert alert-danger bg-dark text-danger border border-danger border-opacity-25 mb-4">
          <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
        </div>
      <?php endif; ?>

      <form method="post">
        <input type="hidden" name="id" value="<?= $s['id'] ?>">

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
            <input type="text" name="course" class="form-control dark-input"
              value="<?= htmlspecialchars($s['course']) ?>" required>
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
            <input type="text" name="gpa" class="form-control dark-input"
              value="<?= htmlspecialchars($s['gpa'] ?? '1.75') ?>" placeholder="e.g. 1.25">
          </div>
          <div class="col-md-6">
            <label class="dark-label">Academic Remarks / Standing</label>
            <select name="remarks" class="form-select dark-select">
              <?php
              $opts = ["Dean's Lister", "Good Standing", "Passed", "No Credits", "Incomplete", "Failed"];
              $currR = $s['remarks'] ?? 'Good Standing';
              foreach ($opts as $opt):
                ?>
                <option value="<?= $opt ?>" <?= (strcasecmp($currR, $opt) === 0) ? 'selected' : '' ?>><?= $opt ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top"
          style="border-color: #202330 !important;">
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