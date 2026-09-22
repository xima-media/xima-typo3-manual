/**
* Turns every occurrence of a glossary term in the manual into an abbreviation carrying the definition, linked to
* the glossary entry itself.
*/
class Glossary {
  #skip = new Set(['A', 'ABBR', 'BUTTON', 'CODE', 'DT', 'DD', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'PRE', 'SCRIPT', 'STYLE', 'TEXTAREA'])

  constructor() {
    const terms = this.collectTerms()
    if (terms.length === 0) {
      return
    }

    document.querySelectorAll('main section').forEach(section => {
      terms.forEach(term => this.annotate(section, term))
    })
  }

  collectTerms() {
    return [...document.querySelectorAll('[data-manual-glossary] .manual-glossary_entry')]
      .map(entry => ({
        id: entry.id,
        term: entry.querySelector('.manual-glossary_term')?.textContent.trim() ?? '',
        definition: entry.querySelector('.manual-glossary_definition')?.textContent.trim() ?? '',
      }))
      .filter(entry => entry.term.length > 2)
      // Longer terms first, so "content element" wins over "content"
      .sort((a, b) => b.term.length - a.term.length)
  }

  annotate(section, term) {
    const pattern = new RegExp(`\\b${term.term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\b`, 'i')
    const walker = document.createTreeWalker(section, NodeFilter.SHOW_TEXT, {
      acceptNode: node => {
        const parent = node.parentElement
        // The glossary itself stays untouched, annotating a definition with its own term is noise
        if (!parent || this.#skip.has(parent.tagName) || parent.closest('[data-manual-glossary]')) {
          return NodeFilter.FILTER_REJECT
        }
        return NodeFilter.FILTER_ACCEPT
      },
    })

    const matches = []
    let node
    while ((node = walker.nextNode())) {
      if (pattern.test(node.nodeValue)) {
        matches.push(node)
      }
    }

    // One annotation per section is enough, repeating it on every occurrence makes the text unreadable
    const target = matches[0]
    if (!target) {
      return
    }

    const at = target.nodeValue.search(pattern)
    const length = target.nodeValue.match(pattern)[0].length
    const after = target.splitText(at)
    after.splitText(length)

    const abbr = document.createElement('a')
    abbr.className = 'manual-term'
    abbr.href = '#' + term.id
    abbr.title = term.definition
    abbr.textContent = after.nodeValue
    after.replaceWith(abbr)
  }
}

export default new Glossary()
