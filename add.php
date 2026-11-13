<?php
include "db_connect.php";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $course     = trim($_POST['course'] ?? '');
    $major      = trim($_POST['major'] ?? '');
    $year_level = (int)($_POST['year_level'] ?? 0);
    $status     = trim($_POST['status'] ?? 'Enrolled');

    if ($student_id === '' || $first_name === '' || $last_name === '' || $email === '' || $course === '' || $year_level <= 0) {
        $error = "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO students (student_id, first_name, last_name, email, course, major, year_level, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssis", $student_id, $first_name, $last_name, $email, $course, $major, $year_level, $status);

        try {
            $stmt->execute();
            header("Location: index.php?msg=" . urlencode("Student added successfully!"));
            exit;
        } catch (mysqli_sql_exception $e) {
            $error = "Could not save. Student ID may already exist or invalid input.";
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
            <input type="text" name="student_id" class="form-control dark-input" 
                   placeholder="e.g. 2026-0105" value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="dark-label">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-select dark-select" required>
              <option value="Enrolled" <?= (($_POST['status'] ?? 'Enrolled') === 'Enrolled') ? 'selected' : '' ?>>Enrolled</option>
              <option value="Inactive" <?= (($_POST['status'] ?? '') === 'Inactive') ? 'selected' : '' ?>>Inactive</option>
              <option value="Graduated" <?= (($_POST['status'] ?? '') === 'Graduated') ? 'selected' : '' ?>>Graduated</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="dark-label">First Name <span class="text-danger">*</span></label>
            <input type="text" name="first_name" class="form-control dark-input" 
                   placeholder="Juan" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="dark-label">Last Name <span class="text-danger">*</span></label>
            <input type="text" name="last_name" class="form-control dark-input" 
                   placeholder="Dela Cruz" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
          </div>
          <div class="col-md-12">
            <label class="dark-label">Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control dark-input" 
                   placeholder="juan.delacruz@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="dark-label">Course <span class="text-danger">*</span></label>
            <input type="text" name="course" class="form-control dark-input" 
                   placeholder="e.g. BSIT, BSCS, BSBA" value="<?= htmlspecialchars($_POST['course'] ?? '') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="dark-label">Major</label>
            <input type="text" name="major" class="form-control dark-input" 
                   placeholder="e.g. Web Development" value="<?= htmlspecialchars($_POST['major'] ?? '') ?>">
          </div>
          <div class="col-md-12">
            <label class="dark-label">Year Level <span class="text-danger">*</span></label>
            <select name="year_level" class="form-select dark-select" required>
              <option value="">Select year level...</option>
              <?php for ($i = 1; $i <= 4; $i++): ?>
                <option value="<?= $i ?>" <?= (isset($_POST['year_level']) && (int)$_POST['year_level'] === $i) ? 'selected' : '' ?>>
                  <?= formatYearLevel($i) ?> Year
                </option>
              <?php endfor; ?>
            </select>
          </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #202330 !important;">
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
