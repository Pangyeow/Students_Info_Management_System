<?php
/**
 * delete.php — Deletes the student whose id is POSTed and returns to the student list.
 * Only accepts POST, so a link or page prefetch can't delete a record.
 */
include "db_connect.php";

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header("Location: index.php?tab=students");
    exit;
}

/* ---------- CRUD: delete a student ---------- */
$id = (int) ($_POST['id'] ?? 0);
if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM students WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
header("Location: index.php?msg=" . urlencode("Student deleted successfully!"));
exit;
