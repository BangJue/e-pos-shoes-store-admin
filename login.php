<?php
// login.php (root)
include 'config/koneksi.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Epos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0A84FF', // biru
                        dark: '#0a0a0a', // hitam
                        light: '#ffffff' // putih
                    },
                    animation: {
                        fadeIn: 'fadeIn 1s ease-in-out',
                        float: 'float 3s ease-in-out infinite'
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: 0 },
                            '100%': { opacity: 1 }
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(-6px)' },
                            '50%': { transform: 'translateY(6px)' }
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-light min-h-screen flex items-center justify-center p-4">

    <!-- Background dekorasi animasi -->
    <div class="absolute inset-0 overflow-hidden -z-10">
        <div class="w-32 h-32 bg-primary opacity-20 rounded-full absolute top-10 left-10 animate-float"></div>
        <div class="w-40 h-40 bg-dark opacity-10 rounded-full absolute bottom-10 right-10 animate-float" style="animation-delay: 1s"></div>
    </div>

    <div class="w-full max-w-md bg-white/80 backdrop-blur-xl shadow-2xl rounded-2xl p-8 animate-fadeIn border border-gray-200">
        <h1 class="text-3xl font-bold text-dark text-center mb-6">Login ke Epos</h1>

        <form action="actions/login.php" method="POST" class="space-y-4">
            <div>
                <label class="block mb-1 text-dark font-medium">Username</label>
                <input type="text" name="username" required
                    class="w-full px-4 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary focus:outline-none transition">
            </div>

            <div>
                <label class="block mb-1 text-dark font-medium">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary focus:outline-none transition">
            </div>

            <button type="submit"
                class="w-full py-2 bg-primary text-white rounded-xl font-semibold shadow-md hover:bg-dark transition-all">
                Login
            </button>
        </form>
    </div>

</body>
</html>
