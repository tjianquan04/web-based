
console.log('JavaScript loaded successfully');

function toggleMenu(menuId) {
    const submenu = document.getElementById(menuId);
    if (submenu.style.display === "block") {
        submenu.style.display = "none";
    } else {
        submenu.style.display = "block";
    }
}

function clearForm() {
    document.getElementById('addAdminForm').reset();
}


document.addEventListener('DOMContentLoaded', () => {

    const currentDateElement = document.getElementById('currentDate');
    const options = { year: 'numeric', month: '2-digit', day: '2-digit', weekday: 'long' };
    const currentDate = new Date().toLocaleDateString('en-US', options);
    currentDateElement.textContent = currentDate;

    //Dropdown interaction
    const userProfile = document.getElementById('userProfile');
    userProfile.addEventListener('click', (e) => {
        e.stopPropagation();
        userProfile.classList.toggle('active');
    });

    document.addEventListener('click', () => {
        userProfile.classList.remove('active');
    });
});

function clearPasswordField(input) {
    if (input.value === '********') {
        input.value = '';
    }
}

function restoreDefaultPwIfEmpty(input) {
    if (input.value.trim() === '') {
        input.value = '********';
    }
}


$(() => {

    // Autofocus
    $('form :input:not(button):first').focus();
    $('.err:first').prev().focus();
    $('.err:first').prev().find(':input:first').focus();

    // Delete confirmation message
    $('[delete-confirm]').on('click', e => {
        const addressIds = e.target.dataset.addressIds; 
        const memberIds = e.target.dataset.memberIds; 
    
        let ids = [];
        if (addressIds) {
            ids = ids.concat(addressIds.split(','));
        }
        if (memberIds) {
            ids = ids.concat(memberIds.split(','));
        }
    
        // Generate the confirmation message
        const text = ids.length > 1
            ? `Are you sure you want to delete the following IDs: ${ids.join(', ')}?`
            : `Are you sure you want to delete ${ids[0]}?`;
    
        // Show the confirmation dialog
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

    // Auto uppercase
    $('[data-upper]').on('input', e => {
        const a = e.target.selectionStart;
        const b = e.target.selectionEnd;
        e.target.value = e.target.value.toUpperCase();
        e.target.setSelectionRange(a, b);
    });

    // Photo preview
    $('label.upload input[type=file]').on('change', e => {
        const f = e.target.files[0];
        const img = $(e.target).siblings('img')[0];

        if (!img) return;

        img.dataset.src ??= img.src;

        if (f?.type.startsWith('image/')) {
            img.src = URL.createObjectURL(f);
        }
        else {
            img.src = img.dataset.src;
            e.target.value = '';
        }
    });

});


let slideIndex = 0;
showSlides();

function showSlides() {
    let i;
    let slides = document.getElementsByClassName("mySlides");

    // Hide all slides
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }

    // Increment slide index
    slideIndex++;

    // Reset to the first slide if out of bounds
    if (slideIndex > slides.length) {
        slideIndex = 1;
    }

    // Display the current slide
    slides[slideIndex - 1].style.display = "block";

    // Set a timeout to move to the next slide
    setTimeout(showSlides, 4000); // Change image every 2 seconds
}

