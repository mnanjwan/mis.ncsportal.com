// Authentication utilities
class AuthManager {
    constructor() {
        this.apiService = apiService;
        this.currentUser = null;
    }

    async init() {
        if (!this.apiService.isAuthenticated()) {
            this.redirectToLogin();
            return false;
        }

        try {
            const response = await this.apiService.getCurrentUser();
            this.currentUser = response.data.user;
            return true;
        } catch (error) {
            console.error('Auth check failed:', error);
            this.redirectToLogin();
            return false;
        }
    }

    redirectToLogin() {
        window.location.href = '../authentication/login.html';
    }

    getUser() {
        return this.currentUser;
    }

    hasRole(roleName) {
        if (!this.currentUser || !this.currentUser.roles) {
            return false;
        }
        return this.currentUser.roles.includes(roleName);
    }

    hasAnyRole(roleNames) {
        if (!this.currentUser || !this.currentUser.roles) {
            return false;
        }
        return roleNames.some(role => this.currentUser.roles.includes(role));
    }

    async logout() {
        await this.apiService.logout();
    }
}

// Create singleton instance
const authManager = new AuthManager();

