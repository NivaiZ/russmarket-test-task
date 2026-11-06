export type Employee = {
  id: number
  name: string
  role: string
  weekly_hours: number
  avatar: string
}

export type ShiftTemplate = {
  id: number
  name: string
  starts_at: string
  ends_at: string
  border_color: string
  background_color: string
  hotkey: number
}

export type Shift = {
  id: number
  schedule_id: number
  employee_id: number
  template_id: number | null
  on_date: string
  starts_at: string
  ends_at: string
  border_color: string
  background_color: string
}

export type ShiftCell = {
  employee_id: number
  on_date: string
}
