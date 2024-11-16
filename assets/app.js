import '/node_modules/@gouvfr/dsfr/dist/dsfr.css';
import "/node_modules/@gouvfr/dsfr/dist/utility/icons/icons.main.min.css";
import "/node_modules/@gouvfr/dsfr/dist/dsfr/dsfr.module";

import './styles/app.scss';

import TimeUpdate from './js/time_update';

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
    btns = {
      to_step2: document.getElementById('btn-go-step2'),
      to_step1: document.getElementById('btn-go-step1')
    },
    step1 = document.getElementById('step-1'),
    step2 = document.getElementById('step-2');

    new TimeUpdate();


  // onReady('#select-from-date').then(elt => {

  //   const // 

  //     /* Début de la réservation dans les filtres */
  //     select_from_date = elt,
  //     select_from_heure = document.getElementById('select-from-heure'),

  //     /* Fin de la réservation dans les filtres */
  //     select_to_date = document.getElementById('select-to-date'),
  //     select_to_heure = document.getElementById('select-to-heure'),

  //     /* Champs <select /> dans la modale */
  //     targets = {
  //       date_debut: document.getElementById('select-from-date--target').children,
  //       heure_debut: document.getElementById('select-from-heure--target'),
  //       date_fin: document.getElementById('select-to-date--target').children,
  //       heure_fin: document.getElementById('select-to-heure--target')
  //     },

  //     /* passage de l'étape 1 à 2 */
  //     btns = {
  //       to_step2: document.getElementById('btn-go-step2'),
  //       to_step1: document.getElementById('btn-go-step1')
  //     },
  //     step1 = document.getElementById('step-1'),
  //     step2 = document.getElementById('step-2'),

  //     // add_options
  //     add_options = (option) => {
  //       const // 
  //         select = option.parentElement,
  //         select_id = select.getAttribute('id'),
  //         [, from_ou_to,] = select_id.split(/-/),
  //         select_heure = document.getElementById(`select-${from_ou_to}-heure`),
  //         select_to_heure = document.getElementById(`select-to-heure`),
  //         { Y, M, D, H, m, s } = time(),
  //         value = option.value,
  //         now = `${Y}-${M}-${D}`,
  //         heures_ouverture = get_heures_ouverture(option),
  //         first_index = value === now ? heures_ouverture.findIndex(h => +h === +H) : 0;

  //       select_heure.innerHTML = '';
  //       select_to_heure.innerHTML = '';

  //       let selected = false

  //       for (let i = heures_ouverture[first_index]; i <= heures_ouverture[heures_ouverture.length - 1]; i++) {
  //         const opt = document.createElement('option');
  //         opt.value = i;
  //         opt.innerText = addZeros(i, 2);
  //         if (!heures_ouverture.includes(i)) {
  //           opt.disabled = true;
  //           opt.title = "En dehors des horaires d'ouverture du CSAG";
  //         } else if (!selected) {
  //           opt.setAttribute('selected', 'selected');
  //           selected = true;
  //           targets.heure_debut.innerText = opt.innerText + ':00';
  //           targets.heure_fin.innerText = addZeros(+opt.value + 1, 2) + ':00';
  //         }
  //         if (from_ou_to === 'from')
  //           select_heure.appendChild(opt);

  //         const opt_to = document.createElement('option');
  //         opt_to.value = i + 1;
  //         opt_to.innerText = addZeros(i + 1, 2);
  //         select_to_heure.appendChild(opt_to);
  //       }
  //     },

  //     // heures_ouverture = [],
  //     get_heures_ouverture = (option) => {
  //       const // 
  //         heures_ouverture = [],
  //         { dataset: { am = '', pm = '' } } = option;

  //       [am, pm].forEach(creneau => {
  //         if (creneau) {
  //           const // 
  //             [debut, fin] = creneau.split(/-/),
  //             [h_debut] = debut.split(/:/),
  //             [h_fin] = fin.split(/:/);

  //           for (let i = +h_debut; i < +h_fin; i++) {
  //             heures_ouverture.push(i);
  //           }
  //         }
  //       });
  //       return heures_ouverture;
  //     },

  //     init = () => {
  //       const // 
  //         { Y, M, D, H, m, s } = time(),
  //         now = `${Y}-${M}-${D}`,
  //         select_date_debut = document.getElementById('select-from-date'),
  //         select_date_fin = document.getElementById('select-to-date');

  //       let // 
  //         trouve = 0,
  //         curr_date = now + ' 00:00:00',
  //         CSAG_ferme = [...select_date_debut.options].filter((option, i) => {

  //           option.dataset.index = i;

  //           const //
  //             nDate = +(option.value.replace(/-/g, '')),
  //             nNow = +`${Y}${M}${D}`;

  //           if (nDate < nNow)
  //             return true;

  //           const heures_ouverture = get_heures_ouverture(option);
  //           if (!heures_ouverture.length)
  //             return true;

  //           if (nDate == nNow && heures_ouverture.findIndex(h => +h >= +H + 1) < 0)
  //             return true;

  //           if (!trouve) {
  //             option.setAttribute('selected', 'selected');
  //             trouve = 1;

  //             add_options(option);
  //             select_date_fin.children[i].setAttribute('selected', 'selected');

  //             targets.date_debut[0].innerText = option.innerText;
  //             targets.date_debut[1].innerText = option.dataset.short;
  //             targets.date_fin[0].innerText = option.innerText;
  //             targets.date_fin[1].innerText = option.dataset.short;

  //           }

  //           return false;

  //         });

  //       CSAG_ferme.forEach(opt => {
  //         opt.removeAttribute('selected');
  //         opt.disabled = true;
  //         const opt_to = select_date_fin.children[opt.dataset.index];
  //         opt_to.removeAttribute('selected');
  //         opt_to.disabled = true;
  //       })

  //     };

  //   init();

  //   [select_from_date/*, select_from_heure*/].forEach(elt => {
  //     elt.addEventListener('change', ({ target }) => {
  //       console.log('change');
  //       const // 
  //         [, , date_ou_heure] = target.id.split(/_/g),
  //         option = select_from_date.options[select_from_date.selectedIndex];
  //       if (date_ou_heure == 'date')
  //         add_options(option);
  //       targets.date_debut[0].innerText = option.innerText;
  //       targets.date_debut[1].innerText = option.dataset.short;
  //       // si je change la date de début, je décalle la date de fin pour qu'elle ne soit jamais antérieure
  //       const //
  //         { value } = option,
  //         next_date = select_to_date.options[select_to_date.selectedIndex].value,
  //         next_heure = addZeros(select_to_heure.options[select_to_heure.selectedIndex].value, 2),
  //         iNext = +`${next_date.replace(/-/g, '')}${next_heure.replace(/:/g, '')}`,
  //         curr_date = option.value,
  //         curr_heure = addZeros(select_to_heure.options[select_to_heure.selectedIndex].value, 2),
  //         iCurr = +`${curr_date.replace(/-/g, '')}${curr_heure.replace(/:/g, '')}`;

  //       if (iNext >= iCurr)
  //         return true;

  //       let done = false;
  //       [...select_to_date.options].forEach(opt => {
  //         if (opt.value === value) {
  //           opt.setAttribute('selected', 'selected');
  //           const // 
  //             heure_debut = select_from_heure.options[select_from_heure.selectedIndex],
  //             heure_mini = +heure_debut.value;
  //           [...select_to_heure.options].forEach(heure => {
  //             if (+heure.value < heure_mini) {
  //               heure.removeAttribute('selected');
  //             } else if (!done) {
  //               heure.setAttribute('selected', 'selected');
  //               done = true;
  //             }
  //           })
  //         } else {
  //           opt.removeAttribute('selected');
  //         }
  //       });
  //     })
  //   });

  //   select_to_date.addEventListener('change', ({ target }) => {
  //     const option = target.options[target.selectedIndex];
  //     add_options(option);
  //     targets.date_fin[0].innerText = option.innerText;
  //     targets.date_fin[1].innerText = option.dataset.short;
  //     // si je change la date de fin, je décalle la date de début pour qu'elle ne soit jamais postérieure
  //     const //
  //       { value } = option,
  //       prev_date = select_from_date.options[select_from_date.selectedIndex].value,
  //       prev_heure = addZeros(select_from_heure.options[select_from_heure.selectedIndex].value, 2),
  //       iPrev = +`${prev_date.replace(/-/g, '')}${prev_heure.replace(/:/g, '')}`,
  //       curr_date = option.value,
  //       curr_heure = addZeros(select_to_heure.options[select_to_heure.selectedIndex].value, 2),
  //       iCurr = +`${curr_date.replace(/-/g, '')}${curr_heure.replace(/:/g, '')}`;

  //     if (iPrev < iCurr)
  //       return true;

  //     let done = false;
  //     [...select_from_date.options].forEach(opt => {
  //       if (opt.value === value) {
  //         opt.setAttribute('selected', 'selected');
  //         const // 
  //           heure_fin = select_to_heure.options[select_to_heure.selectedIndex],
  //           heure_maxi = +heure_fin.value - 1;
  //         [...select_from_heure.options].forEach(heure => {
  //           if (+heure.value >= heure_maxi) {
  //             heure.removeAttribute('selected');
  //           } else if (!done) {
  //             heure.setAttribute('selected', 'selected');
  //             done = true;
  //           }
  //         });
  //       } else {
  //         opt.removeAttribute('selected');
  //       }
  //     });
  //     if (!done) {
  //       select_from_date.options[select_from_date.selectedIndex - 1].setAttribute('selected', 'selected');
  //       select_from_heure.options[select_from_heure.length - 1].setAttribute('selected', 'selected')
  //     }
  //   });

  //   select_from_heure.addEventListener('change', ({ target }) => {
  //     const // 
  //       option = target.options[target.selectedIndex],
  //       { value } = option;
  //     targets.heure_debut.innerText = ` - ${value}:00`;
  //   });

  //   select_to_heure.addEventListener('change', ({ target }) => {
  //     const // 
  //       option = target.options[target.selectedIndex],
  //       { value } = option;
  //     targets.heure_fin.innerText = ` - ${value}:00`;
  //   });

  btns.to_step2.addEventListener('click', e => {
    step1.classList.toggle('hidden');
    step2.classList.toggle('hidden');
  });

  btns.to_step1.addEventListener('click', e => {
    step1.classList.toggle('hidden');
    step2.classList.toggle('hidden');
  });

});