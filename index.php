<?php
// 1. Start the session and connect to the database
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "db.php";

// 2. Include the dynamic header
include "header.php";
?>

<style>
    .hero-section {
        background: linear-gradient(135deg, #303f53 0%, #1a252f 100%);
        color: white;
        text-align: center;
        padding: 80px 20px;
        margin-top: 0px;
        /* <--- THIS IS THE CULPRIT */
        border-bottom: 5px solid #007bff;
    }

    .hero-title {
        font-size: 3em;
        margin-bottom: 15px;
        margin-top: 0;
        font-weight: bold;
    }

    .hero-subtitle {
        font-size: 1.2em;
        color: #ccc;
        max-width: 600px;
        margin: 0 auto 30px auto;
        line-height: 1.6;
    }

    .cta-button {
        display: inline-block;
        padding: 12px 25px;
        background-color: #28a745;
        color: white;
        text-decoration: none;
        font-size: 1.1em;
        font-weight: bold;
        border-radius: 5px;
        transition: 0.3s;
        margin: 5px;
    }

    .cta-button:hover {
        background-color: #218838;
        transform: translateY(-2px);
    }

    .cta-secondary {
        background-color: transparent;
        border: 2px solid white;
    }

    .cta-secondary:hover {
        background-color: white;
        color: #303f53;
    }

    .features-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        justify-content: center;
        padding: 50px 20px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .feature-card {
        flex: 1;
        min-width: 250px;
        max-width: 350px;
        background: white;
        padding: 30px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s;
    }

    .feature-card:hover {
        transform: translateY(-5px);
    }

    .feature-icon {
        font-size: 3em;
        margin-bottom: 15px;
    }
</style>

<div class="hero-section">
    <h1 class="hero-title">Campus Life, Connected.</h1>
    <p class="hero-subtitle">The official Assam Don Bosco University Club & Society Portal.</p>

    <div>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="cta-button">Join the Portal</a>
            <a href="login.php" class="cta-button cta-secondary">Student Login</a>
        <?php else: ?>
            <a href="dashboard.php" class="cta-button">Go to Dashboard</a>
            <a href="clubs.php" class="cta-button cta-secondary">Explore Clubs</a>
        <?php endif; ?>
    </div>
</div>

<div class="features-grid">

    <div class="feature-card">
        <div class="feature-icon">🤝</div>
        <h3 style="color: #2c3e50; margin-top: 0;">Find Your Community</h3>
        <p style="color: #666; line-height: 1.5;">Browse a directory of active campus clubs. Whether you are into tech, arts, or sports, there is a place for you to belong.</p>
    </div>

    <div class="feature-card">
        <div class="feature-icon">📅</div>
        <h3 style="color: #2c3e50; margin-top: 0;">Attend Events</h3>
        <p style="color: #666; line-height: 1.5;">Never miss out on campus activities. View upcoming schedules, RSVP with a single click, and keep track of your plans.</p>
    </div>

    <div class="feature-card">
        <div class="feature-icon">📢</div>
        <h3 style="color: #2c3e50; margin-top: 0;">Stay Informed</h3>
        <p style="color: #666; line-height: 1.5;">Get real-time updates directly from club administrators and university staff so you always know what is happening.</p>
    </div>

</div>

<?php include "footer.php"; ?>