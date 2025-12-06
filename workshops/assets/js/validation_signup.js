let fname = document.getElementById("fname");
    let lname = document.getElementById("lname");
    let email = document.getElementById("email");
    let pass1 = document.getElementById("password1");
    let pass2 = document.getElementById("password2");
    let passFeedback = document.getElementById("liveFeedback"); ////////////
    let signUpBtn = document.getElementById("signUpBtn");

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
   
    document.getElementById("signUpForm").addEventListener("submit", function (e) {
      //validate names (should be without spaces)
      if (!/^[A-Za-z]+$/.test(fname.value) || !/^[A-Za-z]+$/.test(lname.value)) {
        alert("names cannot contain spaces, numbers or symbols!");
        e.preventDefault();
      }
      //validate email 
      else if (!emailPattern.test(email.value)) {
        alert("please enter a valid email address");
        e.preventDefault();
      }
      else if (pass1.value != pass2.value) {
        alert("Passwords do NOT match!");
        e.preventDefault();
      }
      else {
        //after doing php should check if it's working 
        signUpBtn.textContent = "Signing Up..";
        signUpBtn.disabled = true;
      }
    });
    //live password strength checkup
    pass1.addEventListener("input", function (e) {
      //weak
      if (pass1.value.length < 8) {
        passFeedback.textContent = "🔴 Weak Password";
      }
      //strong 
      else if (/[A-Z]/.test(pass1.value) &&
        /[a-z]/.test(pass1.value) &&
        /[0-9]/.test(pass1.value) &&
        /[^A-Za-z0-9]/.test(pass1.value)) {
        passFeedback.textContent = "🟢 Strong Password";
      }
      //medium
      else {
        passFeedback.textContent = "🟡 Medium Password";
      }
    });

    //show/hide passwords
    let visibilityBtn = document.getElementById("togglePassword");

    visibilityBtn.addEventListener("click", function () {
      if (pass1.type == "password" && pass2.type == "password") {
        pass1.type = "text";
        pass2.type = "text";
        visibilityBtn.textContent = "hide password";
      } else {
        pass1.type = "password";
        pass2.type = "password";
        visibilityBtn.textContent = "show password";
      }
    });


    
