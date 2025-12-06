<?php
require_once __DIR__ . '/../../protected/config/database.php';
session_start();

$message = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pdo = Database::getConnection();

    $first_name        = trim($_POST['first_name'] ?? '');
    $last_name         = trim($_POST['last_name'] ?? '');
    $email             = trim($_POST['email'] ?? '');
    $password          = $_POST['password'] ?? '';
    $confirm_password  = $_POST['confirm_password'] ?? '';

    // -----------------------
    //   VALIDATION
    // -----------------------
    if (
        empty($first_name) || empty($last_name) ||
        empty($email) || empty($password) || empty($confirm_password)
    ) {
        $message = "All fields are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";

    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";

    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";

    } else {

        // -----------------------
        //   CHECK EMAIL EXISTS
        // -----------------------
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            $message = "An account with this email already exists.";

        } else {

            // -----------------------
            //   INSERT NEW USER
            // -----------------------
            $full_name = $first_name . " " . $last_name;
            $hashed    = password_hash($password, PASSWORD_DEFAULT);

            $insert = $pdo->prepare("
                INSERT INTO users (full_name, email, password)
                VALUES (:full_name, :email, :password)
            ");

            $insert->execute([
                ':full_name' => $full_name,
                ':email'     => $email,
                ':password'  => $hashed
            ]);

            $success = true;
            $message = "Registration successful! Redirecting to login...";

        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup page</title>

    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/signup.css">
    <?php require_once __DIR__ . '/../shared/styleFonts.php'; ?>
</head>

<body>
    <?php require_once __DIR__ . '/../shared/header.php'; ?>

    <main>
        <section id="signup">
            <h2>Create a New Account</h2>

            <?php if ($message): ?>
                <div class="msg <?php echo $success ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form id="signUpForm" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                
                <fieldset class="row">
                    <div>
                        <label>First name</label>
                        <input name="first_name" type="text" required
                            placeholder="Enter your first name"
                            value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                    </div>

                    <div>
                        <label>Last name</label>
                        <input name="last_name" type="text" required
                            placeholder="Enter your last name"
                            value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                    </div>
                </fieldset>

                <fieldset>
                    <label>Email address</label>
                    <input name="email" type="email" required
                        placeholder="example@example.com"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </fieldset>

                <fieldset class="row">
                    <div>
                        <label>Password</label>
                        <input id="password1" name="password" type="password" required
                            placeholder="Enter your password">


                    </div>

                    <div>
                        <label>Confirm Password</label>
                        <input name="confirm_password" type="password" required
                            placeholder="Confirm your password">
                    </div>
                </fieldset>
                        <p id="liveFeedback"></p>

                <button id="signUpBtn" type="submit">Sign up</button>
            </form>

            <p>Already have an account? <a href="login.php">Login</a></p>
        </section>
    </main>

    <footer id="contactInfo">
        <?php require_once __DIR__ . '/../shared/footer.php'; ?>
    </footer>

    

    <script src="../assets/js/validation_signup.js"></script>

    <?php if ($success): ?>
        <script>
            setTimeout(function() {
                window.location.href = "login.php";
            }, 3500); 
        </script>
        <?php endif; ?>
</body>
</html>
