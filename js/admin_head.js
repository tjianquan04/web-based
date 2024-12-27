
console.log('JavaScript loaded successfully');

$(() => {

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

function toggleMenu(menuId) {
    const submenu = document.getElementById(menuId);
    if (submenu.style.display === "block") {
        submenu.style.display = "none";
    } else {
        submenu.style.display = "block";
    }
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



