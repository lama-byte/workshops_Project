<?php
require_once __DIR__ . '/../../protected/config/database.php';
session_start();
if   (
    !isset($_SESSION['user']) ||
    ($_SESSION['user']['role'] ?? null) !== 'admin'
) {
    // change the path if your login file is somewhere else
    header('Location: ../public/login.php');
    exit;
} 
$pdo = Database::getConnection();
$errors = [];
$success = null;

// Handle form submissions 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action      = $_POST['action'] ?? '';
    $id          = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $title       = trim($_POST['title'] ?? '');
    $category    = trim($_POST['category'] ?? '');

    $capacity    = (int)($_POST['capacity'] ?? 0);
    $seatsAvail  = (int)($_POST['seats_available'] ?? 0);
    $startDate   = trim($_POST['start_date'] ?? '');
    $startTime   = trim($_POST['start_time'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // --------- Basic validation ----------
    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if ($startDate === '') {
        $errors[] = 'Start date is required.';
    }
    
    if ($capacity < 0) {
        $errors[] = 'Capacity cannot be negative.';
    }
    if ($seatsAvail < 0) {
        $errors[] = 'Available seats cannot be negative.';
    }

    // --------- Image upload handling ----------
$imageName = null;
$currentImage = null;

// For update, load existing image so we keep it if no new file
if ($action === 'update' && $id) {
    $stmt = $pdo->prepare("SELECT image FROM workshops WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $currentImage = $row['image']; // this should be just the file name
    }
}

if (empty($errors)) {
    // If a file was uploaded
    if (!empty($_FILES['image_file']['name'])) {
        $uploadDir = __DIR__ . '/../public/uploads/';

        // Create uploads folder if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $originalName = basename($_FILES['image_file']['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

        // 1) Limit size to 2MB
        $maxBytes = 2 * 1024 * 1024; // 2 MB
        if ($_FILES['image_file']['size'] > $maxBytes) {
            $errors[] = 'Image is too large. Max 2MB.';
        }

        // 2) Check real MIME type
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($_FILES['image_file']['tmp_name']);
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($mimeType, $allowedMime, true)) {
            $errors[] = 'Invalid image content type.';
        }

        if (!in_array($ext, $allowedExt)) {
            $errors[] = 'Only JPG, JPEG, PNG, and WEBP images are allowed.';
        } else {
            // Generate safe, unique filename
            $safeName   = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
            $fileName   = time() . '_' . $safeName;           // actual file name
            $targetFile = $uploadDir . $fileName;

            if (!move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
                $errors[] = 'Failed to upload image file.';
            } else {
                // ✅ store only the file name in DB
                $imageName = $fileName;
            }
        }
    } else {
        // No new file uploaded
        if ($action === 'update') {
            // Keep old image
            $imageName = $currentImage;
        } else {
            // Create without image
            $imageName = null;
        }
    }
}


    // --------- Database insert / update ----------
    if (empty($errors)) {
        if ($action === 'create') {
            $stmt = $pdo->prepare("
                INSERT INTO workshops 
                    (title, category, capacity, seats_available, start_date, start_time, location, description, image) 
                VALUES 
                    (:title, :category, :capacity, :seats_available, :start_date, :start_time, :location, :description, :image)
            ");
            $stmt->execute([
                ':title'           => $title,
                ':category'        => $category,
                ':capacity'        => $capacity,
                ':seats_available' => $seatsAvail,
                ':start_date'      => $startDate,
                ':start_time'      => $startTime ?: null,
                ':location'        => $location,
                ':description'     => $description,
                ':image'           => $imageName,
            ]);
            $success = 'Workshop created successfully.';
        } elseif ($action === 'update' && $id) {
            $stmt = $pdo->prepare("
                UPDATE workshops
                SET title = :title,
                    category = :category,
                    capacity = :capacity,
                    seats_available = :seats_available,
                    start_date = :start_date,
                    start_time = :start_time,
                    location = :location,
                    description = :description,
                    image = :image
                WHERE id = :id
            ");
            $stmt->execute([
                ':title'           => $title,
                ':category'        => $category,
                ':capacity'        => $capacity,
                ':seats_available' => $seatsAvail,
                ':start_date'      => $startDate,
                ':start_time'      => $startTime ?: null,
                ':location'        => $location,
                ':description'     => $description,
                ':image'           => $imageName,
                ':id'              => $id,
            ]);
            $success = 'Workshop updated successfully.';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    if ($deleteId > 0) {
        $stmt = $pdo->prepare("DELETE FROM workshops WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
        $success = 'Workshop deleted successfully.';
    }
}

// Handle delete enrolled workshop
if (isset($_GET['delete_item'])) {
    $itemId = (int)$_GET['delete_item'];
    if ($itemId > 0) {
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE id = :id");
        $stmt->execute([':id' => $itemId]);
        $success = 'Purchased workshop (order item) deleted successfully.';
    }
}

// If editing, load that workshop data
$editWorkshop = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    if ($editId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM workshops WHERE id = :id");
        $stmt->execute([':id' => $editId]);
        $editWorkshop = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Get all workshops to list
$stmt = $pdo->query("SELECT * FROM workshops ORDER BY start_date ASC, start_time ASC");
$workshops = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all enrolled workshops with user information
// Get all enrollments with user & workshop info
$sql = "
    SELECT 
        e.id,
        e.user_id,
        e.seats,
        e.enrolled_at,
        w.title      AS workshop_title,
        w.start_date,
        w.start_time,
        u.full_name  AS user_full_name,
        u.email      AS user_email
    FROM enrollments e
    JOIN workshops w ON e.workshop_id = w.id
    JOIN users u     ON e.user_id     = u.id
    ORDER BY e.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin – Manage Workshops</title>
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <?php require_once __DIR__ . '/../shared/styleFonts.php'; ?>
</head>
<body>

<?php require_once __DIR__ . '/../shared/headerAdmin.php'; ?>

<main id="admin-main">
    <section id="admin-content">
        <h1>Admin Dashboard</h1>

        <?php if ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="message error">
                <?php foreach ($errors as $e): ?>
                    <div><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php
            $isEdit = $editWorkshop !== null;
            $formTitle = $isEdit ? 'Edit Workshop' : 'Create New Workshop';
            $action = $isEdit ? 'update' : 'create';
        ?>

        <h2><?= htmlspecialchars($formTitle) ?></h2>

        <form method="post" class="admin-form" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= htmlspecialchars($action) ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int)$editWorkshop['id'] ?>">
            <?php endif; ?>

            <div class="row">
                <label>
                    Title
                    <input type="text" name="title" required
                           value="<?= htmlspecialchars($editWorkshop['title'] ?? '') ?>">
                </label>
                <label>
                    Category
                    <input type="text" name="category"
                           value="<?= htmlspecialchars($editWorkshop['category'] ?? '') ?>">
                </label>
            </div>

            <div class="row">
                
                <label>
                    Capacity
                    <input type="number" name="capacity" min="0" required
                           value="<?= htmlspecialchars($editWorkshop['capacity'] ?? '0') ?>">
                </label>
                <label>
                    Seats Available
                    <input type="number" name="seats_available" min="0" required
                           value="<?= htmlspecialchars($editWorkshop['seats_available'] ?? '0') ?>">
                </label>
            </div>

            <div class="row">
                <label>
                    Start Date
                    <input type="date" name="start_date" required
                           value="<?= htmlspecialchars($editWorkshop['start_date'] ?? '') ?>">
                </label>
                <label>
                    Start Time
                    <input type="time" name="start_time"
                           value="<?= htmlspecialchars($editWorkshop['start_time'] ?? '') ?>">
                </label>
                <label>
                    Location
                    <input type="text" name="location"
                           value="<?= htmlspecialchars($editWorkshop['location'] ?? '') ?>">
                </label>
            </div>

            <div class="row">
                <label>
                    Upload Image
                    <input type="file" name="image_file" accept="image/*">
                </label>

                <?php if (!empty($editWorkshop['image'])): ?>
            <div style="margin-top: 0.5em;">
           <span>Current Image:</span><br>
          <img src="../public/uploads/<?= htmlspecialchars($editWorkshop['image']) ?>"
             alt="Workshop image"
             style="max-width: 150px; max-height: 150px; border-radius: 8px;">
                </div>
               <?php endif; ?>

            </div>

            <div class="row">
                <label>
                    Description
                    <textarea name="description"><?= htmlspecialchars($editWorkshop['description'] ?? '') ?></textarea>
                </label>
            </div>

            <button type="submit"><?= $isEdit ? 'Update Workshop' : 'Create Workshop' ?></button>
            <?php if ($isEdit): ?>
                <a href="admin_workshops.php" class="button">Cancel Edit</a>
            <?php endif; ?>
        </form>

        <h2>All Workshops</h2>
        <table class="admin-table">
            <thead>
            <tr>
               
                <th>Title</th>
                <th>Date / Time</th>
                <th>Category</th>
                <th>Price</th>
                <th>Capacity</th>
                <th>Available</th>
                <th>Location</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$workshops): ?>
                <tr><td colspan="9">No workshops found.</td></tr>
            <?php else: ?>
                <?php foreach ($workshops as $w): ?>
                    <tr>
                       
                        <td><?= htmlspecialchars($w['title']) ?></td>
                        <td>
                            <?= htmlspecialchars($w['start_date']) ?>
                            <?php if (!empty($w['start_time'])): ?>
                                <?= ' ' . htmlspecialchars(substr($w['start_time'], 0, 5)) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($w['category']) ?></td>
                        <td><?= htmlspecialchars($w['price']) ?></td>
                        <td><?= htmlspecialchars($w['capacity']) ?></td>
                        <td><?= htmlspecialchars($w['seats_available']) ?></td>
                        <td><?= htmlspecialchars($w['location']) ?></td>
                        <td>
                            <a class="button"
                               href="admin_workshops.php?edit=<?= (int)$w['id'] ?>">Edit</a>
                            <a class="button delete"
                               href="admin_workshops.php?delete=<?= (int)$w['id'] ?>"
                               onclick="return confirm('Delete this workshop?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

        <h2>Enrolled Workshops</h2>
        <table class="admin-table">
    <thead>
    <tr>
        <th>Enrollment ID</th>
        <th>User ID</th>
        <th>User Name</th>
        <th>User Email</th>
        <th>Workshop</th>
        <th>Date / Time</th>
    
        <th>Enrolled At</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($orderItems)): ?>
        <tr>
            <td colspan="9">No enrollments found.</td>
        </tr>
   <?php else: ?>
                <?php foreach ($orderItems as $item): ?>
                    <tr>
                        <td><?= (int)$item['id'] ?></td>
                        <td><?= (int)$item['user_id'] ?></td>
                        <td><?= htmlspecialchars($item['user_full_name']) ?></td>
                        <td><?= htmlspecialchars($item['user_email']) ?></td>
                        <td><?= htmlspecialchars($item['workshop_title']) ?></td>
                        <td>
                            <?= htmlspecialchars($item['start_date']) ?>
                            <?php if (!empty($item['start_time'])): ?>
                                <?= ' ' . htmlspecialchars(substr($item['start_time'], 0, 5)) ?>
                            <?php endif; ?>
                        </td>
                        
                        <td><?= htmlspecialchars($item['enrolled_at']) ?></td>
                        <td>
                            <a class="button delete"
                               href="admin_workshops.php?delete_item=<?= (int)$item['id'] ?>"
                               onclick="return confirm('Delete this enrollment?');">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
    </tbody>
  </table>



    </section>
</main>



</body>
</html>



