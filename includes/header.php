<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple E-commerce</title>
    <link rel="icon" href="uploads/favicon.ico">
    <link rel="shortcut icon" href="uploads/favicon.ico">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .product-card {
            transition: transform 0.2s;
        }
        .product-card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: bold;
            color: #2c3e50 !important;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .brand-logo {
            width: 36px;
            height: 36px;
            object-fit: contain;
        }
        .required::after {
            content: " *";
            color: #dc3545;
            font-weight: 600;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
<?php
$user_photo = null;
if (isset($_SESSION['user_id'])) {
    // Prefer session photo, fall back to DB lookup
    if (!empty($_SESSION['photo'])) {
        $user_photo = $_SESSION['photo'];
    } else {
        $resPhoto = $conn->query("SELECT photo FROM users WHERE id = " . (int)$_SESSION['user_id'] . " LIMIT 1");
        if ($resPhoto && $resPhoto->num_rows === 1) {
            $rowP = $resPhoto->fetch_assoc();
            $user_photo = $rowP['photo'] ?? null;
            $_SESSION['photo'] = $user_photo;
        }
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="uploads/logo.svg" alt="ShopEase logo" class="brand-logo">
                ShopEase
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Products</a>
                    </li>
                </ul>
                <form class="d-flex me-3" action="products.php" method="GET">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search products...">
                    <button class="btn btn-outline-success" type="submit">Search</button>
                </form>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            Cart <span class="badge bg-primary"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
                        </a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="my-orders.php"><i class="bi bi-bag-check"></i> My Orders</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2" href="profile.php">
                                <?php if(!empty($user_photo)): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($user_photo); ?>" alt="Me" class="rounded-circle" style="width:28px;height:28px;object-fit:cover;">
                                <?php else: ?>
                                    <i class="bi bi-person-circle"></i>
                                <?php endif; ?>
                                <span>Profile</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container pb-5 flex-grow-1">
