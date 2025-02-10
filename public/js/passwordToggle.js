document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".input-group").forEach(group => {
        let passwordInput = group.querySelector("input[type='password']");
        let toggleButton = group.querySelector(".btn-outline-secondary");
        let icon = toggleButton ? toggleButton.querySelector("i") : null;

        if (passwordInput && toggleButton && icon) {
            toggleButton.addEventListener("click", function () {
                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                    icon.classList.replace("bi-eye", "bi-eye-slash");
                } else {
                    passwordInput.type = "password";
                    icon.classList.replace("bi-eye-slash", "bi-eye");
                }
            });
        }
    });
});