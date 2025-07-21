export default {
    init() {
        document.querySelectorAll(".change-form").forEach(element => element.addEventListener("click", this.changeForm));
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
}