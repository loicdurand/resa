import axios from "axios";

console.log('=== parc ===');

const // 
  [table] = document.getElementsByTagName('table'),
  btns = {
    suivi: document.getElementById('suivi'),
    suppr: document.getElementById('suppr'),
    mod: document.getElementById('mod')
  },
  editor = document.getElementById('editor'),

  // clicks sur les images
  images_sur_le_cote = [...document.getElementsByClassName('cs-gallerie-btn')];

let //
  current_image = document.getElementsByClassName('cs-figure--main')[0],
  { clientWidth: w, clientHeight: h } = current_image != null ? current_image : { clientWidth: 0, clientHeight: 0 },
  wasPortrait = h > w,
  isPortrait = wasPortrait,
  ratio = h / w,
  rotation = 0,
  angle = 90;

images_sur_le_cote.forEach(btn => {
  btn.addEventListener('click', ({ currentTarget: image_cliquee }) => {
    const // 
      image_correspondante = image_cliquee.querySelector('img'),
      image_correspondante_src = image_correspondante.getAttribute('src'),
      image_correspondante_id = image_correspondante.dataset.id,

      image_principale = document.querySelector('.cs-figure--main'),
      image_principale_src = image_principale.getAttribute('src'),
      image_principale_id = image_principale.dataset.id;

    image_correspondante.setAttribute('src', image_principale_src);
    image_correspondante.dataset.id = image_principale_id;

    image_principale.setAttribute('src', image_correspondante_src);
    image_principale.dataset.id = image_correspondante_id;

    if (editor !== null) {
      current_image = document.getElementsByClassName('cs-figure--main')[0];
      w = current_image.clientWidth;
      h = current_image.clientHeight;
      current_image.dataset.rotation = rotation;
      wasPortrait = h > w;
      isPortrait = wasPortrait;
      ratio = h / w;

      image_correspondante.style.transform = image_principale.style.transform;
      image_correspondante.style['object-fit'] = image_principale.style['object-fit'];
      image_correspondante.dataset.rotation = rotation;

      image_principale.style.transform = image_correspondante.style.transform;
      image_principale.style['object-fit'] = image_correspondante.style['object-fit'];
      image_principale.dataset.rotation = rotation;

    }
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
        btns.suivi.setAttribute('href', `/resa971/parc/suivi/${selected_vl}`);
        btns.suppr.setAttribute('href', `/resa971/parc/supprimer/${selected_vl}`);
        btns.mod.setAttribute('href', `/resa971/parc/modifier/${selected_vl}`);
      } else {
        Object.entries(btns).forEach(([_, link]) => link.removeAttribute('href'));
      }

    }
  });

}

if (editor !== null) {

  const // 
    no_img = document.getElementById('no-img'),
    suppr = document.getElementById('suppr'),
    submit = document.getElementById('submit'),
    pos = document.getElementById('rotation-positive'),
    neg = document.getElementById('rotation-negative');

  pos.addEventListener('click', () => rotateImage(false));

  neg.addEventListener('click', () => rotateImage(true));

  suppr.addEventListener('click', () => current_image.setAttribute('src', no_img.getAttribute('src')));

  submit.addEventListener('click', () => {
    const // 
      loader = document.querySelector('.loader'),
      imgs = [...document.querySelectorAll('.cs-gallerie img')],
      data = imgs.map(({ src, dataset: { id, rotation } }) => ({ id, rotation, suppr: src.endsWith(no_img.getAttribute('src')) }));
    loader.classList.remove('hidden');
    loader.classList.add('loading');
    axios.post('/resa971/parc/rotate', data).then(({ data = {} }) => {
      loader.classList.remove('loading');
      location.replace('/resa971/parc');
    })
      .catch(err => {
        loader.classList.remove('loading');
      });

  });

  function rotateImage(negative = false) {
    rotation = negative ? (rotation - angle) % 360 : (rotation + angle) % 360;
    isPortrait = !isPortrait;
    current_image.style.transform = `rotate(${rotation}deg)`;
    current_image.style['object-fit'] = !wasPortrait && isPortrait ? 'scale-down' : 'contain';
    current_image.dataset.rotation = rotation;
  }
}

// Ajout d'un évênement sur les inputs de type file pour l'upload des fiches de suivi
const fileInputs = document.querySelectorAll('input.fr-upload');

fileInputs.forEach(input => {
  input.addEventListener('change', (event) => {
    const file = event.target.files[0];
    const [, reservationId, typeSuiviId] = event.target.id.split('-');

    if (file) {
      const formData = new FormData();
      formData.append('file', file);
      formData.append('reservation_id', reservationId);
      formData.append('type_suivi_id', typeSuiviId);
      input.classList.add('uploaded');
      const submit = input.nextElementSibling.nextElementSibling;
      submit.addEventListener('click', () => {
        axios.post(`/resa971/parc/suivi/${reservationId}/${typeSuiviId}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
          .then(response => {
            const data = response.data;
            if (data.status === 'success') {
              const messageGroup = document.getElementById(`upload-${reservationId}-${typeSuiviId}-messages`);
              messageGroup.innerHTML = `<div class="fr-alert fr-alert--success" role="alert">
                                      Fichier uploadé avec succès.
                                    </div>`;
            } else {
              const messageGroup = document.getElementById(`upload-${reservationId}-${typeSuiviId}-messages`);
              messageGroup.innerHTML = `<div class="fr-alert fr-alert--error" role="alert">
                                      Erreur lors de l'upload du fichier.
                                    </div>`;
            }
          })
          .catch(error => {
            const messageGroup = document.getElementById(`upload-${reservationId}-${typeSuiviId}-messages`);
            messageGroup.innerHTML = `<div class="fr-alert fr-alert--error" role="alert">
                                    Erreur lors de l'upload du fichier.
                                  </div>`;
          });
      });


    }
  });
});