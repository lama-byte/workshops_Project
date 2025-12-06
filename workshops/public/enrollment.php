<?php
session_start();

// If user is not logged in, send to login page
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../protected/config/database.php';

$pdo = Database::getConnection();

$userId     = (int)$_SESSION['user']['id'];
$workshopId = isset($_GET['workshop_id']) ? (int)$_GET['workshop_id'] : 0;

$message  = '';
$success  = false;
$workshop = null;

if ($workshopId > 0) {
    
    // Get workshop info
    $stmt = $pdo->prepare("SELECT * FROM workshops WHERE id = :id");
    $stmt->execute([':id' => $workshopId]);
    $workshop = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($workshop) {

        // Check if user already enrolled
        $check = $pdo->prepare("
            SELECT id FROM enrollments
            WHERE user_id = :uid AND workshop_id = :wid
            LIMIT 1
        ");
        $check->execute([
            ':uid' => $userId,
            ':wid' => $workshopId
        ]);

        if ($check->fetch()) {
            $success = true;
            $message = "You are already enrolled in this workshop 🤗.";

        } else {
            // Reserve 1 seat
            $seats = 1;

            try {
                $pdo->beginTransaction();

                // Update available seats
                $update = $pdo->prepare("
                    UPDATE workshops
                    SET seats_available = seats_available - ?
                    WHERE id = ? AND seats_available >= ?
                ");
                $update->execute([$seats, $workshopId, $seats]);

                if ($update->rowCount() === 0) {
                    // No seats left
                    $pdo->rollBack();
                    $message = "No seats available.";

                } else {
                    // Add enrollment row
                    $insert = $pdo->prepare("
                        INSERT INTO enrollments (user_id, workshop_id, seats)
                        VALUES (?, ?, ?)
                    ");
                    $insert->execute([$userId, $workshopId, $seats]);

                    $pdo->commit();
                    $success = true;
                    $message = "Enrollment completed successfully!";
                }

            } catch (Exception $e) {
                $pdo->rollBack();
                // For users, keep it simple
                $message = "Something went wrong. Please try again.";
                // For debugging you can log $e->getMessage() to a file if you want
            }
        }

    } else {
        $message = "Workshop not found.";
    }

} else {
    $message = "Invalid workshop.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Workshop Enrollment</title>

    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/enrollment.css">
    <?php require_once __DIR__ . '/../shared/styleFonts.php'; ?>
</head>
<body>

<?php require_once __DIR__ . '/../shared/header.php'; ?>

<main id="enrollment-main">
    <section class="enrollment-box">

        <h2>Workshop Enrollment</h2>

        <?php if ($message): ?>
            <div class="msg <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($workshop): ?>
            <div class="enrollment-details">
                <h3><?php echo htmlspecialchars($workshop['title']); ?></h3>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($workshop['start_date']); ?></p>

                <?php if ($workshop['start_time']): ?>
                    <p><strong>Time:</strong> <?php echo htmlspecialchars(substr($workshop['start_time'], 0, 5)); ?></p>
                <?php endif; ?>

                <?php if ($workshop['location']): ?>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($workshop['location']); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="enrollment-actions">
            <a href="workshops.php" class="btn-back">Back to Workshops</a>
            <a href="index.php" class="btn-enroll">Go to Home</a>
        </div>

    </section>
</main>

<footer>
    <?php require_once __DIR__ . '/../shared/footer.php'; ?>
</footer>

</body>
</html>
