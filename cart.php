<?php
// File: cart.php � main logic for cart page.
require_once 'includes/db.php';

// Handle Item Removal
if (isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php");
    exit();
}

// Handle Quantity Updates
if (isset($_POST['update_cart'])) {
    foreach($_POST['quantities'] as $id => $qty) {
        $qty = (int)$qty;
        if ($qty <= 0) {
            unset($_SESSION['cart'][$id]);
        } else {
            // Cap to actual stock server-side
            $stock_res = $conn->query("SELECT stock FROM products WHERE id = " . (int)$id);
            $stock_row = $stock_res->fetch_assoc();
            $max_stock = $stock_row ? (int)$stock_row['stock'] : $qty;
            $_SESSION['cart'][(int)$id] = min($qty, $max_stock);
        }
    }
    header("Location: cart.php");
    exit();
}

include 'includes/header.php';

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total_price = 0;
?>

<h2 class="mb-4">Your Shopping Cart</h2>

<?php if(empty($cart_items)): ?>
    <div class="alert alert-info text-center py-5 shadow-sm">
        <h4>Your cart is empty!</h4>
        <p>Go back to the products page to add something.</p>
        <a href="products.php" class="btn btn-primary mt-3">Browse Products</a>
    </div>
<?php else: ?>
    <form method="POST">
        <div class="card shadow-sm mb-4">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($cart_items as $id => $qty): 
                            $res = $conn->query("SELECT * FROM products WHERE id = $id");
                            $p = $res->fetch_assoc();
                            if($p):
                                $subtotal = $p['price'] * $qty;
                                $total_price += $subtotal;
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <?php if($p['image']): ?>
                                        <img src="uploads/<?php echo $p['image']; ?>" width="60" class="rounded me-3">
                                    <?php endif; ?>
                                    <a href="product-details.php?id=<?php echo $p['id']; ?>" class="text-decoration-none text-dark fw-bold"><?php echo $p['name']; ?></a>
                                </div>
                            </td>
                            <td>&#8377;<?php echo number_format($p['price'], 2); ?></td>
                            <td style="width: 150px;">
                                <input type="number"
                                    name="quantities[<?php echo $id; ?>]"
                                    value="<?php echo $qty; ?>"
                                    min="1"
                                    max="<?php echo $p['stock']; ?>"
                                    data-stock="<?php echo $p['stock']; ?>"
                                    class="form-control form-control-sm qty-input"
                                    style="width: 80px;"
                                    oninput="this.value = Math.min(Math.max(1, parseInt(this.value)||1), parseInt(this.dataset.stock));">
                                <small class="text-muted d-block mt-1">Max: <?php echo $p['stock']; ?></small>
                            </td>
                            <td>&#8377;<?php echo number_format($subtotal, 2); ?></td>
                            <td class="text-center">
                                <a href="cart.php?remove=<?php echo $id; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove item?')">&times; Remove</a>
                            </td>
                        </tr>
                        <?php endif; endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <button type="submit" name="update_cart" class="btn btn-outline-secondary">Update Cart</button>
                <a href="products.php" class="btn btn-link">Continue Shopping</a>
            </div>
            <div class="col-md-6 text-end">
                <div class="p-3 bg-light rounded shadow-sm border">
                    <h4>Grand Total: <span class="text-primary">&#8377;<?php echo number_format($total_price, 2); ?></span></h4>
                    <hr>
                    <a href="checkout.php" class="btn btn-success btn-lg px-5 mt-2">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    </form>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

