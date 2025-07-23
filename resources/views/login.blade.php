<x-login-layout>
    <x-slot name="title">Login</x-slot>
    <x-slot name="bodyClass">login-page</x-slot>

    <form method="POST" action="/login" class="login-form">
        <div class="form-contents">
            @csrf

            <h1>Welcome Back!<span class='change-form' data-switch="register">Don't have an account?</span></h1>

            <x-input type="email" name="email" label="Email" required autofocus />
            <x-input type="password" name="password" label="Password" required />
            <button type="submit">Login</button>
        </div>
    </form>

    <form method="POST" action="/register" class="register-form">
        <div class="form-contents">
            @csrf

            <h1>Create an Account<span class='change-form' data-switch="login">Already have an account?</span></h1>

            <x-input type="text" name="name" label="Name" required />
            <x-input type="email" name="email" label="Email" required />
            <x-input type="password" name="password" label="Password" required />

            <button type="submit">Register</button>
        </div>
    </form>

    <img src="{{ asset('images/login-dark.jpg') }}" alt="Login Background" class="login-image">

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            login.init();
        });
    </script>

    <script>
   fetch('http://localhost:8000/api/user', {
    method: 'GET',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    }
})
.then(response => response.json())
.then(data => console.log('Success:', data))
.catch(error => console.error('Error:', error));

</script>
</x-login-layout>