import '/node_modules/@gouvfr/dsfr/dist/dsfr.css';
import "/node_modules/@gouvfr/dsfr/dist/utility/icons/icons.main.min.css";
import "/node_modules/@gouvfr/dsfr/dist/dsfr/dsfr.module";

import './styles/app.scss';

import { addZeros } from './js/utils';

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

onReady('#select-from-date').then(elt => {

  const // 

    /* Début de la réservation dans les filtres */
    select_from_date = elt,
    select_from_heure = document.getElementById('select-from-heure'),

    /* Fin de la réservation dans les filtres */
    select_to_date = document.getElementById('select-to-date'),
    select_to_heure = document.getElementById('select-to-heure'),

    /* Champs <select /> dans la modale */
    targets = {
      date_debut: document.getElementById('select-from-date--target').children,
      heure_debut: document.getElementById('select-from-heure--target'),
      date_fin: document.getElementById('select-to-date--target').children,
      heure_fin: document.getElementById('select-to-heure--target')
    },

    /* passage de l'étape 1 à 2 */
    btns = {
      to_step2: document.getElementById('btn-go-step2'),
      to_step1: document.getElementById('btn-go-step1')
    },
    step1 = document.getElementById('step-1'),
    step2 = document.getElementById('step-2'),

    manage_select_from = ({ target }, aujourd_hui = false) => {
      const // 
        option = target.options[target.selectedIndex],
        { text, value, dataset: { am, pm, short } } = option,
        [big_screens, small_screens] = targets.date_debut;
      big_screens.innerText = text;
      small_screens.innerText = short;
      // MAJ du sélecteur d'heure
      select_from_heure.innerHTML = '';

      const heures_ouverture = [];

      if (!aujourd_hui)
        manage_select_to({ text, value, am, pm, short });

      [am, pm].forEach(creneau => {
        if (creneau) {
          const // 
            [debut, fin] = creneau.split(/-/),
            [h_debut] = debut.split(/:/),
            [h_fin] = fin.split(/:/);

          for (let i = +h_debut; i < +h_fin; i++) {
            heures_ouverture.push(i);
          }
        }
      });

      let //
        heure_now = new Date().getHours() + 1,
        first_index = aujourd_hui ? heures_ouverture.findIndex(idx => idx === heure_now) : 0;
      if (first_index < 0) {
        const opts = [...select_from_date.children];
        let done = false;
        opts.forEach((opt, i, all) => {
          if (done)
            return false;
          if (!opt.disabled) {
            opt.disabled = true;
            if (!all[i + 1].disabled) {
              all[i + 1].setAttribute('selected', 'selected');
              console.log(all[i + 1]);
              manage_select_from({ target });
              done = true;
            }
          }

        })
      }

      for (let i = heures_ouverture[first_index]; i <= heures_ouverture[heures_ouverture.length - 1] + 1; i++) {
        const opt = document.createElement('option');
        opt.value = i;
        opt.innerText = addZeros(i, 2);
        if (!heures_ouverture.includes(i)) {
          opt.disabled = true;
          opt.title = "En dehors des horaires d'ouverture du CSAG";
        }
        select_from_heure.appendChild(opt);
      }

      const // 
        h_ouverture_option = select_from_heure.options[select_from_heure.selectedIndex],
        val = h_ouverture_option.innerText;
      targets.heure_debut.innerText = ` - ${val}:00`;


    },

    manage_select_to = ({ text, value, am, pm, short }) => {

      console.log(select_from_heure);
      console.log({ text, value, am, pm, short });
      // const options = [...select_to_date.children];
      // let found = 0;
      // options.forEach(opt => {
      //   if (!found) {
      //     if (opt.value !== value) {
      //       opt.hidden = true;
      //       opt.disabled = true;
      //       opt.removeAttribute('selected');
      //     } else {
      //       found = 1;
      //       opt.setAttribute('selected', 'selected');

      //     }
      //   } else {
      //     opt.hidden = false;
      //     opt.disabled = false;
      //   }
      // })

    };

  // simule une sélection de date pour MAJ des créneaux horaires
  manage_select_from({ target: select_from_date }, 'aujourd_hui');


  select_from_date.addEventListener('change', manage_select_from);
  select_to_date.addEventListener('change', manage_select_to);


  select_from_heure.addEventListener('change', ({ target }) => {
    const // 
      option = target.options[target.selectedIndex],
      { value } = option;
    targets.heure_debut.innerText = ` - ${value}:00`;
  });

  select_to_heure.addEventListener('change', ({ target }) => {
    const // 
      option = target.options[target.selectedIndex],
      { value } = option;
    targets.heure_to.innerText = ` - ${value}:00`;
  });

  btns.to_step2.addEventListener('click', e => {
    step1.classList.toggle('hidden');
    step2.classList.toggle('hidden');
  });

  btns.to_step1.addEventListener('click', e => {
    step1.classList.toggle('hidden');
    step2.classList.toggle('hidden');
  });

});