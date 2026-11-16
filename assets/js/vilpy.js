/* Vilpy-instellingenpagina: mediakiezer en kleurkiezers. */
document.addEventListener('DOMContentLoaded', () => {
  /* Mediakiezer voor logo en achtergrond */
  const mediaPicker = (title, onSelect) => {
    const frame = window.wp.media({ title, multiple: false, library: { type: 'image' }, button: { text: 'Gebruiken' } });
    frame.on('select', () => onSelect(frame.state().get('selection').first().attributes.url));
    return frame;
  };

  const logoInput = document.getElementById('vilpyLogoMedia');
  const logoImg = document.getElementById('imgLogo');
  const logoSize = document.getElementById('clientLogoSize');
  if (logoInput && logoImg) {
    const logoFrame = mediaPicker('Selecteer logo', (url) => {
      logoInput.value = url;
      logoImg.src = url;
    });
    document.getElementById('logoUploadButton').addEventListener('click', (e) => {
      e.preventDefault();
      logoFrame.open();
    });
    document.getElementById('logoDeleteButton').addEventListener('click', (e) => {
      e.preventDefault();
      logoInput.value = '';
      logoImg.src = document.getElementById('defaultLogoUrl').value;
    });
    if (logoSize) {
      logoImg.style.width = logoSize.value + 'px';
      logoSize.addEventListener('input', () => {
        logoImg.style.width = logoSize.value + 'px';
      });
    }
  }

  const bgInput = document.getElementById('vilpyBgMedia');
  const bgImg = document.getElementById('imgBg');
  if (bgInput && bgImg) {
    const bgFrame = mediaPicker('Selecteer achtergrond', (url) => {
      bgInput.value = url;
      bgImg.src = url;
    });
    document.getElementById('bgUploadButton').addEventListener('click', (e) => {
      e.preventDefault();
      bgFrame.open();
    });
    document.getElementById('bgDeleteButton').addEventListener('click', (e) => {
      e.preventDefault();
      bgInput.value = '';
      bgImg.src = 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=';
    });
  }

  /* Kleurkiezers */
  ['#accentField', '#overlayField', '#titleColorField', '#bgColorField'].forEach((sel) => {
    if (document.querySelector(sel)) {
      jQuery(sel).wpColorPicker();
    }
  });
});
