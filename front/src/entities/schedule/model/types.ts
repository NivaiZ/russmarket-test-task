export type ScheduleStatus = 'draft' | 'published'

export type Schedule = {
  id: number
  slug: string
  name: string
  address: string
  status: ScheduleStatus
  progress: number
  employees_count: number
  on_date: string
}
