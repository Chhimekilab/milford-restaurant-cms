<?php
session_start();

// Configuration
define('DATA_FILE', '../data/restaurant-data.json');
define('ADMIN_PASSWORD', 'milford2025'); // Change this!
define('DATA_DIR', '../data/');

// Ensure data directory exists
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

// Authentication
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_POST['password'] ?? '' === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        showLoginForm();
        exit;
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Load current data
function loadData() {
    if (file_exists(DATA_FILE)) {
        return json_decode(file_get_contents(DATA_FILE), true);
    }
    return getDefaultData();
}

// Save data automatically
function saveData($data) {
    $json = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents(DATA_FILE, $json);
    
    // Also update the JS file for direct website integration
    $jsContent = "window.restaurantData = " . $json . ";";
    file_put_contents('../js/restaurant-data.js', $jsContent);
    
    return true;
}

// Default data structure
function getDefaultData() {
    return [
        'info' => [
            'name' => 'Milford India Spice',
            'tagline' => 'Authentic Indian Cuisine',
            'phone' => '(508) 478-5800',
            'address' => '123 Main Street, Milford, MA 01757',
            'email' => 'info@milfordindispice.com',
            'hours' => [
                'monday' => '11:00 AM - 9:00 PM',
                'tuesday' => '11:00 AM - 9:00 PM',
                'wednesday' => '11:00 AM - 9:00 PM',
                'thursday' => '11:00 AM - 9:00 PM',
                'friday' => '11:00 AM - 11:00 PM',
                'saturday' => '11:00 AM - 10:00 PM',
                'sunday' => '12:00 PM - 9:00 PM'
            ],
            'socialLinks' => [
                'facebook' => '',
                'instagram' => '',
                'twitter' => '',
                'tiktok' => ''
            ]
        ],
        'menuItems' => [],
        'categories' => ['Appetizer', 'Soup', 'Main Dish', 'Biryani', 'Chicken', 'Vegetarian', 'Dessert', 'Beverages'],
        'announcements' => [],
        'orderingLinks' => [
            'ubereats' => '',
            'doordash' => '',
            'grubhub' => '',
            'website' => ''
        ]
    ];
}

// Handle form submissions
if ($_POST) {
    $data = loadData();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'save_info':
            $data['info']['name'] = $_POST['name'] ?? '';
            $data['info']['tagline'] = $_POST['tagline'] ?? '';
            $data['info']['phone'] = $_POST['phone'] ?? '';
            $data['info']['address'] = $_POST['address'] ?? '';
            $data['info']['email'] = $_POST['email'] ?? '';
            
            foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
                $data['info']['hours'][$day] = $_POST["hours_$day"] ?? '';
            }
            
            foreach (['facebook', 'instagram', 'twitter', 'tiktok'] as $platform) {
                $data['info']['socialLinks'][$platform] = $_POST["social_$platform"] ?? '';
            }
            
            saveData($data);
            $success = "Restaurant information updated successfully!";
            break;
            
        case 'add_menu_item':
            $newItem = [
                'id' => time(),
                'name' => $_POST['item_name'] ?? '',
                'price' => floatval($_POST['item_price'] ?? 0),
                'description' => $_POST['item_description'] ?? '',
                'category' => $_POST['item_category'] ?? 'Main Dish',
                'available' => true,
                'availabilityStatus' => 'available',
                'unavailableReason' => '',
                'estimatedBackDate' => '',
                'isSpicy' => isset($_POST['item_spicy']),
                'isVegetarian' => isset($_POST['item_vegetarian']),
                'allergens' => $_POST['item_allergens'] ?? '',
                'preparationTime' => $_POST['item_prep_time'] ?? '15-20 mins',
                'image' => ''
            ];
            $data['menuItems'][] = $newItem;
            saveData($data);
            $success = "Menu item added successfully!";
            break;
            
        case 'update_stock':
            $itemId = intval($_POST['item_id']);
            foreach ($data['menuItems'] as &$item) {
                if ($item['id'] == $itemId) {
                    $item['available'] = $_POST['available'] === 'true';
                    $item['availabilityStatus'] = $_POST['status'] ?? 'available';
                    $item['unavailableReason'] = $_POST['reason'] ?? '';
                    $item['estimatedBackDate'] = $_POST['back_date'] ?? '';
                    break;
                }
            }
            saveData($data);
            $success = "Stock status updated successfully!";
            break;
            
        case 'delete_menu_item':
            $itemId = intval($_POST['item_id']);
            $data['menuItems'] = array_filter($data['menuItems'], function($item) use ($itemId) {
                return $item['id'] != $itemId;
            });
            $data['menuItems'] = array_values($data['menuItems']);
            saveData($data);
            $success = "Menu item deleted successfully!";
            break;
            
        case 'add_announcement':
            $newAnn = [
                'id' => time(),
                'title' => $_POST['ann_title'] ?? '',
                'content' => $_POST['ann_content'] ?? '',
                'active' => isset($_POST['ann_active']),
                'priority' => $_POST['ann_priority'] ?? 'medium',
                'expiryDate' => $_POST['ann_expiry'] ?? ''
            ];
            $data['announcements'][] = $newAnn;
            saveData($data);
            $success = "Announcement added successfully!";
            break;
            
        case 'update_ordering':
            foreach (['ubereats', 'doordash', 'grubhub', 'website'] as $platform) {
                $data['orderingLinks'][$platform] = $_POST["ordering_$platform"] ?? '';
            }
            saveData($data);
            $success = "Ordering links updated successfully!";
            break;
    }
}

$data = loadData();

function showLoginForm() {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Milford India Spice - Admin Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-danger">
        <div class="container d-flex justify-content-center align-items-center min-vh-100">
            <div class="card" style="width: 400px;">
                <div class="card-header bg-danger text-white text-center">
                    <h4>🍛 Admin Access</h4>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Admin Password:</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Milford India Spice - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-danger">
        <div class="container-fluid">
            <span class="navbar-brand">🍛 Restaurant Admin Panel</span>
            <a href="?logout=1" class="btn btn-outline-light">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs" id="adminTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#restaurant-info">
                    <i class="fas fa-info-circle"></i> Restaurant Info
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#menu-management">
                    <i class="fas fa-utensils"></i> Menu Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#stock-management">
                    <i class="fas fa-boxes"></i> Stock Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#announcements">
                    <i class="fas fa-bullhorn"></i> Announcements
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#ordering-links">
                    <i class="fas fa-shopping-cart"></i> Ordering Links
                </a>
            </li>
        </ul>

        <div class="tab-content mt-4">
            <!-- Restaurant Info Tab -->
            <div class="tab-pane fade show active" id="restaurant-info">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-store"></i> Restaurant Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="action" value="save_info">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Restaurant Name</label>
                                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($data['info']['name']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tagline</label>
                                        <input type="text" name="tagline" class="form-control" value="<?= htmlspecialchars($data['info']['tagline']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($data['info']['phone']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['info']['email']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Address</label>
                                        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($data['info']['address']) ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6>Hours of Operation</h6>
                                    <?php foreach ($data['info']['hours'] as $day => $hours): ?>
                                        <div class="mb-2">
                                            <label class="form-label text-capitalize"><?= $day ?></label>
                                            <input type="text" name="hours_<?= $day ?>" class="form-control" value="<?= htmlspecialchars($hours) ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <h6 class="mt-4">Social Media Links</h6>
                            <div class="row">
                                <?php foreach ($data['info']['socialLinks'] as $platform => $url): ?>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label text-capitalize"><?= $platform ?></label>
                                            <input type="url" name="social_<?= $platform ?>" class="form-control" value="<?= htmlspecialchars($url) ?>">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Restaurant Info
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Menu Management Tab -->
            <div class="tab-pane fade" id="menu-management">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-utensils"></i> Add New Menu Item</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="action" value="add_menu_item">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Item Name</label>
                                        <input type="text" name="item_name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label">Price ($)</label>
                                        <input type="number" step="0.01" name="item_price" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Category</label>
                                        <select name="item_category" class="form-control">
                                            <?php foreach ($data['categories'] as $category): ?>
                                                <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Prep Time</label>
                                        <input type="text" name="item_prep_time" class="form-control" value="15-20 mins">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="item_description" class="form-control" rows="2"></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Allergens (optional)</label>
                                        <input type="text" name="item_allergens" class="form-control" placeholder="Contains dairy, nuts...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="item_spicy">
                                        <label class="form-check-label">🌶️ Spicy</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="item_vegetarian">
                                        <label class="form-check-label">🌱 Vegetarian</label>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add Menu Item
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Current Menu Items -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Current Menu Items (<?= count($data['menuItems']) ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($data['menuItems'])): ?>
                            <p class="text-muted">No menu items yet. Add your first item above!</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Price</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['menuItems'] as $item): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($item['name']) ?></strong>
                                                    <?php if ($item['isSpicy']): ?><span class="text-danger">🌶️</span><?php endif; ?>
                                                    <?php if ($item['isVegetarian']): ?><span class="text-success">🌱</span><?php endif; ?>
                                                    <br>
                                                    <small class="text-muted"><?= htmlspecialchars($item['description']) ?></small>
                                                </td>
                                                <td>$<?= number_format($item['price'], 2) ?></td>
                                                <td><?= htmlspecialchars($item['category']) ?></td>
                                                <td>
                                                    <?php if ($item['available']): ?>
                                                        <span class="badge bg-success">Available</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger"><?= ucwords(str_replace('-', ' ', $item['availabilityStatus'])) ?></span>
                                                        <?php if ($item['unavailableReason']): ?>
                                                            <br><small class="text-muted"><?= htmlspecialchars($item['unavailableReason']) ?></small>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <form method="post" style="display: inline;">
                                                        <input type="hidden" name="action" value="delete_menu_item">
                                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this item?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Stock Management Tab -->
            <div class="tab-pane fade" id="stock-management">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-boxes"></i> Stock Management</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($data['menuItems'])): ?>
                            <p class="text-muted">Add menu items first to manage stock.</p>
                        <?php else: ?>
                            <?php foreach ($data['menuItems'] as $item): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                <h6><?= htmlspecialchars($item['name']) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($item['category']) ?> - $<?= number_format($item['price'], 2) ?></small>
                                            </div>
                                            <div class="col-md-8">
                                                <form method="post" class="row">
                                                    <input type="hidden" name="action" value="update_stock">
                                                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                                    
                                                    <div class="col-md-3">
                                                        <select name="available" class="form-control">
                                                            <option value="true" <?= $item['available'] ? 'selected' : '' ?>>Available</option>
                                                            <option value="false" <?= !$item['available'] ? 'selected' : '' ?>>Unavailable</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="col-md-3">
                                                        <select name="status" class="form-control">
                                                            <option value="available" <?= $item['availabilityStatus'] == 'available' ? 'selected' : '' ?>>Available</option>
                                                            <option value="out-of-stock" <?= $item['availabilityStatus'] == 'out-of-stock' ? 'selected' : '' ?>>Out of Stock</option>
                                                            <option value="seasonal" <?= $item['availabilityStatus'] == 'seasonal' ? 'selected' : '' ?>>Seasonal</option>
                                                            <option value="temporarily-unavailable" <?= $item['availabilityStatus'] == 'temporarily-unavailable' ? 'selected' : '' ?>>Temp Unavailable</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="col-md-3">
                                                        <input type="text" name="reason" class="form-control" placeholder="Reason (optional)" value="<?= htmlspecialchars($item['unavailableReason']) ?>">
                                                    </div>
                                                    
                                                    <div class="col-md-2">
                                                        <input type="date" name="back_date" class="form-control" value="<?= $item['estimatedBackDate'] ?>">
                                                    </div>
                                                    
                                                    <div class="col-md-1">
                                                        <button type="submit" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-save"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Announcements Tab -->
            <div class="tab-pane fade" id="announcements">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-bullhorn"></i> Add New Announcement</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="action" value="add_announcement">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" name="ann_title" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Priority</label>
                                        <select name="ann_priority" class="form-control">
                                            <option value="low">Low</option>
                                            <option value="medium" selected>Medium</option>
                                            <option value="high">High</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Expiry Date (optional)</label>
                                        <input type="date" name="ann_expiry" class="form-control">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Content</label>
                                <textarea name="ann_content" class="form-control" rows="3" required></textarea>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="ann_active" checked>
                                <label class="form-check-label">Active (show on website)</label>
                            </div>
                            
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add Announcement
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Current Announcements -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Current Announcements</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($data['announcements'])): ?>
                            <p class="text-muted">No announcements yet.</p>
                        <?php else: ?>
                            <?php foreach ($data['announcements'] as $ann): ?>
                                <div class="alert alert-<?= $ann['priority'] == 'high' ? 'danger' : ($ann['priority'] == 'medium' ? 'warning' : 'info') ?>">
                                    <h6><?= htmlspecialchars($ann['title']) ?></h6>
                                    <p><?= htmlspecialchars($ann['content']) ?></p>
                                    <small>
                                        Priority: <?= ucfirst($ann['priority']) ?> | 
                                        Status: <?= $ann['active'] ? 'Active' : 'Inactive' ?>
                                        <?php if ($ann['expiryDate']): ?>
                                            | Expires: <?= $ann['expiryDate'] ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Ordering Links Tab -->
            <div class="tab-pane fade" id="ordering-links">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-shopping-cart"></i> Online Ordering Platforms</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="action" value="update_ordering">
                            
                            <?php foreach ($data['orderingLinks'] as $platform => $url): ?>
                                <div class="mb-3">
                                    <label class="form-label text-capitalize"><?= $platform ?></label>
                                    <input type="url" name="ordering_<?= $platform ?>" class="form-control" 
                                           value="<?= htmlspecialchars($url) ?>" 
                                           placeholder="https://<?= $platform ?>.com/restaurant-link">
                                </div>
                            <?php endforeach; ?>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Ordering Links
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-save every 30 seconds when forms are modified
        let hasChanges = false;
        
        document.querySelectorAll('input, textarea, select').forEach(element => {
            element.addEventListener('change', () => {
                hasChanges = true;
            });
        });
        
        // Confirmation before leaving if there are unsaved changes
        window.addEventListener('beforeunload', (e) => {
            if (hasChanges) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
        
        // Reset changes flag when form is submitted
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', () => {
                hasChanges = false;
            });
        });
    </script>
</body>
</html>