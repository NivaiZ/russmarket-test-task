export class ApiError extends Error {
  readonly status: number

  constructor(status: number, message: string) {
    super(message)
    this.name = 'ApiError'
    this.status = status
  }
}

export function isAbortError(error: unknown): boolean {
  return error instanceof Error && error.name === 'AbortError'
}

export async function request<T>(path: string, errorMessage: string, init?: RequestInit): Promise<T> {
  const response = await fetch(path, {
    ...init,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...init?.headers,
    },
  })

  if (!response.ok) {
    throw new ApiError(response.status, await errorText(response, errorMessage))
  }

  if (response.status === 204) {
    return undefined as T
  }

  return response.json() as Promise<T>
}

async function errorText(response: Response, fallback: string): Promise<string> {
  try {
    const body: unknown = await response.json()
    if (!body || typeof body !== 'object') {
      return fallback
    }

    const record = body as { message?: unknown; errors?: unknown }
    if (record.errors && typeof record.errors === 'object') {
      for (const value of Object.values(record.errors as Record<string, unknown>)) {
        if (Array.isArray(value) && typeof value[0] === 'string' && value[0] !== '') {
          return value[0]
        }
      }
    }

    if (typeof record.message === 'string' && record.message !== '') {
      return record.message
    }
  } catch {
    // ответ без JSON
  }

  return fallback
}
