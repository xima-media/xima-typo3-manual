const makeIdentifierActive = (href) => {
  const navItem = document.querySelector('nav a[href="' + href + '"]')
  if (!navItem) {
    return
  }
  // remove active states
  document.querySelectorAll('nav a').forEach(item => item.classList.remove('active'))
  document.querySelectorAll('details').forEach(item => item.removeAttribute('open'))
  // add active states
  navItem.classList.add('active')
  navItem.closest('nav > ol > li > details')?.setAttribute('open', 'open')
  navItem.closest('nav > ol > li > details')?.querySelectorAll('details').forEach(item => item.setAttribute('open', 'open'))
};

class Navigation {

  constructor() {
    document.querySelectorAll('a.edit-record').forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault()
        const url = e.currentTarget.getAttribute('href')
        top.document.dispatchEvent(new CustomEvent('edit-link-clicked', {detail: {url: url}}))
      })
    })

    this.bindObserver()

    document.querySelectorAll('nav a').forEach(function (link) {
      link.addEventListener('click', e => {
        const href = e.currentTarget.getAttribute('href')
        setTimeout(() => makeIdentifierActive(href), 50)
      })
    })

    this.languageMenu()
  }

  bindObserver() {
    const self = this
    document.querySelectorAll("h2,h3").forEach(function (headline) {
      self.headlineObserver.observe(headline);
    })
  }

  headlineObserver = new IntersectionObserver(function (entries, observer) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        const id = entry.target.getAttribute('id')
        makeIdentifierActive('#' + id)
      }
    });
  }, {rootMargin: '-5%', threshold: 1});

  languageMenu() {
    document.querySelectorAll(".language-menu ul").forEach(function (menu) {
      const active = menu.querySelector('li.active')
      if (active) {
        menu.insertBefore(active, menu.firstChild)
      }
    })
  }

}

export default new Navigation()

