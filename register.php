<?php
// File: register.php — main logic for register page.
require_once 'includes/db.php';

$error = '';
$success = '';
$state_city_map = [
    'Delhi' => ['New Delhi'],
    'Maharashtra' => ['Mumbai', 'Pune', 'Nagpur'],
    'Karnataka' => ['Bengaluru', 'Mysuru'],
    'Tamil Nadu' => ['Chennai', 'Coimbatore'],
    'Gujarat' => ['Ahmedabad', 'Surat']
];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = $conn->real_escape_string($_POST['phone'] ?? '');
    $address = $conn->real_escape_string($_POST['address'] ?? '');
    $state = $conn->real_escape_string($_POST['state'] ?? '');
    $city = $conn->real_escape_string($_POST['city'] ?? '');
    $pincode = $conn->real_escape_string($_POST['pincode'] ?? '');
    $photo_name = null;

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($phone) || empty($address) || empty($state) || empty($city) || empty($pincode)) {
        $error = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (!preg_match('/^\\d{10}$/', $phone)) {
        $error = "Mobile number must be exactly 10 digits.";
    } elseif (!isset($state_city_map[$state]) || !in_array($city, $state_city_map[$state])) {
        $error = "Please choose a valid state and city combination.";
    } elseif (!preg_match('/^\\d{6}$/', $pincode)) {
        $error = "Pincode must be 6 digits.";
    } else {
        // Handle photo upload if provided
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($_FILES['photo']['type'], $allowed)) {
                $error = "Please upload JPG, PNG, or WEBP image.";
            } else {
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $photo_name = 'user-' . time() . '-' . mt_rand(1000,9999) . '.' . $ext;
                $target = __DIR__ . '/uploads/' . $photo_name;
                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                    $error = "Failed to upload profile photo.";
                }
            }
        }

        // If upload failed, clear photo name
        if (!empty($error)) {
            $photo_name = null;
        }

        // Check if user already exists
        if (empty($error)) {
            $check_user = "SELECT id FROM users WHERE username='$username' OR email='$email'";
            $result = $conn->query($check_user);
            
            if ($result->num_rows > 0) {
                $error = "Username or Email already exists!";
                if ($photo_name) {
                    $saved = __DIR__ . '/uploads/' . $photo_name;
                    if (file_exists($saved)) @unlink($saved);
                    $photo_name = null;
                }
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user
                $photo_sql = $photo_name ? "'$photo_name'" : "NULL";
                $sql = "INSERT INTO users (username, email, password, phone, address, state, city, pincode, photo) VALUES ('$username', '$email', '$hashed_password', '$phone', '$address', '$state', '$city', '$pincode', $photo_sql)";
                
                if ($conn->query($sql)) {
                    $success = "Registration successful! You can now <a href='login.php'>Login</a>.";
                } else {
                    $error = "Error: " . $conn->error;
                }
            }
        }
    }
}

include 'includes/header.php';
?>
<script>
const stateCityMap = <?php echo json_encode($state_city_map); ?>;
function populateCities(stateValue) {
    const citySelect = document.getElementById('city');
    citySelect.innerHTML = '<option value=\"\">Select city</option>';
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
    <div class="col-lg-8 col-xl-7">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">User Registration</h4>
            </div>
            <div class="card-body">
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                                <form action="register.php" method="POST" enctype="multipart/form-data" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Email address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Mobile Number</label>
                        <input type="text" name="phone" pattern="\d{10}" maxlength="10" class="form-control" placeholder="10-digit mobile" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Pincode</label>
                        <input type="text" name="pincode" pattern="\d{6}" maxlength="6" class="form-control" placeholder="6-digit pincode" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label required">Address</label>
                        <input type="text" name="address" class="form-control" placeholder="House / Street" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">State</label>
                        <select name="state" id="state" class="form-select" required>
                            <option value="">Select state</option>
                            <?php foreach($state_city_map as $s => $cities): ?>
                                <option value="<?php echo $s; ?>"><?php echo $s; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">City</label>
                        <select name="city" id="city" class="form-select" required>
                            <option value="">Select city</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Profile Photo (optional)</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" name="register" class="btn btn-primary px-4">Register</button>
                    </div>
                </form>
                <div class="mt-3 text-center">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>


