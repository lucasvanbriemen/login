<x-login-layout>
    <x-slot name="title">Login</x-slot>
    <x-slot name="bodyClass">login-page</x-slot>

    <form method="POST" action="/login">
        @csrf
        <div>
            <label for="email">Email:</label>
            <input id="email" type="email" name="email" required autofocus>
        </div>
        <div>
            <label for="password">Password:</label>
            <input id="password" type="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>

    <hr>
    <hr>
    <hr>

    <form method="POST" action="/register">
        @csrf
        <div>
            <label for="name">Name:</label>
            <input id="name" type="text" name="name" required>
        </div>

        <div>
            <label for="email">Email:</label>
            <input id="email" type="email" name="email" required>
        </div>

        <div>
            <label for="password">Password:</label>
            <input id="password" type="password" name="password" required>
        </div>

        <button type="submit">Register</button>
    </form>

    <hr>
    <hr>
    <hr>

    <form method="POST" action="/logout">
        @csrf
        <button type="submit">Logout</button>
    </form>

    <script>
        const credentials = {
            email: 'vanbriemenlucas@gmail.com',
            password: '13November.2006'
        };

        function getUserDetails() {
                fetch('/api/user', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    }
                })
                .then(userResponse => userResponse.json())
                .then(userData => {
                    console.log('User details:', userData);
                })
                .catch(error => {
                    console.error('Error fetching user details:', error);
                });
        }

        getUserDetails();

    </script>
</x-login-layout>