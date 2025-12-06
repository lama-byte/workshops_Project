document.addEventListener("DOMContentLoaded", function () {

    const email = document.getElementById("email");
    const loginForm = document.getElementById("loginForm");
    const loginBtn = document.getElementById("loginBtn");

    loginForm.addEventListener("submit", function (e) {

        // Email format validation
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailPattern.test(email.value.trim())) {
            alert("Please enter a valid email address");
            e.preventDefault();
            return;
        }
        // NO password validation here (for security)

        loginBtn.textContent = "Logging in...";
        loginBtn.disabled = true;
    });
});
   
