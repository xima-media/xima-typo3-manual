/**
* Lets readers override the operating system colour scheme. Loaded as a classic script in the head, so a stored
* choice is applied before the first paint. A theme set by the server (the backend module passes the colour scheme of
* the backend user) wins and hides the switcher.
*/
(() => {
  const STORAGE_KEY = 'xima-manual-color-scheme'
  const SCHEMES = ['auto', 'light', 'dark']
  const root = document.documentElement

  if (root.hasAttribute('data-theme')) {
    return
  }

  const read = () => {
    try {
      const value = window.localStorage.getItem(STORAGE_KEY)
      return SCHEMES.includes(value) ? value : 'auto'
    } catch {
      return 'auto'
    }
  }

  const write = (scheme) => {
    try {
      if (scheme === 'auto') {
        window.localStorage.removeItem(STORAGE_KEY)
      } else {
        window.localStorage.setItem(STORAGE_KEY, scheme)
      }
    } catch {
      // Without storage the choice only lasts for this page view
    }
  }

  const apply = (scheme) => {
    if (scheme === 'auto') {
      root.removeAttribute('data-theme')
    } else {
      root.setAttribute('data-theme', scheme)
    }
  }

  let current = read()
  apply(current)

  document.addEventListener('DOMContentLoaded', () => {
    const switcher = document.querySelector('[data-manual-color-scheme]')
    if (!switcher) {
      return
    }

    const buttons = [...switcher.querySelectorAll('[data-scheme]')]
    const select = (scheme) => {
      buttons.forEach((button) => {
        const active = button.dataset.scheme === scheme
        button.setAttribute('aria-checked', String(active))
        button.tabIndex = active ? 0 : -1
      })
    }

    const choose = (scheme, focus = false) => {
      current = scheme
      apply(scheme)
      write(scheme)
      select(scheme)
      if (focus) {
        buttons.find((button) => button.dataset.scheme === scheme)?.focus()
      }
    }

    buttons.forEach((button) => {
      button.addEventListener('click', () => choose(button.dataset.scheme))
    })

    switcher.addEventListener('keydown', (event) => {
      const step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[event.key]
      if (!step) {
        return
      }
      event.preventDefault()
      const index = SCHEMES.indexOf(current)
      choose(SCHEMES[(index + step + SCHEMES.length) % SCHEMES.length], true)
    })

    select(current)
    switcher.hidden = false
  })
})()
