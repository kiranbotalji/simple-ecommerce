<?php
// File: manage-categories.php — main logic for manage-categories page.
require_once 'auth_check.php';
require_once '../includes/db.php';

$message = '';

// Handle category creation
if (isset($_POST['add_category'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $desc = $conn->real_escape_string($_POST['description']);
    $conn->query("INSERT INTO categories (name, description) VALUES ('$name', '$desc')");
    $message = "Category added successfully!";
}

// Handle category update
if (isset($_POST['update_category'])) {
    $id = (int)$_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    $desc = $conn->real_escape_string($_POST['description']);
    $conn->query("UPDATE categories SET name='$name', description='$desc' WHERE id = $id");
    $message = "Category updated!";
}

// Handle category deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM categories WHERE id = $id");
    $message = "Category deleted!";
}

// Fetch categories
$categories = $conn->query("SELECT * FROM categories ORDER BY id DESC");
?>
<?php
$page_title = 'Manage Categories';
include 'includes/header.php';
include 'includes/sidebar.php';
?>
        <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Manage Categories</h1>
        </div>

        <?php if($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">Add New Category</div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="name" class="form-control" placeholder="Category Name" required>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="description" class="form-control" placeholder="Description">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="add_category" class="btn btn-primary w-100">Add</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $categories->fetch_assoc()): ?>
                        <tr>
                            <form method="POST" class="row g-2 align-items-center">
                                <td class="col-1"><?php echo $row['id']; ?>
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                </td>
                                <td class="col-3"><input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" class="form-control form-control-sm" required></td>
                                <td class="col-6"><input type="text" name="description" value="<?php echo htmlspecialchars($row['description']); ?>" class="form-control form-control-sm"></td>
                                <td class="col-2 d-flex gap-1">
                                    <button type="submit" name="update_category" class="btn btn-sm btn-success">Save</button>
                                    <a href="manage-categories.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                </td>
                            </form>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
<?php include 'includes/footer.php'; ?>
