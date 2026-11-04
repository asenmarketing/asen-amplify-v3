// Mounts the Splide carousels the theme renders (collection partial, banner slideshow); options come from each element's data-splide JSON.
// The collection partial's pagination placeholder holds a stand-in dot for layout; it's emptied so Splide fills it with the real ones.
document.querySelectorAll('.splide[data-splide]').forEach((el) => {
  if (el.classList.contains('is-initialized')) return;
  el.querySelector('.splide__pagination')?.replaceChildren();
  new Splide(el).mount();
});
