<?php
// File: add-product.php — main logic for add-product page.
require_once 'auth_check.php';
require_once '../includes/db.php';

$message = '';
$categories = $conn->query("SELECT * FROM categories");

if (isset($_POST['add_product'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $description = $conn->real_escape_string($_POST['description']);
    
    // Image Upload (saved to /uploads)
    $image = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = realpath(__DIR__ . '/../uploads') ?: (__DIR__ . '/../uploads');
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $allowed = ['image/jpeg','image/png','image/webp'];
        if (in_array($_FILES['image']['type'], $allowed)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = time() . "_" . mt_rand(1000,9999) . "." . $ext;
            $target_file = $target_dir . "/" . $image_name;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image = $image_name;
            }
        }
    }

    $sql = "INSERT INTO products (name, category_id, price, stock, description, image) 
            VALUES ('$name', $category_id, $price, $stock, '$description', '$image')";
    
    if ($conn->query($sql)) {
        $message = "Product added successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>
<?php
$page_title = 'Add Product';
include 'includes/header.php';
include 'includes/sidebar.php';
?>
        <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Add New Product</h1>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">Add New Product</h4>
                    </div>
                    <div class="card-body">
                        <?php if($message): ?>
                            <div class="alert alert-info"><?php echo $message; ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label required">Product Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label required">Category</label>
                                    <select name="category_id" class="form-select" required>
                                        <option value="">Select Category</option>
                                        <?php while($cat = $categories->fetch_assoc()): ?>
                                            <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Price</label>
                                    <input type="number" step="0.01" name="price" class="form-control" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label required">Stock Quantity</label>
                                    <input type="number" name="stock" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Product Image</label>
                                    <input type="file" name="image" class="form-control">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4"></textarea>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="manage-products.php" class="btn btn-secondary">Back to List</a>
                                <button type="submit" name="add_product" class="btn btn-primary">Save Product</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include 'includes/footer.php'; ?>
