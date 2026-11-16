<?php
/**
 * includes/header.php — Shared page header.
 * Outputs the <head>, sidebar navigation (offcanvas on mobile) and the top bar
 * with search, theme toggle and notifications. Works out which tab is active.
 */
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$currentTab = $_GET['tab'] ?? $_GET['view'] ?? '';

if (empty($currentTab)) {
  if ($currentPage === 'dashboard.php' || $currentPage === 'index.php') {
    $currentTab = 'dashboard';
  } else {
    $currentTab = 'students';
  }
}
$searchVal = htmlspecialchars($_GET['search'] ?? '');
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SIMS CICI - Student Information Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>" rel="stylesheet">
  <script>
    (function () {
      const savedTheme = localStorage.getItem('sims-cici-theme') || 'dark';
      document.documentElement.setAttribute('data-bs-theme', savedTheme);
      if (savedTheme === 'light') {
        document.documentElement.classList.add('light-mode');
        document.addEventListener('DOMContentLoaded', () => {
          document.body.classList.add('light-mode');
          const icon = document.getElementById('themeIcon');
          if (icon) icon.className = 'bi bi-moon-stars-fill';
        });
      } else {
        document.documentElement.classList.remove('light-mode');
      }
    })();
  </script>
</head>

<body>

  <div class="app-container">
    <nav class="offcanvas-lg offcanvas-start app-sidebar navbar flex-column align-items-stretch" tabindex="-1" id="sidebarMenu" aria-label="Main Navigation">
      <div class="offcanvas-header d-lg-none d-flex align-items-center justify-content-between p-3 border-bottom border-subtle">
        <a class="navbar-brand brand-wrapper p-0 m-0" href="index.php?tab=dashboard" aria-label="SIMS CICI">
          <div class="brand-icon">
            <i class="bi bi-grid-fill"></i>
          </div>
          <span class="brand-title">SIMS CICI</span>
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
      </div>

      <div class="offcanvas-body d-flex flex-column h-100 w-100 justify-content-between p-0">
        <div class="w-100 p-3 p-lg-0">
          <a class="navbar-brand brand-wrapper d-none d-lg-flex" href="index.php?tab=dashboard" aria-label="SIMS CICI">
            <div class="brand-icon">
              <i class="bi bi-grid-fill"></i>
            </div>
            <span class="brand-title">SIMS CICI</span>
          </a>

          <ul class="navbar-nav flex-column sidebar-nav">
            <li class="nav-item">
              <a href="index.php?tab=dashboard"
                class="nav-link nav-link-custom <?= ($currentTab === 'dashboard') ? 'active' : '' ?>"
                title="Dashboard" aria-label="Dashboard">
                <i class="bi bi-speedometer2"></i>
                <span class="nav-label">Dashboard</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="index.php?tab=students"
                class="nav-link nav-link-custom <?= ($currentTab === 'students') ? 'active' : '' ?>"
                title="Students" aria-label="Students">
                <i class="bi bi-people-fill"></i>
                <span class="nav-label">Students</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="index.php?tab=courses"
                class="nav-link nav-link-custom <?= ($currentTab === 'courses') ? 'active' : '' ?>"
                title="Courses" aria-label="Courses">
                <i class="bi bi-journal-bookmark"></i>
                <span class="nav-label">Courses</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="index.php?tab=grades"
                class="nav-link nav-link-custom <?= ($currentTab === 'grades') ? 'active' : '' ?>"
                title="Grades" aria-label="Grades">
                <i class="bi bi-award"></i>
                <span class="nav-label">Grades</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="index.php?tab=reports"
                class="nav-link nav-link-custom <?= ($currentTab === 'reports') ? 'active' : '' ?>"
                title="Reports" aria-label="Reports">
                <i class="bi bi-bar-chart-line"></i>
                <span class="nav-label">Reports</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="app-main">
      <header class="top-bar">
        <div class="top-bar-left d-flex align-items-center gap-2">
          <button class="hamburger-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-label="Toggle navigation">
            <i class="bi bi-list"></i>
          </button>
          <a class="mobile-brand d-lg-none" href="index.php?tab=dashboard" aria-label="SIMS CICI">
            <div class="brand-icon">
              <i class="bi bi-grid-fill"></i>
            </div>
            <span class="brand-title">SIMS CICI</span>
          </a>
        </div>

        <?php if ($currentTab !== 'dashboard'): ?>
        <form class="search-box-wrapper" method="get" action="index.php" role="search">
          <div class="search-field">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="search-input-top" placeholder="Search students"
              aria-label="Search students"
              value="<?= $searchVal ?>">
          </div>
          <button type="submit" class="btn btn-primary search-btn" aria-label="Search">
            <i class="bi bi-search"></i>
            <span class="search-btn-label">Search</span>
          </button>
          <input type="hidden" name="tab" value="<?= htmlspecialchars($currentTab) ?>">
          <?php if (!empty($_GET['course'])): ?>
            <input type="hidden" name="course" value="<?= htmlspecialchars($_GET['course']) ?>">
          <?php endif; ?>
          <?php if (!empty($_GET['year'])): ?>
            <input type="hidden" name="year" value="<?= htmlspecialchars($_GET['year']) ?>">
          <?php endif; ?>
        </form>
        <?php endif; ?>

        <div class="top-bar-right d-flex align-items-center gap-3">
          <button class="theme-toggle-btn" id="themeToggleBtn" title="Toggle Light / Dark mode" aria-label="Toggle Light / Dark mode" type="button"
            onclick="toggleTheme()">
            <i class="bi bi-sun-fill" id="themeIcon"></i>
          </button>
          <div class="dropdown">
            <button class="notification-btn" title="Notifications" aria-label="Notifications" type="button"
              data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
              <i class="bi bi-bell"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end notification-dropdown shadow">
              <div class="notification-dropdown-header">
                <span>Notifications</span>
              </div>
              <div class="notification-empty">
                <i class="bi bi-bell-slash"></i>
                <p class="mb-0">No new notifications</p>
                <small>You're all caught up!</small>
              </div>
            </div>
          </div>
          <div class="user-profile-badge">
            <div class="user-avatar" aria-hidden="true">AD</div>
            <span class="user-name">Admin</span>
          </div>
        </div>
      </header>

      <main class="content-body">
