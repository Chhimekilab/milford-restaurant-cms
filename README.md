# Milford India Spice - Restaurant Admin Panel

A comprehensive content management system for the Milford India Spice restaurant website with enhanced stock management capabilities.

## 🚀 Features

### Menu Management
- ✅ **Add, Edit, Delete Menu Items**
- ✅ **Category Management** - Custom categories for organizing menu
- ✅ **Photo Upload** - Upload images for menu items
- ✅ **Advanced Stock Management**:
  - Out of Stock tracking
  - Seasonal item management
  - Temporarily unavailable items
  - Custom unavailability reasons
  - Estimated restock dates
- ✅ **Excel Import/Export** - Bulk menu management
- ✅ **Filtering** - Filter by category and availability status
- ✅ **Stock Summary Dashboard** - Real-time inventory overview

### Restaurant Information
- ✅ **Basic Info** - Name, tagline, phone, address, email
- ✅ **Hours Management** - Set operating hours for each day
- ✅ **Social Media Links** - Facebook, Instagram, Twitter, TikTok

### Announcements & Promotions
- ✅ **Dynamic Announcements** - Add/edit/delete announcements
- ✅ **Priority Levels** - High, medium, low priority announcements
- ✅ **Expiry Dates** - Set automatic expiration for time-sensitive announcements
- ✅ **Active/Inactive Toggle** - Control which announcements are displayed

### Online Ordering Links
- ✅ **Platform Management** - Add/edit/delete ordering platforms
- ✅ **Multiple Platforms** - UberEats, DoorDash, Grubhub, etc.
- ✅ **Custom Platforms** - Add any ordering service

### Data Management
- ✅ **Excel Export** - Download menu data as spreadsheet
- ✅ **JSON Export** - Export all data for website integration
- ✅ **Auto-save** - Automatic data persistence every 30 seconds
- ✅ **Data Migration** - Seamless updates to existing data

## 🔒 Security

- **Password Protection**: Admin panel is protected with password authentication
- **Local Storage**: Data persists locally in browser storage
- **Session Management**: Secure login/logout functionality

Default password: `milford2025` (change this for production use)

## 📱 Integration with Your Website

### Step 1: Add Admin Panel to Your Website

1. Upload `admin.html` to your website's root directory
2. Access the admin panel at: `https://yourdomain.com/admin.html`
3. Log in with the admin password

### Step 2: Export Data for Website Integration

1. Use the admin panel to manage your content
2. Go to "Export & Deploy" tab
3. Click "Download JSON Data" to get the updated restaurant data
4. Use this JSON data in your main website

### Step 3: Update Your Main Website Code

Replace your existing restaurant data with the exported JSON structure:

```javascript
// Use the exported JSON data in your website
const restaurantData = {
  info: {
    name: "Milford India Spice",
    tagline: "Authentic Indian Cuisine",
    phone: "(508) 478-5800",
    address: "123 Main Street, Milford, MA 01757",
    email: "info@milfordindispice.com",
    hours: { /* operating hours */ },
    socialLinks: { /* social media links */ }
  },
  menuItems: [ /* array of menu items with availability status */ ],
  announcements: [ /* array of active announcements */ ],
  orderingLinks: { /* ordering platform links */ }
};
```

### Step 4: Display Stock Status on Your Website

Use the new availability fields to show stock status:

```javascript
// Example: Display menu item with availability status
function displayMenuItem(item) {
  let statusHTML = '';
  
  if (!item.available) {
    if (item.availabilityStatus === 'out-of-stock') {
      statusHTML = `<span class="out-of-stock">Out of Stock</span>`;
      if (item.estimatedBackDate) {
        statusHTML += `<small>Expected: ${item.estimatedBackDate}</small>`;
      }
    } else if (item.availabilityStatus === 'seasonal') {
      statusHTML = `<span class="seasonal">Seasonal Item</span>`;
    } else if (item.availabilityStatus === 'temporarily-unavailable') {
      statusHTML = `<span class="temp-unavailable">Temporarily Unavailable</span>`;
    }
    
    if (item.unavailableReason) {
      statusHTML += `<div class="reason">${item.unavailableReason}</div>`;
    }
  }
  
  return `
    <div class="menu-item ${!item.available ? 'unavailable' : ''}">
      <h3>${item.name}</h3>
      <p>${item.description}</p>
      <span class="price">$${item.price.toFixed(2)}</span>
      ${statusHTML}
    </div>
  `;
}
```

## 📊 Stock Management Features

### Availability Statuses
- **Available** - Item is in stock and can be ordered
- **Out of Stock** - Temporarily out of stock with expected restock date
- **Seasonal** - Item not available due to seasonal restrictions
- **Temporarily Unavailable** - Short-term unavailability (kitchen issues, etc.)

### Stock Management Workflow
1. **Mark Item Unavailable**: Select reason and add details
2. **Set Restock Date**: Provide estimated availability date
3. **Custom Reasons**: Add specific explanations for unavailability
4. **Filter by Status**: View items by availability status
5. **Stock Summary**: Dashboard overview of inventory status

## 🛠️ Customization

### Changing the Password
Edit the password in the JavaScript section:
```javascript
if (password === 'your-new-password') {
```

### Adding New Availability Statuses
Add new options to the availability filter:
```javascript
// In the manageAvailability function, add new cases:
case '5':
    status = 'custom-status';
    reasonText = 'Custom reason';
    break;
```

### Styling
Modify the CSS variables to match your website's theme:
```css
:root {
    --primary-color: #your-color;
    --secondary-color: #your-color;
    --accent-color: #your-color;
}
```

## 📋 Data Structure

### Menu Item Object
```javascript
{
  id: 1234567890,
  name: "Chicken Tikka Masala",
  price: 16.99,
  description: "Tender chicken in creamy tomato sauce",
  category: "Main Dish",
  image: "data:image/jpeg;base64,/9j/4AAQ...", // Base64 image data
  available: false,
  availabilityStatus: "out-of-stock", // available, out-of-stock, seasonal, temporarily-unavailable
  unavailableReason: "Supply chain issues",
  estimatedBackDate: "2024-01-15",
  isSpicy: true,
  isVegetarian: false,
  allergens: "Contains dairy",
  preparationTime: "15-20 mins"
}
```

### Announcement Object
```javascript
{
  id: 1234567890,
  title: "🎉 Grand Opening Special",
  content: "Enjoy 20% off your first order this week!",
  active: true,
  priority: "high", // high, medium, low
  expiryDate: "2024-12-31"
}
```

## 🚀 Deployment

### Option 1: Add to Existing Website
1. Upload `admin.html` to your web server
2. Navigate to `/admin.html` on your domain
3. Bookmark for easy access

### Option 2: Separate Admin Subdomain
1. Create subdomain: `admin.yourdomain.com`
2. Upload `admin.html` as `index.html`
3. Access at: `https://admin.yourdomain.com`

### Option 3: Vercel Deployment
1. Create new Vercel project
2. Upload admin panel files
3. Deploy and get admin URL
4. Share URL with authorized staff

## 📱 Mobile Responsive

The admin panel is fully responsive and works on:
- ✅ Desktop computers
- ✅ Tablets
- ✅ Mobile phones
- ✅ Touch devices

## 🔄 Auto-Save

- Data automatically saves every 30 seconds
- Manual save on all update actions
- Local storage persistence
- No data loss on browser refresh

## 📞 Support

For technical support or customization requests:
- Review this README for common issues
- Check browser console for error messages
- Ensure JavaScript is enabled
- Contact your web developer for advanced customizations

## 🔒 Security Best Practices

1. **Change Default Password** - Use a strong, unique password
2. **HTTPS Only** - Always use HTTPS for admin access
3. **Limited Access** - Only share admin credentials with authorized staff
4. **Regular Backups** - Export data regularly for backup
5. **Keep Updated** - Update browser and clear cache periodically

---

**Version**: 2.0  
**Last Updated**: December 2024  
**Compatible**: All modern browsers (Chrome, Firefox, Safari, Edge)