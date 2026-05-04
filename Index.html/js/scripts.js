// scripts.js - Client-side JavaScript for Greenlife Wellness project

// This file is currently empty but can be used for:
// - Client-side form validation (e.g., checking password strength, matching passwords)
// - Dynamic UI updates
// - AJAX requests
// - Any other interactive features

document.addEventListener('DOMContentLoaded', function() {
    // Code to run when the DOM is fully loaded.
    // Example: Add a simple form validation
    const registrationForm = document.querySelector('form');
    if (registrationForm) {
        registrationForm.addEventListener('submit', function(event) {
            // Example: Client-side password length check
            const passwordField = document.querySelector('input[name="password"]');
            if (passwordField && passwordField.value.length < 6) {
                alert('Password must be at least 6 characters long.');
                event.preventDefault(); // Prevent form submission
            }

            // You can add more client-side validation here
            // to provide immediate feedback to the user without a server trip.
        });
    }

    // Example: Smooth scroll for navigation links (if you implement a single-page layout)
    document.querySelectorAll('a.nav-link[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

});
