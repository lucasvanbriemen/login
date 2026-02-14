export default {
    init() {
        document.querySelectorAll(".change-form").forEach(element => element.addEventListener("click", this.changeForm));
        document.querySelectorAll("button").forEach(element => element.addEventListener("click", this.submitForm));

        // Set the side-image src based on the theme
        const theme = window.theme.getTheme();
        this.setImageSrcBasedOnTheme(theme);
    },

    setImageSrcBasedOnTheme(theme) {
        const image = document.querySelector(".login-image");
        const src = theme == "dark" ? "/images/login-dark.jpg" : "/images/login-light.jpg";
        image.setAttribute("src", src);
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
            let redirectUrl = urlParams.get("redirect") || "/";

            if (data.success) {
                // If redirecting to a different domain and we have a token, append it to the URL
                if (data.token && redirectUrl !== "/") {
                    const url = new URL(redirectUrl);
                    const currentHost = window.location.hostname;
                    const redirectHost = url.hostname;

                    // Only append token if redirecting to a different domain
                    if (currentHost !== redirectHost) {
                        url.searchParams.set('auth_token', data.token);
                        redirectUrl = url.toString();
                    }
                }
                window.location.href = redirectUrl;
            } else {
                alert(data.message);
            }
        })
    },
}