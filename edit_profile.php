<?php
// 1. Start session and connect to DB first!
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$profile_msg = "";
$password_msg = "";

// --- Handle Updating Profile Details ---
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $department = trim($_POST['department']);

    if (!empty($name) && !empty($department)) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, department = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $phone, $department, $user_id);
        if ($stmt->execute()) {
            $profile_msg = "<p style='color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; text-align: center;'>✅ Profile updated successfully!</p>";
        }
        $stmt->close();
    }
}

// --- Handle Changing Password ---
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // First, fetch the user's current hashed password from the database
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Verify the current password
    if (password_verify($current_password, $user['password'])) {
        // Ensure new passwords match
        if ($new_password === $confirm_password) {
            // Hash the new password and save it
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            if ($update_stmt->execute()) {
                $password_msg = "<p style='color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; text-align: center;'>✅ Password changed successfully!</p>";
            }
            $update_stmt->close();
        } else {
            $password_msg = "<p style='color: red; background: #ffebee; padding: 10px; border-radius: 5px; text-align: center;'>❌ New passwords do not match.</p>";
        }
    } else {
        $password_msg = "<p style='color: red; background: #ffebee; padding: 10px; border-radius: 5px; text-align: center;'>❌ Current password is incorrect.</p>";
    }
}

// Fetch Current Data to pre-fill the form
$stmt = $conn->prepare("SELECT name, email, phone, department FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_info = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Now include the HTML header
include "header.php";
?>

<div class="container" style="min-height: 70vh;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h2>Account Settings</h2>
        <a href="profile.php" style="color: #007bff; text-decoration: none;">&larr; Back to Profile</a>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 30px; justify-content: center; align-items: flex-start;">

        <div class="card" style="flex: 1; min-width: 300px; max-width: 450px; padding: 30px; border-top: 4px solid #007bff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); background: var(--bg-color);">
            <h3 style="margin-top: 0; border-bottom: 1px solid var(--header-bg); padding-bottom: 10px;">Personal Details</h3>

            <?php echo $profile_msg; ?>

            <form method="POST">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user_info['name']); ?>" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">

                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Phone Number</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user_info['phone'] ?? ''); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">

                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Department</label>
                <input type="text" name="department" value="<?php echo htmlspecialchars($user_info['department']); ?>" required style="width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">

                <button name="update_profile" style="width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Update Profile</button>
            </form>
        </div>

        <div class="card" style="flex: 1; min-width: 300px; max-width: 450px; padding: 30px; border-top: 4px solid #dc3545; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); background: var(--bg-color);">
            <h3 style="margin-top: 0; border-bottom: 1px solid var(--header-bg); padding-bottom: 10px;">Change Password</h3>

            <?php echo $password_msg; ?>

            <form method="POST">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Current Password</label>
                <input type="password" name="current_password" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">

                <label style="font-weight: bold; display: block; margin-bottom: 5px;">New Password</label>
                <input type="password" name="new_password" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">

                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Confirm New Password</label>
                <input type="password" name="confirm_password" required style="width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">

                <button name="change_password" style="width: 100%; padding: 12px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Update Password</button>
            </form>
        </div>

    </div>
</div>

<?php include "footer.php"; ?>