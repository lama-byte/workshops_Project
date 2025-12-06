<?php
require_once __DIR__ . '/../../protected/config/database.php';
session_start();

$pdo = Database::getConnection();

// ===== 1) Get all distinct categories =====
$catStmt = $pdo->query("
    SELECT DISTINCT category
    FROM workshops
    WHERE category IS NOT NULL AND category <> ''
    ORDER BY category ASC
");
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

// Selected category from filter (if any)
$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : '';

// ===== 2) Get workshops (filtered if category selected) =====
if ($selectedCategory !== '') {
    $stmt = $pdo->prepare("
        SELECT id, title, category, start_date, start_time, location, image, description
        FROM workshops
        WHERE category = :cat
        ORDER BY start_date ASC, start_time ASC
    ");
    $stmt->execute([':cat' => $selectedCategory]);
} else {
    $stmt = $pdo->query("
        SELECT id, title, category, start_date, start_time, location, image, description
        FROM workshops
        ORDER BY start_date ASC, start_time ASC
    ");
}

$workshops = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Workshops</title>
    <link rel="stylesheet" href="base.css">
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/workshops.css">

    <?php require_once __DIR__ . '/../shared/styleFonts.php'; ?>
</head>

<body>
<?php require_once __DIR__ . '/../shared/header.php'; ?>
<section class="workshops-hero">
        
        <div class="category-filter">
            <form method="get">
                <label for="category">Filter by category:</label>
                <select name="category" id="category" onchange="this.form.submit()">
                    <option value="">All categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>"
                            <?= ($selectedCategory === $cat ? 'selected' : '') ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </section>


<main id="workshops-main">

   
    <section class="workshop-grid">


        <?php if (!$workshops): ?>
            <p class="empty-state">
                No workshops available at the moment. <br>
                Please check back soon!
            </p>

        <?php else: ?>
            <?php foreach ($workshops as $w): ?>

                <?php
                // Build image path safely
                $imagePath = null;
                if (!empty($w['image'])) {
                    $img = $w['image'];
                    if (strpos($img, 'uploads/') === 0) {
                        $imagePath = $img;              // already has uploads/
                    } else {
                        $imagePath = 'uploads/' . $img; // just filename
                    }
                }
                ?>

                <article class="workshop-card">

                    <div class="workshop-image">
                        <?php if ($imagePath): ?>
                            <img src="<?= htmlspecialchars($imagePath); ?>"
                                 alt="<?= htmlspecialchars($w['title']); ?>">
                        <?php else: ?>
                          <div class="workshop-placeholder">
                                <?= htmlspecialchars($w['category'] ?: 'Workshop') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="workshop-body">
                        <h2 class="workshop-title">
                            <?= htmlspecialchars($w['title']) ?>
                        </h2>
                        <p class="workshop-meta">
                            <br>
                            <?php if (!empty($w['start_date'])): ?>
                            📅 <?= htmlspecialchars($w['start_date']) ?>
                            <?php endif; ?>
                            <br>
                            <?php if (!empty($w['start_time'])): ?>
                            ⏰ <?= htmlspecialchars(substr($w['start_time'], 0, 5)) ?>
                            <?php endif; ?>
                             <br>
                            <?php if (!empty($w['location'])): ?>
                                📍 <?= htmlspecialchars($w['location']) ?>
                            <?php endif; ?>
                            </p>
                        <?php if (!empty($w['description'])): ?>
                            <p class="workshop-desc">
                                <?= htmlspecialchars(mb_strimwidth($w['description'], 0, 120, '...')) ?>
                            </p>
                        <?php endif; ?>

                        <div class="workshop-footer">
                        <a href="workshop_details.php?id=<?= (int)$w['id'] ?>" class="enroll-btn">
                            View Details
                                  </a>
                              </div>

                        </div>

                </article>

            <?php endforeach; ?>
        <?php endif; ?>

    </section>

</main>

<footer id="contactInfo">
    <?php require_once __DIR__ . '/../shared/footer.php'; ?>
</footer>

</body>
</html>
