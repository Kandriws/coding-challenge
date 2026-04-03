import { useState } from 'react'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? '/api/v1'
const TOKEN_STORAGE_KEY = 'airo.jwt'

const initialLoginForm = {
  email: 'test@example.com',
  password: 'password',
}

const initialQuotationForm = {
  age: '28,35',
  currency_id: 'EUR',
  start_date: getTodayDate(),
  end_date: getTodayDate()
}

function App() {
  const [token, setToken] = useState(readStoredToken)
  const [loginForm, setLoginForm] = useState(initialLoginForm)
  const [quotationForm, setQuotationForm] = useState(initialQuotationForm)
  const [quotation, setQuotation] = useState(null)
  const [sessionMessage, setSessionMessage] = useState('')
  const [loginState, setLoginState] = useState({
    loading: false,
    message: '',
    error: '',
    details: [],
  })
  const [quotationState, setQuotationState] = useState({
    loading: false,
    message: '',
    error: '',
    details: [],
  })

  const isAuthenticated = token.length > 0

  function handleLoginChange(event) {
    const { name, value } = event.target

    setLoginForm((currentForm) => ({
      ...currentForm,
      [name]: value,
    }))
  }

  function handleQuotationChange(event) {
    const { name, value } = event.target

    setQuotationForm((currentForm) => ({
      ...currentForm,
      [name]: value,
    }))
  }

  async function handleLoginSubmit(event) {
    event.preventDefault()

    setLoginState({
      loading: true,
      message: '',
      error: '',
      details: [],
    })
    setSessionMessage('')

    try {
      const payload = await requestJson('/login', {
        method: 'POST',
        body: loginForm,
      })

      const nextToken = payload.data.token

      window.localStorage.setItem(TOKEN_STORAGE_KEY, nextToken)
      setToken(nextToken)
      setLoginState({
        loading: false,
        message: payload.message ?? 'Login successful.',
        error: '',
        details: [],
      })
    } catch (error) {
      setLoginState({
        loading: false,
        message: '',
        error: error.message,
        details: extractErrorDetails(error.payload),
      })
    }
  }

  async function handleQuotationSubmit(event) {
    event.preventDefault()

    setQuotationState({
      loading: true,
      message: '',
      error: '',
      details: [],
    })
    setSessionMessage('')

    try {
      const payload = await requestJson('/quotation', {
        method: 'POST',
        token,
        body: quotationForm,
      })

      setQuotation(payload.data)
      setQuotationState({
        loading: false,
        message: payload.message ?? 'Quotation created successfully.',
        error: '',
        details: [],
      })
    } catch (error) {
      if (error.status === 401) {
        window.localStorage.removeItem(TOKEN_STORAGE_KEY)
        setToken('')
        setQuotation(null)
        setSessionMessage('Your JWT is no longer valid. Please sign in again.')
      }

      setQuotationState({
        loading: false,
        message: '',
        error: error.message,
        details: extractErrorDetails(error.payload),
      })
    }
  }

  function handleLogout() {
    window.localStorage.removeItem(TOKEN_STORAGE_KEY)
    setToken('')
    setQuotation(null)
    setLoginState({
      loading: false,
      message: '',
      error: '',
      details: [],
    })
    setQuotationState({
      loading: false,
      message: '',
      error: '',
      details: [],
    })
    setSessionMessage('You have been signed out.')
  }

  return (
    <div className="min-h-screen bg-gray-100">
      <main className="mx-auto max-w-3xl space-y-6 px-4 py-8">
        <header className="space-y-2">
          <h1 className="text-2xl font-semibold text-gray-900">
            AIRO API by KEWIN CAVIEDES :D
          </h1>
          <p className="text-sm text-gray-500">
            API: <code>{API_BASE_URL}</code>
          </p>
        </header>

        {sessionMessage ? (
          <StatusCard
            tone="warning"
            title="Session update"
            message={sessionMessage}
          />
        ) : null}

        <div className="space-y-6">
          <section className="rounded border border-gray-300 bg-white p-4">
            <div className="mb-4 flex items-start justify-between gap-3">
              <div>
                <p className="text-xs text-gray-500">
                  Step 1
                </p>
              </div>
              <span
                className={`rounded border px-2 py-1 text-xs ${isAuthenticated
                    ? 'border-green-300 bg-green-50 text-green-700'
                    : 'border-gray-300 bg-gray-50 text-gray-600'
                  }`}
              >
                {isAuthenticated ? 'Authenticated' : 'Logged out'}
              </span>
            </div>

            {isAuthenticated ? (
              <div className="space-y-4">
                <div className="rounded border border-gray-200 bg-gray-50 p-3">
                  <p className="text-sm font-medium text-gray-900">JWT token</p>
                  <code className="mt-2 block break-all text-xs text-gray-600">
                    {maskToken(token)}
                  </code>
                </div>

                {loginState.message ? (
                  <StatusCard
                    tone="success"
                    title="Login successful"
                    message={loginState.message}
                  />
                ) : null}

                <button
                  type="button"
                  className="w-full rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                  onClick={handleLogout}
                >
                  Sign out
                </button>
              </div>
            ) : (
              <form className="space-y-4" onSubmit={handleLoginSubmit}>
                <label className="block">
                  <span className="mb-1 block text-sm text-gray-700">Email</span>
                  <input
                    name="email"
                    type="email"
                    autoComplete="email"
                    value={loginForm.email}
                    onChange={handleLoginChange}
                    placeholder="test@example.com"
                    required
                    className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-gray-500"
                  />
                </label>

                <label className="block">
                  <span className="mb-1 block text-sm text-gray-700">Password</span>
                  <input
                    name="password"
                    type="password"
                    autoComplete="current-password"
                    value={loginForm.password}
                    onChange={handleLoginChange}
                    placeholder="password"
                    required
                    className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-gray-500"
                  />
                </label>

                <button
                  type="submit"
                  className="w-full rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800 disabled:cursor-not-allowed disabled:bg-gray-400"
                  disabled={loginState.loading}
                >
                  {loginState.loading ? 'Signing in...' : 'Get JWT'}
                </button>

                {loginState.error ? (
                  <StatusCard
                    tone="error"
                    title="Login failed"
                    message={loginState.error}
                    details={loginState.details}
                  />
                ) : null}
              </form>
            )}
          </section>

          <section className="rounded border border-gray-300 bg-white p-4">
            <div className="mb-4">
              <p className="text-xs text-gray-500">
                Step 2
              </p>
            </div>

            <form className="space-y-4" onSubmit={handleQuotationSubmit}>
              <div className="grid gap-4 md:grid-cols-2">
                <label className="block md:col-span-2">
                  <span className="mb-1 block text-sm text-gray-700">Ages</span>
                  <input
                    name="age"
                    type="text"
                    value={quotationForm.age}
                    onChange={handleQuotationChange}
                    placeholder="28,35"
                    disabled={!isAuthenticated || quotationState.loading}
                    required
                    className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-gray-500 disabled:cursor-not-allowed disabled:bg-gray-100"
                  />
                </label>

                <label className="block">
                  <span className="mb-1 block text-sm text-gray-700">Currency</span>
                  <select
                    name="currency_id"
                    value={quotationForm.currency_id}
                    onChange={handleQuotationChange}
                    disabled={!isAuthenticated || quotationState.loading}
                    className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-gray-500 disabled:cursor-not-allowed disabled:bg-gray-100"
                  >
                    <option value="EUR">EUR</option>
                    <option value="GBP">GBP</option>
                    <option value="USD">USD</option>
                  </select>
                </label>

                <label className="block">
                  <span className="mb-1 block text-sm text-gray-700">Start date</span>
                  <input
                    name="start_date"
                    type="date"
                    value={quotationForm.start_date}
                    onChange={handleQuotationChange}
                    disabled={!isAuthenticated || quotationState.loading}
                    required
                    className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-gray-500 disabled:cursor-not-allowed disabled:bg-gray-100"
                  />
                </label>

                <label className="block">
                  <span className="mb-1 block text-sm text-gray-700">End date</span>
                  <input
                    name="end_date"
                    type="date"
                    value={quotationForm.end_date}
                    onChange={handleQuotationChange}
                    disabled={!isAuthenticated || quotationState.loading}
                    required
                    className="w-full rounded border border-gray-300 px-3 py-2 text-sm outline-none focus:border-gray-500 disabled:cursor-not-allowed disabled:bg-gray-100"
                  />
                </label>
              </div>

              <button
                type="submit"
                className="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800 disabled:cursor-not-allowed disabled:bg-gray-400"
                disabled={!isAuthenticated || quotationState.loading}
              >
                {quotationState.loading ? 'Calculating...' : 'Create quotation'}
              </button>

              {quotationState.error ? (
                <StatusCard
                  tone="error"
                  title="Quotation failed"
                  message={quotationState.error}
                  details={quotationState.details}
                />
              ) : null}

              {quotationState.message && quotation ? (
                <StatusCard
                  tone="success"
                  title="Quotation created"
                  message={quotationState.message}
                />
              ) : null}
            </form>

            <div className="mt-6 rounded border border-gray-200 bg-gray-50 p-4">
              <h3 className="text-sm font-semibold text-gray-900">Latest response</h3>

              {quotation ? (
                <dl className="mt-3 space-y-2 text-sm">
                  <div>
                    <dt className="font-medium text-gray-700">Total</dt>
                    <dd className="text-gray-900">
                      {quotation.currency_id} {quotation.total}
                    </dd>
                  </div>
                  <div>
                    <dt className="font-medium text-gray-700">Currency</dt>
                    <dd className="text-gray-900">
                      {quotation.currency_id}
                    </dd>
                  </div>
                  <div>
                    <dt className="font-medium text-gray-700">Quotation ID</dt>
                    <dd className="text-gray-900">
                      {quotation.quotation_id}
                    </dd>
                  </div>
                </dl>
              ) : (
                <p className="mt-3 text-sm text-gray-500">
                  No quotation created yet.
                </p>
              )}
            </div>
          </section>
        </div>
      </main>
    </div>
  )
}

function StatusCard({ tone, title, message, details = [] }) {
  const toneClasses = {
    error: 'border-red-200 bg-red-50 text-red-700',
    success: 'border-green-200 bg-green-50 text-green-700',
    warning: 'border-yellow-200 bg-yellow-50 text-yellow-700',
  }

  return (
    <div
      className={`rounded-md border px-3 py-3 text-sm ${toneClasses[tone]}`}
      aria-live="polite"
    >
      <strong className="block font-medium">{title}</strong>
      <p className="mt-1">{message}</p>
      {details.length > 0 ? (
        <ul className="mt-2 list-disc space-y-1 pl-5">
          {details.map((detail) => (
            <li key={detail}>{detail}</li>
          ))}
        </ul>
      ) : null}
    </div>
  )
}

async function requestJson(path, { method = 'GET', token = '', body } = {}) {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    method,
    headers: {
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    body: body ? JSON.stringify(body) : undefined,
  })

  const payload = await parseJsonSafely(response)

  if (!response.ok || payload?.success === false) {
    const error = new Error(payload?.message ?? 'The request could not be completed.')
    error.status = response.status
    error.payload = payload
    throw error
  }

  return payload
}

async function parseJsonSafely(response) {
  try {
    return await response.json()
  } catch {
    return null
  }
}

function extractErrorDetails(payload) {
  if (!payload?.errors) {
    return []
  }

  if (Array.isArray(payload.errors)) {
    return payload.errors.map(String)
  }

  if (typeof payload.errors !== 'object') {
    return [String(payload.errors)]
  }

  return Object.entries(payload.errors).flatMap(([field, messages]) => {
    if (Array.isArray(messages)) {
      return messages.map((message) => `${humanizeField(field)}: ${message}`)
    }

    return [`${humanizeField(field)}: ${String(messages)}`]
  })
}

function humanizeField(field) {
  return field.replaceAll('_', ' ')
}

function maskToken(token) {
  if (token.length <= 18) {
    return token
  }

  return `${token.slice(0, 12)}...${token.slice(-6)}`
}

function readStoredToken() {
  if (typeof window === 'undefined') {
    return ''
  }

  return window.localStorage.getItem(TOKEN_STORAGE_KEY) ?? ''
}

function getTodayDate() {
  const today = new Date()
  const year = today.getFullYear()
  const month = String(today.getMonth() + 1).padStart(2, '0')
  const day = String(today.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}

export default App
