<?php
// 1. Start the session and connect to the database FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "db.php";

// 2. If already logged in, redirect immediately before any HTML loads
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

// 3. Process the login form logic
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        if (password_verify($password, $row['password'])) {
            // Success! Set session variables
            $_SESSION['user'] = $email;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];

            // THE TRAFFIC COP: Check the role and route accordingly
            if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin') {
                header("Location: admin.php"); // Send admins to the control panel
            } else {
                header("Location: dashboard.php"); // Send students to their dashboard
            }
            exit();
        } else {
            $error = "Invalid Password.";
        }
    } else {
        $error = "No account found with that email.";
    }
    $stmt->close();
}

// 4. NOW it is safe to output the visual page, so we include the header
include "header.php";
?>

<div class="container" style="display: flex; justify-content: center; align-items: center; min-height: 60vh;">
    <div class="card" style="width: 100%; max-width: 400px; padding: 30px; text-align: center; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); background: var(--bg-color);">
        <h2 style="margin-top: 0; border-bottom: 2px solid #2c3e50; padding-bottom: 10px;">Login</h2>

        <?php if ($error): ?>
            <p style="color: red; background: #ffebee; padding: 10px; border-radius: 5px; font-weight: bold;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Email" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">

            <input type="password" name="password" placeholder="Password" required style="width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">

            <button name="login" style="width: 100%; padding: 12px; background: #2c3e50; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 16px;">Login</button>
        </form>

        <br>
        <a href="forgot_password.php" style="color: #007bff; text-decoration: none; font-size: 0.9em;">Forgot Password?</a>
        <br><br>
        <p style="font-size: 0.9em; color: #666;">Don't have an account? <a href="register.php" style="color: #007bff; text-decoration: none;">Register here</a></p>
    </div>
</div>

<?php include "footer.php"; ?>