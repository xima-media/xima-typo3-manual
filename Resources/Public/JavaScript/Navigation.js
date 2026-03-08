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

    this.navigateToSlug()
  }

  navigateToSlug() {
    const slug = window.location.pathname
    const section = Array.from(document.querySelectorAll('section[data-page-slug]'))
      .find(s => s.getAttribute('data-page-slug') === slug)
    if (!section) {
      return
    }
    const headline = section.querySelector('h2[id]')
    if (headline) {
      headline.scrollIntoView()
      makeIdentifierActive('#' + CSS.escape(headline.getAttribute('id')))
    }
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

}

export default new Navigation()
