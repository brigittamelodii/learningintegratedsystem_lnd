<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LIS</title>
    @vite('resources/css/app.css')
</head>

<body class="m-0 p-0">

    <div class="relative h-screen w-screen">
        <!-- Fullscreen Background Image -->
        <img src="{{ asset('img/bg_page.jpg') }}" alt="background"
            class="absolute top-0 left-0 w-full h-full object-cover z-0" />

        <!-- Login Form -->
        <div class="relative z-10 flex justify-center items-center h-full">
            <div class="bg-black p-8 rounded-lg shadow-lg w-full max-w-md bg-opacity-70">
                <h2 class="text-2xl font-bold mb-6 text-center" style="color: rgb(255, 255, 255)">Learning Integrated
                    Systems</h2>

                <!-- Role Selection Tabs -->
                <div class="mb-6">
                    <div class="flex rounded-lg overflow-hidden border border-gray-600">
                        <button type="button" id="adminTab" onclick="switchRole('admin')"
                            class="flex-1 py-2 px-4 text-sm font-medium text-center transition-all duration-200 bg-blue-600 text-white">
                            Admin Login
                        </button>
                        <button type="button" id="userTab" onclick="switchRole('user')"
                            class="flex-1 py-2 px-4 text-sm font-medium text-center transition-all duration-200 bg-gray-600 text-gray-300 hover:bg-gray-500">
                            User Login
                        </button>
                    </div>
                </div>

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <!-- Hidden field to track selected role type -->
                    <input type="hidden" name="login_as" id="loginAs" value="admin">

                    <div class="mb-4">
                        <label for="email" class="block mb-1 font-medium text-white">Email</label>
                        <input id="email" type="email" name="email" required autofocus
                            class="w-full border border-gray-300 rounded px-3 py-2" />
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block mb-1 font-medium text-white">Password</label>
                        <input id="password" type="password" name="password" required
                            class="w-full border border-gray-300 rounded px-3 py-2" />
                    </div>

                    <!-- Admin Role Section (visible by default) -->
                    <div class="mb-4" id="adminRoleSection">
                        <div class="text-white text-sm mb-2">
                            <i class="fas fa-user-shield mr-2"></i>
                            Your admin role will be automatically detected
                        </div>
                    </div>

                    <!-- User Role Section (hidden by default) -->
                    <div class="mb-4 hidden" id="userRoleSection">
                        <div class="text-white text-sm mb-2">
                            <i class="fas fa-user mr-2"></i>
                            Logging in as Participant
                        </div>
                    </div>

                    <div class="mb-4 flex items-center">
                        <input type="checkbox" name="remember" class="mr-2" />
                        <label class="text-white">Remember me</label>
                    </div>

                    <div class="mb-4 text-right">
                        <a href="{{ route('password.request') }}"
                            class="text-blue-400 text-sm hover:text-blue-300">Forgot your password?</a>
                    </div>

                    <button type="submit"
                        class="w-full text-white py-2 rounded font-medium transition-all duration-200 hover:opacity-90"
                        style="background-color:rgb(0, 98, 255)" id="loginButton">
                        LOG IN AS ADMIN
                    </button>
                </form>

                <!-- Role Description -->
                <div class="mt-4 text-xs text-gray-400 text-center" id="roleDescription">
                    System will detect your administrative privileges automatically
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchRole(roleType) {
            const adminTab = document.getElementById('adminTab');
            const userTab = document.getElementById('userTab');
            const adminRoleSection = document.getElementById('adminRoleSection');
            const userRoleSection = document.getElementById('userRoleSection');
            const loginAs = document.getElementById('loginAs');
            const loginButton = document.getElementById('loginButton');
            const roleDescription = document.getElementById('roleDescription');

            if (roleType === 'admin') {
                // Switch to admin tab
                adminTab.classList.remove('bg-gray-600', 'text-gray-300');
                adminTab.classList.add('bg-blue-600', 'text-white');
                userTab.classList.remove('bg-blue-600', 'text-white');
                userTab.classList.add('bg-gray-600', 'text-gray-300', 'hover:bg-gray-500');

                // Show admin role section, hide user section
                adminRoleSection.classList.remove('hidden');
                userRoleSection.classList.add('hidden');

                // Update hidden field and button
                loginAs.value = 'admin';
                loginButton.textContent = 'LOG IN AS ADMIN';
                roleDescription.textContent = 'System will detect your administrative privileges automatically';
            } else {
                // Switch to user tab
                userTab.classList.remove('bg-gray-600', 'text-gray-300', 'hover:bg-gray-500');
                userTab.classList.add('bg-blue-600', 'text-white');
                adminTab.classList.remove('bg-blue-600', 'text-white');
                adminTab.classList.add('bg-gray-600', 'text-gray-300');

                // Show user role section, hide admin section
                userRoleSection.classList.remove('hidden');
                adminRoleSection.classList.add('hidden');

                // Update hidden field and button
                loginAs.value = 'user';
                loginButton.textContent = 'LOG IN AS PARTICIPANT';
                roleDescription.textContent = 'Participant access for learning activities';
            }
        }

        // Add some visual feedback for form submission
        document.getElementById('loginForm').addEventListener('submit', function() {
            const button = document.getElementById('loginButton');
            button.disabled = true;
            button.textContent = 'Logging in...';
            button.style.opacity = '0.7';
        });
    </script>

</body>

</html>
