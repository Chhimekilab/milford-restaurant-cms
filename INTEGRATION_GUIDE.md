# MilfordIndiaSpice Integration Guide

Complete guide to integrate the admin CMS with your existing MilfordIndiaSpice website.

## 🔍 Step 1: Analyze Your Current Website Structure

First, let's understand your current setup. Check your main website for:

### Common File Structure:
```
MilfordIndiaSpice/
├── index.html                 # Main homepage
├── menu.html                  # Menu page
├── about.html                 # About page
├── contact.html               # Contact page
├── css/
│   └── style.css             # Your styles
├── js/
│   ├── main.js               # Main JavaScript
│   └── menu.js               # Menu functionality
├── images/                   # Restaurant images
├── data/                     # Data files (if any)
│   └── menu-data.js          # Menu data
└── admin.html                # ← NEW: Admin panel (add this)
```

## 🚀 Step 2: Add Admin Panel to Your Website

### Option A: Direct Upload (Recommended)
1. Upload `admin.html` to your website's root directory
2. Access it at: `https://milfordindispice.com/admin.html`

### Option B: Admin Folder (For Organization)
```
MilfordIndiaSpice/
├── admin/
│   └── index.html            # Rename admin.html to index.html
```
Access at: `https://milfordindispice.com/admin/`

## 📊 Step 3: Current Website Integration Patterns

### Pattern 1: Static HTML with JavaScript Data

If your current menu looks like this:
```html
<!-- In menu.html -->
<div id="menu-container">
  <!-- Menu items hardcoded or loaded via JavaScript -->
</div>

<script>
// Current menu data (example)
const menuItems = [
  {
    name: "Chicken Tikka Masala",
    price: 16.99,
    description: "Tender chicken in creamy sauce",
    category: "Main Dish"
  }
  // ... more items
];
</script>
```

**Integration Solution:**
Replace with dynamic data loading from admin panel.

### Pattern 2: External JavaScript File

If you have `js/menu-data.js`:
```javascript
// Current menu-data.js
const restaurantData = {
  menuItems: [...],
  info: {...}
};
```

**Integration Solution:**
Update this file with exported JSON from admin panel.

### Pattern 3: Database-Driven (PHP/Node.js)

If your site uses a backend, the admin panel can export JSON that your backend consumes.

## 🔄 Step 4: Implementation Methods

### Method 1: LocalStorage Sync (Simplest)

Add this to your main website's JavaScript:

```html
<!-- Add to your menu.html or main layout -->
<script>
// Function to load data from admin panel
function loadRestaurantData() {
  // Try to get data from admin panel's localStorage
  const adminData = localStorage.getItem('restaurantData');
  
  if (adminData) {
    const data = JSON.parse(adminData);
    updateMenuDisplay(data.menuItems);
    updateRestaurantInfo(data.info);
    updateAnnouncements(data.announcements);
  } else {
    // Fallback to default data
    loadDefaultData();
  }
}

// Update menu display with stock status
function updateMenuDisplay(menuItems) {
  const menuContainer = document.getElementById('menu-container');
  
  menuContainer.innerHTML = menuItems.map(item => {
    let stockStatus = '';
    
    if (!item.available) {
      const statusClass = `status-${item.availabilityStatus}`;
      stockStatus = `
        <div class="stock-status ${statusClass}">
          <span class="status-badge">${getStatusText(item.availabilityStatus)}</span>
          ${item.unavailableReason ? `<p class="reason">${item.unavailableReason}</p>` : ''}
          ${item.estimatedBackDate ? `<p class="return-date">Expected: ${formatDate(item.estimatedBackDate)}</p>` : ''}
        </div>
      `;
    }
    
    return `
      <div class="menu-item ${!item.available ? 'unavailable' : ''}">
        <div class="item-image">
          <img src="${item.image || '/images/default-dish.jpg'}" alt="${item.name}">
        </div>
        <div class="item-content">
          <h3>${item.name}</h3>
          <p class="description">${item.description}</p>
          <div class="item-footer">
            <span class="price">$${item.price.toFixed(2)}</span>
            ${item.isSpicy ? '<span class="spicy-icon">🌶️</span>' : ''}
            ${item.isVegetarian ? '<span class="veg-icon">🌱</span>' : ''}
          </div>
          ${stockStatus}
        </div>
      </div>
    `;
  }).join('');
}

// Helper functions
function getStatusText(status) {
  const statusMap = {
    'out-of-stock': 'Out of Stock',
    'seasonal': 'Seasonal',
    'temporarily-unavailable': 'Temporarily Unavailable'
  };
  return statusMap[status] || 'Unavailable';
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  });
}

// Load data when page loads
document.addEventListener('DOMContentLoaded', loadRestaurantData);
</script>
```

### Method 2: JSON File Sync (For Static Hosting)

Create a data bridge:

1. **Export from Admin Panel** → Download JSON
2. **Replace data file** in your website
3. **Deploy updated website**

```javascript
// In your main website - js/data-loader.js
async function loadRestaurantData() {
  try {
    // First try to load from admin panel localStorage
    const localData = localStorage.getItem('restaurantData');
    if (localData) {
      const data = JSON.parse(localData);
      return data;
    }
    
    // Fallback to static JSON file
    const response = await fetch('/data/restaurant-data.json');
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Failed to load restaurant data:', error);
    return getDefaultData();
  }
}
```

### Method 3: API Integration (For Dynamic Sites)

If you have a backend API:

```javascript
// Admin panel modification - add to export function
function syncWithAPI(data) {
  fetch('/api/restaurant-data', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + adminToken
    },
    body: JSON.stringify(data)
  })
  .then(response => response.json())
  .then(result => {
    alert('Data synced with website successfully!');
  })
  .catch(error => {
    console.error('Sync failed:', error);
  });
}
```

## 🎨 Step 5: Add CSS for Stock Status

Add these styles to your main website's CSS:

```css
/* Stock Status Styles */
.menu-item.unavailable {
  opacity: 0.7;
  position: relative;
}

.menu-item.unavailable::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.8);
  pointer-events: none;
}

.stock-status {
  background: #fff3cd;
  border: 1px solid #ffeaa7;
  border-radius: 4px;
  padding: 8px;
  margin-top: 8px;
  position: relative;
  z-index: 1;
}

.stock-status.status-out-of-stock {
  background: #f8d7da;
  border-color: #f5c6cb;
}

.stock-status.status-seasonal {
  background: #d1ecf1;
  border-color: #bee5eb;
}

.stock-status.status-temporarily-unavailable {
  background: #fff3cd;
  border-color: #ffeaa7;
}

.status-badge {
  font-weight: bold;
  color: #721c24;
}

.reason, .return-date {
  margin: 4px 0;
  font-size: 0.9em;
  color: #6c757d;
}

.spicy-icon, .veg-icon {
  margin-left: 8px;
  font-size: 1.2em;
}

/* Admin Link (Hidden) */
.admin-access {
  position: fixed;
  bottom: 10px;
  right: 10px;
  background: #dc3545;
  color: white;
  padding: 8px 12px;
  border-radius: 20px;
  text-decoration: none;
  font-size: 12px;
  opacity: 0.1;
  transition: opacity 0.3s;
}

.admin-access:hover {
  opacity: 1;
  color: white;
  text-decoration: none;
}
```

## 🔗 Step 6: Add Admin Access Link

Add a discreet admin link to your website:

```html
<!-- Add to footer or bottom of any page -->
<a href="/admin.html" class="admin-access">Admin</a>
```

## 📱 Step 7: Update Your Menu Page

Here's a complete example of how to update your menu page:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Milford India Spice</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Add the new CSS above -->
</head>
<body>
    <!-- Your existing header/navigation -->
    
    <main class="menu-page">
        <div class="container">
            <h1>Our Menu</h1>
            
            <!-- Live Announcements -->
            <div id="announcements-container"></div>
            
            <!-- Menu Categories Filter -->
            <div class="menu-filters">
                <button class="filter-btn active" data-category="all">All</button>
                <button class="filter-btn" data-category="Appetizer">Appetizers</button>
                <button class="filter-btn" data-category="Main Dish">Main Dishes</button>
                <button class="filter-btn" data-category="Biryani">Biryani</button>
                <button class="filter-btn" data-category="Dessert">Desserts</button>
            </div>
            
            <!-- Menu Items (populated by JavaScript) -->
            <div id="menu-container"></div>
            
            <!-- Ordering Links -->
            <div id="ordering-links-container"></div>
        </div>
    </main>
    
    <!-- Your existing footer -->
    
    <script>
        // Add the JavaScript from Method 1 above
        // Plus your existing JavaScript
    </script>
</body>
</html>
```

## 🚀 Step 8: Testing Integration

### Test Checklist:
1. ✅ Upload admin.html to your website
2. ✅ Access admin panel: `yourdomain.com/admin.html`
3. ✅ Add test menu items with different stock statuses
4. ✅ Export JSON data
5. ✅ Verify data appears on main website
6. ✅ Test stock status display
7. ✅ Test on mobile devices

### Debug Steps:
1. **Check browser console** for JavaScript errors
2. **Verify localStorage** contains admin data
3. **Test data export/import** functions
4. **Validate JSON** structure matches expected format

## 🔄 Step 9: Deployment Workflow

### Daily Operations:
1. **Update Content** → Use admin panel
2. **Auto-sync** → Data saves automatically
3. **Manual Export** → If using static files, export JSON monthly

### When Adding New Items:
1. **Admin Panel** → Add new menu item
2. **Set Stock Status** → Available/Out of Stock
3. **Upload Photo** → Use built-in image upload
4. **Export Data** → Download for backup

## 🛡️ Step 10: Security & Maintenance

### Security:
- Change default password from `milford2025`
- Use HTTPS for admin access
- Regular data backups via export

### Maintenance:
- Weekly menu reviews
- Monthly data exports for backup
- Update stock status as needed

## 📞 Need Help?

If you encounter issues:
1. Check browser console for errors
2. Verify file paths and URLs
3. Test on different devices
4. Review data structure format

Would you like me to create specific integration code for your current website structure? Share your current `menu.html` or main page structure and I can provide targeted integration code!