<?php
/**
 * includes/footer.php — Shared page footer.
 * Closes the layout, loads Bootstrap JS, holds the student details modal and
 * the page scripts (modals, grade remarks, sidebar, light/dark theme).
 */
?>
</main>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<div class="modal fade" id="viewStudentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content dark-modal">
      <div class="modal-header dark-modal-header">
        <h5 class="modal-title text-body-emphasis fs-6">
          <i class="bi bi-person-badge me-2 text-primary"></i>Student Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="d-flex align-items-center gap-3 mb-4">
          <div class="user-avatar" style="width: 52px; height: 52px; font-size: 1.25rem;" id="modalAvatar">
            ST
          </div>
          <div>
            <h5 class="mb-0" id="modalName">Student Name</h5>
            <span class="text-secondary small" id="modalStudentId">ID</span>
          </div>
          <div class="ms-auto" id="modalStatusBadge">
            <span class="status-badge badge-enrolled">Enrolled</span>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-6">
            <div class="detail-box">
              <div class="text-secondary small mb-1">Course</div>
              <div class="fw-semibold" id="modalCourse">-</div>
            </div>
          </div>
          <div class="col-6">
            <div class="detail-box">
              <div class="text-secondary small mb-1">Major</div>
              <div class="fw-semibold" id="modalMajor">-</div>
            </div>
          </div>
          <div class="col-6">
            <div class="detail-box">
              <div class="text-secondary small mb-1">Year Level</div>
              <div class="fw-semibold" id="modalYear">-</div>
            </div>
          </div>
          <div class="col-6">
            <div class="detail-box">
              <div class="text-secondary small mb-1">Email</div>
              <div class="small text-truncate" id="modalEmail">-</div>
            </div>
          </div>
          <div class="col-6">
            <div class="detail-box">
              <div class="text-secondary small mb-1">Grade / GPA</div>
              <div class="fw-bold fs-6" id="modalGpa">-</div>
            </div>
          </div>
          <div class="col-6">
            <div class="detail-box">
              <div class="text-secondary small mb-1">Academic Standing</div>
              <div class="fw-semibold small" id="modalRemarks">-</div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer dark-modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <a href="#" id="modalEditBtn" class="btn btn-primary btn-sm">
          <i class="bi bi-pencil me-1"></i> Edit Student
        </a>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content dark-modal">
      <div class="modal-body p-4 text-center">
        <div class="confirm-delete-icon mx-auto mb-3">
          <i class="bi bi-trash3"></i>
        </div>
        <h5 class="mb-2 fs-6 fw-semibold" id="confirmDeleteTitle">Delete record?</h5>
        <p class="text-secondary small mb-0">
          Are you sure you want to delete <strong id="confirmDeleteName" class="text-break"></strong>?
          This can't be undone.
        </p>
      </div>
      <div class="modal-footer dark-modal-footer justify-content-center gap-2">
        <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger btn-sm px-3" id="confirmDeleteBtn">
          <i class="bi bi-trash3 me-1"></i> Delete
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  /* Delete confirmation: any form with data-confirm-delete="<name>" opens the
     modal above instead of submitting, and only submits after "Delete" is clicked. */
  (function () {
    const modalEl = document.getElementById('confirmDeleteModal');
    if (!modalEl || !window.bootstrap) return;
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    let pendingForm = null;

    document.addEventListener('submit', function (e) {
      const form = e.target.closest('form[data-confirm-delete]');
      if (!form) return;
      e.preventDefault();
      pendingForm = form;
      document.getElementById('confirmDeleteName').textContent = form.dataset.confirmDelete;
      modal.show();
    });

    document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
      if (!pendingForm) return;
      this.disabled = true;
      pendingForm.submit();
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
      pendingForm = null;
      document.getElementById('confirmDeleteBtn').disabled = false;
    });
  })();

  function showStudentModal(data) {
    document.getElementById('modalName').textContent = data.first_name + ' ' + data.last_name;
    document.getElementById('modalStudentId').textContent = 'ID: ' + data.student_id;
    document.getElementById('modalCourse').textContent = data.course || 'N/A';
    document.getElementById('modalMajor').textContent = data.major || 'None';
    document.getElementById('modalYear').textContent = data.year_formatted || data.year_level;
    document.getElementById('modalEmail').textContent = data.email || 'N/A';
    document.getElementById('modalGpa').textContent = data.gpa || '—';
    document.getElementById('modalRemarks').textContent = data.remarks || 'Good Standing';

    const initials = (data.first_name.charAt(0) + data.last_name.charAt(0)).toUpperCase();
    document.getElementById('modalAvatar').textContent = initials;

    const statusBadge = document.getElementById('modalStatusBadge');
    if ((data.status || '').toLowerCase() === 'enrolled') {
      statusBadge.innerHTML = '<span class="status-badge badge-enrolled">Enrolled</span>';
    } else if ((data.status || '').toLowerCase() === 'inactive') {
      statusBadge.innerHTML = '<span class="status-badge badge-inactive">Inactive</span>';
    } else {
      statusBadge.innerHTML = '<span class="status-badge badge-graduated">' + (data.status || 'Active') + '</span>';
    }

    document.getElementById('modalEditBtn').href = 'edit.php?id=' + data.id;

    const modal = new bootstrap.Modal(document.getElementById('viewStudentModal'));
    modal.show();
  }

  function openGradeModal(data) {
    document.getElementById('editGradeStudentId').value = data.id;
    document.getElementById('editGradeStudentName').textContent = data.name;
    document.getElementById('editGradeStudentIdText').textContent = data.student_id;
    document.getElementById('editGradeStudentCourse').textContent = data.course;
    document.getElementById('editGradeStudentYear').textContent = data.year;
    document.getElementById('editGradeGpaInput').value = data.gpa;

    const sel = document.getElementById('editGradeRemarksSelect');
    if (sel) {
      const parser = document.createElement('textarea');
      parser.innerHTML = data.remarks || 'Good Standing';
      const cleanRemarks = parser.value.trim().toLowerCase();

      let matched = false;
      for (let i = 0; i < sel.options.length; i++) {
        if (sel.options[i].value.trim().toLowerCase() === cleanRemarks) {
          sel.selectedIndex = i;
          matched = true;
          break;
        }
      }
      if (!matched && data.remarks) {
        const opt = new Option(parser.value, parser.value, true, true);
        sel.add(opt);
      }
    }

    const modal = new bootstrap.Modal(document.getElementById('editGradeModal'));
    modal.show();
  }

  /**
   * Sets the Academic Standing dropdown from the grade typed in the GPA field.
   * Accepts both scales:
   *   1.00–1.50 or 90–100 → Dean's Lister
   *   1.51–2.50 or 80–89  → Good Standing
   *   2.51–3.00 or 75–79  → Passed
   *   3.01–5.00 or <75    → Failed
   * Adds the option to the dropdown if it doesn't exist yet. Leaves it unchanged
   * when the field is empty or not a number.
   */
  function autoUpdateRemarks(gpaInputId, remarksSelectId) {
    const gpaInput = document.getElementById(gpaInputId);
    const sel = document.getElementById(remarksSelectId);
    if (!gpaInput || !sel) return;

    const rawVal = gpaInput.value.trim();
    if (rawVal === '') return;

    const gpaVal = parseFloat(rawVal);
    if (isNaN(gpaVal)) return;

    let targetRemark = "";
    if (gpaVal >= 1.0 && gpaVal <= 1.50) {
      targetRemark = "Dean's Lister";
    } else if (gpaVal > 1.50 && gpaVal <= 2.50) {
      targetRemark = "Good Standing";
    } else if (gpaVal > 2.50 && gpaVal <= 3.00) {
      targetRemark = "Passed";
    } else if (gpaVal > 3.00 && gpaVal <= 5.00) {
      targetRemark = "Failed";
    } else if (gpaVal >= 90.0 && gpaVal <= 100.0) {
      targetRemark = "Dean's Lister";
    } else if (gpaVal >= 80.0 && gpaVal < 90.0) {
      targetRemark = "Good Standing";
    } else if (gpaVal >= 75.0 && gpaVal < 80.0) {
      targetRemark = "Passed";
    } else if (gpaVal > 5.0 && gpaVal < 75.0) {
      targetRemark = "Failed";
    }

    if (targetRemark) {
      let matched = false;
      for (let i = 0; i < sel.options.length; i++) {
        if (sel.options[i].value.toLowerCase() === targetRemark.toLowerCase()) {
          sel.selectedIndex = i;
          matched = true;
          break;
        }
      }
      if (!matched) {
        const opt = new Option(targetRemark, targetRemark, true, true);
        sel.add(opt);
      }
    }
  }

  function openEditCourseModal(course) {
    const idInput = document.getElementById('editCourseId');
    const codeInput = document.getElementById('editCourseCode');
    const nameInput = document.getElementById('editCourseName');
    if (idInput && codeInput && nameInput) {
      idInput.value = course.id;
      codeInput.value = course.course_code;
      nameInput.value = course.course_name;
      const modalEl = document.getElementById('editCourseModal');
      if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
      }
    }
  }

  document.querySelectorAll('.sidebar-nav .nav-link-custom').forEach(item => {
    item.addEventListener('click', function () {
      document.querySelectorAll('.sidebar-nav .nav-link-custom').forEach(link => link.classList.remove('active'));
      this.classList.add('active');
      const sidebarMenu = document.getElementById('sidebarMenu');
      if (sidebarMenu && window.bootstrap && bootstrap.Offcanvas) {
        const instance = bootstrap.Offcanvas.getInstance(sidebarMenu);
        if (instance) instance.hide();
      }
    });
  });

  function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-bs-theme') || (document.body.classList.contains('light-mode') ? 'light' : 'dark');
    const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-bs-theme', nextTheme);
    if (nextTheme === 'light') {
      document.body.classList.add('light-mode');
    } else {
      document.body.classList.remove('light-mode');
    }
    localStorage.setItem('sims-cici-theme', nextTheme);
    const icon = document.getElementById('themeIcon');
    if (icon) {
      icon.className = nextTheme === 'light' ? 'bi bi-moon-stars-fill' : 'bi bi-sun-fill';
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    const currentTheme = localStorage.getItem('sims-cici-theme') || 'dark';
    document.documentElement.setAttribute('data-bs-theme', currentTheme);
    const icon = document.getElementById('themeIcon');
    if (currentTheme === 'light') {
      document.body.classList.add('light-mode');
      if (icon) icon.className = 'bi bi-moon-stars-fill';
    } else {
      document.body.classList.remove('light-mode');
      if (icon) icon.className = 'bi bi-sun-fill';
    }
  });
</script>
</body>

</html>