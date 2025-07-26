export default {
    theme: "light",

    colors: {
        "primary": {
            dark: "#2323FF",
            light: "#305CDE",
        },

        "primary-dark": {
            dark: "#1919b3",
            light: "#274AB3",
        },

        "primary-light": {
            dark: "#1655de",
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

        "border-color": {
            dark: "#1F1F1F",
            light: "#E6E6E6",
        },

        "text": {
            dark: "#ebebeb",
            light: "#1c1c1c",
        },

        "text-one": {
            dark: "#b5b5b5",
            light: "#4c4c4d",
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