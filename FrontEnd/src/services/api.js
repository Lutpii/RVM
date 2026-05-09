import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 30000,
})

// Registered by auth store so the interceptor can clear Pinia state on 401
let _clearAuthFn = null
export function registerClearAuth(fn) { _clearAuthFn = fn }

let _kioskMode = false
export function setKioskToken(token) {
  _kioskMode = !!token
  if (token) {
    api.defaults.headers.common['X-Kiosk-Token'] = token
  } else {
    delete api.defaults.headers.common['X-Kiosk-Token']
  }
}

// Response interceptor — skip login redirect when kiosk token is active
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401 && !_kioskMode) {
      if (_clearAuthFn) {
        _clearAuthFn()
      } else {
        localStorage.removeItem('rvm_token')
        localStorage.removeItem('rvm_user')
      }
      window.location.hash = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
