<?php
// Start the session so we can check if the user is logged in
session_start();

require_once __DIR__ . '/../../protected/config/database.php';

$pdo = Database::getConnection();

// Get workshop id from URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$workshop = null;
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM workshops WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $workshop = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php if ($workshop): ?>
            <?= htmlspecialchars($workshop['title']) ?> – Workshop Details
        <?php else: ?>
            Workshop Not Found
        <?php endif; ?>
    </title>

    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/details.css">
    <?php require_once __DIR__ . '/../shared/styleFonts.php'; ?>
    
</head>
<body>
<?php require_once __DIR__ . '/../shared/header.php'; ?>

<main class="workshop-page">

    <?php if (!$workshop): ?>
        <!-- If no workshop found, show a simple message -->
        <section class="workshop-info">
            <h2>Workshop Not Found</h2>
            <p>The workshop you are looking for does not exist.</p>
            <a href="workshops.php" class="btn-back">Back to Workshops</a>
        </section>
    <?php else: ?>

        <!-- Left side: Image -->
        <section class="workshop-image">
            <?php if (!empty($workshop['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($workshop['image']) ?>" 
                     alt="Workshop Image">
            <?php else: ?>
                <div class="no-image">No image available</div>
            <?php endif; ?>
        </section>

        <!-- Right side: Info + Register button -->
        <section class="workshop-info">
            <h2 class="workshop-title">
                <?= htmlspecialchars($workshop['title']) ?>
            </h2>

            <?php if (!empty($workshop['category'])): ?>
                <p class="workshop-category">
                    Category: <?= htmlspecialchars($workshop['category']) ?>
                </p>
            <?php endif; ?>

            <ul class="workshop-meta">
                <li><strong>Date:</strong> <?= htmlspecialchars($workshop['start_date']) ?></li>
                <?php if (!empty($workshop['start_time'])): ?>
                    <li><strong>Time:</strong> <?= htmlspecialchars(substr($workshop['start_time'], 0, 5)) ?></li>
                <?php endif; ?>
                <?php if (!empty($workshop['location'])): ?>
                    <li><strong>Location:</strong> <?= htmlspecialchars($workshop['location']) ?></li>
                <?php endif; ?>
                <li><strong>Capacity:</strong> <?= (int)$workshop['capacity'] ?> seats</li>
                <li><strong>Seats Available:</strong> <?= (int)$workshop['seats_available'] ?> seats</li>
            </ul>

            <p class="workshop-description">
                <?= nl2br(htmlspecialchars($workshop['description'] ?? 'No description available.')) ?>
            </p>

            <div class="workshop-actions">
                <?php if (isset($_SESSION['user'])): ?>
                    <!-- Logged-in user → go directly to enrollment -->
                    <a href="enrollment.php?workshop_id=<?= (int)$workshop['id'] ?>" class="btn-enroll">
                        Register Now
                    </a>
                <?php else: ?>
                    <!-- Guest user → go to login with return parameters -->
                    <a href="login.php?return=details&id=<?= (int)$workshop['id'] ?>" class="btn-enroll">
                        Register Now
                    </a>
                <?php endif; ?>

                <a href="workshops.php" class="btn-back">
                    Back to Workshops
                </a>
            </div>
        </section>

    <?php endif; ?>

</main>

<footer>
    <?php require_once __DIR__ . '/../shared/footer.php'; ?>
</footer>
</body>
</html>
