import axios from 'axios';

console.log('=== validation ===');
let // 
  nb = document.getElementById('nb'),
  nb_plur = document.getElementById('nb-plur'),
  nb_resas = +nb.innerText;

const // 
  valid_resa = [...document.getElementsByClassName('valid-resa')],
  annul_resa = [...document.getElementsByClassName('annul-resa')],
  annul_resa_confirm = document.getElementById('annul-resa--confirm');


valid_resa.forEach(btn => btn.addEventListener('click', ({ target }) => {

  const { dataset: { id } } = target;
  if (!target.classList.contains('valid-resa'))
    return false;

  const // 
    ctnr = document.getElementById(`valid-resa-${id}`),
    loader = ctnr.querySelector('.loader');
  loader.classList.remove('hidden');
  loader.classList.add('loading');

  axios.post('/validation/valid', { id })
    .then(({ status, data = {} }) => {
      if (status === 200) {
        loader.classList.remove('loading');
        ctnr.classList.add('removed');
        setTimeout(() => ctnr.outerHTML = '', 1e3);
        nb_resas--;
        nb.innerText = nb_resas;
        nb_plur.innerText = nb_resas > 1 ? 's' : '';
        if (!nb_resas)
          location.reload();
      }
    })
    .catch(err => {
      loader.classList.remove('loading');
    });
}));

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
      if (status === 200) {
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
      }
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