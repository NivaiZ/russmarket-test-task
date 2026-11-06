import { request } from '@/shared/api'
import type { Schedule, ScheduleStatus } from '../model/types'

type ScheduleResponse = {
  data: Schedule
}

type ScheduleListResponse = {
  data: Schedule[]
}

const errorMessage = 'Не удалось получить графики'

export function fetchSchedules(search: string, status: ScheduleStatus | ''): Promise<Schedule[]> {
  const params = new URLSearchParams()
  if (search.trim() !== '') {
    params.set('search', search.trim())
  }
  if (status !== '') {
    params.set('status', status)
  }
  const query = params.toString()

  return request<ScheduleListResponse>(
    `/api/schedules${query ? `?${query}` : ''}`,
    errorMessage,
  ).then((body) => body.data)
}

export function createSchedule(name: string): Promise<Schedule> {
  return request<ScheduleResponse>(
    '/api/schedules',
    errorMessage,
    {
      method: 'POST',
      body: JSON.stringify({ name, address: '' }),
    },
  ).then((body) => body.data)
}

export function fetchSchedule(slug: string): Promise<Schedule> {
  return request<ScheduleResponse>(`/api/schedules/${slug}`, errorMessage).then((body) => body.data)
}

export function updateScheduleStatus(id: number, status: ScheduleStatus): Promise<Schedule> {
  return request<ScheduleResponse>(`/api/schedules/${id}`, errorMessage, {
    method: 'PATCH',
    body: JSON.stringify({ status }),
  }).then((body) => body.data)
}
