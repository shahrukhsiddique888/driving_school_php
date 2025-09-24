'use strict';

/**
 * navbar toggle
 */

const overlay = document.querySelector("[data-overlay]");
const navbar = document.querySelector("[data-navbar]");
const navToggleBtn = document.querySelector("[data-nav-toggle-btn]");
const navbarLinks = document.querySelectorAll("[data-nav-link]");

const navToggleFunc = function () {
  navToggleBtn.classList.toggle("active");
  navbar.classList.toggle("active");
  overlay.classList.toggle("active");
}

navToggleBtn.addEventListener("click", navToggleFunc);
overlay.addEventListener("click", navToggleFunc);

for (let i = 0; i < navbarLinks.length; i++) {
  navbarLinks[i].addEventListener("click", navToggleFunc);
}



/**
 * header active on scroll
 */

const header = document.querySelector("[data-header]");

window.addEventListener("scroll", function () {
  window.scrollY >= 10 ? header.classList.add("active")
    : header.classList.remove("active");
});

document.getElementById('partnerForm').addEventListener('submit', function(event) {
    let isValid = true;

    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const company = document.getElementById('company').value.trim();
    const message = document.getElementById('message').value.trim();

    if (name === "") {
        isValid = false;
        document.getElementById('nameError').style.display = 'block';
    } else {
        document.getElementById('nameError').style.display = 'none';
    }

    if (email === "" || !validateEmail(email)) {
        isValid = false;
        document.getElementById('emailError').style.display = 'block';
    } else {
        document.getElementById('emailError').style.display = 'none';
    }

    if (phone === "" || !validatePhone(phone)) {
        isValid = false;
        document.getElementById('phoneError').style.display = 'block';
    } else {
        document.getElementById('phoneError').style.display = 'none';
    }

    if (company === "") {
        isValid = false;
        document.getElementById('companyError').style.display = 'block';
    } else {
        document.getElementById('companyError').style.display = 'none';
    }

    if (message === "") {
        isValid = false;
        document.getElementById('messageError').style.display = 'block';
    } else {
        document.getElementById('messageError').style.display = 'none';
    }

    if (!isValid) {
        event.preventDefault();
    }
});

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(String(email).toLowerCase());
}

function validatePhone(phone) {
    const re = /^[0-9]{10}$/;
    return re.test(String(phone));
}


document.getElementById('reservationForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent form submission

    // Validation checks
    const pickupLocation = document.getElementById('pickupLocation').value.trim();
    const returnLocation = document.getElementById('returnLocation').value.trim();
    const pickupDate = document.getElementById('pickupDate').value.trim();
    const returnDate = document.getElementById('returnDate').value.trim();
    const carType = document.getElementById('carType').value;

    // Check if any field is empty
    if (pickupLocation === '' || returnLocation === '' || pickupDate === '' || returnDate === '' || carType === '') {
        alert('All fields are required');
        return;
    }

    // Check if return date is after pickup date
    if (new Date(returnDate) <= new Date(pickupDate)) {
        alert('Return date must be after pickup date');
        return;
    }

    // If all validation passed, submit the form
    this.submit();
});
