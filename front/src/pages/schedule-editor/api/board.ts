import { request } from '@/shared/api'
import type { Employee, Shift, ShiftCell, ShiftTemplate } from '../model/types'

const errorMessage = 'Не удалось сохранить смену'

export function fetchEmployees(): Promise<Employee[]> {
  return request<{ data: Employee[] }>('/api/employees', errorMessage).then((body) => body.data)
}

export function fetchTemplates(): Promise<ShiftTemplate[]> {
  return request<{ data: ShiftTemplate[] }>('/api/shift-templates', errorMessage).then(
    (body) => body.data,
  )
}

export function fetchShifts(scheduleId: number, from: string, to: string): Promise<Shift[]> {
  return request<{ data: Shift[] }>(
    `/api/schedules/${scheduleId}/shifts?from=${from}&to=${to}`,
    errorMessage,
  ).then((body) => body.data)
}

export function assignShifts(
  scheduleId: number,
  cells: ShiftCell[],
  startsAt: string,
  endsAt: string,
  templateId: number | null,
): Promise<Shift[]> {
  return request<{ data: Shift[] }>(`/api/schedules/${scheduleId}/shifts`, errorMessage, {
    method: 'POST',
    body: JSON.stringify({
      cells,
      starts_at: startsAt,
      ends_at: endsAt,
      template_id: templateId,
    }),
  }).then((body) => body.data)
}

export function moveShift(shiftId: number, employeeId: number, onDate: string): Promise<Shift> {
  return request<{ data: Shift }>(`/api/shifts/${shiftId}`, errorMessage, {
    method: 'PATCH',
    body: JSON.stringify({ employee_id: employeeId, on_date: onDate }),
  }).then((body) => body.data)
}

export function updateShiftTime(shiftId: number, startsAt: string, endsAt: string): Promise<Shift> {
  return request<{ data: Shift }>(`/api/shifts/${shiftId}`, errorMessage, {
    method: 'PATCH',
    body: JSON.stringify({ starts_at: startsAt, ends_at: endsAt }),
  }).then((body) => body.data)
}

export function deleteShift(shiftId: number): Promise<void> {
  return request<void>(`/api/shifts/${shiftId}`, errorMessage, { method: 'DELETE' })
}
