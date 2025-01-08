
console.log('=== parc ===');

const // 
  [table] = document.getElementsByTagName('table'),
  btns = {
    suppr: document.getElementById('suppr'),
    mod: document.getElementById('mod')
  },
  // clicks sur les images
  images_sur_le_cote = [...document.getElementsByClassName('cs-gallerie-btn')];

images_sur_le_cote.forEach(btn => {
  btn.addEventListener('click', ({ currentTarget: image_cliquee }) => {
    const // 
      image_correspondante = image_cliquee.querySelector('img'),
      image_correspondante_src = image_correspondante.getAttribute('src'),
      image_principale = document.querySelector('.cs-figure--main'),
      image_principale_src = image_principale.getAttribute('src');
    image_correspondante.setAttribute('src', image_principale_src);
    image_principale.setAttribute('src', image_correspondante_src);
  });
});

if (table) {

  // limite la sélection à un VL à la fois
  table.addEventListener('click', ({ target }) => {
    if (target.matches('input[name="row-select"]')) {

      const // 
        is_checked = target.checked,
        parent_row = target.parentElement.parentElement.parentElement;

      [...document.querySelectorAll('[aria-selected="true"]')].forEach(elt => elt.setAttribute('aria-selected', false));
      [...document.querySelectorAll('[name="row-select"]')].forEach(elt => elt.checked = false);

      parent_row.setAttribute('aria-selected', !is_checked);
      target.checked = is_checked;

      if (is_checked) {
        const { id: selected_vl } = parent_row.dataset;
        btns.suppr.setAttribute('href', `/parc/supprimer/${selected_vl}`);
        btns.mod.setAttribute('href', `/parc/modifier/${selected_vl}`);
      } else {
        Object.entries(btns).forEach(([_, link]) => link.removeAttribute('href'));
      }

    }
  });

}