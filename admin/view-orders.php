<?php
// File: view-orders.php — main logic for view-orders page.
require_once 'auth_check.php';
require_once '../includes/db.php';

// Handle Status Update
if(isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE orders SET status = '$status' WHERE id = $order_id");
}

$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
$status_where = '';
if ($status_filter && $status_filter !== 'All') {
    $status_where = "WHERE o.status = '$status_filter'";
}

$orders = $conn->query("SELECT o.*, u.username FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        $status_where
                        ORDER BY o.created_at DESC");
?>
<?php
$page_title = 'View Orders';
include 'includes/header.php';
include 'includes/sidebar.php';
?>
        <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2 mb-0">Manage Orders</h1>
            <form method="GET" class="d-flex align-items-center gap-2">
                <label class="form-label mb-0">Status:</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php 
                        $statuses = ['All','Pending','Processing','Shipped','Delivered','Cancelled'];
                        foreach($statuses as $s):
                    ?>
                        <option value="<?php echo $s; ?>" <?php echo ($status_filter === $s || ($status_filter==='' && $s==='All')) ? 'selected' : ''; ?>>
                            <?php echo $s; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <noscript><button class="btn btn-sm btn-primary">Filter</button></noscript>
            </form>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Order ID</th>
                            <th>User</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td>&#8377;<?php echo number_format($row['total_price'], 2); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo ($row['status'] == 'Delivered') ? 'success' : 
                                         (($row['status'] == 'Cancelled') ? 'danger' : 'warning'); 
                                ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <form method="POST" class="d-flex">
                                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                    <select name="status" class="form-select form-select-sm me-2">
                                        <?php 
                                            $all_statuses = ['Pending','Processing','Shipped','Delivered','Cancelled'];
                                            foreach($all_statuses as $opt):
                                        ?>
                                            <option value="<?php echo $opt; ?>" <?php echo ($row['status']===$opt)?'selected':''; ?>>
                                                <?php echo $opt; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-sm btn-info">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
<?php include 'includes/footer.php'; ?>
