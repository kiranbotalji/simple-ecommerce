<?php
// File: index.php � main logic for index page.
require_once 'includes/db.php';

$products = [];
$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC LIMIT 8";
$result = $conn->query($sql);
while($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$categories = $conn->query("SELECT * FROM categories LIMIT 6");

$hero = $conn->query("SELECT * FROM hero_content WHERE id = 1 LIMIT 1")->fetch_assoc();
$hero_image = $hero['image'] ?? '';
if (empty($hero_image)) {
    // Use bundled fallback hero background
    $hero_image = '1773396492_DL-Technology.jpg';
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<?php
    $hasImage = !empty($hero_image);
    $hero_bg = 'linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%)';
    if ($hasImage) {
        $hero_bg = "linear-gradient(135deg, rgba(0,0,0,0.35), rgba(0,0,0,0.55)), url('uploads/" . htmlspecialchars($hero_image) . "') center/cover";
    }
?>
<div class="p-5 mb-4 rounded-3 shadow-sm <?php echo $hasImage ? 'text-white' : ''; ?>" style="background: <?php echo $hero_bg; ?>;">
    <div class="container-fluid py-5">
        <?php if(!empty($hero['offer_text'])): ?>
            <span class="badge bg-warning text-dark mb-3 px-3 py-2 shadow-sm"><?php echo htmlspecialchars($hero['offer_text']); ?></span>
        <?php endif; ?>
        <h1 class="display-5 fw-bold" style="text-shadow: 0 2px 6px rgba(0,0,0,0.2); color: <?php echo $hasImage ? '#fff' : '#0d6efd'; ?>;"><?php echo htmlspecialchars($hero['headline'] ?? 'Simple E-commerce'); ?></h1>
        <p class="col-md-8 fs-4" style="text-shadow: 0 1px 3px rgba(0,0,0,0.3); color: <?php echo $hasImage ? '#f8f9fa' : '#34495e'; ?>;">
            <?php echo htmlspecialchars($hero['subheadline'] ?? 'A clean PHP & MySQL store template—easy to browse, easy to extend.'); ?>
        </p>
        <a href="<?php echo htmlspecialchars($hero['cta_url'] ?? 'products.php'); ?>" class="btn btn-primary btn-lg"><?php echo htmlspecialchars($hero['cta_text'] ?? 'Shop Now'); ?></a>
    </div>
</div>

<div class="row">
    <!-- Categories Sidebar -->
    <div class="col-md-3">
        <h4 class="mb-3">Categories</h4>
        <div class="list-group shadow-sm">
            <a href="products.php" class="list-group-item list-group-item-action">All Products</a>
            <?php while($cat = $categories->fetch_assoc()): ?>
                <a href="products.php?category=<?php echo $cat['id']; ?>" class="list-group-item list-group-item-action">
                    <?php echo $cat['name']; ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Featured Products -->
    <div class="col-md-9">
        <h4 class="mb-3 d-flex justify-content-between">
            Latest Products
        </h4>
        <div class="row g-4">
            <?php if(empty($products)): ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No products found. Add some from the admin panel!</p>
                </div>
            <?php else: ?>
                <?php foreach($products as $p): ?>
                <div class="col-md-4 col-sm-6">
                    <div class="card h-100 product-card shadow-sm border-0">
                        <div class="ratio ratio-4x3 bg-light rounded-top overflow-hidden">
                            <?php if($p['image']): ?>
                                <img src="uploads/<?php echo $p['image']; ?>" alt="<?php echo $p['name']; ?>" class="w-100 h-100" style="object-fit: contain; padding: 8px;">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center text-muted w-100 h-100 small">
                                    No Image
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title text-truncate"><?php echo $p['name']; ?></h5>
                            <p class="text-primary fw-bold">₹<?php echo number_format($p['price'], 2); ?></p>
                            <p class="card-text text-muted small text-truncate"><?php echo $p['description']; ?></p>
                        </div>
                        <div class="card-footer bg-white border-0 d-grid pb-3">
                            <a href="product-details.php?id=<?php echo $p['id']; ?>" class="btn btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
