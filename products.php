<?php
// File: products.php - main logic for products page.
require_once 'includes/db.php';

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$safeSearch = htmlspecialchars($search, ENT_QUOTES);

// Build simple filter clause
$where = "WHERE 1=1";
if ($category_id > 0) $where .= " AND p.category_id = $category_id";
if (!empty($search)) $where .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";

$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $where ORDER BY p.id DESC";
$result = $conn->query($sql);

include 'includes/header.php';
?>

<div class="row">
    <div class="col-12 mb-4">
        <h2>
            <?php 
                if ($category_id > 0) {
                    $cat_res = $conn->query("SELECT name FROM categories WHERE id = $category_id");
                    $cat = $cat_res->fetch_assoc();
                    echo "Category: " . htmlspecialchars($cat['name'] ?? 'Products');
                } elseif (!empty($search)) {
                    echo "Search results for: '" . $safeSearch . "'";
                } else {
                    echo "All Products";
                }
            ?>
        </h2>
    </div>

    <?php if($result && $result->num_rows > 0): ?>
        <?php while($p = $result->fetch_assoc()): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card h-100 product-card shadow-sm border-0">
                    <div class="ratio ratio-4x3 bg-light rounded-top overflow-hidden">
                        <?php $img_file = !empty($p['image']) ? $p['image'] : 'placeholder-product.svg'; ?>
                        <?php $imgSrc = 'uploads/' . rawurlencode($img_file); ?>
                        <img src="<?php echo $imgSrc; ?>" class="w-100 h-100" alt="<?php echo htmlspecialchars($p['name']); ?>" style="object-fit: contain; padding: 8px;">
                    </div>
                    <div class="card-body">
                        <h6 class="card-category text-muted x-small"><?php echo htmlspecialchars($p['category_name']); ?></h6>
                        <h5 class="card-title text-truncate"><?php echo htmlspecialchars($p['name']); ?></h5>
                        <p class="text-primary fw-bold">&#8377;<?php echo number_format($p['price'], 2); ?></p>
                    </div>
                    <div class="card-footer bg-white border-0 d-grid pb-3">
                        <a href="product-details.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-primary">View Product</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12 text-center py-5">
            <h4 class="text-muted">No products found matching your criteria.</h4>
            <a href="products.php" class="btn btn-secondary mt-3">Back to All Products</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
