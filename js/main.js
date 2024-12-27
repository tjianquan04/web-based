
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

