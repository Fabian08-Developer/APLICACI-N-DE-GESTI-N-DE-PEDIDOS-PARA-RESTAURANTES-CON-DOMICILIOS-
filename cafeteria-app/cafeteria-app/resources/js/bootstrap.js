import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Echo + Service Worker setup (notificaciones + PWA base)
import './echo-setup.js';
