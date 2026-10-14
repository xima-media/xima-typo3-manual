/**
* The chapter headings tracked by both the navigation's scroll spy and the "on this page" list, so what counts as a
* trackable heading stays defined in one place.
*/
export function getChapterHeadings() {
  return [...document.querySelectorAll('.manual-chapter [data-level][id]')]
}
