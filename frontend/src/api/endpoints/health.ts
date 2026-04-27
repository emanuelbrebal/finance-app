import { apiClient } from '@/api/client'

export type HealthCheck = 'ok' | 'fail'

export interface HealthResponse {
  data: {
    status: 'ok' | 'degraded'
    checks: {
      app: HealthCheck
      database: HealthCheck
      redis: HealthCheck
    }
    timestamp: string
  }
}

export async function getHealth(): Promise<HealthResponse> {
  const response = await apiClient.get<HealthResponse>('/health')
  return response.data
}
