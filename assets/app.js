import '/node_modules/@gouvfr/dsfr/dist/dsfr.css';
import "/node_modules/@gouvfr/dsfr/dist/utility/icons/icons.main.min.css";
import "/node_modules/@gouvfr/dsfr/dist/dsfr/dsfr.module";

import './styles/app.scss';

import {
  pluralize,
  addZeros,
  add1Day,
  time
} from './js/utils';
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
            iDebut = +debut.replace(/[^\d]/g, ''),
            iFin = +fin.replace(/[^\d]/g, ''),
            FR = en_date => {
              const // 
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
                const // 
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

        },

        addTag = (msg = '', field = '', updater) => {
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
      let _periode = 'from';
      const // 
        filtres_ctnr = document.getElementById('cs-filtres-container'),
        ctnr_offset = filtres_ctnr.offsetTop,
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

      ['left', 'right'].forEach(position => {
        const btn = document.querySelector(`.cs-btn-from-to.cs-btn--${position}`);
        btn.addEventListener('click', ({
          currentTarget
        }) => {
          if (currentTarget.classList.contains('cs-btn--left'))
            _periode = 'from';
          else
            _periode = 'to';
          [...document.querySelectorAll(`div[aria-controls="fr-modal--${_periode === 'from' ? 'to' : 'from'}"]`)].forEach(elt => {
            elt.setAttribute('aria-controls', `fr-modal--${_periode}`);
          });
        });
      });

      ctnr.addEventListener('click', ({
        target
      }) => {

        let max_date_fin = false;
        const selectable = !['striked', 'before_now', 'after_limit', 'csag_ferme'].find(cls => target.classList.contains(cls));

        if (!selectable)
          return false;

        console.log({
          target
        });

        [...document.getElementsByClassName(`selected-${_periode}`)].forEach(elt => {
          elt.classList.remove(`selected-${_periode}`);
          target.setAttribute('data-fr-opened', 'false');
        });

        target.classList.add(`selected-${_periode}`);

        max_date_fin = setBetweenClass();

        target.setAttribute('data-fr-opened', 'true');
        [...document.getElementsByClassName('bordered')].forEach(btn => btn.classList.remove('bordered'));

        document.getElementById(`cs-btn--${_periode == 'to' ? 'left' : 'right'}`).classList.add('bordered');
        const // 
          label = document.getElementById(`${_periode}-date-lib`),
          affichage = document.querySelector(`#select-${_periode}-date--target .cs-from-to-value--date`),
          option_prec = select.heure[_periode].options[select.heure[_periode].selectedIndex]?.value,
          {
            dataset: {
              ref,
              date
            }
          } = max_date_fin || target,
          th = document.getElementById(`th-${ref}`),
          {
            dataset: {
              horaires
            }
          } = th,
          heures = horaires.split(','),
          [Y, m, d] = date.split('-'),
          date_en_toutes_lettres = `${refs.jours[ref]} ${d} ${(refs.mois[+m]).slice(0, 3)}<span class="hide-s">&nbsp;${Y}</span>`;

        label.innerHTML = date_en_toutes_lettres;
        affichage.innerHTML = date_en_toutes_lettres;
        select.heure[_periode].innerText = '';
        heures.forEach((h, idx) => {
          const option = document.createElement('option');
          option.value = addZeros(h, 2);
          option.innerText = addZeros(h, 2);
          if ((!option_prec && !idx) || option_prec == addZeros(h, 2))
            option.selected = true;
          select.heure[_periode].appendChild(option);
        });
        select.heure[_periode].dispatchEvent(new Event('change'));
        _periode = _periode === 'from' ? 'to' : 'from';
        [...document.querySelectorAll(`div[aria-controls="fr-modal--${_periode === 'from' ? 'to' : 'from'}"]`)].forEach(elt => {
          elt.setAttribute('aria-controls', `fr-modal--${_periode}`);
        });
      });

      ['heure', 'minute'].forEach(field => {
        ['from', 'to'].forEach(periode => {
          select[field][periode].addEventListener('change', () => {

            console.log(select[field][periode]);
            const // 
              affichage = document.querySelector(`#select-${periode}-date--target .cs-from-to-value--heure`),
              {
                value: heure_debut
              } = select.heure[periode].options[select.heure[periode].selectedIndex],
              {
                value: minute_debut
              } = select.minute[periode].options[select.minute[periode].selectedIndex],
              heure_en_toutes_lettres = `${heure_debut}:${minute_debut}`;
            console.log(heure_en_toutes_lettres);

            affichage.innerText = heure_en_toutes_lettres;
          });
        })
      });

      window.addEventListener('scroll', () => {
        const // 
          ctnr = document.getElementById('cs-filtres-container'),
          scrollTop = document.body.scrollTop || document.documentElement.scrollTop,
          filtres_pos = ctnr_offset - scrollTop;
        if (filtres_pos < 0) {
          ctnr.classList.add('fix');
        } else {
          ctnr.classList.remove('fix');
        }
      });

      function setBetweenClass() {

        const bandeau = document.getElementById('bandeau-info');
        bandeau.classList.add('hidden');
        [...document.getElementsByClassName('between')].forEach(elt => elt.classList.remove('between'));

        const // 
          from = document.querySelector('.selected-from'),
          to = document.querySelector('.selected-to');
        if (from === null || to === null)
          return false;

        const // 
          {
            dataset: {
              date: date_debut
            }
          } = from, {
            dataset: {
              date: date_fin
            }
          } = to,
          time_debut = ts(`${date_debut} 08:00:00`),
          time_fin = ts(`${date_fin} 08:00:00`);

        if (time_debut > time_fin)
          return false;

        let // 
          i = 0,
          curr = time_debut,
          prev_target = from;
        while (curr < subDay(time_fin)) {
          curr = addDay(curr);
          const target = document.querySelector(`.cs-td-daynum[data-date="${val(curr)}"]`);
          if (target.classList.contains('striked')) {
            document.querySelector('.selected-to').classList.remove('selected-to');
            prev_target.classList.add('selected-to');
            bandeau.classList.remove('hidden');
            break;
          }
          prev_target = target;
          target.classList.add('between');
          i++;
        }

        return prev_target;
      };

      function ts(datetime) {
        return +new Date(Date.parse(datetime));
      }

      function val(ts) {
        const {
          Y,
          M,
          D
        } = time(ts);
        return `${Y}-${M}-${D}`;
      }

      function addDay(ts) {
        return ts + (3600 * 1000 * 24);
      }

      function subDay(ts) {
        return ts - (3600 * 1000 * 24);
      }

    })
    .catch(e => void (0));

});