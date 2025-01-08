import axios from 'axios';

console.log('=== validation ===');
let // 
  nb = document.getElementById('nb'),
  nb_plur = document.getElementById('nb-plur'),
  nb_resas = +nb.innerText;

const // 
  valid_resa = [...document.getElementsByClassName('valid-resa')],
  valid_resa_confirm = document.getElementById('valid-resa--confirm'),

  modif_resa = [...document.getElementsByClassName('modif-resa')],
  modif_resa_confirm = document.getElementById('modif-resa--confirm'),

  annul_resa = [...document.getElementsByClassName('annul-resa')],
  annul_resa_confirm = document.getElementById('annul-resa--confirm'),

  select_vl = document.getElementById('vl-remplacement');

valid_resa.forEach(btn => btn.addEventListener('click', ({ target }) => {

  const { dataset: { id } } = target;
  if (!target.classList.contains('valid-resa'))
    return false;

  valid_resa_confirm.dataset.id = id;

}));

valid_resa_confirm.addEventListener('click', ({ target }) => {

  const //
    { dataset: { id } } = target,
    ctnr = document.getElementById(`valid-resa-${id}`),
    loader = ctnr.querySelector('.loader');
  loader.classList.remove('hidden');
  loader.classList.add('loading');
  close_modale('confirmation');

  axios.post('/validation/valid', { id })
    .then(({ status, data = {} }) => {

      loader.classList.remove('loading');
      const statuts = ctnr.querySelectorAll('.en.attente');
      statuts.forEach(elt => {
        elt.classList.remove('attente');
        elt.classList.add('confirmee');
      });
      setTimeout(() => {
        ctnr.classList.add('removed');
        setTimeout(() => ctnr.outerHTML = '', 1e3);
        nb_resas--;
        nb.innerText = nb_resas;
        nb_plur.innerText = nb_resas > 1 ? 's' : '';
        if (!nb_resas)
          location.reload();
      }, 2e3);

    })
    .catch(err => {
      loader.classList.remove('loading');
    });
});

modif_resa.forEach(btn => btn.addEventListener('click', ({ target }) => {
  const { dataset: { id } } = target;
  if (!target.classList.contains('modif-resa'))
    return false;

  modif_resa_confirm.dataset.id = id;
  modif_resa_confirm.disabled = true;

  axios.post('/validation/vehicules', { id })
    .then(({ status, data = {} }) => {
      const vls = data.vl;
      select_vl.innerHTML = '';
      if (vls.length) {
        modif_resa_confirm.disabled = false;
        vls.forEach(vl => select_vl.appendChild(get_option(vl)));
      } else {
        select_vl.innerHTML = '<option value="" disabled selected>Aucun véhicule équivalent trouvé</option>';
        modif_resa_confirm.disabled = true;
      }
    });
}));

modif_resa_confirm.addEventListener('click', ({ target }) => {
  const // 
    { dataset: { id }, disabled } = target;

  if (disabled)
    return false;

  const //
    selected = select_vl.options[select_vl.selectedIndex],
    ctnr = document.getElementById(`valid-resa-${id}`),
    loader = ctnr.querySelector('.loader');
  loader.classList.remove('hidden');
  loader.classList.add('loading');
  close_modale('modification');

  axios.post('/validation/modif', { id, vl: selected.value })
    .then(({ status, data = {} }) => {
      loader.classList.remove('loading');
      const // 
        vl = selected.dataset,
        marque = ctnr.querySelector('.cs-marque'),
        motorisation = ctnr.querySelector('.motorisation'),
        tags = [...ctnr.querySelectorAll('.cs-tags .fr-tag')],
        [transm, carbur] = tags,
        nb_places = ctnr.querySelector('.nb-places'),
        statuts = ctnr.querySelectorAll('.en.attente');

      marque.innerHTML = `${vl.marque} ${vl.modele}`;
      carbur.innerHTML = vl.carburant;
      transm.innerHTML = vl.transmission;
      nb_places.innerHTML = [...Array(+vl.nb_places)]
        .map(_ => `<i class="cs-user-icon fr-btn fr-icon-user-line fr-btn--icon-left"></i>`)
        .join('');
      statuts.forEach(elt => {
        elt.classList.remove('attente');
        elt.classList.add('confirmee');
      });
      if (motorisation !== null)
        motorisation.innerText = '';
      setTimeout(() => {
        ctnr.classList.add('removed');
        setTimeout(() => ctnr.outerHTML = '', 1e3);
        nb_resas--;
        nb.innerText = nb_resas;
        nb_plur.innerText = nb_resas > 1 ? 's' : '';
        if (!nb_resas)
          location.reload();
      }, 2e3);

    })
    .catch(err => {
      loader.classList.remove('loading');
    });
});

annul_resa.forEach(btn => btn.addEventListener('click', ({ target }) => {

  const { dataset: { id } } = target;
  if (!target.classList.contains('annul-resa'))
    return false;

  annul_resa_confirm.dataset.id = id;

}));

annul_resa_confirm.addEventListener('click', ({ target: { dataset: { id } } }) => {
  const // 
    ctnr = document.getElementById(`valid-resa-${id}`),
    loader = ctnr.querySelector('.loader');
  loader.classList.remove('hidden');
  loader.classList.add('loading');
  close_modale('suppression');

  axios.post('/validation/suppr', { id })
    .then(({ status, data = {} }) => {

      loader.classList.remove('loading');
      const statuts = ctnr.querySelectorAll('.en.attente');
      statuts.forEach(elt => {
        elt.classList.remove('attente');
        elt.classList.add('annulee');
      });
      setTimeout(() => {
        ctnr.classList.add('removed');
        setTimeout(() => ctnr.outerHTML = '', 1e3);
        nb_resas--;
        nb.innerText = nb_resas;
        nb_plur.innerText = nb_resas > 1 ? 's' : '';
        if (!nb_resas)
          location.reload();
      }, 2e3);

    })
    .catch(err => {
      loader.classList.remove('loading');
    });
});

function close_modale(modal_name = 'suppression') {
  const // 
    modal = document.getElementById(`fr-modal-${modal_name}`),
    triggers = document.querySelectorAll('[data-fr-opened="true"]');
  modal.setAttribute('open', 'false');
  modal.classList.remove('fr-modal--opened');
  triggers.forEach(trigger => trigger.setAttribute('data-fr-opened', 'false'));
}

function get_option(vl) {
  const  // 
    option = document.createElement('option'),
    { marque, modele, carburant, transmission, nb_places } = vl;
  Object.entries({ marque, modele, carburant, transmission, nb_places }).forEach(([prop, value]) => {
    option.dataset[prop] = value;
  });
  option.value = vl.id;
  option.innerHTML = `${vl.marque} ${vl.modele} ${vl.immatriculation} - ${vl.nb_places} places - ${vl.serigraphie ? 'sérigraphié' : 'non sérigraphié'}`;
  return option;
}