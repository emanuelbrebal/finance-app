import axios from 'axios'
import { queryClient } from '@/lib/queryClient'

export const apiClient = axios.create({
  baseURL: '/api/v1',
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
})

let csrfFetched = false

export async function ensureCsrfCookie() {
  if (csrfFetched) return
  await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
  csrfFetched = true
}

apiClient.interceptors.request.use(async (config) => {
  const method = config.method?.toLowerCase() ?? 'get'
  if (['post', 'put', 'patch', 'delete'].includes(method)) {
    await ensureCsrfCookie()
  }
  return config
})

apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    const status = error.response?.status

    if (status === 401) {
      queryClient.clear()
      window.location.href = '/login'
      return Promise.reject(error)
    }

    // 419 = CSRF token mismatch — reset and retry once
    if (status === 419) {
      csrfFetched = false
      await ensureCsrfCookie()
      return apiClient.request(error.config)
    }

    return Promise.reject(error)
  },
)
