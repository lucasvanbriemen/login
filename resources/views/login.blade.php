<x-login-layout>
    <x-slot name="title">Login</x-slot>
    <x-slot name="bodyClass">login-page</x-slot>

    <form method="POST" action="/login" class="login-form">
        <div class="form-contents">
            @csrf

            <h1>Welcome Back!</h1>

            <x-input type="email" name="email" label="Email" id="login-email" required autofocus />
            <x-input type="password" name="password" label="Password" id="login-password" required />
            <button type="submit">Login</button>
        </div>
    </form>

    <img src="{{ asset('images/login-dark.jpg') }}" alt="Login Background" class="login-image">

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            login.init();
        });
    </script>
</x-login-layout>