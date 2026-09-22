/**
* Full text search over the rendered manual. The manual is a single document, so the index is built from the DOM
* instead of asking the server: that keeps the search instant and works inside the backend module iframe as well.
*/
class Search {
  #index = []
  #form
  #input
  #results
  #status

  constructor() {
    this.#form = document.querySelector('[data-manual-search]')
    if (!this.#form) {
      return
    }

    this.#input = this.#form.querySelector('.manual-search_input')
    this.#results = this.#form.querySelector('.manual-search_results')
    this.#status = this.#form.querySelector('.manual-search_status')

    this.#form.addEventListener('submit', e => e.preventDefault())
    this.#index = this.buildIndex()

    let timer = null
    this.#input.addEventListener('input', () => {
      clearTimeout(timer)
      timer = setTimeout(() => this.search(this.#input.value.trim()), 150)
    })
    this.#input.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        this.#input.value = ''
        this.search('')
      }
    })
  }

  /**
  * One entry per heading, holding the text of everything up to the next heading of the same or a higher rank.
  */
  buildIndex() {
    const headings = [...document.querySelectorAll('main section h2[id], main section h3[id], main section h4[id]')]

    return headings.map((heading, i) => {
      const next = headings[i + 1]
      const text = []
      let node = heading.nextElementSibling ?? heading.parentElement?.nextElementSibling

      while (node && node !== next && !node.contains(next)) {
        text.push(node.textContent)
        node = node.nextElementSibling ?? node.parentElement?.nextElementSibling
      }

      return {
        id: heading.id,
        title: heading.textContent.trim(),
        haystack: (heading.textContent + ' ' + text.join(' ')).replace(/\s+/g, ' ').toLowerCase(),
        raw: text.join(' ').replace(/\s+/g, ' ').trim(),
      }
    })
  }

  search(query) {
    this.#results.replaceChildren()

    if (query.length < 2) {
      this.#results.hidden = true
      this.#status.hidden = true
      return
    }

    const needle = query.toLowerCase()
    const hits = this.#index
      .map(entry => ({entry, count: entry.haystack.split(needle).length - 1}))
      .filter(hit => hit.count > 0)
      .sort((a, b) => b.count - a.count)

    this.#status.hidden = false
    this.#status.textContent = hits.length
      ? (this.#form.dataset.labelResults || '%s results').replace('%s', hits.length)
      : (this.#form.dataset.labelNoResults || 'No results')

    hits.slice(0, 20).forEach(hit => this.#results.append(this.renderHit(hit.entry, needle)))
    this.#results.hidden = hits.length === 0
  }

  renderHit(entry, needle) {
    const link = document.createElement('a')
    link.href = '#' + entry.id
    link.className = 'manual-search_hit'

    const title = document.createElement('span')
    title.className = 'manual-search_hit-title'
    title.textContent = entry.title
    link.append(title)

    const snippet = this.buildSnippet(entry.raw, needle)
    if (snippet) {
      link.append(snippet)
    }

    const item = document.createElement('li')
    item.append(link)
    return item
  }

  /**
  * Builds the "… match …" excerpt as real nodes, so the manual content can never inject markup here.
  */
  buildSnippet(raw, needle) {
    const at = raw.toLowerCase().indexOf(needle)
    if (at === -1) {
      return null
    }

    const start = Math.max(0, at - 40)
    const snippet = document.createElement('span')
    snippet.className = 'manual-search_hit-snippet'

    if (start > 0) {
      snippet.append('…')
    }
    snippet.append(raw.slice(start, at))

    const mark = document.createElement('mark')
    mark.textContent = raw.slice(at, at + needle.length)
    snippet.append(mark)

    snippet.append(raw.slice(at + needle.length, at + needle.length + 60))
    snippet.append('…')

    return snippet
  }
}

export default new Search()
