class ManualModule {
  constructor() {
    // top.TYPO3.Backend.NavigationContainer.showComponent('@typo3/backend/page-tree/page-tree-element.js');

    setTimeout(function () {

      const tree = top.document.querySelector('typo3-backend-navigation-component-pagetree');

      if (tree && !tree.classList.contains('filtered-for-manuals')) {
        tree.classList.add('filtered-for-manuals');
        tree.refresh();
      }

    }, 500);
  }

}

export default new ManualModule();
