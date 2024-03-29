import AjaxRequest from "@typo3/core/ajax/ajax-request.js";

class Installation {
  constructor() {
    this.bindEvents()
  }

  bindEvents() {
    document.querySelectorAll('button.install[data-preset]').forEach(btn => {
      btn.addEventListener('click', this.onInstallClick.bind(this))
    })
  }

  navigateToStep(stepNr) {
    document.querySelectorAll('.manual-installation-step').forEach(step => step.classList.add('hidden'))
    document.querySelector(`.manual-installation-step[data-step="${stepNr}"]`).classList.remove('hidden')
  }

  onInstallClick(e) {
    e.preventDefault()
    e.currentTarget.classList.add('disabled')
    this.navigateToStep(2)
    const preset = e.currentTarget.getAttribute('data-preset')

    const request = new AjaxRequest(TYPO3.settings.ajaxUrls.manual_installation_create)
    request.post({
      preset: preset
    }).then(async data => {
      // insert html
      document.querySelector('.manual-installation-step[data-step="3"]').innerHTML = await data.resolve()

      // bind links (if opened in iframe)
      document.querySelectorAll('.manual-installation-step[data-step="3"] a[data-page-uid]').forEach(link => {
        link.addEventListener('click', e => {
          e.preventDefault()
          top.document.location.href = e.currentTarget.getAttribute('href')
          const event = new CustomEvent('typo3:pagetree:mountPoint', {
            detail: {
              pageId: parseInt(e.currentTarget.getAttribute('data-page-uid'))
            },
          })
          top.document.dispatchEvent(event)
          top.document.dispatchEvent(new CustomEvent('typo3:pagetree:selectFirstNode'))
        })
      })

      // navigate to step 3
      this.navigateToStep(3)
    })
  }
}

export default new Installation()
