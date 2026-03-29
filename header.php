<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Portal</title>

    <style>
        /* 1. THEME VARIABLES */
        :root {
            --bg-color: #f4f7f6;
            --text-color: #333;
            --header-bg: #303f53;
            /* Dark blue-grey from your image */
            --header-text: #ffffff;
            --card-bg: #ffffff;
            --border-color: #ddd;
        }

        /* Dark Mode Overrides */
        body.dark-mode {
            --bg-color: #1a252f;
            --text-color: #f4f7f6;
            --card-bg: #2c3e50;
            --border-color: #444;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: background-color 0.3s, color 0.3s;
        }

        /* Add this inside the <style> tag of header.php under body.dark-mode */
        body.dark-mode h3 {
            color: #bdc3c7;
            /* A light silver color that pops against dark cards */
        }

        /* 2. RESPONSIVE CONTAINER */
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            box-sizing: border-box;
        }

        /* 3. CARD STYLES */
        .card {
            background: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s;
        }

        /* 4. NAVIGATION STYLES */
        header {
            background-color: var(--header-bg);
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            flex-wrap: wrap;
        }

        .logo {
            color: var(--header-text);
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
            letter-spacing: 1px;
        }

        nav {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        nav a {
            color: var(--header-text);
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            transition: 0.2s;
            font-weight: 500;
            font-size: 15px;
        }

        nav a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* 5. TOGGLE & BUTTON STYLES */
        .theme-toggle {
            background: none;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            margin-right: 5px;
        }

        .theme-toggle:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .logout-button {
            background-color: #e74c3c !important;
            color: white !important;
            padding: 8px 18px;
            border-radius: 5px;
            font-weight: bold;
        }

        /* 6. MOBILE MEDIA QUERY */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            nav {
                justify-content: center;
                width: 100%;
            }

            nav a,
            .logout-button,
            .theme-toggle {
                display: block;
                width: 100%;
                text-align: center;
                margin: 5px 0;
            }
        }

        .logo2{
            width:60px;
            height:60px;
        }
    </style>
</head>

<body>

    <header>
        <div class="nav-container">
            <img class ="logo2" src="OIP.webp">
            <a href="index.php" class="logo">ADBU Club & Society Management</a>

            <nav>
                <button class="theme-toggle" onclick="toggleTheme()" id="theme-btn">🌙 Dark Mode</button>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="index.php">Home</a>
                    <a href="announcements.php">Notices</a>

                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin')): ?>
                        <a href="admin.php">Admin Dashboard</a>
                    <?php else: ?>
                        <a href="dashboard.php">Dashboard</a>
                    <?php endif; ?>

                    <a href="clubs.php">Clubs</a>
                    <a href="events.php">Events</a>
                    <a href="profile.php">Profile</a>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin'): ?>
                        <a href="manage_users.php" style="color: #ffdd57;">Users</a>
                    <?php endif; ?>

                    <a href="logout.php" class="logout-button">Logout</a>

                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php" style="background: #28a745; color: white;">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <script>
        // JavaScript for Persistent Dark Mode
        function toggleTheme() {
            const body = document.body;
            const btn = document.getElementById('theme-btn');

            body.classList.toggle('dark-mode');

            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('theme', 'dark');
                btn.innerHTML = '☀️ Light Mode';
            } else {
                localStorage.setItem('theme', 'light');
                btn.innerHTML = '🌙 Dark Mode';
            }
        }

        // Apply saved theme on page load
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const btn = document.getElementById('theme-btn');
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-mode');
                btn.innerHTML = '☀️ Light Mode';
            }
        })();
    </script>