<?php
// 1. LOGIC FIRST: Start session and connect to DB securely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "db.php";

// 2. SECURITY CHECK: Must be logged in as either admin or super_admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'super_admin')) {
    include "header.php";
    echo "<div class='container' style='min-height: 60vh; display: flex; align-items: center; justify-content: center;'>
            <div class='card' style='text-align: center; border-top: 5px solid red; padding: 40px; width: 100%; max-width: 500px;'>
                <h3 style='color: red; margin-top: 0; font-size: 1.5em;'>Access Denied</h3>
                <p style='color: #666; font-size: 1.1em;'>Only authorized administrators can manage users.</p>
                <a href='dashboard.php' style='display: inline-block; margin-top: 15px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Return to Dashboard</a>
            </div>
          </div>";
    include "footer.php";
    exit();
}

$message = "";

// 3. HANDLE ACTIONS: Only a Super Admin is allowed to run these commands
if ($_SESSION['role'] === 'super_admin') {

    // Handle Deleting a User
    if (isset($_POST['delete_user'])) {
        $delete_id = intval($_POST['user_id']);

        // Failsafe 1: Prevent self-deletion
        if ($delete_id === $_SESSION['user_id']) {
            $message = "<p style='color: red; text-align: center; background: #ffebee; padding: 10px; border-radius: 5px;'>❌ You cannot delete your own account.</p>";
        } else {
            // Failsafe 2: Prevent deleting other Super Admins
            $check_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
            $check_stmt->bind_param("i", $delete_id);
            $check_stmt->execute();
            $target_user = $check_stmt->get_result()->fetch_assoc();
            $check_stmt->close();

            if ($target_user && $target_user['role'] === 'super_admin') {
                $message = "<p style='color: red; text-align: center; background: #ffebee; padding: 10px; border-radius: 5px;'>❌ You cannot delete another Super Admin.</p>";
            } else {
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $delete_id);
                if ($stmt->execute()) {
                    $message = "<p style='color: green; text-align: center; background: #e8f5e9; padding: 10px; border-radius: 5px;'>🗑️ User deleted successfully.</p>";
                }
                $stmt->close();
            }
        }
    }

    // Handle Changing a User's Role
    if (isset($_POST['update_role'])) {
        $update_id = intval($_POST['user_id']);
        $new_role = $_POST['new_role'];

        // Failsafe 1: Prevent self-demotion
        if ($update_id === $_SESSION['user_id']) {
            $message = "<p style='color: red; text-align: center; background: #ffebee; padding: 10px; border-radius: 5px;'>❌ You cannot alter your own role from here.</p>";
        }
        // Failsafe 2: Prevent making ANYONE a Super Admin
        elseif ($new_role === 'super_admin') {
            $message = "<p style='color: red; text-align: center; background: #ffebee; padding: 10px; border-radius: 5px;'>❌ Security Alert: You cannot grant Super Admin privileges.</p>";
        } else {
            // Failsafe 3: Prevent demoting existing Super Admins
            $check_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
            $check_stmt->bind_param("i", $update_id);
            $check_stmt->execute();
            $target_user = $check_stmt->get_result()->fetch_assoc();
            $check_stmt->close();

            if ($target_user && $target_user['role'] === 'super_admin') {
                $message = "<p style='color: red; text-align: center; background: #ffebee; padding: 10px; border-radius: 5px;'>❌ You cannot alter the role of another Super Admin.</p>";
            } else {
                $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->bind_param("si", $new_role, $update_id);
                if ($stmt->execute()) {
                    $message = "<p style='color: green; text-align: center; background: #e8f5e9; padding: 10px; border-radius: 5px;'>✅ User role updated.</p>";
                }
                $stmt->close();
            }
        }
    }
}

// 4. Fetch all users from the database, sorted by Role and then Name
$users = $conn->query("SELECT id, name, email, department, role FROM users ORDER BY role DESC, name ASC");

// 5. UI SECOND: Draw the page
include "header.php";
?>

<div class="container" style="min-height: 70vh;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2>User Directory & Management</h2>
        <a href="admin.php" style="color: #007bff; text-decoration: none;">&larr; Back to Admin Panel</a>
    </div>

    <?php echo $message; ?>

    <div class="card" style="padding: 20px; overflow-x: auto; border-top: 4px solid #dc3545;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <tr style="background-color: var(--header-bg); color: white;">
                <th style="padding: 12px;">Name</th>
                <th style="padding: 12px;">Email</th>
                <th style="padding: 12px;">Department</th>
                <th style="padding: 12px;">Role</th>

                <?php if ($_SESSION['role'] === 'super_admin'): ?>
                    <th style="padding: 12px; text-align: right;">Actions</th>
                <?php endif; ?>
            </tr>

            <?php while ($u = $users->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 12px; font-weight: 500;"><?php echo htmlspecialchars($u['name']); ?></td>
                    <td style="padding: 12px;"><?php echo htmlspecialchars($u['email']); ?></td>
                    <td style="padding: 12px;"><?php echo htmlspecialchars($u['department']); ?></td>

                    <td style="padding: 12px;">
                        <?php
                        $badge_bg = ($u['role'] === 'super_admin') ? '#dc3545' : (($u['role'] === 'admin') ? '#ffc107' : '#e8f5e9');
                        $badge_text = ($u['role'] === 'super_admin') ? '#fff' : (($u['role'] === 'admin') ? '#333' : '#28a745');
                        ?>
                        <span style="background: <?php echo $badge_bg; ?>; color: <?php echo $badge_text; ?>; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; text-transform: uppercase;">
                            <?php echo str_replace('_', ' ', $u['role']); ?>
                        </span>
                    </td>

                    <?php if ($_SESSION['role'] === 'super_admin'): ?>
                        <td style="padding: 12px; text-align: right; white-space: nowrap;">

                            <?php if ($u['id'] === $_SESSION['user_id']): ?>
                                <span style="color: #888; font-size: 0.9em; font-style: italic; margin-right: 10px;">(You)</span>
                            <?php elseif ($u['role'] === 'super_admin'): ?>
                                <span style="color: #dc3545; font-size: 0.9em; font-weight: bold; margin-right: 10px;">Protected</span>
                            <?php else: ?>

                                <form method="POST" style="display: inline-block; margin-right: 5px;">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <input type="hidden" name="update_role" value="1">
                                    <select name="new_role" onchange="this.form.submit()" style="padding: 6px; border-radius: 4px; border: 1px solid #ccc; font-size: 14px; cursor: pointer;">
                                        <option value="student" <?php if ($u['role'] === 'student') echo 'selected'; ?>>Student</option>
                                        <option value="admin" <?php if ($u['role'] === 'admin') echo 'selected'; ?>>Admin</option>
                                    </select>
                                </form>

                                <form method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to completely delete this user?');">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <button name="delete_user" style="background: #dc3545; color: white; border: none; padding: 7px 12px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: bold;">Delete</button>
                                </form>

                            <?php endif; ?>

                        </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php include "footer.php"; ?>