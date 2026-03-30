const users = {
  students: [
    { id: 1, name: "ali", password: "1234" },
    { id: 2, name: "sara", password: "1234" },
  ],
  supervisors: [{ id: 1, name: "dr_ahmed", password: "admin" }],
};

const STORAGE_KEY = "pg_dissertations";

const el = {
  loginForm: document.getElementById("loginForm"),
  loginView: document.getElementById("loginView"),
  studentView: document.getElementById("studentView"),
  supervisorView: document.getElementById("supervisorView"),
  role: document.getElementById("role"),
  name: document.getElementById("name"),
  password: document.getElementById("password"),
  logoutBtn: document.getElementById("logoutBtn"),
  uploadForm: document.getElementById("uploadForm"),
  fileName: document.getElementById("fileName"),
  studentTableBody: document.getElementById("studentTableBody"),
  supervisorTableBody: document.getElementById("supervisorTableBody"),
  statusFilter: document.getElementById("statusFilter"),
  toast: document.getElementById("toast"),
  totalCount: document.getElementById("totalCount"),
  pendingCount: document.getElementById("pendingCount"),
  revisionCount: document.getElementById("revisionCount"),
  approvedCount: document.getElementById("approvedCount"),
};

let currentUser = null;
let dissertations = readDissertations();

function readDissertations() {
  const raw = localStorage.getItem(STORAGE_KEY);
  if (!raw) return [];
  try {
    return JSON.parse(raw);
  } catch {
    return [];
  }
}

function writeDissertations() {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(dissertations));
}

function nowDate() {
  return new Date().toLocaleString();
}

function showToast(message) {
  el.toast.textContent = message;
  el.toast.classList.remove("hidden");
  setTimeout(() => {
    el.toast.classList.add("hidden");
  }, 2300);
}

function statusClass(status) {
  const map = {
    Pending: "pending",
    "Under Review": "under-review",
    "Needs Revision": "needs-revision",
    Approved: "approved",
  };
  return map[status] || "pending";
}

function resetViews() {
  el.loginView.classList.add("hidden");
  el.studentView.classList.add("hidden");
  el.supervisorView.classList.add("hidden");
}

function renderStudentTable() {
  const myItems = dissertations
    .filter((d) => d.studentId === currentUser.id)
    .sort((a, b) => b.version - a.version);

  el.studentTableBody.innerHTML = myItems
    .map(
      (item) => `
      <tr>
        <td>${item.fileName}</td>
        <td>V${item.version}</td>
        <td class="status ${statusClass(item.status)}">${item.status}</td>
        <td>${item.comment || "-"}</td>
        <td>${item.uploadDate}</td>
      </tr>
    `
    )
    .join("");

  const summary = myItems.reduce(
    (acc, it) => {
      acc.total += 1;
      if (it.status === "Pending") acc.pending += 1;
      if (it.status === "Needs Revision") acc.revision += 1;
      if (it.status === "Approved") acc.approved += 1;
      return acc;
    },
    { total: 0, pending: 0, revision: 0, approved: 0 }
  );

  el.totalCount.textContent = summary.total;
  el.pendingCount.textContent = summary.pending;
  el.revisionCount.textContent = summary.revision;
  el.approvedCount.textContent = summary.approved;
}

function renderSupervisorTable() {
  const filter = el.statusFilter.value;
  const items = dissertations
    .filter((d) => (filter === "all" ? true : d.status === filter))
    .sort((a, b) => b.id - a.id);

  el.supervisorTableBody.innerHTML = items
    .map((item) => {
      const student = users.students.find((s) => s.id === item.studentId);
      return `
      <tr>
        <td>${student ? student.name : "Unknown"}</td>
        <td>${item.fileName}</td>
        <td>V${item.version}</td>
        <td class="status ${statusClass(item.status)}">${item.status}</td>
        <td>${item.comment || "-"}</td>
        <td>${item.uploadDate}</td>
        <td>
          <div class="actions">
            <textarea id="comment-${item.id}" placeholder="Write feedback...">${item.comment || ""}</textarea>
            <select id="status-${item.id}">
              <option ${item.status === "Under Review" ? "selected" : ""}>Under Review</option>
              <option ${item.status === "Needs Revision" ? "selected" : ""}>Needs Revision</option>
              <option ${item.status === "Approved" ? "selected" : ""}>Approved</option>
            </select>
            <button class="btn btn-primary small" data-save-id="${item.id}">Save</button>
          </div>
        </td>
      </tr>
    `;
    })
    .join("");
}

function showStudentDashboard() {
  resetViews();
  el.studentView.classList.remove("hidden");
  el.logoutBtn.classList.remove("hidden");
  renderStudentTable();
}

function showSupervisorDashboard() {
  resetViews();
  el.supervisorView.classList.remove("hidden");
  el.logoutBtn.classList.remove("hidden");
  renderSupervisorTable();
}

function login(role, name, password) {
  if (role === "student") {
    return users.students.find(
      (s) => s.name === name.toLowerCase().trim() && s.password === password
    );
  }
  if (role === "supervisor") {
    return users.supervisors.find(
      (s) => s.name === name.toLowerCase().trim() && s.password === password
    );
  }
  return null;
}

el.loginForm.addEventListener("submit", (e) => {
  e.preventDefault();
  const role = el.role.value;
  const user = login(role, el.name.value, el.password.value);

  if (!user) {
    showToast("Invalid credentials. Try demo accounts.");
    return;
  }

  currentUser = { ...user, role };
  showToast(`Welcome ${currentUser.name}`);

  if (role === "student") showStudentDashboard();
  else showSupervisorDashboard();
});

el.uploadForm.addEventListener("submit", (e) => {
  e.preventDefault();
  if (!currentUser || currentUser.role !== "student") return;

  const fileName = el.fileName.value.trim();
  if (!fileName) return;

  const myVersions = dissertations
    .filter((d) => d.studentId === currentUser.id)
    .map((d) => d.version);
  const nextVersion = myVersions.length ? Math.max(...myVersions) + 1 : 1;

  dissertations.push({
    id: Date.now(),
    studentId: currentUser.id,
    fileName,
    version: nextVersion,
    status: "Pending",
    comment: "",
    uploadDate: nowDate(),
  });

  writeDissertations();
  renderStudentTable();
  el.fileName.value = "";
  showToast("File uploaded successfully.");
});

el.statusFilter.addEventListener("change", renderSupervisorTable);

el.supervisorTableBody.addEventListener("click", (e) => {
  const btn = e.target.closest("button[data-save-id]");
  if (!btn) return;

  const itemId = Number(btn.dataset.saveId);
  const item = dissertations.find((d) => d.id === itemId);
  if (!item) return;

  const selectedStatus = document.getElementById(`status-${itemId}`)?.value;
  const comment = document.getElementById(`comment-${itemId}`)?.value.trim();
  item.status = selectedStatus || item.status;
  item.comment = comment || "";

  writeDissertations();
  renderSupervisorTable();
  showToast("Submission updated successfully.");
});

el.logoutBtn.addEventListener("click", () => {
  currentUser = null;
  el.logoutBtn.classList.add("hidden");
  el.loginView.classList.remove("hidden");
  el.studentView.classList.add("hidden");
  el.supervisorView.classList.add("hidden");
  el.loginForm.reset();
  showToast("Logged out.");
});
