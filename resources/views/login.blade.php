<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
</head>
<body>
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
            // fetch('/api/login', {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //         'X-CSRF-TOKEN': "{{ csrf_token() }}"
            //     },
            //     body: JSON.stringify(credentials)
            // })
            // .then(response => {
            //     console.log('Login successful:', response);

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
            // })
            // .catch(error => {
            //     console.error('Error logging in:', error);
            // });

            // await axios.post('/login', credentials);

            // let response = await axios.get('/api/user');

            // console.log(response.data);
        }

        getUserDetails();

    </script>
</body>
