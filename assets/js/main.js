document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburger-menu');
    const body = document.body;

    if (hamburger) {
        hamburger.addEventListener('click', function() {
            body.classList.toggle('nav-open');
        });
    }
});