import {
  addZeros,
  time
} from '../lib/utils';
import * as refs from '../lib/refs';

console.log('=== reserver ===');

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
  },
  form = {
    date: {
      from: document.getElementById('form-field--date_debut'),
      to: document.getElementById('form-field--date_fin')
    },
    heure: {
      from: document.getElementById('form-field--heure_debut'),
      to: document.getElementById('form-field--heure_fin')
    },
    submit: document.getElementById('form-submit-ctnr')
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
    [...document.getElementsByClassName('bordered')].forEach(btn => btn.classList.remove('bordered'));
    currentTarget.classList.add('bordered');
  });
});

ctnr.addEventListener('click', ({
  target
}) => {

  let max_date_fin = false;
  const selectable = !['striked', 'before_now', 'after_limit', 'csag_ferme'].find(cls => target.classList.contains(cls));

  if (!selectable)
    return false;

  [...document.getElementsByClassName(`selected-${_periode}`)].forEach(elt => {
    elt.classList.remove(`selected-${_periode}`);
    target.setAttribute('data-fr-opened', 'false');
  });

  target.classList.add(`selected-${_periode}`);

  max_date_fin = setBetweenClass();

  if (!max_date_fin)
    form.submit.classList.add('hidden');

  target.setAttribute('data-fr-opened', 'true');
  [...document.getElementsByClassName('bordered')].forEach(btn => btn.classList.remove('bordered'));

  const // 
    cs_btn = document.getElementById(`cs-btn--${_periode == 'to' ? 'left' : 'right'}`),
    label = document.getElementById(`${_periode}-date-lib`),
    form_elt = form.date[_periode],
    affichage = document.querySelector(`#select-${_periode}-date--target .cs-from-to-value--date`),
    option_prec = select.heure[_periode].options[select.heure[_periode].selectedIndex]?.value,
    {
      dataset: {
        ref,
        date
      }
    } = _periode == 'from' ? target : (max_date_fin || target),
    th = document.getElementById(`th-${ref}`),
    {
      dataset: {
        horaires
      }
    } = th,
    heures = horaires.split(','),
    [Y, m, d] = date.split('-'),
    date_en_toutes_lettres = `${refs.jours[ref]} ${d} ${(refs.mois[+m]).slice(0, 3)}<span class="hide-s">&nbsp;${Y}</span>`;

  cs_btn.classList.add('bordered');
  label.innerHTML = date_en_toutes_lettres;
  affichage.innerHTML = date_en_toutes_lettres;
  form_elt.value = date;

  console.log({ max_date_fin, res: max_date_fin === false });
  if (max_date_fin === false)
    cs_btn.classList.add('red');
  else
    cs_btn.classList.remove('red');

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
      form.heure[periode].value = heure_en_toutes_lettres;
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
    return undefined;

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

  if (time_debut >= time_fin)
    return false;

  form.submit.classList.remove('hidden');

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
      form.submit.classList.add('hidden');
      break;
    }
    prev_target = target;
    target.classList.add('between');
    i++;
  }

  // if(bandeau.classList.contains('hidden'))
  form.submit.classList.remove('hidden');

  return document.querySelector('.selected-to');
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
