// Core Embed YouTube facades (includes/embeds.php): the iframe replaces the thumbnail button on click.
let warmed = false;
const warm = () => {
  if (warmed) return;
  warmed = true;
  const link = document.createElement('link');
  link.rel = 'preconnect';
  link.href = 'https://www.youtube-nocookie.com';
  document.head.append(link);
};

document.querySelectorAll('.asen-youtube-facade').forEach((button) => {
  button.addEventListener('pointerover', warm, { once: true });
  button.addEventListener('focus', warm, { once: true });
  button.addEventListener('click', () => {
    const iframe = document.createElement('iframe');
    iframe.src = button.dataset.src;
    iframe.title = button.dataset.title;
    iframe.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
    iframe.allowFullscreen = true;
    iframe.referrerPolicy = 'strict-origin-when-cross-origin';
    button.replaceWith(iframe);
    iframe.focus({ preventScroll: true });
  });
});
