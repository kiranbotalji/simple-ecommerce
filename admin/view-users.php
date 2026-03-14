<?php
// File: view-users.php — main logic for view-users page.
require_once 'auth_check.php';
require_once '../includes/db.php';

$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<?php
$page_title = 'View Users';
include 'includes/header.php';
include 'includes/sidebar.php';
?>
        <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Registered Users</h1>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Address</th>
                            <th>State</th>
                            <th>City</th>
                            <th>Pincode</th>
                            <th>Photo</th>
                            <th>Reg. Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['phone'] ?? '-'; ?></td>
                            <td style="max-width:220px;"><?php echo $row['address'] ? htmlspecialchars($row['address']) : '-'; ?></td>
                            <td><?php echo $row['state'] ?? '-'; ?></td>
                            <td><?php echo $row['city'] ?? '-'; ?></td>
                            <td><?php echo $row['pincode'] ?? '-'; ?></td>
                            <td>
                                <?php if(!empty($row['photo'])): ?>
                                    <img src="../uploads/<?php echo $row['photo']; ?>" alt="Photo" class="rounded" style="width:45px;height:45px;object-fit:cover;">
                                <?php else: ?>
                                    <span class="text-muted small">No photo</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
<?php include 'includes/footer.php'; ?>
