import axios from 'axios'

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
