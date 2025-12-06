<?php

// Load database and start session
require_once __DIR__ . '/../../protected/config/database.php';
require_once __DIR__ . '/../shared/csrf.php';

$error = null;

// Read "return" and "id" from URL to know where to redirect after login
$return   = $_GET['return'] ?? null;
$returnId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) Validate CSRF token first
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $error = 'Something went wrong. Please refresh the page and try again.';
    } else {

        // 2) Get email + password from form
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // 3) Basic validation
        if ($email === '' || $password === '') {
            $error = 'Please enter both email and password.';
        } else {
            try {
                $pdo = Database::getConnection();

                // Find user by email
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Check password
                if ($user && password_verify($password, $user['password'])) {

                    // Normalize role value (defensive)
                    $role = strtolower(trim($user['role'] ?? 'user'));

                    // Save user in session after successful login
                    $_SESSION['user'] = [
                        'id'        => $user['id'],
                        'full_name' => $user['full_name'],
                        'email'     => $user['email'],
                        'role'      => $role,
                    ];

                    // Admin always goes to admin page
                    if ($role === 'admin') {
                        header('Location: ../admin/admin_workshops.php');
                        exit;
                    }

                    // Handle normal user redirects based on previous page
                    if ($return === 'index') {
                        header('Location: index.php');
                        exit;
                    }

                    if ($return === 'list') {
                        header('Location: workshops.php');
                        exit;
                    }

                    // If user came from workshop details → send to enrollment
                    if ($return === 'details' && $returnId) {
                        header('Location: enrollment.php?workshop_id=' . $returnId);
                        exit;
                    }

                    // Default redirect if no "return" was provided
                    header('Location: index.php');
                    exit;

                } else {
                    // Wrong email or password
                    $error = 'Invalid email or password.';
                }

            } catch (Exception $e) {
                // General fallback error
                $error = 'An error occurred. Please try again later.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login page</title>

    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/login.css">
    <?php require_once __DIR__ . '/../shared/styleFonts.php'; ?>
</head>

<body>
    <?php require_once __DIR__ . '/../shared/header.php'; ?>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <main>
        <section id="login">
            <h2>Welcome Back!</h2>
            <form
                id="loginForm"
                method="POST"
                action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>"
            >         <?= csrf_field(); ?>   <!-- field for  CSRF -->
                <fieldset>
                    <label for="email">Email Address</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        placeholder="Enter your email address"
                        required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    >
                    <br><br>

                    <label for="password">Password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >
                </fieldset>
                <br>
                <button id="loginBtn" type="submit">Login</button>
            </form>
            <p>Don't have an account? <a href="signup.php">Sign up</a></p>
        </section>
    </main>

    <footer id="contactInfo">
        <?php require_once __DIR__ . '/../shared/footer.php'; ?>
    </footer>

    <script src="../assets/js/validation_login.js"></script>
</body>
</html>
