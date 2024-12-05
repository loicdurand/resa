import {
  addZeros,
  time,
  onReady
} from '../lib/utils';
import * as refs from '../lib/refs';

console.log('=== reserver ===');

let // 
  _periode = 'from',
  _heures = [];

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

  let // 
    has_error = false,
    selectable = !['striked', 'before_now', 'after_limit', 'csag_ferme'].find(cls => target.classList.contains(cls));

  selectable = selectable || target.classList.contains('striked_debut') || target.classList.contains('striked_fin');

  const //
    notice = document.getElementById(`notice-resa-${_periode == 'from' ? 'debut' : 'fin'}`),
    list = document.getElementById(`text-resa-${_periode == 'from' ? 'debut' : 'fin'}`),
    { dataset } = target;

  _heures = [];
  for (let prop in dataset) {
    if (/resa_[\d+]_debut/.test(prop)) {
      const [, index, period] = prop.split('_');
      _heures.push({ debut: dataset[prop], fin: dataset[`resa_${index}_fin`] });
    }
  }

  notice.classList.add('hidden');
  list.innerHTML = '';

  console.log({_heures});
  if (!selectable)
    return false;

  if (_heures.length) {
    notice.classList.remove('hidden');
    _heures.forEach(({ debut: heure_debut, fin: heure_fin }) => {
      const li = document.createElement('li');
      if (heure_debut && heure_fin)
        li.innerText = `de ${heure_debut} à ${heure_fin}`;
      else if (heure_debut)
        li.innerText = `à partir de ${heure_debut}`;
      else
        li.innerText = `à partir de ${heure_fin}`;
      list.appendChild(li);
    });

  }

  [...document.getElementsByClassName(`selected-${_periode}`)].forEach(elt => {
    elt.classList.remove(`selected-${_periode}`);
    target.setAttribute('data-fr-opened', 'false');
  });

  target.classList.add(`selected-${_periode}`);

  has_error = setBetweenClass();

  target.setAttribute('data-fr-opened', 'true');
  [...document.getElementsByClassName('bordered')].forEach(btn => btn.classList.remove('bordered'));

  const // 
    cs_btn = document.getElementById(`cs-btn--${_periode == 'to' ? 'left' : 'right'}`),
    other_btn = document.getElementById(`cs-btn--${_periode == 'to' ? 'right' : 'left'}`),
    label = document.getElementById(`${_periode}-date-lib`),
    form_elt = form.date[_periode],
    affichage = document.querySelector(`#select-${_periode}-date--target .cs-from-to-value--date`),
    option_prec = select.heure[_periode].options[select.heure[_periode].selectedIndex]?.value,
    {
      dataset: {
        ref,
        date
      }
    } = target,
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

  if (has_error === false) {
    [cs_btn, other_btn].forEach(elt => elt.classList.remove('red'));
  } else {
    cs_btn.classList.add('red');
  }

  select.heure[_periode].innerText = '';
  heures.forEach((h, idx) => {
    const minutes = [...document.querySelectorAll(`#select-${_periode}-minute option`)];
    const option = document.createElement('option');
    option.value = addZeros(h, 2);

    option.disabled = _heures.map(({ debut: heure_debut, fin: heure_fin }) => {
      return minutes.map(({ value: minute }) => {
        return is_disabled(h, minute, heure_debut, heure_fin) ? '' + h + ':' + minute : false;
      }).filter(Boolean).length
    }).find(length => length === minutes.length) ? true : false;

    option.innerText = addZeros(h, 2);
    if ((!option_prec && !idx) || option_prec == addZeros(h, 2))
      option.selected = !option.disabled && true;
    select.heure[_periode].appendChild(option);
  });

  select.heure[_periode].dispatchEvent(new Event('change'));
  _periode = _periode === 'from' ? 'to' : 'from';
  //const is_striked = target.classList.contains('striked_debut') || target.classList.contains('striked_fin');
  [...document.querySelectorAll(`div[aria-controls="fr-modal--${_periode === 'from' ? 'to' : 'from'}"]`)].forEach(elt => {
    elt.setAttribute('aria-controls', `fr-modal--${_periode}`);
  });
});

['heure', 'minute'].forEach(field => {
  ['from', 'to'].forEach(periode => {
    select[field][periode].addEventListener('change', ({ target: { value } }) => {

      const // 
        affichage = document.querySelector(`#select-${periode}-date--target .cs-from-to-value--heure`),
        {
          value: heure_debut
        } = select.heure[periode].options[select.heure[periode].selectedIndex],
        {
          value: minute_debut
        } = select.minute[periode].options[select.minute[periode].selectedIndex],
        heure_en_toutes_lettres = `${heure_debut}:${minute_debut}`;

      affichage.innerText = heure_en_toutes_lettres;
      form.heure[periode].value = heure_en_toutes_lettres;

      if (field === 'heure') {
        onReady(`#select-${periode}-minute option`).then(() => {
          const //
            minutes_options = [...document.querySelectorAll(`#select-${periode}-minute option`)],
            open_hours = minutes_options.map(({ value: minute }) => {
              return _heures.find(({ debut: heure_debut, fin: heure_fin }) => {
                return is_disabled(value, minute, heure_debut, heure_fin);
              }) ? false : minute;

            }).filter(Boolean);
          minutes_options.forEach(opt => {
            opt.disabled = !open_hours.includes(opt.value);
          });

        });
      }
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
  [...document.getElementsByClassName('aie')].forEach(elt => elt.classList.remove('aie'));

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
    curr = time_debut,
    has_error = false;
  while (curr < time_fin) {
    curr = addDay(curr);
    const target = document.querySelector(`.cs-td-daynum[data-date="${val(curr)}"]`);
    if (target.classList.contains('striked')) {
      target.classList.add('aie');
      bandeau.classList.remove('hidden');
      form.submit.classList.add('hidden');
      has_error = true;
    } else {
      target.classList.add('between');
    }
  }

  if (document.getElementsByClassName('aie').length === 0)
    form.submit.classList.remove('hidden');

  return has_error;
};

function is_disabled(h, m = '00', start = '24:00', end = '00:00') {

  const //
    int = heure => +heure.replace(/[^\d]/g, ''),
    curr = +('' + h + m),
    heure_debut = int(start),
    heure_fin = int(end);
  // - je peux choisir une heure AVANT heure_debut, ou APRÈS heure fin de réservation en cours
  if (curr < heure_debut)
    return false;
  if (curr >= heure_fin) {
    return false;
  }

  return true;
}

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
