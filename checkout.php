<?php
// File: checkout.php � main logic for checkout page.
require_once 'includes/db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=Please login to checkout");
    exit();
}

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$subtotal = 0;
$items_to_save = [];

// Calculate totals and prepare items
foreach ($cart_items as $id => $qty) {
    $res = $conn->query("SELECT * FROM products WHERE id = $id");
    $p = $res->fetch_assoc();
    if ($p) {
        $line_total = $p['price'] * $qty;
        $subtotal += $line_total;
        $items_to_save[] = [
            'id' => $id,
            'qty' => $qty,
            'price' => $p['price']
        ];
    }
}

// GST split 18% => CGST 9% + SGST 9%
$cgst = $subtotal * 0.09;
$sgst = $subtotal * 0.09;
$gst_total = $cgst + $sgst;
$grand_total = $subtotal + $gst_total;

$success = false;
$order_id = 0;

if (isset($_POST['place_order'])) {
    // 1. Insert into orders table (store total including GST)
    $sql_order = "INSERT INTO orders (user_id, total_price, status) VALUES ($user_id, $grand_total, 'Pending')";
    if ($conn->query($sql_order)) {
        $order_id = $conn->insert_id;

        // 2. Insert into order_items
        foreach ($items_to_save as $item) {
            $pid = $item['id'];
            $pqty = $item['qty'];
            $pprice = $item['price'];
            $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($order_id, $pid, $pqty, $pprice)");

            // 3. Update stock
            $conn->query("UPDATE products SET stock = stock - $pqty WHERE id = $pid");
        }

        // 4. Clear cart and redirect
        unset($_SESSION['cart']);
        $success = true;
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <?php if ($success): ?>
            <div class="card shadow border-0 text-center py-5">
                <div class="card-body">
                    <div class="mb-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 5rem;"></i>
                        <h2 class="text-success mt-2">Order Placed Successfully!</h2>
                    </div>
                    <h4>Order ID: #<?php echo $order_id; ?></h4>
                    <p class="text-muted">Thank you for your purchase. Your order is being processed.</p>
                    <a href="index.php" class="btn btn-primary btn-lg mt-3">Back to Home</a>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0">Order Summary & Checkout</h4>
                </div>
                <div class="card-body">
                    <h5>Shipping Information</h5>
                    <p class="text-muted mb-4">Username: <strong><?php echo $_SESSION['username']; ?></strong> (Shipping to registered address)</p>

                    <table class="table mb-4">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach ($items_to_save as $item): 
                                $res = $conn->query("SELECT name FROM products WHERE id = " . $item['id']);
                                $p = $res->fetch_assoc();
                            ?>
                            <tr>
                                <td><?php echo $p['name']; ?> (x<?php echo $item['qty']; ?>)</td>
                                <td class="text-end">&#8377;<?php echo number_format($item['price'] * $item['qty'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td>Subtotal</td>
                                <td class="text-end">&#8377;<?php echo number_format($subtotal, 2); ?></td>
                            </tr>
                            <tr>
                                <td>CGST (9%)</td>
                                <td class="text-end">&#8377;<?php echo number_format($cgst, 2); ?></td>
                            </tr>
                            <tr>
                                <td>SGST (9%)</td>
                                <td class="text-end">&#8377;<?php echo number_format($sgst, 2); ?></td>
                            </tr>
                            <tr>
                                <td class="text-uppercase">Grand Total</td>
                                <td class="text-end text-primary fs-5">&#8377;<?php echo number_format($grand_total, 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="alert alert-warning small">
                        <strong>Note:</strong> Demo checkout only. GST @18% (CGST 9% + SGST 9%) is applied. No real payment gateway is integrated. Clicking "Place Order" will simulate a successful transaction.
                    </div>

                    <form method="POST">
                        <div class="d-grid mt-4">
                            <button type="submit" name="place_order" class="btn btn-success btn-lg">Place Order (&#8377;<?php echo number_format($grand_total, 2); ?>)</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="mt-3 text-center">
                <a href="cart.php" class="btn btn-link">Back to Cart</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

