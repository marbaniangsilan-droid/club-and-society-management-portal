<?php
// 1. LOGIC FIRST: Start the session and connect to the database
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "db.php"; // <--- This fixes your $conn error!

$message = "";

// 2. Handle "Join Club" Action securely via POST
if (isset($_POST['join_club']) && isset($_SESSION['user_id'])) {
    $club_id = intval($_POST['club_id']);
    $user_id = $_SESSION['user_id'];

    // Double-check they aren't already in the club
    $check_stmt = $conn->prepare("SELECT id FROM club_members WHERE user_id = ? AND club_id = ?");
    $check_stmt->bind_param("ii", $user_id, $club_id);
    $check_stmt->execute();

    if ($check_stmt->get_result()->num_rows === 0) {
        // Insert the new membership
        $join_stmt = $conn->prepare("INSERT INTO club_members (user_id, club_id) VALUES (?, ?)");
        $join_stmt->bind_param("ii", $user_id, $club_id);
        if ($join_stmt->execute()) {
            $message = "<p style='color: green; text-align: center; background: #e8f5e9; padding: 10px; border-radius: 5px; max-width: 600px; margin: 0 auto 20px auto;'>✅ Successfully joined the club!</p>";
        }
        $join_stmt->close();
    }
    $check_stmt->close();
}

// 3. Fetch all available clubs
$clubs = $conn->query("SELECT * FROM clubs ORDER BY club_name ASC");

// 4. If logged in, fetch an array of clubs the user has ALREADY joined
$joined_clubs = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT club_id FROM club_members WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $joined_clubs[] = $row['club_id'];
    }
    $stmt->close();
}

// 5. UI SECOND: Now draw the webpage
include "header.php";
?>

<div class="container">
    <h2 style="text-align: center; margin-bottom: 30px;">Campus Clubs & Societies</h2>

    <?php echo $message; ?>

    <div style="display: flex; flex-wrap: wrap; gap: 30px; justify-content: center;">

        <?php if ($clubs && $clubs->num_rows > 0): ?>
            <?php while ($club = $clubs->fetch_assoc()): ?>

                <div class="card" style="flex: 1; min-width: 300px; max-width: 400px; padding: 30px; border-radius: 8px; display: flex; flex-direction: column;">

                    <h3 style="margin-top: 0; border-bottom: 2px solid var(--header-bg); padding-bottom: 10px;">
                        <?php echo htmlspecialchars($club['club_name']); ?>
                    </h3>

                    <p style="flex-grow: 1; color: var(--text-color); line-height: 1.6; opacity: 0.9;">
                        <?php echo nl2br(htmlspecialchars($club['description'])); ?>
                    </p>

                    <div style="margin-top: 20px;">
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <a href="login.php" style="display: block; text-align: center; width: 100%; padding: 10px; background: #6c757d; color: white; border-radius: 4px; text-decoration: none; font-weight: bold; box-sizing: border-box;">Login to Join</a>

                        <?php elseif (in_array($club['id'], $joined_clubs)): ?>
                            <button disabled style="width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: not-allowed; box-sizing: border-box;">✅ You are a Member</button>

                        <?php else: ?>
                            <form method="POST">
                                <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                                <button name="join_club" style="width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; box-sizing: border-box;">Join Club</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #666; width: 100%;">
                <h3>No clubs found.</h3>
                <p>Check back later for new organizations!</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include "footer.php"; ?>