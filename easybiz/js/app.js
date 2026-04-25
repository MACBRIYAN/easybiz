// ==========================
// Sidebar controls
// ==========================

function openSidebar() {
    const sidebar = document.getElementById("sidebar");

    if (sidebar) {
        sidebar.classList.add("open");
    } else {
        console.error("Sidebar element not found");
    }
}

function closeSidebar() {
    const sidebar = document.getElementById("sidebar");

    if (sidebar) {
        sidebar.classList.remove("open");
    } else {
        console.error("Sidebar element not found");
    }
}

// ==========================
// Close sidebar when clicking outside
// ==========================

document.addEventListener("click", function (event) {
    const sidebar = document.getElementById("sidebar");
    const button = document.querySelector("[onclick='openSidebar()']");

    if (!sidebar || !button) return;

    const isClickInsideSidebar = sidebar.contains(event.target);
    const isButton = button.contains(event.target);

    if (!isClickInsideSidebar && !isButton) {
        sidebar.classList.remove("open"); // FIXED HERE
    }
});