import Modal from "@typo3/backend/modal.js";

class ManualModal {
  constructor() {
    document.querySelectorAll('a[data-manual-modal]').forEach(item => {
      item.addEventListener('click', e => {
        e.preventDefault()
        const url = e.currentTarget.getAttribute('href')
        Modal.advanced({
          type: Modal.types.iframe,
          title: 'Manual',
          content: url,
          size: Modal.sizes.large,
          staticBackdrop: true
        });
      })
    })
  }
}

export default new ManualModal()
