export default {
    theme: "auto",

    colors: {
        "primary": {
            dark: "#2323FF",
            light: "#305CDE",
        },

        "primary-dark": {
            dark: "#3E51C9",
            light: "#274AB3",
        },

        "primary-light": {
            dark: "#99A7FF",
            light: "#59B5F7",
        },

        "background": {
            dark: "#121212",
            light: "#FFFFFF",
        },

        "background-one": {
            dark: "#1A1A1A",
            light: "#F2F2F2",
        },

        "background-two": {
            dark: "#1F1F1F",
            light: "#E6E6E6",
        },

        "text": {
            dark: "#E0E0E0",
            light: "#000000",
        },

        "text-one": {
            dark: "#E0E0E0",
            light: "#000000",
        },
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
            document.documentElement.style.setProperty(`--${key}`, value[this.getTheme()]);
        }
    }
}