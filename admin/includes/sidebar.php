<?php
// File: sidebar.php — main logic for sidebar page.
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<nav class="col-md-2 d-none d-md-block sidebar px-0">
    <div class="p-4 text-center text-white">
        <i class="bi bi-person-circle display-4 mb-2"></i>
        <h5 class="mb-0"><?php echo $_SESSION['admin_username']; ?></h5>
        <small class="text-muted">Administrator</small>
    </div>
    <div class="mt-3">
        <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
        <a href="manage-categories.php" class="<?php echo ($current_page == 'manage-categories.php') ? 'active' : ''; ?>">Categories</a>
        <a href="manage-products.php" class="<?php echo ($current_page == 'manage-products.php' || $current_page == 'add-product.php' || $current_page == 'edit-product.php') ? 'active' : ''; ?>">Products</a>
        <a href="view-users.php" class="<?php echo $current_page == 'view-users.php' ? 'active' : ''; ?>">Users</a>
        <a href="view-orders.php" class="<?php echo $current_page == 'view-orders.php' ? 'active' : ''; ?>">Orders</a>
        <a href="manage-hero.php" class="<?php echo $current_page == 'manage-hero.php' ? 'active' : ''; ?>">Homepage Hero</a>
        <hr>
        <a href="../index.php" target="_blank">View Site</a>
        <a href="logout.php" class="text-danger">Logout</a>
    </div>
</nav>
<!-- Main Content -->
<main class="col-md-10 ms-sm-auto px-md-4 py-4">
