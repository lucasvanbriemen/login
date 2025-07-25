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
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                // Handle validation errors
                console.error(data.errors);
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
    },
}