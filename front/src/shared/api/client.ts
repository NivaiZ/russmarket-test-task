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
    throw new Error(errorMessage)
  }

  if (response.status === 204) {
    return undefined as T
  }

  return response.json() as Promise<T>
}
