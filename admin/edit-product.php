<?php
// File: edit-product.php — main logic for edit-product page.
require_once 'auth_check.php';
require_once '../includes/db.php';

$message = '';
$id = (int)$_GET['id'];

// Fetch product data
$res = $conn->query("SELECT * FROM products WHERE id = $id");
$product = $res->fetch_assoc();

if (!$product) {
    header("Location: manage-products.php");
    exit();
}

$categories = $conn->query("SELECT * FROM categories");

if (isset($_POST['update_product'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $description = $conn->real_escape_string($_POST['description']);
    
    $image_sql = "";
    // Image Update (saved to /uploads)
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
                // Delete old image
                if (!empty($product['image'])) {
                    $old_img = $target_dir . "/" . $product['image'];
                    if (file_exists($old_img)) unlink($old_img);
                }
                $image_sql = ", image = '$image_name'";
            }
        }
    }

    $sql = "UPDATE products SET 
            name = '$name', 
            category_id = $category_id, 
            price = $price, 
            stock = $stock, 
            description = '$description' 
            $image_sql 
            WHERE id = $id";
    
    if ($conn->query($sql)) {
        header("Location: manage-products.php?message=Product updated");
        exit();
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>
<?php
$page_title = 'Edit Product';
include 'includes/header.php';
include 'includes/sidebar.php';
?>
<script>
function previewImage(evt) {
    const file = evt.target.files[0];
    const wrap = document.getElementById('newPreviewWrap');
    const img = document.getElementById('newPreview');
    if (!file) {
        if (wrap) wrap.classList.add('d-none');
        return;
    }
    const reader = new FileReader();
    reader.onload = e => {
        img.src = e.target.result;
        wrap.classList.remove('d-none');
    };
    reader.readAsDataURL(file);
}
</script>
        <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Edit Product</h1>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">Edit Product</h4>
                    </div>
                    <div class="card-body">
                        <?php if($message): ?>
                            <div class="alert alert-danger"><?php echo $message; ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label required">Product Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo $product['name']; ?>" required>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label required">Category</label>
                                    <select name="category_id" class="form-select" required>
                                        <?php while($cat = $categories->fetch_assoc()): ?>
                                            <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $product['category_id']) ? 'selected' : ''; ?>>
                                                <?php echo $cat['name']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Price</label>
                                    <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label required">Stock Quantity</label>
                                    <input type="number" name="stock" class="form-control" value="<?php echo $product['stock']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Update Image (Optional)</label>
                                    <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(event)">
                                    <?php if($product['image']): ?>
                                        <div class="mt-2">
                                            <small class="text-muted d-block">Current:</small>
                                            <div class="ratio ratio-4x3 bg-light rounded overflow-hidden" style="max-width: 200px;">
                                                <img id="currentPreview" src="../uploads/<?php echo $product['image']; ?>" alt="Current image" class="w-100 h-100" style="object-fit: contain; padding:6px;">
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="mt-2 text-muted small">No current image</div>
                                    <?php endif; ?>
                                    <div id="newPreviewWrap" class="mt-2 d-none">
                                        <small class="text-muted d-block">New preview:</small>
                                        <div class="ratio ratio-4x3 bg-light rounded overflow-hidden" style="max-width: 200px;">
                                            <img id="newPreview" src="" alt="New preview" class="w-100 h-100" style="object-fit: contain; padding:6px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4"><?php echo $product['description']; ?></textarea>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="manage-products.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" name="update_product" class="btn btn-warning">Update Product</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
<?php include 'includes/footer.php'; ?>
