import {
  pluralize,
} from '../../lib/utils';
import Updater from './Updater';

console.log('=== accueil ===');

let init = false;
const //
  scrollLen = 160,
  hideOnScroll = document.getElementById('hideOnScroll'),
  updater = new Updater();

if (hideOnScroll !== null) {

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

}

// Début du code

const // 
  /* passage de l'étape 1 à 2 */
  step1 = document.getElementById('step-1'),
  step2 = document.getElementById('step-2');

updater.addEventListener('update', filter);
updater.addEventListener('change', filter);

[
  document.getElementById('to-step-2-btn'), // bouton "Début" dans les filtres
  document.getElementById('btn-go-step2') // 
].forEach(btn => {

  btn?.addEventListener('click', () => {

    if (!init)
      init = updater.init();

    step1.classList.add('hidden');
    step2.classList.remove('hidden');

    document.getElementById('select-from-date').dispatchEvent(new Event('change'));


  });
});

[
  document.getElementById('to-step-1-btn'),
  document.getElementById('btn-appliquer'),
  document.getElementById('btn-go-step1')
].forEach(btn => {

  btn?.addEventListener('click', () => {
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
        [, MM, DD] = date.split('-'),
        [hh, mm] = heure.split(/:/);
      return `${DD}/${MM} ${hh}:${mm}`;
    },
    filtres_appliques = [],
    filtres_elt = document.getElementById('filtres_appliques'),
    nb_vls = document.getElementById('X-vls-dispos'),
    vls = [...document.getElementsByClassName('vehicule-card--result')],
    no_result = document.getElementById('no-result');

  console.log({ debut, fin, data });

  if (debut !== '*' && fin !== '*') {
    filtres_appliques.push(addTag(`${FR(debut)}&nbsp;&rarr;&nbsp;${FR(fin)}`));
  }

  filtres_elt.innerText = '';
  no_result.classList.add('hidden');

  let count_vls = vls.length;

  const fields = {
    nbplaces: 'Nb places: ',
    categorie: '',
    serigraphie: 'Sérigraphie: ',
    transmission: '',
  };

  for (let field in data) {
    if (data[field] !== '*') {
      if (field !== 'unite')
        filtres_appliques.push(addTag(`${fields[field]}${data[field]}`, field, target));
      else
        filtres_appliques.push(addTag(`unites: ${data[field].replaceAll(/,/g, ', ')}`, field, target));
    }
  }

  vls.forEach(vl => {
    let dates = '';
    const // 
      {
        dataset: {
          index: vl_idx,
          reservations
        }
      } = vl,
      href = vl.getAttribute('href'),
      [, page, v_id] = href.split(/\//).filter(Boolean);
    vl.classList.remove('hidden');
    if (debut && debut !== '*')
      dates += `/${debut.replace(/\s/, 'T')}/${fin.replace(/\s/, 'T')}`;
    vl.setAttribute('href', `/resa971/${page}/${v_id}${dates}`);

    const // 
      resas = reservations.split('|'),
      is_indispo = resas.find(resa => {

        if (!resa)
          return false;
        // if (debut !== '*' && fin !== '*')
        //   return false;

        const // 
          iDebut = +debut.replace(/[^\d]/g, ''),
          iFin = +fin.replace(/[^\d]/g, ''),
          [start, end] = resa.split('_'),
          resa_start = +start.replace(/[^\d]/g, ''),
          resa_end = +end.replace(/[^\d]/g, '');

        let pas_reserve = false;
        if (
          // CAS 1 resa fini avant recherche
          (resa_end <= iDebut) ||
          // CAS 2 resa commence après recherche
          (resa_start >= iFin)
        ) {
          pas_reserve = true;
        }

        if (!pas_reserve) {
          // if (resa_start >= iDebut && resa_start <= iFin || iFin >= iDebut && resa_end <= iFin) {
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
              vl.classList.add('hidden');
              if (!mem.includes(vl_idx)) {
                count_vls--;
                mem.push(vl_idx);
              }
            }
          } else if (field === 'unite' && !mem.includes(vl_idx)) {
            const unites = data[field].split(',');
            if (!unites.includes(vl.dataset.unite)) {
              vl.classList.add('hidden');
              count_vls--;
              mem.push(vl_idx);
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

// Tableau triable automatiquement
document.querySelectorAll('th.sortable').forEach(headerCell => {
  headerCell.addEventListener('click', () => {
    const table = headerCell.closest('table');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const index = Array.from(headerCell.parentElement.children).indexOf(headerCell);
    const type = headerCell.getAttribute('data-type');
    const isAscending = headerCell.classList.contains('sort-asc');

    // Reset des icônes/classes sur les autres colonnes
    table.querySelectorAll('th').forEach(th => th.classList.remove('sort-asc', 'sort-desc'));

    const sortedRows = rows.sort((a, b) => {
      let valA = a.children[index].innerText.trim();
      let valB = b.children[index].innerText.trim();

      if (type === 'date') {
        // Conversion DD/MM/YYYY en objet Date pour comparer
        const parseDate = (s) => {
          const parts = s.replace(/\s.*$/, '').split('/');
          return new Date(parts[2], parts[1] - 1, parts[0]);
        };
        return parseDate(valA) - parseDate(valB);
      }

      return valA.localeCompare(valB, undefined, { numeric: true, sensitivity: 'base' });
    });

    if (isAscending) {
      sortedRows.reverse();
      headerCell.classList.add('sort-desc');
    } else {
      headerCell.classList.add('sort-asc');
    }

    tbody.append(...sortedRows);
  });
});

const add1Day = (date) => new Date(date.setDate(date.getDate() + 1));

const filterTable = () => {
  const valVehicule = document.getElementById('filter-vehicule').value.toLowerCase();
  const valUser = document.getElementById('filter-user').value.toLowerCase();
  const valDateDebut = document.getElementById('filter-datedebut').value; // Format YYYY-MM-DD
  const valDateFin = document.getElementById('filter-datefin').value;


  const rows = document.querySelectorAll('tbody tr:not(.no-result)'); // On ignore la ligne "aucun résultat" si elle existe

  rows.forEach(row => {
    // On récupère le texte des colonnes (index 2 pour Véhicule, 3 pour User, 0 pour Date)
    const txtVehicule = row.children[2].dataset.vl.toLowerCase();
    const txtUser = row.children[3].innerText.toLowerCase();

    // Extraction de la date pour comparaison (on transforme JJ/MM/AAAA en objet Date)
    const cellDateDebutParts = row.children[0].innerText.trim().replace(/\s.*$/, '').split('/');
    const cellDateFinParts = row.children[1].innerText.trim().replace(/\s.*$/, '').split('/');

    const rowDateDebut = new Date(cellDateDebutParts[2], cellDateDebutParts[1] - 1, cellDateDebutParts[0]);
    const rowDateFin = new Date(cellDateFinParts[2], cellDateFinParts[1] - 1, cellDateFinParts[0]);

    const filterDateDebut = valDateDebut ? new Date(valDateDebut) : null;
    const filterDateFin = valDateFin ? add1Day(new Date(valDateFin)) : null;

    // Conditions de visibilité
    const matchVehicule = valVehicule === "" || txtVehicule.includes(valVehicule);
    const matchUser = txtUser.includes(valUser);
    const matchDateDebut = !filterDateDebut || rowDateDebut >= filterDateDebut;
    const matchDateFin = !filterDateFin || rowDateFin <= filterDateFin;

    if (matchVehicule && matchUser && matchDateDebut && matchDateFin) {
      row.style.display = "";
    } else {
      row.style.display = "none";
    }
  });
};

// On lie l'événement 'input' (clavier ou sélection date) à notre fonction
['filter-vehicule', 'filter-user', 'filter-datedebut', 'filter-datefin'].forEach(id => {
  document.getElementById(id)?.addEventListener('input', filterTable);
});