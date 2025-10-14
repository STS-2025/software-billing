<?php
require_once 'config.php';
$title = "Edit Category";
include 'header.php';

$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($category_id <= 0) {
    die("Invalid category ID");
}

// Fetch category
$stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    die("Category not found!");
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['category_name']);

    if ($name === '') {
        $message = "Category name cannot be empty!";
    } else {
        $stmt = $pdo->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
        $stmt->execute([$name, $category_id]);
        $message = "Category updated successfully!";
        header("Location: category.php");
        exit;
    }
}
?>

<div class="container mt-4">
    <h2>Edit Category</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label>Category Name</label>
            <input type="text" name="category_name" class="form-control" value="<?= htmlspecialchars($category['category_name']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="category.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<?php include 'footer.php'; ?>
