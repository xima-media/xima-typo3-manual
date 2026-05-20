class Navigation {

  anchorMap = {}

  makeIdentifierActive(href) {
    const navItem = document.querySelector('nav a[href="' + href + '"]')
    if (!navItem) {
      return
    }
    document.querySelectorAll('nav a').forEach(item => item.classList.remove('active'))
    document.querySelectorAll('details').forEach(item => item.removeAttribute('open'))
    navItem.classList.add('active')
    navItem.closest('nav > ol > li > details')?.setAttribute('open', 'open')
    navItem.closest('nav > ol > li > details')?.querySelectorAll('details').forEach(item => item.setAttribute('open', 'open'))
  }

  constructor() {
    this.bindEditLinks()
    this.bindNavLinks()
    this.bindObserver()
    this.rewriteManualLinksToAnchors()
    this.navigateToCurrentAnchor()
    window.addEventListener('hashchange', () => this.navigateToCurrentAnchor())
  }

  bindEditLinks() {
    document.querySelectorAll('a.edit-record').forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault()
        const url = e.currentTarget.getAttribute('href')
        top.document.dispatchEvent(new CustomEvent('edit-link-clicked', {detail: {url: url}}))
      })
    })
  }

  bindNavLinks() {
    document.querySelectorAll('nav a').forEach(link => {
      link.addEventListener('click', e => {
        const href = e.currentTarget.getAttribute('href')
        setTimeout(() => this.makeIdentifierActive(href), 50)
      })
    })
  }

  bindObserver() {
    document.querySelectorAll('h2,h3').forEach(headline => {
      this.headlineObserver.observe(headline)
    })
  }

  rewriteManualLinksToAnchors() {
    document.querySelectorAll('[data-page-url]').forEach(el => {
      try {
        const url = new URL(el.dataset.pageUrl, window.location.href)
        this.anchorMap[url.pathname] = '#' + el.id
      } catch {
      }
    })

    document.querySelectorAll('main a[href]').forEach(a => {
      try {
        const url = new URL(a.href, window.location.href)
        if (url.origin === window.location.origin && this.anchorMap[url.pathname]) {
          a.href = this.anchorMap[url.pathname]
        }
      } catch {
      }
    })
  }

  navigateToCurrentAnchor() {
    const hash = window.location.hash
      || this.anchorMap[window.location.pathname]
    if (!hash) {
      return
    }
    this.makeIdentifierActive(hash)
    document.querySelector(hash)?.scrollIntoView()
  }

  #debounceTimer = null

  headlineObserver = new IntersectionObserver((entries) => {
    const intersecting = entries.find(entry => entry.isIntersecting)
    if (!intersecting) {
      return
    }
    clearTimeout(this.#debounceTimer)
    this.#debounceTimer = setTimeout(() => {
      this.makeIdentifierActive('#' + intersecting.target.getAttribute('id'))
    }, 50)
  }, {rootMargin: '-5%', threshold: 1})

}

export default new Navigation()
