<?php
// File: product-details.php � main logic for product-details page.
require_once 'includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = $id";
$result = $conn->query($sql);
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: products.php");
    exit();
}

// Handle Add to Cart
$message = "";
if (isset($_POST['add_to_cart'])) {
    $qty = max(1, (int)$_POST['quantity']);
    $stock = (int)$product['stock'];

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // How many already in cart?
    $already_in_cart = isset($_SESSION['cart'][$id]) ? (int)$_SESSION['cart'][$id] : 0;

    // How many can still be added?
    $can_add = $stock - $already_in_cart;

    if ($can_add <= 0) {
        $message = "<div class='alert alert-warning'>You already have all available stock (<strong>$stock</strong>) in your cart. <a href='cart.php' class='btn btn-sm btn-link'>View Cart</a></div>";
    } else {
        // Cap qty to what's actually available
        $capped_qty = min($qty, $can_add);
        $_SESSION['cart'][$id] = $already_in_cart + $capped_qty;

        if ($capped_qty < $qty) {
            $message = "<div class='alert alert-warning'>Only <strong>$capped_qty</strong> unit(s) added — that's all the remaining stock. <a href='cart.php' class='btn btn-sm btn-link'>View Cart</a></div>";
        } else {
            $message = "<div class='alert alert-success'>Product added to cart! <a href='cart.php' class='btn btn-sm btn-link'>Go to Cart</a></div>";
        }
    }
}

include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-6 mb-4 d-flex justify-content-center">
        <?php 
            $img_file = !empty($product['image']) ? $product['image'] : 'placeholder-product.svg';
            $imgSrc = 'uploads/' . rawurlencode($img_file);
        ?>
        <div class="ratio rounded shadow overflow-hidden bg-light" style="--bs-aspect-ratio: 75%; width: min(100%, 420px);">
            <img src="<?php echo $imgSrc; ?>" class="w-100 h-100" alt="<?php echo htmlspecialchars($product['name']); ?>" style="object-fit: contain;">
        </div>
    </div>
    <div class="col-md-6">
        <h2 class="mb-3"><?php echo $product['name']; ?></h2>
        <h6 class="text-muted mb-4">Category: <?php echo $product['category_name']; ?></h6>
        <h3 class="text-primary mb-1">₹<?php echo number_format($product['price'], 2); ?></h3>
        <div class="text-muted mb-3 small">Price excludes GST. CGST 9% + SGST 9% (total 18%) added at checkout.</div>
        
        <p class="mb-4"><?php echo nl2br($product['description']); ?></p>
        
        <div class="mb-4">
            <span class="badge <?php echo ($product['stock'] > 0) ? 'bg-success' : 'bg-danger'; ?>">
                <?php echo ($product['stock'] > 0) ? 'In Stock ('.$product['stock'].')' : 'Out of Stock'; ?>
            </span>
        </div>

        <?php if($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>


        <?php if($product['stock'] <= 0): ?>
            <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Out of Stock</strong> &mdash; This product is currently unavailable.
            </div>
        <?php endif; ?>

        <form method="POST" class="row g-3 align-items-center">
            <div class="col-auto">
                <label class="form-label">Quantity:</label>
            </div>
            <div class="col-auto">
                <input type="number" name="quantity" value="1" min="1" max="<?php echo max(1, $product['stock']); ?>" class="form-control" style="width: 80px;" <?php if($product['stock'] <= 0) echo 'disabled'; ?>>
            </div>
            <div class="col-auto">
                <?php if($product['stock'] <= 0): ?>
                    <button type="submit" name="add_to_cart" class="btn btn-danger btn-lg" disabled>
                        <i class="bi bi-x-circle me-1"></i> Out of Stock
                    </button>
                <?php else: ?>
                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg">
                        <i class="bi bi-cart-plus me-1"></i> Add to Cart
                    </button>
                <?php endif; ?>
            </div>
        </form>


        
        
    </div>
</div>

<?php include 'includes/footer.php'; ?>
