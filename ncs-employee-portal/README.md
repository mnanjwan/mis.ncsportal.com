# NCS Employee Portal - Frontend

## Overview
This is the frontend application for the NCS Employee Portal system. It provides a user-friendly interface for all system roles to interact with the backend API.

## File Structure

```
ncs-employee-portal/
├── authentication/
│   └── login.html              # Login page with API integration
├── dashboards/
│   ├── officer/                # Officer dashboard
│   ├── hrd/                    # HRD dashboard
│   ├── staff-officer/          # Staff Officer dashboard
│   └── [other roles]/          # Other role dashboards
├── forms/
│   ├── emolument/              # Emolument forms
│   ├── leave/                  # Leave application forms
│   ├── pass/                   # Pass application forms
│   └── onboarding/              # Onboarding multi-step form
├── components/
│   ├── sidebar.js              # Reusable sidebar component
│   └── layout.js               # Base layout component
├── config/
│   └── api.js                  # API configuration and service
├── js/
│   ├── auth.js                 # Authentication manager
│   └── onboarding.js           # Onboarding form handler
└── dist/                       # Static assets (CSS, JS, images)
```

## Features

### 1. Authentication
- Dual login support (email or service number)
- Token-based authentication
- Automatic role-based redirection
- Session management

### 2. Shared Components
- **Sidebar Component**: Reusable sidebar for all pages based on officer dashboard
- **Layout Component**: Base layout with authentication check
- **API Service**: Centralized API communication

### 3. Onboarding Flow
- Multi-step form (4 steps)
- Data persistence between steps
- Form validation
- API integration

### 4. Role-Based Dashboards
- Officer Dashboard
- HRD Dashboard
- Staff Officer Dashboard
- And more...

## Setup

### 1. Configure API Endpoint
Edit `config/api.js` and update the `baseURL`:
```javascript
const API_CONFIG = {
    baseURL: 'http://localhost:8000/api/v1', // Update this
    // ...
};
```

### 2. Serve the Application
You can serve the application using any static file server:

**Using Python:**
```bash
cd ncs-employee-portal
python3 -m http.server 8080
```

**Using Node.js (http-server):**
```bash
npm install -g http-server
cd ncs-employee-portal
http-server -p 8080
```

**Using PHP:**
```bash
cd ncs-employee-portal
php -S localhost:8080
```

### 3. Access the Application
Open your browser and navigate to:
```
http://localhost:8080/authentication/login.html
```

## Usage

### Using the Sidebar Component
```javascript
// In your HTML page
<div id="sidebar-container"></div>

<script src="../../config/api.js"></script>
<script src="../../js/auth.js"></script>
<script src="../../components/sidebar.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', async () => {
    // Initialize auth
    const authenticated = await authManager.init();
    if (!authenticated) return;
    
    // Get user and role
    const user = authManager.getUser();
    const role = user.roles[0];
    
    // Initialize sidebar
    const sidebar = new SidebarComponent(user, role);
    sidebar.mount('sidebar-container');
  });
</script>
```

### Using the API Service
```javascript
// Load API config first
<script src="../../config/api.js"></script>

// Use in your scripts
async function loadData() {
  try {
    const response = await apiService.get('/officers');
    console.log(response.data);
  } catch (error) {
    console.error('Error:', error);
  }
}
```

### Onboarding Flow
The onboarding flow is handled automatically by `onboarding.js`. Just include it in your onboarding pages:
```html
<script src="../../config/api.js"></script>
<script src="../../js/onboarding.js"></script>
```

## API Integration

All API calls go through the `apiService` which:
- Automatically includes authentication tokens
- Handles errors consistently
- Provides a clean interface for all endpoints

### Available Methods
- `apiService.get(endpoint)` - GET request
- `apiService.post(endpoint, data)` - POST request
- `apiService.put(endpoint, data)` - PUT request
- `apiService.patch(endpoint, data)` - PATCH request
- `apiService.delete(endpoint)` - DELETE request
- `apiService.login(emailOrServiceNumber, password)` - Login
- `apiService.logout()` - Logout
- `apiService.getCurrentUser()` - Get current user

## Testing

### Test Credentials
After running backend seeders:
- **HRD Admin**: `hrd@ncs.gov.ng` / `password123`
- **Staff Officer**: `staff.officer@ncs.gov.ng` / `password123`

### Testing Checklist
1. ✅ Login with email
2. ✅ Login with service number
3. ✅ Role-based dashboard redirection
4. ✅ Sidebar navigation
5. ✅ Onboarding flow (all 4 steps)
6. ✅ Form validation
7. ✅ API error handling
8. ✅ Logout functionality

## Development Notes

### Adding New Pages
1. Create the HTML file
2. Include necessary scripts:
   - `config/api.js`
   - `js/auth.js`
   - `components/sidebar.js` (if needed)
3. Initialize sidebar if needed
4. Use `apiService` for API calls

### Customizing Sidebar
Edit `components/sidebar.js` and update the `getMenuItemsForRole()` method to add/remove menu items for specific roles.

## Browser Support
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Troubleshooting

### CORS Issues
If you encounter CORS errors, ensure:
1. Backend CORS is configured to allow your frontend origin
2. API baseURL matches your backend server

### Authentication Issues
- Check that token is being stored in localStorage
- Verify API endpoint is correct
- Check browser console for errors

### API Connection Issues
- Verify backend server is running
- Check API baseURL in `config/api.js`
- Ensure backend is accessible from your browser

## Next Steps
1. Complete all dashboard pages
2. Add form pages for all workflows
3. Implement real-time notifications
4. Add data tables for lists
5. Implement file uploads
6. Add charts and analytics

