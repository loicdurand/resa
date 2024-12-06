import { time } from '../../lib/utils';

import Periode from './Periode';
import ModalManager from './ModalManager';

console.log('=== reserver ===');

window.addEventListener('scroll', () => {
  const // 
    ctnr = document.getElementById('cs-filtres-container'),
    ctnr_offset = ctnr.offsetTop,
    scrollTop = document.body.scrollTop || document.documentElement.scrollTop,
    filtres_pos = ctnr_offset - scrollTop;
  if (filtres_pos < 0) {
    ctnr.classList.add('fix');
  } else {
    ctnr.classList.remove('fix');
  }
});

const // 
  calendriers = document.getElementById('calendars-container'),
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
  },
  modal = new ModalManager(),
  periode = new Periode()
    .setListeners([
      {
        elt: document.getElementById('cs-btn--from'),
        evt: 'click',
        cb: (periode, { target }) => {
          if (!target.classList.contains('bordered'))
            [...document.getElementsByClassName('desactivee-from')].forEach(elt => elt.classList.remove('desactivee-from'))
          periode.set('from');
        }
      },
      {
        elt: document.getElementById('cs-btn--to'),
        evt: 'click',
        cb: (periode, { target }) => {
          if (!target.classList.contains('bordered'))
            [...document.getElementsByClassName('desactivee-to')].forEach(elt => elt.classList.remove('desactivee-to'))
          periode.set('to');
        }
      },
      {
        elt: document.getElementById('modal-suivant'),
        evt: 'click',
        cb: periode => periode.set('to')
      },
      {
        elt: document.getElementById('modal-fermer'),
        evt: 'click',
        cb: periode => periode.set('from')
      },
      {
        elt: calendriers,
        evt: 'click',
        cb: click_on_date
      },
      {
        elt: document.getElementById('fr-modal--from'),
        evt: 'click',
        cb: (periode, { target }) => target.matches('#fr-modal--from') && periode.set('to')
      },
      {
        elt: document.getElementById('fr-modal--to'),
        evt: 'click',
        cb: (periode, { target }) => target.matches('#fr-modal--to') && periode.set('from')
      }
    ])
    .onchange(that => {

      // bordure bleue au dessus des boutons Début: __ -> Fin: __
      document.getElementById(`cs-btn--${that.periode}`).classList.add('bordered');
      document.getElementById(`cs-btn--${that.other}`).classList.remove('bordered');

      [...document.querySelectorAll(`div[aria-controls="fr-modal--${that.other}"]`)].forEach(elt => {
        elt.setAttribute('aria-controls', `fr-modal--${that.periode}`);
      });

      modal
        .setPeriode(that.periode);

    });

['heure', 'minute'].forEach(field => {
  ['from', 'to'].forEach(periode => {
    select[field][periode].addEventListener('change', ({ target: { value } }) => {

      const { value: heure_debut } = select.heure[periode].options[select.heure[periode].selectedIndex] || { value: '08' };

      if (field === 'heure')
        modal.manage_minutes(heure_debut, select.minute[periode]);

      const // 
        affichage = document.querySelector(`#select-${periode}-date--target .cs-from-to-value--heure`),
        { value: minute_debut } = select.minute[periode].options[select.minute[periode].selectedIndex],
        heure_en_toutes_lettres = `${heure_debut}:${minute_debut}`;

      affichage.innerText = heure_en_toutes_lettres;

      updateForm(periode, heure_en_toutes_lettres);

    });

  });
});

function updateForm(periode, heure) {
  const // 
    clicked_date = document.querySelector(`.selected-${periode}`),
    { dataset: { date } } = clicked_date;

  form.date[periode].value = date;
  form.heure[periode].value = heure;

  const //
    other = periode === 'from' ? 'to' : 'from',
    is_valid_form = !!(form.date[other].value && form.heure[other].value);

  if (is_valid_form)
    form.submit.classList.remove('hidden');
  else
    form.submit.classList.add('hidden');

}

/**
 * 
 * @param {Periode} periode 
 * @param {Event} event 
 * @void
 * fonction principale, déclenchée lors d'un click sur une date d'un calendrier
 * 
 */
function click_on_date(periode, { target: tgt }) {

  const target = tgt.dataset.date ? tgt : tgt.previousElementSibling;

  // s'il y avait déjà une précédente sélection, on la vire
  const selection_precedente = document.querySelector(`.selected-${periode.get()}`);
  if (selection_precedente) {
    selection_precedente.classList.remove(`selected-${periode.get()}`);
  }

  /**
   * Si l'utilisateur clique sur une case "désactivée", il ne se passe rien.
   * - pas d'ouverture de modale
   * - on quitte
   */
  if (!modal.is_clickable(target)) {
    modal.close(target)
    return false;
  }

  target.classList.add(`selected-${periode.get()}`);

  const // 
    from = document.querySelector('.selected-from'),
    to = document.querySelector('.selected-to');

  /**
   * Si l'utilisateur choisi la date de début,
   * on l'empêche de choisir une date antérieure pour la fin,
   * ou une date après une réservation.
   */
  if (periode.get() === 'from') {
    [...document.getElementsByClassName('desactivee-to')].forEach(elt => elt.classList.remove('desactivee-to'));
    masque_dates_avant(from);
    masque_apres_resa(target);
  } else {
    [...document.getElementsByClassName('desactivee-from')].forEach(elt => elt.classList.remove('desactivee-from'));
    masque_dates_apres(to);
    masque_avant_resa(target);
  }

  setBetweenDates(from, to);

  /**
   * Lorsque la modale s'ouvre, on affiche la date
   * en toutes lettres en guise de titre,
   * et on affiche cette date dans le champs Début: __ -> Fin: __
   */
  const //
    titre_de_la_modale = modal.affiche_date_dans_titre(target),
    affichage_date_choisie = document.getElementById(`${periode.get()}-value--date`);
  affichage_date_choisie.innerHTML = titre_de_la_modale;

  modal.open(target);

  modal.manage_heures(target);

  select.heure[periode.get()].dispatchEvent(new Event('change'));

  /**
   * Quand tout est fini, on peut switcher de période (ex: début -> fin)
   */
  periode.toggle();

}

// PÉRIODE === from
function masque_dates_avant(target) {
  const // 
    premiere_date = document.querySelector('.cs-td-daynum'),
    { dataset: { date: date_debut } } = target,
    { dataset: { date: date_fin } } = premiere_date,
    time_debut = ts(`${date_debut} 08:00:00`),
    time_fin = ts(`${date_fin} 08:00:00`);

  let curr = time_debut;
  while (curr > time_fin) {
    curr = subDay(curr);
    const curr_target = document.querySelector(`.cs-td-daynum[data-date="${val(curr)}"]`);
    curr_target.classList.add('desactivee-from');
    curr_target.removeAttribute('aria-controls');
  }
}

function masque_apres_resa(target) {
  const // 
    dates = document.querySelectorAll('.cs-td-daynum'),
    derniere_date = dates[dates.length - 1],
    { dataset: { date: date_debut } } = target,
    { dataset: { date: date_fin } } = derniere_date,
    time_debut = ts(`${date_debut} 08:00:00`),
    time_fin = ts(`${date_fin} 08:00:00`);

  let //
    trouve = false,
    curr = time_debut;
  while (curr < time_fin) {
    curr = addDay(curr);
    const curr_target = document.querySelector(`.cs-td-daynum[data-date="${val(curr)}"]`);
    if (trouve) {
      curr_target.classList.add('desactivee-from');
      curr_target.removeAttribute('aria-controls');
    }
    if (curr_target.classList.contains('striked')) {
      /**
       * on ajoute une résa particulière, de manière à empêcher les chevauchements
       * ex: si VL déjà réservé de 8H à 9H, et de 12H à 14H,
       * l'utilisateur ne peut rien choisir après 8H
       */
      !trouve && modal.createPseudoResaApres(curr_target);
      trouve = true;
    }
  }
}

// PÉRIODE === to
function masque_dates_apres(target) {
  const // 
    dates = document.querySelectorAll('.cs-td-daynum'),
    derniere_date = dates[dates.length - 1],
    { dataset: { date: date_debut } } = target,
    { dataset: { date: date_fin } } = derniere_date,
    time_debut = ts(`${date_debut} 08:00:00`),
    time_fin = ts(`${date_fin} 08:00:00`);

  let curr = time_debut;
  while (curr < time_fin) {
    curr = addDay(curr);
    const curr_target = document.querySelector(`.cs-td-daynum[data-date="${val(curr)}"]`);
    curr_target.classList.add('desactivee-to');
    curr_target.removeAttribute('aria-controls');
  }
}

function masque_avant_resa(target) {
  const // 
    premiere_date = document.querySelector('.cs-td-daynum'),
    { dataset: { date: date_debut } } = target,
    { dataset: { date: date_fin } } = premiere_date,
    time_debut = ts(`${date_debut} 08:00:00`),
    time_fin = ts(`${date_fin} 08:00:00`);

  let //
    trouve = false,
    curr = time_debut;
  while (curr > time_fin) {
    curr = subDay(curr);
    const curr_target = document.querySelector(`.cs-td-daynum[data-date="${val(curr)}"]`);
    if (trouve) {
      curr_target.classList.add('desactivee-from');
      curr_target.removeAttribute('aria-controls');
    }
    if (curr_target.classList.contains('striked')) {
      /**
       * on ajoute une résa particulière, de manière à empêcher les chevauchements
       * ex: si VL déjà réservé de 8H à 9H, et de 12H à 14H,
       * l'utilisateur ne peut rien choisir après avant 14H
       */
      !trouve && modal.createPseudoResaAvant(curr_target);
      trouve = true;
    }
  }
}

function setBetweenDates(debut, fin) {

  if (debut === null || fin === null)
    return false;

  [...document.getElementsByClassName('between')].forEach(elt => elt.classList.remove('between'));

  const // 
    derniere_date = fin,
    { dataset: { date: date_debut } } = debut,
    { dataset: { date: date_fin } } = derniere_date,
    time_debut = ts(`${date_debut} 08:00:00`),
    time_fin = ts(`${date_fin} 08:00:00`);

  let curr = (time_debut);
  while (curr < time_fin) {
    curr = addDay(curr);
    const curr_target = document.querySelector(`.cs-td-daynum[data-date="${val(curr)}"]`);
    curr_target.classList.add('between');
  }
}

/*
 * FONCTIONS UTILES POUR LES DATES
 */

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

