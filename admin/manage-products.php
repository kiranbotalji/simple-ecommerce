<?php
// File: manage-products.php — main logic for manage-products page.
require_once 'auth_check.php';
require_once '../includes/db.php';

$message = '';

// Handle product deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Delete image file first
    $res = $conn->query("SELECT image FROM products WHERE id = $id");
    $product = $res->fetch_assoc();
    if ($product && !empty($product['image'])) {
        $img_path = "../uploads/" . $product['image'];
        if (file_exists($img_path)) unlink($img_path);
    }
    
    $conn->query("DELETE FROM products WHERE id = $id");
    $message = "Product deleted successfully!";
    
    // Clear cache
    $cache_file = "../cache/homepage_products.json";
    if (file_exists($cache_file)) unlink($cache_file);
}

// Fetch products with category names
$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.id DESC";
$products = $conn->query($sql);
?>
<?php
$page_title = 'Manage Products';
include 'includes/header.php';
include 'includes/sidebar.php';
?>
        <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Manage Products</h1>
            <a href="add-product.php" class="btn btn-primary">Add New Product</a>
        </div>

        <?php if($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $products->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td>
                                    <?php if($row['image']): ?>
                                        <img src="../uploads/<?php echo $row['image']; ?>" width="50" height="50" class="rounded">
                                    <?php else: ?>
                                        <div class="bg-secondary text-white rounded text-center" style="width:50px; height:50px; line-height:50px;">N/A</div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['category_name']; ?></td>
                                <td>â‚¹<?php echo number_format($row['price'], 2); ?></td>
                                <td><?php echo $row['stock']; ?></td>
                                <td>
                                    <a href="edit-product.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="manage-products.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
<?php include 'includes/footer.php'; ?>
