<?php // THE ULTIMATE LOGIN PAGE ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login • La Rose Noire</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/jpg" href="assets/favicon.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles/style.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#f472b6',
                        'primary-dark': '#ec4899',
                        secondary: '#a78bfa',
                        success: '#34d399',
                        warning: '#fbbf24',
                        danger: '#f87171'
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-glow': 'pulse-glow 2s ease-in-out infinite',
                        'bounce-in': 'bounce-in 0.6s ease-out'
                    }
                }
            }
        }
    </script>
    <style>
        /* Mobile Portrait Tweaks */
        @media (max-width: 640px) {
            body {
                height: 100vh !important;
                overflow: hidden !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 0 !important;
            }

            .glass-panel {
                border-radius: 20px !important;
                width: 90% !important;
                max-height: 100vh !important;
                margin: 0 !important;
                margin-top: 15% !important;
                display: flex;
                flex-direction: column;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
                position: relative;
                z-index: 50;
            }

            .left-panel {
                padding: 1rem !important;
                flex: 0 0 auto;
                border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            }

            .right-panel {
                padding: 1.5rem 1rem !important;
                flex: 1;
                display: flex;
                flex-direction: column;
                justify-content: center;
                overflow-y: auto;
            }

            .brand-title {
                font-size: 1.25rem !important;
            }

            .brand-subtitle {
                font-size: 0.7rem !important;
            }

            .logo-container {
                width: 2.5rem !important;
                height: 2.5rem !important;
                margin-bottom: 0.5rem !important;
            }

            .logo-icon {
                font-size: 1.25rem !important;
            }

            h2 {
                font-size: 1.25rem !important;
                margin-bottom: 0.25rem !important;
            }

            input.form-input {
                padding: 0.75rem 2.5rem !important;
                font-size: 0.9rem !important;
            }

            .space-y-6>*+* {
                margin-top: 0.75rem !important;
            }

            .space-y-8>*+* {
                margin-top: 0.5rem !important;
            }

            .space-y-3>*+* {
                margin-top: 0.25rem !important;
            }

            .absolute.left-4,
            .absolute.right-4 {
                width: 1rem !important;
                height: 1rem !important;
            }

            .absolute.left-4 {
                left: 1rem !important;
            }

            .absolute.right-4 {
                right: 1rem !important;
            }

            p.text-gray-500 {
                font-size: 0.8rem !important;
                margin-bottom: 0.5rem !important;
            }
        }

        /* Mobile Landscape Tweaks */
        @media (max-height: 500px) and (orientation: landscape) {
            body {
                height: 100vh !important;
                overflow: hidden !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 0 !important;
            }

            .glass-panel {
                flex-direction: row !important;
                height: auto !important;
                max-height: 85vh !important;
                width: 80% !important;
                height: 60% !important;
                max-width: 700px !important;
                border-radius: 20px !important;
                margin: 0 !important;
                margin-top: 45px !important;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
            }

            .left-panel {
                width: 40% !important;
                padding: 1rem !important;
                justify-content: center;
                border-right: 1px solid rgba(255, 255, 255, 0.2);
            }

            .right-panel {
                width: 60% !important;
                padding: 1rem 2rem !important;
                overflow-y: auto;
                justify-content: center;
            }

            .brand-title {
                font-size: 1.5rem !important;
            }

            .logo-container {
                width: 3rem !important;
                height: 3rem !important;
            }

            .logo-icon {
                font-size: 1.5rem !important;
            }

            h2 {
                font-size: 1.5rem !important;
            }

            .form-input {
                padding-top: 0.5rem !important;
                padding-bottom: 0.5rem !important;
            }

            .space-y-6>*+* {
                margin-top: 0.5rem !important;
            }

            .space-y-8>*+* {
                margin-top: 0.5rem !important;
            }
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Animated Background Blobs -->
    <div class="fixed inset-0 -z-10">
        <div
            class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-gradient-to-r from-pink-300 to-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-float">
        </div>
        <div class="absolute top-[-10%] right-[-10%] w-96 h-96 bg-gradient-to-r from-purple-300 to-blue-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-float"
            style="animation-delay: 2s;"></div>
        <div class="absolute bottom-[-20%] left-[20%] w-96 h-96 bg-gradient-to-r from-yellow-200 to-pink-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-float"
            style="animation-delay: 4s;"></div>
        <div class="absolute bottom-[-10%] right-[10%] w-72 h-72 bg-gradient-to-r from-green-200 to-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float"
            style="animation-delay: 1s;"></div>
    </div>

    <!-- Main Login Container -->
    <div
        class="w-full max-w-5xl glass-panel rounded-3xl overflow-hidden flex flex-col md:flex-row shadow-2xl relative z-10 animate-bounce-in">

        <!-- Left Panel - Brand -->
        <div
            class="left-panel w-full md:w-5/12 bg-gradient-to-br from-pink-400 via-pink-300 to-purple-300 p-12 text-white flex flex-col justify-center items-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent"></div>
            <div class="absolute inset-0 backdrop-blur-[1px]"></div>

            <div class="relative z-10 text-center space-y-6">
                <!-- Logo/Icon -->
                <div
                    class="logo-container w-24 h-24 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center mx-auto animate-pulse-glow shadow-2xl">
                    <i class="logo-icon fas fa-spa text-5xl text-white drop-shadow-lg"></i>
                </div>

                <!-- Brand Name -->
                <div class="space-y-2">
                    <h1
                        class="brand-title text-5xl font-black tracking-wide bg-gradient-to-r from-white to-pink-100 bg-clip-text text-transparent drop-shadow-sm">
                        La Rose Noire
                    </h1>
                    <p class="brand-subtitle text-pink-50 text-xl font-medium leading-relaxed">
                        Facilities Management Department
                    </p>
                </div>

                <!-- Decorative Elements -->
                <div class="flex items-center justify-center gap-2 pt-4">
                    <div class="w-12 h-1 bg-white/60 rounded-full"></div>
                    <div class="w-8 h-1 bg-white/40 rounded-full"></div>
                    <div class="w-4 h-1 bg-white/20 rounded-full"></div>
                </div>
            </div>

            <!-- Floating Decorative Elements -->
            <div class="absolute top-8 right-8 w-16 h-16 bg-white/10 rounded-full flex items-center justify-center animate-float"
                style="animation-delay: 1s;">
                <i class="fas fa-star text-yellow-200 text-lg"></i>
            </div>
            <div class="absolute bottom-12 left-8 w-12 h-12 bg-white/10 rounded-full flex items-center justify-center animate-float"
                style="animation-delay: 3s;">
                <i class="fas fa-heart text-pink-200 text-sm"></i>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="right-panel w-full md:w-7/12 p-12 bg-gradient-to-br from-white/80 to-white/60 backdrop-blur-xl">
            <div class="space-y-8">
                <!-- Header -->
                <div class="text-center space-y-3">
                    <h2
                        class="text-4xl font-bold text-gray-800 bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                        Welcome Back
                    </h2>
                    <p class="text-gray-500 text-lg leading-relaxed">
                        Please enter your details to access your dashboard
                    </p>
                    <div class="w-16 h-1 bg-gradient-to-r from-pink-400 to-purple-400 rounded-full mx-auto"></div>
                </div>

                <!-- Login Form -->
                <form action="./auth/authenticate.php" method="POST" class="space-y-6">
                    <!-- Username Field -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <i class="fas fa-user text-pink-400"></i>
                            Username
                        </label>
                        <div class="relative">
                            <input type="text" name="username" required
                                class="form-input pl-12 pr-4 py-4 text-gray-700 placeholder-gray-400"
                                placeholder="Enter your username">
                            <div
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-6 h-6 bg-gradient-to-r from-pink-400 to-purple-400 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <i class="fas fa-lock text-pink-400"></i>
                            Password
                        </label>
                        <div class="relative">
                            <input type="password" name="password" required
                                class="form-input pl-12 pr-12 py-4 text-gray-700 placeholder-gray-400"
                                placeholder="••••••••" id="password">
                            <div
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-6 h-6 bg-gradient-to-r from-pink-400 to-purple-400 rounded-full flex items-center justify-center">
                                <i class="fas fa-lock text-white text-xs"></i>
                            </div>
                            <button type="button" onclick="togglePassword()"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-pink-500 transition-colors p-1">
                                <i class="fas fa-eye text-sm" id="password-toggle"></i>
                            </button>
                        </div>

                        <!-- Forgot Password Link -->
                        <div class="flex justify-end">
                            <button type="button" id="forgot-link"
                                class="text-sm font-semibold text-pink-500 hover:text-pink-600 transition-all duration-300 flex items-center gap-2 group">
                                <i class="fas fa-key text-xs group-hover:rotate-12 transition-transform"></i>
                                Forgot Password?
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-center">
                        <button type="submit" class="btn-primary w-auto px-8 py-2.5 text-base font-semibold group">
                            <span>Sign In</span>
                            <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                        </button>
                    </div>
                </form>

                <!-- Footer -->
                <div class="pt-8 border-t border-gray-200/50 text-center">
                    <p class="text-gray-500 text-sm flex items-center justify-center gap-2">
                        <i class="fas fa-shield-alt text-green-500"></i>
                        Secure access to your workspace
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgot-modal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 bg-gradient-to-r from-pink-100 to-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-key text-2xl text-pink-500"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Reset Password</h3>
                        <p class="text-sm text-gray-600">Password recovery</p>
                    </div>
                </div>
                <button class="modal-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body text-center">
                <p class="text-gray-600 mb-6 leading-relaxed">
                    For security reasons, please contact the IT department directly to reset your credentials.
                </p>
                <button class="btn-secondary" onclick="closeModal()">
                    <i class="fas fa-check mr-2"></i>Understood
                </button>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="error-modal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 bg-gradient-to-r from-red-100 to-pink-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-2xl text-red-500"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Authentication Error</h3>
                        <p class="text-sm text-gray-600">Login failed</p>
                    </div>
                </div>
                <button class="modal-close" onclick="closeErrorModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body text-center">
                <p class="text-gray-600 mb-6 leading-relaxed">
                    Mismatch between username and password
                </p>
                <button class="btn-primary" onclick="closeErrorModal()">
                    <i class="fas fa-check mr-2"></i>OK
                </button>
            </div>
        </div>
    </div>

    <script>
        // Password Toggle
        function togglePassword() {
            const password = document.getElementById('password');
            const toggle = document.getElementById('password-toggle');
            if (password.type === 'password') {
                password.type = 'text';
                toggle.className = 'fas fa-eye-slash text-sm';
            } else {
                password.type = 'password';
                toggle.className = 'fas fa-eye text-sm';
            }
        }

        // Modal Functions
        function openModal() {
            document.getElementById('forgot-modal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('forgot-modal').classList.remove('active');
        }

        function openErrorModal() {
            document.getElementById('error-modal').classList.add('active');
        }

        function closeErrorModal() {
            document.getElementById('error-modal').classList.remove('active');
            // Remove error parameter from URL without reloading
            const url = new URL(window.location);
            url.searchParams.delete('error');
            window.history.replaceState({}, '', url);
        }

        // Event Listeners
        document.getElementById('forgot-link').addEventListener('click', openModal);

        // Keyboard Navigation
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeModal();
                closeErrorModal();
            }
        });

        // Close modal when clicking overlay
        document.getElementById('forgot-modal').addEventListener('click', function (e) {
            if (e.target === this) closeModal();
        });

        document.getElementById('error-modal').addEventListener('click', function (e) {
            if (e.target === this) closeErrorModal();
        });

        // Check for error parameter in URL and show modal
        window.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('error') === 'invalid') {
                openErrorModal();
            } else if (urlParams.get('error') === 'unauthorized') {
                // Change modal text
                document.querySelector('#error-modal .modal-body p').textContent = "You are not allowed to make disposal forms";
                // Optionally change title if needed, but 'Authentication Error' is fine
                openErrorModal();
            }
        });
    </script>
</body>

</html>