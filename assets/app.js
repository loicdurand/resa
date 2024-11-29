import '/node_modules/@gouvfr/dsfr/dist/dsfr.css';
import "/node_modules/@gouvfr/dsfr/dist/utility/icons/icons.main.min.css";
import "/node_modules/@gouvfr/dsfr/dist/dsfr/dsfr.module";

import './styles/app.scss';

import { pluralize, addZeros } from './js/utils';
import * as refs from './js/refs';
import TimeUpdate from './js/Updater';

console.clear();

const //
  scrollLen = 160,
  $ = sel => document.querySelector(sel),
  onReady = selector => new Promise((res, rej) => $(selector) === null ? rej() : res($(selector)));

document.addEventListener('DOMContentLoaded', () => {

  // masquage des filtres de recherche au SCROLL sur petits écrans
  onReady('#hideOnScroll')
    .then(elt => {

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

    })
    .catch(e => void (0));

  onReady('#select-from-date')
    .then(() => {

      const // 
        /* passage de l'étape 1 à 2 */
        step1 = document.getElementById('step-1'),
        step2 = document.getElementById('step-2'),

        filter = (e) => {
          const //
            { target } = e,
            { dataset } = target,
            mem = [],
            { debut, fin, ...data } = dataset,
            FR = en_date => {
              const  // 
                [date, heure] = en_date.split(/\s|T|\+/),
                [YYYY, MM, DD] = date.split('-'),
                [hh, mm] = heure.split(/:/);
              return `${DD}/${MM} ${hh}:${mm}`;
            },
            filtres_appliques = [addTag(`${FR(debut)}&nbsp;&rarr;&nbsp;${FR(fin)}`)],
            filtres_elt = document.getElementById('filtres_appliques'),
            nb_vls = document.getElementById('X-vls-dispos'),
            vls = [...document.getElementsByClassName('vehicule-card--result')],
            no_result = document.getElementById('no-result');
          console.log({ data, debut, fin });

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
              vl_idx = vl.dataset.index,
              href = vl.getAttribute('href'),
              [page, v_id] = href.split(/\//).filter(Boolean);
            vl.classList.remove('hidden');
            vl.setAttribute('href', `/${page}/${v_id}/${debut}/${fin}`);

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
            nb_vls.innerText = `${count_vls} vehicule${pluralize(count_vls)} disponible${pluralize(count_vls)}`;
            if (!count_vls)
              no_result.classList.remove('hidden');
            filtres_appliques.forEach(tag => filtres_elt.appendChild(tag));
            //}
          });

        },

        addTag = (msg = '', field = '', updater) => {
          const  // 
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
            filter({ target: updater });

            if (target.parentNode !== null)
              target.outerHTML = '';
          });
          return li;
        },

        updater = new TimeUpdate();

      updater.addEventListener('update', filter);

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

    })
    .catch(e => void (0));

  onReady('#fiche-vehicule')
    .then(elt => {
      const // 
        ctnr = document.getElementById('calendars-container'),
        select = {
          heure: {
            from: document.getElementById('select-from-heure'),
            to: document.getElementById('select-to-heure')
          },
          minute: {
            from: document.getElementById('select-from-minute'),
            to: document.getElementById('select-to-minute')
          }
        };

      ctnr.addEventListener('click', ({ target }) => {
        const selectable = !['striked', 'before_now', 'after_limit', 'csag_ferme'].find(cls => target.classList.contains(cls));
        if (!selectable)
          return false;

        [...document.getElementsByClassName('selected')].forEach(elt => {
          elt.classList.remove('selected');
          target.setAttribute('data-fr-opened', 'false');
        });
        target.classList.add('selected');
        target.setAttribute('data-fr-opened', 'true');
        const // 
          label = document.getElementById('from-date-lib'),
          affichage = document.querySelector('#select-from-date--target .cs-from-to-value--date'),
          option_prec = select.heure.from.options[select.heure.from.selectedIndex]?.value,
          { dataset: { ref, date } } = target,
          th = document.getElementById(`th-${ref}`),
          { dataset: { horaires } } = th,
          heures = horaires.split(','),
          [Y, m, d] = date.split('-'),
          date_en_toutes_lettres = `${refs.jours[ref]} ${d}<span class="hide-s">&nbsp;${(refs.mois[+m]).slice(0, 3)} ${Y}</span>`;

        label.innerHTML = date_en_toutes_lettres;
        affichage.innerHTML = date_en_toutes_lettres;
        select.heure.from.innerText = '';
        heures.forEach((h, idx) => {
          const option = document.createElement('option');
          option.value = addZeros(h, 2);
          option.innerText = addZeros(h, 2);
          if ((!option_prec && !idx) || option_prec == addZeros(h, 2))
            option.selected = true;
          select.heure.from.appendChild(option);
        });
        select.heure.from.dispatchEvent(new Event('change'));
      });

      ['heure', 'minute'].forEach(field => {
        ['from', 'to'].forEach(periode => {
          select[field][periode].addEventListener('change', () => {
            const // 
              affichage = document.querySelector('#select-from-date--target .cs-from-to-value--heure'),
              { value: heure_debut } = select.heure[periode].options[select.heure[periode].selectedIndex],
              { value: minute_debut } = select.minute[periode].options[select.minute[periode].selectedIndex],
              heure_en_toutes_lettres = `${heure_debut}:${minute_debut}`;

            affichage.innerText = heure_en_toutes_lettres;
          });
        })
      });
    })
    .catch(e => void (0));

});