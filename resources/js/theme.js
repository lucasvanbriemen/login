export default {
    theme: "auto",

    colors: {
    },

    getSystemTheme() {
        return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
    },

    getTheme() {
        if (this.theme === "auto") {
            return this.getSystemTheme();
        }

        return this.theme;
    },

    setColors() {
        for (const [key, value] of Object.entries(this.colors)) {
            document.documentElement.style.setProperty(`--${key}`, value);
        }
    }
}