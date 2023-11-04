const makeIdentifierActive = (href) => {
  const navItem = document.querySelector('nav a[href="' + href + '"]')
  // remove active states
  document.querySelectorAll('nav a').forEach(item => item.classList.remove('active'))
  document.querySelectorAll('details').forEach(item => item.removeAttribute('open'))
  // add active states
  navItem.classList.add('active')
  navItem.closest('nav > ol > li > details').setAttribute('open', 'open')
  navItem.closest('nav > ol > li > details').querySelectorAll('details').forEach(item => item.setAttribute('open', 'open'))
};

class Navigation {

  constructor() {
    this.bindObserver()

    document.querySelectorAll('nav a').forEach(function (link) {
      link.addEventListener('click', e => {
        const href = e.currentTarget.getAttribute('href')
        setTimeout(() => makeIdentifierActive(href), 50)
      })
    })
  }

  bindObserver() {
    const self = this
    document.querySelectorAll("h2 a,h3 a").forEach(function (headline) {
      self.headlineObserver.observe(headline);
    })
  }

  headlineObserver = new IntersectionObserver(function (entries, observer) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        const href = entry.target.getAttribute('href')
        makeIdentifierActive(href)
      }
    });
  }, {rootMargin: '-5%', threshold: 1});

}

export default new Navigation()
