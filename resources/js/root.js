const modules = import.meta.glob('./**/*.js', { eager: true });

const exportsMap = {
    theme: './theme.js',
    login: './login.js',
    api: './api.js',
};

for (const [key, path] of Object.entries(exportsMap)) {
    window[key] = modules[path].default;
}

theme.applyTheme();