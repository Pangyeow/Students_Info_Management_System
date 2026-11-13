    </main>
  </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- View Student Modal -->
<div class="modal fade" id="viewStudentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content dark-modal">
      <div class="modal-header dark-modal-header">
        <h5 class="modal-title text-white fs-6">
          <i class="bi bi-person-badge me-2 text-primary"></i>Student Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="d-flex align-items-center gap-3 mb-4">
          <div class="user-avatar" style="width: 52px; height: 52px; font-size: 1.25rem;" id="modalAvatar">
            ST
          </div>
          <div>
            <h5 class="text-white mb-0" id="modalName">Student Name</h5>
            <span class="text-secondary small" id="modalStudentId">ID</span>
          </div>
          <div class="ms-auto" id="modalStatusBadge">
            <span class="status-badge badge-enrolled">Enrolled</span>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-6">
            <div class="p-3 rounded" style="background: #111319; border: 1px solid #202330;">
              <div class="text-secondary small mb-1">Course</div>
              <div class="text-white fw-semibold" id="modalCourse">-</div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 rounded" style="background: #111319; border: 1px solid #202330;">
              <div class="text-secondary small mb-1">Major</div>
              <div class="text-white fw-semibold" id="modalMajor">-</div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 rounded" style="background: #111319; border: 1px solid #202330;">
              <div class="text-secondary small mb-1">Year Level</div>
              <div class="text-white fw-semibold" id="modalYear">-</div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 rounded" style="background: #111319; border: 1px solid #202330;">
              <div class="text-secondary small mb-1">Email</div>
              <div class="text-white small text-truncate" id="modalEmail">-</div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 rounded" style="background: #111319; border: 1px solid #202330;">
              <div class="text-secondary small mb-1">Grade / GPA</div>
              <div class="text-white fw-bold fs-6" id="modalGpa">-</div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 rounded" style="background: #111319; border: 1px solid #202330;">
              <div class="text-secondary small mb-1">Academic Standing</div>
              <div class="text-white fw-semibold small" id="modalRemarks">-</div>
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

<script>
function showStudentModal(data) {
  document.getElementById('modalName').textContent = data.first_name + ' ' + data.last_name;
  document.getElementById('modalStudentId').textContent = 'ID: ' + data.student_id;
  document.getElementById('modalCourse').textContent = data.course || 'N/A';
  document.getElementById('modalMajor').textContent = data.major || 'None';
  document.getElementById('modalYear').textContent = data.year_formatted || data.year_level;
  document.getElementById('modalEmail').textContent = data.email || 'N/A';
  document.getElementById('modalGpa').textContent = data.gpa || '1.75';
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
    let matched = false;
    for (let i = 0; i < sel.options.length; i++) {
      if (sel.options[i].value.toLowerCase() === (data.remarks || '').toLowerCase()) {
        sel.selectedIndex = i;
        matched = true;
        break;
      }
    }
    if (!matched && data.remarks) {
      const opt = new Option(data.remarks, data.remarks, true, true);
      sel.add(opt);
    }
  }

  const modal = new bootstrap.Modal(document.getElementById('editGradeModal'));
  modal.show();
}

// Ensure active styling updates immediately on click in sidebar
document.querySelectorAll('.sidebar-nav .nav-link-custom').forEach(item => {
  item.addEventListener('click', function() {
    document.querySelectorAll('.sidebar-nav .nav-link-custom').forEach(link => link.classList.remove('active'));
    this.classList.add('active');
  });
});

// Theme Toggle Functionality
function toggleTheme() {
  const isLight = document.body.classList.toggle('light-mode');
  const icon = document.getElementById('themeIcon');
  if (isLight) {
    localStorage.setItem('sims-theme', 'light');
    if (icon) icon.className = 'bi bi-moon-stars-fill';
  } else {
    localStorage.setItem('sims-theme', 'dark');
    if (icon) icon.className = 'bi bi-sun-fill';
  }
}

// Ensure icon matches current theme on load
document.addEventListener('DOMContentLoaded', function() {
  const currentTheme = localStorage.getItem('sims-theme');
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
