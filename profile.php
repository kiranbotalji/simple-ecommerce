<?php
// File: profile.php — main logic for profile page.
require_once 'includes/db.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=Please login to manage your profile");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$message = '';
$error = '';

// Fetch user
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

$state_city_map = [
    'Delhi' => ['New Delhi'],
    'Maharashtra' => ['Mumbai', 'Pune', 'Nagpur'],
    'Karnataka' => ['Bengaluru', 'Mysuru'],
    'Tamil Nadu' => ['Chennai', 'Coimbatore'],
    'Gujarat' => ['Ahmedabad', 'Surat']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $phone = $conn->real_escape_string($_POST['phone'] ?? '');
    $address = $conn->real_escape_string($_POST['address'] ?? '');
    $state = $conn->real_escape_string($_POST['state'] ?? '');
    $city = $conn->real_escape_string($_POST['city'] ?? '');
    $pincode = $conn->real_escape_string($_POST['pincode'] ?? '');

    // Email uniqueness check
    $check = $conn->query("SELECT id FROM users WHERE email = '$email' AND id <> $user_id");
    if ($check && $check->num_rows > 0) {
        $error = "Email is already in use by another account.";
    }

    // Phone validation: exactly 10 digits
    if (!$error && !preg_match('/^\\d{10}$/', $phone)) {
        $error = "Mobile number must be exactly 10 digits.";
    }

    // State/City validation
    if (!$error && (!isset($state_city_map[$state]) || !in_array($city, $state_city_map[$state]))) {
        $error = "Please select a valid state and city.";
    }

    // Pincode validation: 6 digits
    if (!$error && !preg_match('/^\\d{6}$/', $pincode)) {
        $error = "Pincode must be 6 digits.";
    }

    // Handle avatar upload
    $photo_name = $user['photo'];
    if (!$error && isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $allowed = ['image/jpeg','image/png','image/webp'];
        if (!in_array($_FILES['photo']['type'], $allowed)) {
            $error = "Please upload JPG, PNG, or WEBP.";
        } else {
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $photo_name = 'user-' . $user_id . '-' . time() . '.' . $ext;
            $target = __DIR__ . '/uploads/' . $photo_name;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                // remove old photo
                if (!empty($user['photo'])) {
                    $old = __DIR__ . '/uploads/' . $user['photo'];
                    if (file_exists($old)) @unlink($old);
                }
            } else {
                $error = "Failed to upload photo.";
            }
        }
    }

    // Password change
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';
    $password_sql = '';
    if (!$error && ($new_pass || $confirm_pass)) {
        if ($new_pass !== $confirm_pass) {
            $error = "New passwords do not match.";
        } elseif (strlen($new_pass) < 6) {
            $error = "Password should be at least 6 characters.";
        } else {
            $password_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $password_sql = ", password = '$password_hash'";
        }
    }

    if (!$error) {
        $conn->query("UPDATE users SET email='$email', phone='$phone', address='$address', state='$state', city='$city', pincode='$pincode', photo=" . ($photo_name ? "'$photo_name'" : "NULL") . " $password_sql WHERE id = $user_id");
        $message = "Profile updated successfully.";
        // refresh user data & session username/email maybe
        $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
        $_SESSION['username'] = $user['username'];
        $_SESSION['photo'] = $user['photo'];
    }
}

include 'includes/header.php';
?>
<script>
const stateCityMap = <?php echo json_encode($state_city_map); ?>;
function populateCities(stateValue) {
    const citySelect = document.getElementById('city');
    citySelect.innerHTML = '<option value="">Select city</option>';
    if (!stateCityMap[stateValue]) return;
    stateCityMap[stateValue].forEach(city => {
        const opt = document.createElement('option');
        opt.value = city;
        opt.textContent = city;
        citySelect.appendChild(opt);
    });
}
document.addEventListener('DOMContentLoaded', () => {
    const stateSelect = document.getElementById('state');
    if (stateSelect) {
        stateSelect.addEventListener('change', e => populateCities(e.target.value));
    }
});
</script>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">My Profile</h4>
            </div>
            <div class="card-body">
                <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
                <?php if($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Mobile Number</label>
                        <input type="text" name="phone" pattern="\d{10}" maxlength="10" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="10-digit mobile" required>
                        <div class="form-text">Exactly 10 digits.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Street Address</label>
                        <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" placeholder="House / Street" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">State</label>
                        <select name="state" id="state" class="form-select" required>
                            <option value="">Select state</option>
                            <?php foreach($state_city_map as $s => $cities): ?>
                                <option value="<?php echo $s; ?>" <?php echo ($user['state'] ?? '') === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">City</label>
                        <select name="city" id="city" class="form-select" required>
                            <option value="">Select city</option>
                            <?php 
                                $current_state = $user['state'] ?? '';
                                $current_city = $user['city'] ?? '';
                                if ($current_state && isset($state_city_map[$current_state])):
                                    foreach($state_city_map[$current_state] as $c):
                            ?>
                                <option value="<?php echo $c; ?>" <?php echo ($current_city === $c) ? 'selected' : ''; ?>><?php echo $c; ?></option>
                            <?php 
                                    endforeach;
                                endif;
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Pincode</label>
                        <input type="text" name="pincode" pattern="\d{6}" maxlength="6" class="form-control" value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>" placeholder="6-digit pincode" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Profile Photo</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <?php if(!empty($user['photo'])): ?>
                                <img src="uploads/<?php echo $user['photo']; ?>" alt="Photo" class="rounded" style="width:60px;height:60px;object-fit:cover;">
                            <?php else: ?>
                                <span class="text-muted small">No photo</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Change Password (optional)</label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="password" name="new_password" class="form-control" placeholder="New password">
                            </div>
                            <div class="col-md-6">
                                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password">
                            </div>
                        </div>
                        <div class="form-text">Leave blank if you don't want to change password.</div>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-outline-secondary">Back</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
