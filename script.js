// Handles register and login using fetch API
document.addEventListener('DOMContentLoaded', function(){
  const registerForm = document.getElementById('registerForm');
  const loginForm = document.getElementById('loginForm');

  if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(registerForm);

      const res = await fetch('backend/register_user.php', {
        method: 'POST',
        body: formData
      });

      const text = (await res.text()).trim();
      console.log('register response ->', text);

      if (text === 'Success') {
        alert('Registration successful! Please login.');
        window.location.href = 'login.html';
      } else if (text === 'UserExists') {
        alert('This email is already registered.');
      } else if (text === 'PasswordMismatch') {
        alert('Passwords do not match.');
      } else {
        alert('Something went wrong: ' + text);
      }
    });
  }

  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(loginForm);

      const res = await fetch('backend/login_user.php', {
        method: 'POST',
        body: formData
      });

      const text = (await res.text()).trim();
      console.log('login response ->', text);

      if (text === 'LoginSuccess') {
        window.location.href = 'dashboard.php';
      } else if (text === 'InvalidPassword') {
        alert('Invalid password.');
      } else if (text === 'NoUser') {
        alert('No user found with this email.');
      } else {
        alert('Something went wrong: ' + text);
      }
    });
  }
});



document.addEventListener('DOMContentLoaded', function () {
  // Logout functionality
  const logoutBtn = document.getElementById("logoutBtn");
  const logoutModal = document.getElementById("logoutModal");
  const cancelLogout = document.getElementById("cancelLogout");
  const confirmLogout = document.getElementById("confirmLogout");

  if (logoutBtn && logoutModal && cancelLogout && confirmLogout) {
    logoutBtn.addEventListener("click", () => logoutModal.style.display = "flex");
    cancelLogout.addEventListener("click", () => logoutModal.style.display = "none");
    confirmLogout.addEventListener("click", () => window.location.href = "backend/logout.php");
  }

  // Profile edit button logic
  const editBtn = document.getElementById("editProfileBtn");
  const profileModal = document.getElementById("editProfileModal");
  const closeEditModal = document.getElementById("closeEditModal");

  editBtn.addEventListener("click", () => profileModal.style.display = "flex");
  closeEditModal.addEventListener("click", () => profileModal.style.display = "none");

  // AJAX upload
  const profileForm = document.getElementById("profileForm");
  profileForm.addEventListener("submit", function (e) {
    e.preventDefault();
    let formData = new FormData(this);

    fetch("backend/update_profile_img.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.text())
    .then(data => {
      if (data === "success") {
        location.reload(); // refresh to update photo
      } else {
        alert(data);
      }
    });
  });
});

// -------------------- CLASS SCHEDULE PAGE --------------------//
// -------------------- CLASS SCHEDULE PAGE --------------------//
document.addEventListener("DOMContentLoaded", () => {
  const addBtn = document.getElementById("addClassBtn");
  const modal = document.getElementById("addClassModal");
  const cancelBtn = document.getElementById("cancelAddClass");

  if (addBtn && modal && cancelBtn) {
    // Open modal
    addBtn.addEventListener("click", () => {
      modal.style.display = "flex";
    });

    // Close modal
    cancelBtn.addEventListener("click", () => {
      modal.style.display = "none";
    });

    // Close modal when clicking outside
    window.addEventListener("click", (e) => {
      if (e.target === modal) modal.style.display = "none";
    });
  }
});

// -------------------- EDIT CLASS MODAL --------------------
document.addEventListener("DOMContentLoaded", () => {
  const editModal = document.getElementById("editClassModal");
  const cancelEdit = document.getElementById("cancelEditClass");

  document.querySelectorAll(".edit-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      editModal.style.display = "flex";
      document.getElementById("editClassId").value = btn.dataset.id;
      document.getElementById("editClassName").value = btn.dataset.name;
      document.getElementById("editInstructor").value = btn.dataset.instructor;
      document.getElementById("editTime").value = btn.dataset.time;
      document.getElementById("editDay").value = btn.dataset.day;
    });
  });

  cancelEdit.addEventListener("click", () => {
    editModal.style.display = "none";
  });

  window.addEventListener("click", (e) => {
    if (e.target === editModal) editModal.style.display = "none";
  });
});


// -------------------- ASSIGNMENT ADD / EDIT MODALS --------------------

function filterAssign(status) {
  window.location.href = "assignments.php?status=" + status;
}

function openAssignModal() {
  document.getElementById("assignModal").style.display = "flex";
  document.getElementById("modalTitle").innerText = "Add Assignment";
  document.getElementById("assign_id").value = "";
  document.getElementById("title").value = "";
  document.getElementById("description").value = "";
  document.getElementById("due_date").value = "";
  document.getElementById("status").value = "Pending";
}

function editAssign(id, title, desc, due, status) {
  document.getElementById("assignModal").style.display = "flex";
  document.getElementById("modalTitle").innerText = "Edit Assignment";
  document.getElementById("assign_id").value = id;
  document.getElementById("title").value = title;
  document.getElementById("description").value = desc;
  document.getElementById("due_date").value = due;
  document.getElementById("status").value = status;
}

function closeAssignModal() {
  document.getElementById("assignModal").style.display = "none";
}

// Close when clicked outside modal
window.onclick = function(e) {
  const assignModal = document.getElementById("assignModal");
  const sessionModal = document.getElementById("sessionModal");
  const logoutModal = document.getElementById("logoutModal");

  if (e.target === assignModal) assignModal.style.display = "none";
  if (e.target === sessionModal) sessionModal.style.display = "none";
  if (e.target === logoutModal) logoutModal.style.display = "none";
};

// ================================= Study Sessions JS =========================================== //
function openSessionModal() {
  document.getElementById("sessionModal").style.display = "flex";
  document.getElementById("sessionTitle").innerText = "Add Study Session";
  document.getElementById("session_id").value = "";
  document.getElementById("subject").value = "";
  document.getElementById("duration").value = "";
  document.getElementById("date").value = "";
  document.getElementById("notes").value = "";
}

function editSession(id, subject, duration, date, notes) {
  document.getElementById("sessionModal").style.display = "flex";
  document.getElementById("sessionTitle").innerText = "Edit Study Session";
  document.getElementById("session_id").value = id;
  document.getElementById("subject").value = subject;
  document.getElementById("duration").value = duration;
  document.getElementById("date").value = date;
  document.getElementById("notes").value = notes;
}

function closeSessionModal() {
  document.getElementById("sessionModal").style.display = "none";
}


