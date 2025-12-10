<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Taller Mecánico</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .credentials {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }

        .credentials h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .credentials p {
            color: #666;
            font-size: 13px;
            margin: 5px 0;
        }

        .credentials code {
            background: white;
            padding: 2px 6px;
            border-radius: 4px;
            color: #667eea;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .loader {
            display: none;
            text-align: center;
            margin-top: 10px;
        }

        .loader.active {
            display: block;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .response {
            margin-top: 20px;
            padding: 15px;
            border-radius: 10px;
            display: none;
        }

        .response.success {
            background: #d4edda;
            border: 2px solid #c3e6cb;
            color: #155724;
        }

        .response.error {
            background: #f8d7da;
            border: 2px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔐 Iniciar Sesión</h1>
        <p class="subtitle">Sistema de Taller Mecánico</p>

        <div class="credentials">
            <h3>👤 Credenciales de Prueba:</h3>
            <p><strong>Admin:</strong> <code>admin@taller.com</code> / <code>admin123</code></p>
            <p><strong>Demo:</strong> <code>demo@taller.com</code> / <code>demo123</code></p>
        </div>

        <form id="loginForm">
            @csrf
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="admin@taller.com" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" value="admin123" required>
            </div>

            <button type="submit">Iniciar Sesión</button>
        </form>

        <div class="loader" id="loader">
            <div class="spinner"></div>
        </div>

        <div class="response" id="response"></div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const responseDiv = document.getElementById('response');
        const loader = document.getElementById('loader');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            loader.classList.add('active');
            responseDiv.style.display = 'none';

            try {
                const response = await fetch('{{ url('/api/login') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        email,
                        password
                    })
                });

                const data = await response.json();
                loader.classList.remove('active');

                if (response.ok) {
                    responseDiv.className = 'response success';
                    responseDiv.innerHTML = `
                        <h3>✅ Login Exitoso!</h3>
                        <p>Redirigiendo al dashboard...</p>
                    `;
                    responseDiv.style.display = 'block';

                    localStorage.setItem('auth_token', data.access_token);
                    localStorage.setItem('user', JSON.stringify(data.user));

                    setTimeout(() => {
                        window.location.href = '{{ route('dashboard') }}';
                    }, 1000);

                } else {
                    responseDiv.className = 'response error';
                    responseDiv.innerHTML = `
                        <h3>❌ Error de Login</h3>
                        <p>${data.message || 'Credenciales incorrectas'}</p>
                    `;
                    responseDiv.style.display = 'block';
                }

            } catch (error) {
                loader.classList.remove('active');
                responseDiv.className = 'response error';
                responseDiv.innerHTML = `
                    <h3>❌ Error de Conexión</h3>
                    <p>${error.message}</p>
                `;
                responseDiv.style.display = 'block';
            }
        });
    </script>
</body>

</html>
