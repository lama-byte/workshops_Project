<?php
require_once '../handlers/config.php';
if (isLoggedIn()) {
    header("Location: catalog.php");
    exit();
}
 
$message = "";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home page</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <?php require_once "../shared/styleFonts.php" ?>
</head>

<body>
    <?php require_once "../shared/header.php" ?>
    <main>
        <section id="welcome">
            <p>Welcome to .. </p>
            <h1>Learning Aura Workshops</h1>
            <p>Where ideas glow brighter ⭐</p>
            <br>
            <p>At Learning Aura, we provide an easy and effective way 
                for students to discover and register for in-person workshops 
                in the field of computer science.
                Our platform gathers all the opportunities you need in one 
                place — helping you build your skills, explore new areas, 
                and grow with confidence.

                Whether you're interested in cybersecurity, AI, networking, 
                or general tech development, Learning Aura makes it simple 
                to stay updated and take the next step in your learning journey.</p>

            <?php if ($message !== ""): ?>
                <div class="message">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
    <main id="reasons">
        <h2>Why you should choose Us ? </h2>
        <div id="cards">
            <div class="card" id="1">
                    <h3>✨ All Workshops in One Place</h3>
                    <p>No more searching across multiple platforms — we gather all important tech workshops and announcements in one easy-to-access website.</p>
            </div>
            <div class="card" id="2">
                    <h3>✨ Simple & Fast Registration</h3>
                    <p>Register for workshops effortlessly with a clean, organized interface designed specifically for students.</p>
            </div>

            <div class="card" id="3">
                    <h3>✨ Focused on Real Skill Growth</h3>
                    <p>Every workshop is selected to help students strengthen practical skills in areas like cybersecurity, AI, networking, programming, and more.</p>
            </div>
            <div class="card" id="4">
                    <h3>✨ Boost Your Career & Confidence</h3>
                    <p>Never miss a valuable workshop again. Learning Aura keeps you informed about upcoming training sessions and learning events.</p>
            </div>
            </div>
    </main>
    <footer id="contactInfo">
    <?php require_once "../shared/footer.php"?>
    </footer>
</body>

</html>