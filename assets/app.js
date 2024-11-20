import '/node_modules/@gouvfr/dsfr/dist/dsfr.css';
import "/node_modules/@gouvfr/dsfr/dist/utility/icons/icons.main.min.css";
import "/node_modules/@gouvfr/dsfr/dist/dsfr/dsfr.module";

import './styles/app.scss';

import { pluralize } from './js/utils';
import TimeUpdate from './js/Updater';

console.clear();

const //
  scrollLen = 160,
  onReady = async selector => {
    while (document.querySelector(selector) === null)
      await new Promise(resolve => requestAnimationFrame(resolve))
    return document.querySelector(selector);
  };

// masquage des filtres de recherche au SCROLL sur petits écrans
onReady('#hideOnScroll').then(elt => {

  elt.addEventListener('click', () => {
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
      elt.classList.add('hiddenOnScroll');
    } else {
      elt.classList.remove('hiddenOnScroll');
    }
  });

});

onReady('#select-from-date').then(() => {

  const // 
    /* passage de l'étape 1 à 2 */
    step1 = document.getElementById('step-1'),
    step2 = document.getElementById('step-2');

  const updater = new TimeUpdate();
  updater.addEventListener('update', ({ target: { dataset } }) => {
    const //
      mem = [],
      { debut, fin, ...data } = dataset,
      nb_vls = document.getElementById('X-vls-dispos'),
      vls = [...document.getElementsByClassName('vehicule-card')];
    console.log({ data });

    let count_vls = vls.length;

    vls.forEach(vl => {
      const vl_idx = vl.dataset.index;
      vl.classList.remove('hidden');
      for (let field in data) {
        if (data[field] !== '*') {
          console.log({ field });
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
      nb_vls.innerText = `${count_vls} vehicule${pluralize(count_vls)} disponible${pluralize(count_vls)}`;
    });

  });

  [
    document.getElementById('to-step-2-btn'), // bouton "Début" dans les filtres
    document.getElementById('btn-go-step2') // 
  ].forEach(btn => {
    btn.addEventListener('click', e => {
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
      step1.classList.remove('hidden');
      step2.classList.add('hidden');
    });
  });

});