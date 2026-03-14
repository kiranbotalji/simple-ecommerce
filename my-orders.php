<?php
// File: my-orders.php � main logic for my-orders page.
require_once 'includes/db.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=Please login to view your orders");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Handle order cancellation
if (isset($_POST['cancel_order'])) {
    $cancel_id = (int)$_POST['order_id'];
    // Verify the order belongs to this user
    $check = $conn->query("SELECT id, status, restocked FROM orders WHERE id = $cancel_id AND user_id = $user_id");
    if ($check && $check->num_rows > 0) {
        $ord = $check->fetch_assoc();

        // If already cancelled but not restocked (old orders), restock once
        if ($ord['status'] === 'Cancelled' && (int)$ord['restocked'] === 0) {
            $conn->begin_transaction();
            $items_res = $conn->query("SELECT product_id, quantity FROM order_items WHERE order_id = $cancel_id");
            if ($items_res) {
                while ($it = $items_res->fetch_assoc()) {
                    if (!empty($it['product_id'])) {
                        $pid = (int)$it['product_id'];
                        $qty = (int)$it['quantity'];
                        $conn->query("UPDATE products SET stock = stock + $qty WHERE id = $pid");
                    }
                }
            }
            $conn->query("UPDATE orders SET restocked = 1 WHERE id = $cancel_id");
            $conn->commit();
        }

        // Normal cancellation flow
        if (!in_array($ord['status'], ['Delivered', 'Cancelled'])) {
            $conn->begin_transaction();

            $conn->query("UPDATE orders SET status = 'Cancelled', restocked = 1 WHERE id = $cancel_id");

            $items_res = $conn->query("SELECT product_id, quantity FROM order_items WHERE order_id = $cancel_id");
            if ($items_res) {
                while ($it = $items_res->fetch_assoc()) {
                    if (!empty($it['product_id'])) {
                        $pid = (int)$it['product_id'];
                        $qty = (int)$it['quantity'];
                        $conn->query("UPDATE products SET stock = stock + $qty WHERE id = $pid");
                    }
                }
            }

            $conn->commit();
        }
    }
    header("Location: my-orders.php");
    exit();
}

// Fetch all orders for the logged-in user
$orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC");

include 'includes/header.php';
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <h2 class="fw-bold"><i class="bi bi-bag-check me-2"></i>My Orders</h2>
    <a href="products.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-left me-1"></i>Continue Shopping</a>
</div>

<?php if ($orders->num_rows === 0): ?>
    <div class="alert alert-info text-center py-5 shadow-sm">
        <i class="bi bi-receipt" style="font-size: 3rem;"></i>
        <h4 class="mt-3">No Orders Yet</h4>
        <p>You haven't placed any orders. Start shopping!</p>
        <a href="products.php" class="btn btn-primary mt-2">Browse Products</a>
    </div>
<?php else: ?>
    <div class="accordion shadow-sm" id="ordersAccordion">
        <?php while ($order = $orders->fetch_assoc()): ?>
        <?php
            // Status badge colour
            $status = $order['status'];
            $badge_map = [
                'Delivered'  => 'success',
                'Shipped'    => 'info',
                'Processing' => 'primary',
                'Cancelled'  => 'danger',
                'Pending'    => 'warning'
            ];
            $badge = $badge_map[$status] ?? 'warning';

            // Progress step (1-4)
            $steps = ['Pending' => 1, 'Processing' => 2, 'Shipped' => 3, 'Delivered' => 4, 'Cancelled' => 0];
            $step = $steps[$status] ?? 1;

            // Fetch order items and compute totals
            $items = [];
            $order_subtotal = 0;
            $items_res = $conn->query("SELECT oi.*, p.name, p.image FROM order_items oi
                                       LEFT JOIN products p ON oi.product_id = p.id
                                       WHERE oi.order_id = " . $order['id']);
            if ($items_res) {
                while ($row = $items_res->fetch_assoc()) {
                    $items[] = $row;
                    $order_subtotal += $row['price'] * $row['quantity'];
                }
            }
            $order_cgst = $order_subtotal * 0.09;
            $order_sgst = $order_subtotal * 0.09;
            $order_total_with_gst = $order_subtotal + $order_cgst + $order_sgst;
        ?>
        <div class="accordion-item border-0 mb-3 rounded shadow-sm">
            <h2 class="accordion-header" id="heading<?php echo $order['id']; ?>">
                <button class="accordion-button <?php echo ($status == 'Cancelled') ? 'text-danger' : ''; ?> collapsed rounded"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#order<?php echo $order['id']; ?>"
                        aria-expanded="false">
                    <div class="d-flex w-100 justify-content-between align-items-center pe-3">
                        <span><strong>Order #<?php echo $order['id']; ?></strong>
                            &nbsp;<span class="badge bg-<?php echo $badge; ?>"><?php echo $status; ?></span>
                        </span>
                        <span class="text-muted small">
                            <?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?>
                            &nbsp;|&nbsp;
                            <strong class="text-dark">₹<?php echo number_format($order_total_with_gst, 2); ?></strong>
                        </span>
                    </div>
                </button>
            </h2>

            <div id="order<?php echo $order['id']; ?>" class="accordion-collapse collapse"
                 aria-labelledby="heading<?php echo $order['id']; ?>">
                <div class="accordion-body pt-0">

                    <?php if ($status !== 'Cancelled'): ?>
                    <!-- Progress Tracker -->
                    <div class="my-4">
                        <div class="d-flex justify-content-between position-relative" style="--bs-progress-height: 4px;">
                            <?php
                            $track_steps = [
                                ['icon' => 'bi-clock',         'label' => 'Pending'],
                                ['icon' => 'bi-gear',          'label' => 'Processing'],
                                ['icon' => 'bi-truck',         'label' => 'Shipped'],
                                ['icon' => 'bi-check-circle',  'label' => 'Delivered'],
                            ];
                            foreach ($track_steps as $i => $ts):
                                $done  = ($step > $i + 1);
                                $active = ($step == $i + 1);
                            ?>
                            <div class="text-center" style="flex: 1;">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1"
                                     style="width:40px;height:40px;font-size:1.1rem;
                                            background-color:<?php echo $done ? '#198754' : ($active ? '#0d6efd' : '#dee2e6'); ?>;
                                            color:<?php echo ($done || $active) ? '#fff' : '#6c757d'; ?>;">
                                    <i class="bi <?php echo $done ? 'bi-check-lg' : $ts['icon']; ?>"></i>
                                </div>
                                <div class="small <?php echo $active ? 'fw-bold text-primary' : ($done ? 'text-success' : 'text-muted'); ?>">
                                    <?php echo $ts['label']; ?>
                                </div>
                            </div>
                            <?php if ($i < 3): ?>
                            <div class="align-self-center" style="flex:1; height:3px;
                                 background:<?php echo ($step > $i + 1) ? '#198754' : '#dee2e6'; ?>;
                                 margin-top:-24px;"></div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-danger my-3"><i class="bi bi-x-circle me-2"></i>This order has been <strong>cancelled</strong>.</div>
                    <?php endif; ?>

                    <!-- Items Table -->
                    <table class="table table-sm table-bordered align-middle mt-3 mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if ($item['image']): ?>
                                            <a href="product-details.php?id=<?php echo $item['product_id']; ?>">
                                                <img src="uploads/<?php echo $item['image']; ?>" width="40" height="40" class="rounded object-fit-cover" style="object-fit:cover;">
                                            </a>
                                        <?php endif; ?>
                                        <a href="product-details.php?id=<?php echo $item['product_id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </a>
                                    </div>
                                </td>
                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                <td class="text-end">₹<?php echo number_format($item['price'], 2); ?></td>
                                <td class="text-end fw-bold">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="3" class="text-end">Subtotal</td>
                                <td class="text-end">₹<?php echo number_format($order_subtotal, 2); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end">CGST (9%)</td>
                                <td class="text-end">₹<?php echo number_format($order_cgst, 2); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end">SGST (9%)</td>
                                <td class="text-end">₹<?php echo number_format($order_sgst, 2); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end text-uppercase">Grand Total</td>
                                <td class="text-end text-primary">₹<?php echo number_format($order_total_with_gst, 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- Cancel Order Button -->
                    <?php if (!in_array($status, ['Delivered', 'Cancelled'])): ?>
                    <div class="mt-3 text-end">
                        <form method="POST" onsubmit="return confirm('Are you sure you want to cancel Order #<?php echo $order['id']; ?>? This cannot be undone.');">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <button type="submit" name="cancel_order" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-x-circle me-1"></i> Cancel Order
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
