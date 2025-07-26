export default {
    init() {
        document.querySelectorAll(".change-form").forEach(element => element.addEventListener("click", this.changeForm));
        document.querySelectorAll("button").forEach(element => element.addEventListener("click", this.submitForm));
    },

    changeForm(e) {
        e.preventDefault();

        const switchTo = e.target.dataset.switch;

        if (switchTo == "login") {
            document.querySelector("#app").classList.remove("register-form");
            document.querySelector("#app").classList.add("login-form");
            return;
        }

        document.querySelector("#app").classList.add("register-form");
        document.querySelector("#app").classList.remove("login-form");
    },

    submitForm(e) {
        e.preventDefault();

        const form = e.target.closest("form");
        const action = form.getAttribute("action");
        const method = form.getAttribute("method");
        const formData = new FormData(form);

        fetch(action, {
            method: method,
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            // Get the redirect URL from the URL query parameter
            const urlParams = new URLSearchParams(window.location.search);
            const redirectUrl = urlParams.get("from") || "/";
            if (data.success) {
                window.location.href = redirectUrl;
            } else {
                alert(data.message);
            }
        })
    },
}