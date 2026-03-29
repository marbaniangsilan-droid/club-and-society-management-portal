<?php
// Start session and connect to DB first!
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "db.php";
include "header.php";

// Fetch only the General Campus Announcements
$announcements = $conn->query("SELECT * FROM general_announcements ORDER BY created_at DESC");
?>

<div class="container" style="min-height: 60vh;">
    <h2 style="text-align: center; margin-bottom: 30px;">Campus Announcements</h2>

    <div style="max-width: 800px; margin: 0 auto;">

        <?php if ($announcements && $announcements->num_rows > 0): ?>

            <?php while ($ann = $announcements->fetch_assoc()): ?>
                <div class="card" style="padding: 20px; margin-bottom: 20px; border-left: 5px solid #6c757d; border-radius: 8px; background: var(--bg-color); box-shadow: 0 4px 10px rgba(0,0,0,0.1);">

                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--header-bg); padding-bottom: 10px; margin-bottom: 10px; flex-wrap: wrap; gap: 10px;">
                        <h3 style="margin: 0; color: var(--text-color); font-size: 1.2em;">
                            <?php echo htmlspecialchars($ann['title']); ?>
                        </h3>
                        <span style="font-size: 0.85em; background: #eee; color: #555; padding: 3px 8px; border-radius: 12px;">
                            <?php echo date("F j, Y, g:i a", strtotime($ann['created_at'])); ?>
                        </span>
                    </div>

                    <p style="margin: 0; line-height: 1.6; color: var(--text-color); font-size: 1.05em;">
                        <?php echo nl2br(htmlspecialchars($ann['message'])); ?>
                    </p>

                </div>
            <?php endwhile; ?>

        <?php else: ?>

            <div style="text-align: center; padding: 50px 20px; background: var(--bg-color); border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                <h3 style="color: #666; margin-top: 0;">No new campus announcements</h3>
                <p style="color: #888; font-size: 1.1em;">Check back later for university-wide updates and notices.</p>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="login.php" style="display: inline-block; margin-top: 15px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Login to Dashboard</a>
                <?php endif; ?>
            </div>

        <?php endif; ?>

    </div>
</div>

<?php include "footer.php"; ?>