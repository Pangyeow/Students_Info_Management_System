<?php

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$currentTab = $_GET['tab'] ?? $_GET['view'] ?? '';

if (empty($currentTab)) {
  if ($currentPage === 'dashboard.php') {
    $currentTab = 'dashboard';
  } else {
    $currentTab = 'students';
  }
}
$searchVal = htmlspecialchars($_GET['search'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SIMS CICI - Student Information Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  <script>
    (function () {
      const savedTheme = localStorage.getItem('sims-cici-theme');
      if (savedTheme === 'light') {
        document.documentElement.classList.add('light-mode');
        document.addEventListener('DOMContentLoaded', () => {
          document.body.classList.add('light-mode');
          const icon = document.getElementById('themeIcon');
          if (icon) icon.className = 'bi bi-moon-stars-fill';
        });
      }
    })();
  </script>
</head>

<body>

  <div class="app-container">
    <nav class="navbar navbar-expand-lg app-sidebar flex-column align-items-stretch">
      <div class="d-flex flex-column h-100 w-100 justify-content-between">
        <div>
          <a class="navbar-brand brand-wrapper" href="index.php?tab=students">
            <div class="brand-icon">
              <i class="bi bi-grid-fill"></i>
            </div>
            <span class="brand-title">SIMS CICI</span>
          </a>

          <ul class="navbar-nav flex-column sidebar-nav">
            <li class="nav-item">
              <a href="index.php?tab=dashboard"
                class="nav-link nav-link-custom <?= ($currentTab === 'dashboard') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i>
                <span class="nav-label">Dashboard</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="index.php?tab=students"
                class="nav-link nav-link-custom <?= ($currentTab === 'students') ? 'active' : '' ?>">
                <i class="bi bi-people-fill"></i>
                <span class="nav-label">Students</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="index.php?tab=courses"
                class="nav-link nav-link-custom <?= ($currentTab === 'courses') ? 'active' : '' ?>">
                <i class="bi bi-journal-bookmark"></i>
                <span class="nav-label">Courses</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="index.php?tab=grades"
                class="nav-link nav-link-custom <?= ($currentTab === 'grades') ? 'active' : '' ?>">
                <i class="bi bi-award"></i>
                <span class="nav-label">Grades</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="index.php?tab=reports"
                class="nav-link nav-link-custom <?= ($currentTab === 'reports') ? 'active' : '' ?>">
                <i class="bi bi-bar-chart-line"></i>
                <span class="nav-label">Reports</span>
              </a>
            </li>
          </ul>
        </div>

        <div>
          <a href="javascript:void(0)"
            onclick="if(confirm('Are you sure you want to log out?')) location.href='index.php?msg=Logged+out+successfully';"
            class="nav-link nav-link-custom logout">
            <i class="bi bi-box-arrow-left"></i>
            <span class="nav-label">Logout</span>
          </a>
        </div>
      </div>
    </nav>

    <div class="app-main">
      <header class="top-bar">
        <form class="search-box-wrapper" method="get" action="index.php">
          <i class="bi bi-search"></i>
          <input type="text" name="search" class="search-input-top" placeholder="Search students"
            value="<?= $searchVal ?>">
          <input type="hidden" name="tab" value="<?= htmlspecialchars($currentTab) ?>">
          <?php if (!empty($_GET['course'])): ?>
            <input type="hidden" name="course" value="<?= htmlspecialchars($_GET['course']) ?>">
          <?php endif; ?>
          <?php if (!empty($_GET['year'])): ?>
            <input type="hidden" name="year" value="<?= htmlspecialchars($_GET['year']) ?>">
          <?php endif; ?>
        </form>

        <div class="top-bar-right d-flex align-items-center gap-3">
          <button class="theme-toggle-btn" id="themeToggleBtn" title="Toggle Light / Dark mode" type="button"
            onclick="toggleTheme()">
            <i class="bi bi-sun-fill" id="themeIcon"></i>
          </button>
          <button class="notification-btn" title="Notifications" type="button" onclick="alert('No new notifications')">
            <i class="bi bi-bell"></i>
          </button>
          <div class="user-profile-badge">
            <div class="user-avatar">AD</div>
            <span class="user-name">Admin</span>
          </div>
        </div>
      </header>

      <main class="content-body">