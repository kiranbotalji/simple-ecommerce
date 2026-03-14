<?php
// File: dashboard.php — main logic for dashboard page.
require_once 'auth_check.php';
require_once '../includes/db.php';

// Stats for dashboard
$product_count = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
$category_count = $conn->query("SELECT COUNT(*) as total FROM categories")->fetch_assoc()['total'];
$user_count = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$order_count = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];

?>
<?php
$page_title = 'Dashboard';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <div class="welcome-header">
                        <h1 class="h2 mb-0">Dashboard</h1>
                    </div>
                    <div class="text-muted">Welcome, <span class="fw-bold text-dark"><?php echo $_SESSION['admin_username']; ?></span></div>
                </div>

                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="card stats-card bg-white border-start border-primary border-4 mb-4">
                            <div class="card-body">
                                <h3 class="text-primary"><?php echo $product_count; ?></h3>
                                <p class="text-muted mb-0">Total Products</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card bg-white border-start border-success border-4 mb-4">
                            <div class="card-body">
                                <h3 class="text-success"><?php echo $category_count; ?></h3>
                                <p class="text-muted mb-0">Categories</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card bg-white border-start border-info border-4 mb-4">
                            <div class="card-body">
                                <h3 class="text-info"><?php echo $user_count; ?></h3>
                                <p class="text-muted mb-0">Registered Users</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card bg-white border-start border-warning border-4 mb-4">
                            <div class="card-body">
                                <h3 class="text-warning"><?php echo $order_count; ?></h3>
                                <p class="text-muted mb-0">Total Orders</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5">
                    <h4>Recent Activity</h4>
                    <p class="text-muted">Quick links to common tasks:</p>
                    <div class="list-group">
                        <a href="add-product.php" class="list-group-item list-group-item-action">Add New Product</a>
                        <a href="manage-products.php" class="list-group-item list-group-item-action">Manage Inventory</a>
                        <a href="view-orders.php" class="list-group-item list-group-item-action">Process Pending Orders</a>
                    </div>
                </div>
            </main>
<?php include 'includes/footer.php'; ?>
