<?php
// File: manage-hero.php — main logic for manage-hero page.
require_once 'auth_check.php';
require_once '../includes/db.php';

$page_title = 'Homepage Hero';

// Fetch existing hero content
$hero = $conn->query("SELECT * FROM hero_content WHERE id = 1 LIMIT 1")->fetch_assoc();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $headline = trim($_POST['headline'] ?? '');
    $subheadline = trim($_POST['subheadline'] ?? '');
    $cta_text = trim($_POST['cta_text'] ?? '');
    $cta_url = trim($_POST['cta_url'] ?? '');
    $offer_text = trim($_POST['offer_text'] ?? '');
    $image_name = $hero['image'] ?? null;

    if ($headline === '' || $subheadline === '' || $cta_text === '' || $cta_url === '') {
        $errors[] = 'Headline, subheadline, CTA text, and CTA URL are required.';
    }

    // Handle upload
    if (!empty($_FILES['hero_image']['name'])) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($_FILES['hero_image']['type'], $allowed)) {
            $errors[] = 'Only JPG, PNG, or WEBP images are allowed.';
        } else {
            $ext = pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION);
            $new_name = 'hero-' . time() . '.' . $ext;
            $target = __DIR__ . '/../uploads/' . $new_name;
            if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $target)) {
                $image_name = $new_name;
            } else {
                $errors[] = 'Failed to upload image. Please try again.';
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE hero_content SET headline=?, subheadline=?, cta_text=?, cta_url=?, offer_text=?, image=? WHERE id=1");
        $stmt->bind_param('ssssss', $headline, $subheadline, $cta_text, $cta_url, $offer_text, $image_name);
        $stmt->execute();
        $stmt->close();

        $success = 'Homepage hero updated successfully.';

        // Refresh hero data
        $hero = $conn->query("SELECT * FROM hero_content WHERE id = 1 LIMIT 1")->fetch_assoc();
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 mb-0">Homepage Hero</h1>
                    <div class="text-muted">Customize the banner on the public homepage.</div>
                </div>

                <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach($errors as $err): ?>
                            <div><?php echo htmlspecialchars($err); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">Headline</label>
                                <input type="text" name="headline" class="form-control" value="<?php echo htmlspecialchars($hero['headline'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Subheadline</label>
                                <input type="text" name="subheadline" class="form-control" value="<?php echo htmlspecialchars($hero['subheadline'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">CTA Text</label>
                                <input type="text" name="cta_text" class="form-control" value="<?php echo htmlspecialchars($hero['cta_text'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label required">CTA URL</label>
                                <input type="text" name="cta_url" class="form-control" value="<?php echo htmlspecialchars($hero['cta_url'] ?? ''); ?>" placeholder="products.php" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Offer Badge Text</label>
                                <input type="text" name="offer_text" class="form-control" value="<?php echo htmlspecialchars($hero['offer_text'] ?? ''); ?>" placeholder="e.g., Summer sale: 30% off" />
                                <div class="form-text">Leave blank to hide the badge.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hero Background Image</label>
                                <input type="file" name="hero_image" class="form-control" accept="image/*">
                                <?php if(!empty($hero['image'])): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">Current:</small><br>
                                        <img src="../uploads/<?php echo htmlspecialchars($hero['image']); ?>" alt="Hero" class="img-thumbnail" style="max-height: 150px;">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12 d-flex justify-content-end gap-2">
                                <a href="../index.php" target="_blank" class="btn btn-outline-secondary">Preview Site</a>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>

            </main>
<?php include 'includes/footer.php'; ?>
