<?php
// 1. Start the session and connect to the database FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "db.php";

// If they are already logged in, bounce them to the dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$message = "";

// 2. Handle the Registration Form Submission
if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $department = trim($_POST['department']);
    $phone = trim($_POST['phone']);

    // Check if the email is already registered
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();

    if ($check_stmt->get_result()->num_rows > 0) {
        $message = "<p style='color: red; text-align: center; background: #ffebee; padding: 10px; border-radius: 5px;'>❌ An account with this email already exists.</p>";
    } else {
        // Hash the password securely and set the default role to 'student'
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'student';

        $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password, department, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("ssssss", $name, $email, $hashed_password, $department, $phone, $role);

        if ($insert_stmt->execute()) {
            $message = "<p style='color: green; text-align: center; background: #e8f5e9; padding: 10px; border-radius: 5px;'>✅ Registration successful! <a href='login.php' style='color: #28a745; font-weight: bold; text-decoration: underline;'>Login here</a>.</p>";
        } else {
            $message = "<p style='color: red; text-align: center; background: #ffebee; padding: 10px; border-radius: 5px;'>❌ Registration failed. Please try again.</p>";
        }
        $insert_stmt->close();
    }
    $check_stmt->close();
}

// 3. NOW include the visual header
include "header.php";
?>

<div class="container" style="display: flex; justify-content: center; align-items: center; min-height: 70vh;">
    <div class="card" style="width: 100%; max-width: 500px; padding: 30px; border-radius: 8px; border-top: 4px solid #28a745; background: var(--card-bg);">

        <h2 style="margin-top: 0; text-align: center; color: var(--text-color);">Create an Account</h2>

        <p style="text-align: center; color: var(--text-color); opacity: 0.7; margin-bottom: 25px;">Join the Club Portal</p>

        <?php echo $message; ?>

        <form method="POST">
            <label style="font-weight: bold; display: block; margin-bottom: 5px; color: var(--text-color);">Full Name</label>
            <input type="text" name="name" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; background: var(--bg-color); color: var(--text-color);">

            <label style="font-weight: bold; display: block; margin-bottom: 5px; color: var(--text-color);">University Email</label>
            <input type="email" name="email" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; background: var(--bg-color); color: var(--text-color);">

            <label style="font-weight: bold; display: block; margin-bottom: 5px; color: var(--text-color);">Password</label>
            <input type="password" name="password" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; background: var(--bg-color); color: var(--text-color);">

            <label style="font-weight: bold; display: block; margin-bottom: 5px; color: var(--text-color);">Department (e.g., MCA, BTech)</label>
            <input type="text" name="department" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; background: var(--bg-color); color: var(--text-color);">

            <label style="font-weight: bold; display: block; margin-bottom: 5px; color: var(--text-color);">Phone Number (Optional)</label>
            <input type="text" name="phone" style="width: 100%; padding: 10px; margin-bottom: 25px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; background: var(--bg-color); color: var(--text-color);">

            <button name="register" style="width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 16px;">Register Account</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <span style="color: var(--text-color); opacity: 0.8;">Already have an account? </span>
            <a href="login.php" style="color: #007bff; text-decoration: none; font-weight: bold;">Login here</a>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>