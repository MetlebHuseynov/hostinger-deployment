/*
 * Global configuration for ProLine website
 */

// Check if window.ProLine already exists to avoid redeclaration
if (typeof window.ProLine === 'undefined') {
    // PHP API configuration - API endpoints are now handled by PHP
    const currentDomain = window.location.origin;
    
    window.ProLine = {
        API_URL: `${currentDomain}/api`,
        PRODUCTS_URL: `${currentDomain}/api/products`,
        MARKAS_URL: `${currentDomain}/api/markas`,
        CATEGORIES_URL: `${currentDomain}/api/categories`,
        USERS_URL: `${currentDomain}/api/users`,
        AUTH_URL: `${currentDomain}/api/auth`,
        DASHBOARD_URL: `${currentDomain}/api/dashboard`
    };
}