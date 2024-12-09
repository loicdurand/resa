import {
  pluralize,
} from '../../lib/utils';
import TimeUpdate from './Updater';

console.log('=== accueil ===');

let init = false;
const //
  scrollLen = 160,
  hideOnScroll = document.getElementById('hideOnScroll'),
  updater = new TimeUpdate();

// masquage des filtres de recherche au SCROLL sur petits écrans
hideOnScroll.addEventListener('click', () => {
  let i = document.body.scrollTop || document.documentElement.scrollTop;
  while (i > scrollLen) {
    (function (i) {
      setTimeout(function () {
        document.body.scrollTop -= 1;
        document.documentElement.scrollTop -= 1;
      }, i / 100)
    })(i--)
  }
});

window.addEventListener('scroll', () => {
  if (document.body.scrollTop > scrollLen || document.documentElement.scrollTop > scrollLen) {
    hideOnScroll.classList.add('hiddenOnScroll');
  } else {
    hideOnScroll.classList.remove('hiddenOnScroll');
  }
});

// Début du code

const // 
  /* passage de l'étape 1 à 2 */
  step1 = document.getElementById('step-1'),
  step2 = document.getElementById('step-2');

updater.addEventListener('update', filter);

[
  document.getElementById('to-step-2-btn'), // bouton "Début" dans les filtres
  document.getElementById('btn-go-step2') // 
].forEach(btn => {

  btn.addEventListener('click', e => {
    if (!init)
      init = updater.init();

    step1.classList.add('hidden');
    step2.classList.remove('hidden');
  });
});

[
  document.getElementById('to-step-1-btn'),
  document.getElementById('btn-appliquer'),
  document.getElementById('btn-go-step1')
].forEach(btn => {

  btn.addEventListener('click', e => {
    if (!init)
      init = updater.init();

    step1.classList.remove('hidden');
    step2.classList.add('hidden');
  });
});

function addTag(msg = '', field = '', updater) {
  const // 
    li = document.createElement('li'),
    p = document.createElement('p'),
    classes = ['fr-tag', 'fr-tag--icon-right'];

  if (field)
    ['fr-fi-close-line', 'clickable'].forEach(cls => classes.push(cls));

  classes.forEach(cls => p.classList.add(cls));
  p.innerHTML = msg;
  li.appendChild(p);

  if (!field)
    return li;

  p.dataset.field = field;
  p.addEventListener('click', (e) => {
    const // 
      target = e.currentTarget,
      field = target.dataset.field;
    let form_elt;
    switch (field) {
      case 'categorie':
        form_elt = document.querySelector('[data-categorie="*"]');
        form_elt.click();
        break;
      case 'serigraphie':
      case 'transmission':
        form_elt = document.querySelector(`input[name=radio--${field}][value="*"]`);
        form_elt.checked = true;
        break;
      case 'nbplaces':
        form_elt = document.getElementById('input--nb-places');
        form_elt.value = 'Indifférent';
      default:
        break;
    }
    updater.dataset[field] = '*';
    filter({
      target: updater
    });

    if (target.parentNode !== null)
      target.outerHTML = '';
  });
  return li;
};

function filter(e) {

  const //
    {
      target
    } = e, {
      dataset
    } = target,
    mem = [], {
      debut,
      fin,
      ...data
    } = dataset,
    FR = en_date => {
      const // 
        [date, heure] = en_date.split(/\s|T|\+/),
        [YYYY, MM, DD] = date.split('-'),
        [hh, mm] = heure.split(/:/);
      return `${DD}/${MM} ${hh}:${mm}`;
    },
    filtres_appliques = [],
    filtres_elt = document.getElementById('filtres_appliques'),
    nb_vls = document.getElementById('X-vls-dispos'),
    vls = [...document.getElementsByClassName('vehicule-card--result')],
    no_result = document.getElementById('no-result');

  if (debut !== '*' && fin !== '*') {
    filtres_appliques.push(addTag(`${FR(debut)}&nbsp;&rarr;&nbsp;${FR(fin)}`));
  }

  console.log({
    data,
    debut,
    fin
  });

  filtres_elt.innerText = '';
  no_result.classList.add('hidden');

  let count_vls = vls.length;

  const fields = {
    nbplaces: 'Nb places: ',
    categorie: '',
    serigraphie: 'Sérigraphie: ',
    transmission: ''
  };

  for (let field in data) {
    if (data[field] !== '*') {
      filtres_appliques.push(addTag(`${fields[field]}${data[field]}`, field, target));
    }
  }

  vls.forEach(vl => {
    const // 
      {
        dataset: {
          index: vl_idx,
          reservations
        }
      } = vl,
      href = vl.getAttribute('href'),
      [page, v_id] = href.split(/\//).filter(Boolean);
    vl.classList.remove('hidden');
    vl.setAttribute('href', `/${page}/${v_id}/${debut}/${fin}`);

    const // 
      resas = reservations.split('|'),
      is_indispo = resas.find(resa => {

        if (debut !== '*' && fin !== '*')
          return false;

        const // 
          iDebut = +debut.replace(/[^\d]/g, ''),
          iFin = +fin.replace(/[^\d]/g, ''),
          [start, end] = resa.split('_'),
          iStart = +start.replace(/[^\d]/g, ''),
          iEnd = +end.replace(/[^\d]/g, '');
        if (iStart >= iDebut && iStart <= iFin || iFin >= iDebut && iEnd <= iFin) {
          vl.classList.add('hidden');
          count_vls--;
          mem.push(vl_idx);
          return true;
        }
        return false;
      });

    if (!is_indispo) {
      for (let field in data) {
        if (data[field] !== '*') {
          if (field === 'nbplaces') {
            if (+data[field] > +vl.dataset[field]) {
              console.log(data[field], vl.dataset[field]);
              vl.classList.add('hidden');
              if (!mem.includes(vl_idx)) {
                count_vls--;
                mem.push(vl_idx);
              }
            }
          } else if (vl.dataset[field] !== data[field]) {
            vl.classList.add('hidden');
            if (!mem.includes(vl_idx)) {
              count_vls--;
              mem.push(vl_idx);
            }
          }
        }
      }
    }

    nb_vls.innerText = `${count_vls} vehicule${pluralize(count_vls)} disponible${pluralize(count_vls)}`;
    if (!count_vls)
      no_result.classList.remove('hidden');
    filtres_appliques.forEach(tag => filtres_elt.appendChild(tag));
    //}
  });

};