console.log('JavaScript loaded successfully');

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
window.onclick = function (event) {
    if (event.target === document.getElementById('addAdminModal')) {
        closeModal();
    }
};

function clearForm() {
    document.getElementById('addAdminForm').reset();
}

$('label.upload input[type=file]').on('change', e => {
    const file = e.target.files[0]; // Get the selected file
    const img = $(e.target).siblings('img')[0]; // Reference the <img> tag

    if (!img) return;

    img.dataset.src ??= img.src; // Backup the original image src if not already backed up

    if (file?.type.startsWith('image/')) {
        img.src = URL.createObjectURL(file); // Display the new image preview
    } else {
        img.src = img.dataset.src; // Revert to the original image if invalid file
        e.target.value = ''; // Clear the file input
    }
});

$(() => {

    // Autofocus
    $('form :input:not(button):first').focus();
    $('.err:first').prev().focus();
    $('.err:first').prev().find(':input:first').focus();

    // Delete confirmation message
    $('[delete-confirm]').on('click', e => {
        // Get the member_id from the data-confirm attribute
        const memberId = e.target.dataset.confirm;
        // Customize the confirmation message
        const text = `Are you sure you want to delete member ${memberId}?`;

        if (!confirm(text)) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    });

    // Initiate GET request
    $('[data-get]').on('click', e => {
        e.preventDefault();
        const url = e.target.dataset.get;
        location = url || location;
    });

    // Initiate POST request
    $('[data-post]').on('click', e => {
        e.preventDefault();
        const url = e.target.dataset.post;
        const f = $('<form>').appendTo(document.body)[0];
        f.method = 'POST';
        f.action = url || location;
        f.submit();
    });

    // Reset form
    $('[type=reset]').on('click', e => {
        e.preventDefault();
        location = location;
    });

});