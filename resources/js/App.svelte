<script>
    import { onMount } from 'svelte';
    import Input from './components/Input.svelte';

    const themeUrl = "https://components.lucasvanbriemen.nl/api/colors?theme=THEME_NAME";
    let imageSrc = $state('/images/login-dark.jpg');

    function getTheme() {
        const darkModeMediaQuery = window.matchMedia("(prefers-color-scheme: dark)");
        return darkModeMediaQuery.matches ? "dark" : "light";
    }

    async function applyTheme() {
        const theme = getTheme();
        document.documentElement.setAttribute("data-theme", theme);

        const url = themeUrl.replace("THEME_NAME", theme);
        const response = await fetch(url);
        const colors = await response.json();

        colors.forEach(color => {
            document.documentElement.style.setProperty(`--${color.name}`, color.value);
        });

        imageSrc = theme === "dark" ? "/images/login-dark.jpg" : "/images/login-light.jpg";
    }

    async function handleSubmit(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);

        const response = await fetch(form.action, {
            method: form.method,
            body: formData,
        });
        const data = await response.json();

        const urlParams = new URLSearchParams(window.location.search);
        let redirectUrl = urlParams.get("redirect") || "/";

        if (data.success) {
            if (data.token && redirectUrl !== "/") {
                const url = new URL(redirectUrl);
                const currentHost = window.location.hostname;
                const redirectHost = url.hostname;

                if (currentHost !== redirectHost) {
                    url.searchParams.set('auth_token', data.token);
                    redirectUrl = url.toString();
                }
            }
            window.location.href = redirectUrl;
        } else {
            alert(data.message);
        }
    }

    onMount(() => {
        applyTheme();
    });
</script>

<form method="POST" action="/login" class="login-form" onsubmit={handleSubmit}>
    <div class="form-contents">
        <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')} />

        <h1>Welcome Back!</h1>

        <Input type="email" name="email" label="Email" id="login-email" required autofocus />
        <Input type="password" name="password" label="Password" id="login-password" required />
        <button type="submit">Login</button>
    </div>
</form>

<img src={imageSrc} alt="Login Background" class="login-image" />

<style>
    :global(body) {
        background-color: var(--background-color);
        color: var(--text-color);
        font-family: 'Inter', sans-serif;
    }

    :global(body *) {
        transition: all 0.3s ease-in-out;
    }

    :global(#app) {
        width: calc(100vw - 4rem);
        height: calc(100vh - 4rem);
        position: relative;
        transition: all 0.3s ease-in-out;
        margin: 2rem;
        display: flex;
        gap: 2rem;
    }

    form {
        width: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .form-contents {
        background-color: var(--background-color-one);
        padding: 2rem;
        border-radius: 2rem;
        width: 75%;
        box-shadow: 0 0 4rem -3.5rem var(--primary-color-dark);
    }

    .form-contents h1 {
        color: var(--text-color);
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 1.5rem;
    }

    button {
        width: 50%;
        padding: 0.75rem 1rem;
        margin-top: 1rem;
        border-radius: 1rem;
        border: none;
        background-color: var(--primary-color);
        color: var(--text-color);
        font-weight: bold;
        cursor: pointer;
    }

    button:hover {
        background-color: var(--primary-color-dark);
    }

    .login-image {
        position: absolute;
        top: 0;
        left: 50%;
        width: 50%;
        height: 100%;
        object-fit: cover;
        filter: blur(1px);
        z-index: 99;
        border-radius: 1rem 2rem 2rem 1rem;
    }

    @media screen and (max-width: 768px) {
        :global(#app) {
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: calc(100vw - 2rem);
            height: calc(100vh - 2rem);
            margin: 1rem;
        }

        form {
            z-index: 100;
            height: 100%;
            box-shadow: 0 0 4rem -2.5rem var(--primary-color-dark);
        }

        .form-contents {
            width: 90%;
        }

        .login-image {
            width: 100%;
            left: 0;
            top: 0;
            border-radius: 1rem;
        }
    }
</style>
