// API Configuration
const API_CONFIG = {
    baseURL: 'http://localhost:8000/api/v1',
    endpoints: {
        auth: {
            login: '/auth/login',
            logout: '/auth/logout',
            refresh: '/auth/refresh',
            me: '/auth/me'
        },
        officers: {
            list: '/officers',
            show: (id) => `/officers/${id}`,
            update: (id) => `/officers/${id}`,
            onboarding: '/officers/onboarding'
        },
        emoluments: {
            list: '/emoluments',
            show: (id) => `/emoluments/${id}`,
            raise: (officerId) => `/officers/${officerId}/emoluments`,
            assess: (id) => `/emoluments/${id}/assess`,
            validate: (id) => `/emoluments/${id}/validate`,
            validated: '/emoluments/validated'
        },
        emolumentTimelines: {
            active: '/emolument-timelines',
            create: '/emolument-timelines',
            extend: (id) => `/emolument-timelines/${id}/extend`
        },
        leave: {
            applications: '/leave-applications',
            show: (id) => `/leave-applications/${id}`,
            apply: (officerId) => `/officers/${officerId}/leave-applications`,
            minute: (id) => `/leave-applications/${id}/minute`,
            approve: (id) => `/leave-applications/${id}/approve`,
            print: (id) => `/leave-applications/${id}/print`
        },
        leaveTypes: {
            list: '/leave-types',
            create: '/leave-types'
        },
        pass: {
            applications: '/pass-applications',
            apply: (officerId) => `/officers/${officerId}/pass-applications`,
            approve: (id) => `/pass-applications/${id}/approve`
        },
        commands: {
            list: '/commands',
            show: (id) => `/commands/${id}`
        },
        notifications: {
            list: '/notifications',
            markRead: (id) => `/notifications/${id}/read`,
            markAllRead: '/notifications/read-all'
        }
    }
};

// API Service
class ApiService {
    constructor() {
        this.baseURL = API_CONFIG.baseURL;
        this.token = localStorage.getItem('auth_token');
    }

    setToken(token) {
        this.token = token;
        if (token) {
            localStorage.setItem('auth_token', token);
        } else {
            localStorage.removeItem('auth_token');
        }
    }

    getHeaders() {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
        
        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }
        
        return headers;
    }

    async request(method, endpoint, data = null) {
        const url = `${this.baseURL}${endpoint}`;
        const options = {
            method,
            headers: this.getHeaders()
        };

        if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'An error occurred');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    get(endpoint) {
        return this.request('GET', endpoint);
    }

    post(endpoint, data) {
        return this.request('POST', endpoint, data);
    }

    put(endpoint, data) {
        return this.request('PUT', endpoint, data);
    }

    patch(endpoint, data) {
        return this.request('PATCH', endpoint, data);
    }

    delete(endpoint) {
        return this.request('DELETE', endpoint);
    }

    // Auth methods
    async login(emailOrServiceNumber, password) {
        const data = emailOrServiceNumber.includes('@') 
            ? { email: emailOrServiceNumber, password }
            : { service_number: emailOrServiceNumber, password };
        
        const response = await this.post(API_CONFIG.endpoints.auth.login, data);
        if (response.data && response.data.token) {
            this.setToken(response.data.token);
        }
        return response;
    }

    async logout() {
        try {
            await this.post(API_CONFIG.endpoints.auth.logout);
        } finally {
            this.setToken(null);
            window.location.href = '../authentication/login.html';
        }
    }

    async getCurrentUser() {
        return this.get(API_CONFIG.endpoints.auth.me);
    }

    // Check if user is authenticated
    isAuthenticated() {
        return !!this.token;
    }
}

// Create singleton instance
const apiService = new ApiService();

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { API_CONFIG, ApiService, apiService };
}

