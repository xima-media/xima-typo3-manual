import Modal from "@typo3/backend/modal.js";

class EditRecords {
  constructor() {

    top.document.addEventListener('edit-link-clicked', (e) => {
      Modal.advanced({
        type: Modal.types.iframe,
        title: 'Edit record',
        content: e.detail.url,
        size: Modal.sizes.large,
        staticBackdrop: true
      });
    })
  }
}

export default new EditRecords()
