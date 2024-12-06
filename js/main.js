function toggleMenu(menuId) {
    const submenu = document.getElementById(menuId);
    if (submenu.style.display === "block") {
        submenu.style.display = "none";
    } else {
        submenu.style.display = "block";
    }
}

function openModal() {
    document.getElementById('addAdminModal').style.display = 'block';
}

// Close the modal
function closeModal() {
    document.getElementById('addAdminModal').style.display = 'none';
}

// Close the modal when clicking anywhere outside of it
window.onclick = function(event) {
    if (event.target === document.getElementById('addAdminModal')) {
        closeModal();
    }
};

function clearForm() {
    document.getElementById('addAdminForm').reset();
}