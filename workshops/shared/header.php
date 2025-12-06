<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
  // Determine current page and set login link accordingly
$current_page = basename($_SERVER['PHP_SELF']);
$query = $_GET;
//set default login link
$login_link = "login.php";

// come from homepage
if ($current_page === "index.php") {
    $login_link = "login.php?return=index";
}

//come from workshops list
if ($current_page === "workshops.php") {
    $login_link = "login.php?return=list";
}

// come from workshop details page
if ($current_page === "workshop_details.php" && isset($query['id'])) {
    $login_link = "login.php?return=details&id=" . (int)$query['id'];
}

//is logged in
$isLoggedIn = isset($_SESSION['user']);
$userName   = $isLoggedIn ? $_SESSION['user']['full_name'] : null;

echo "
<p>✨ Unlock Your Tech Potential With Our Online Workshops!</p>
<header>
    <section>
        <h1 class=\"logo\">Learning Aura</h1>
    </section>
    <nav class=\"headerNav\">
        <div id=\"div_left\">
            <a href=\"index.php\">Home</a>
            <a href=\"#contactInfo\">Contact</a>
            <a href=\"workshops.php\">Workshops</a>
        </div>
        <div id=\"div_right\">
";
        // if not logged in → show Login
        if (!$isLoggedIn) {
            echo "<a href=\"$login_link\">Login</a>";
        } else {
            // if logged in, show user name and logout link
            echo "
                <span class=\"user-name\">Hi, " . htmlspecialchars($userName) . "</span>
                <a href=\"../handlers/signout.php\">Logout</a>
            ";
        }

echo "
        </div>
    </nav>
</header>
";
?>
