import * as refs from '../../lib/refs';
import { addZeros } from '../../lib/utils';

export default class ModalManager {

  periode;

  constructor() {
    return this;
  }

  setPeriode(periode) {
    this.periode = periode;
    return this;
  }

  is_clickable(target) {

    const classes_non_cliquables = [
      'desactivee-from',
      'desactivee-to',
      'striked',
      'before_now',
      'after_limit',
      'csag_ferme'
    ];

    if (target === null || !target.classList.contains('cs-td-daynum'))
      return false;

    let class_bloquante = !classes_non_cliquables.find(cls => target.classList.contains(cls));
    return class_bloquante || target.classList.contains('striked_debut') || target.classList.contains('striked_fin');
  }

  open(trigger) {
    trigger.setAttribute('data-fr-opened', 'true');
  }

  close(trigger) {
    trigger?.setAttribute('data-fr-opened', 'false');
  }

  affiche_date_dans_titre(target) {
    const //
      {
        dataset: {
          ref,
          date
        }
      } = target,
      [Y, m, d] = date.split('-'),
      titre_de_la_modale = document.getElementById(`${this.periode}-date-lib`),
      date_en_toutes_lettres = `${refs.jours[ref]} ${d} ${(refs.mois[+m]).slice(0, 3)}<span class="hide-s">&nbsp;${Y}</span>`

    titre_de_la_modale.innerHTML = date_en_toutes_lettres;
    return date_en_toutes_lettres;

  }

  manage_heures(target) {
    const // 
      { dataset } = target,
      { ref } = dataset,
      th = document.getElementById(`th-${ref}`),
      { dataset: { horaires } } = th,
      heures = horaires.split(','),
      select_heures = document.getElementById(`select-${this.periode}-heure`),
      is_striked = target.classList.contains('striked');

    // CAS FACILE: pas de réservation
    if (!is_striked) {
      this.addOptions(select_heures, heures);
    } else {
      let nbs = []; // les heures affichées dans le <select/>
      const //
        disabledNbs = [], // les heures "disabled"
        reservations = this.getResas(dataset);
      reservations.forEach(({ debut: heure_debut, fin: heure_fin }) => {
        let // 
          iDebut = ModalManager.int(heure_debut),  // ex: 08:00 -> 800
          iFin = ModalManager.int(heure_fin);      // ex: 17:30 -> 1730
        // si période == début, on peut réserver au moins 15mn avant une réservation
        console.log({ iDebut });
        if (this.periode === 'from')
          iDebut -= iDebut % 100 ? (40 + 15) : 15; // ex: 800 -> 745, 730 -> 715
        else
          iDebut += ('' + iDebut).endsWith('45') ? (40 + 15) : 15; // ex: 845 -> 900, 730 -> 745
        console.log({ iDebut });


        nbs = heures
          .map(h => {
            const iHeure = this.periode === 'from' ? +h * 100 + 45 : +h * 100 // ex: 8 -> 845, 10 -> 1045;
            if (iHeure >= iDebut && iHeure < iFin)
              disabledNbs.push(h);
          });

      });

      this.addOptions(select_heures, heures, disabledNbs);

      this.affiche_infos_resas(reservations);

    }

  }

  manage_minutes(heure_choisie, select_minute) {
    let nbs = []; // les minutes affichées dans le <select/>
    const //
      minutes = [...select_minute.options].map(opt => opt.value),
      disabledNbs = [], // les minutes "disabled"
      clicked_date = document.querySelector(`.selected-${this.periode}`),
      { dataset } = clicked_date,
      h = +heure_choisie,
      reservations = this.getResas(dataset);
    reservations.forEach(({ debut: heure_debut, fin: heure_fin }) => {
      let // 
        iDebut = ModalManager.int(heure_debut),  // ex: 08:00 -> 800
        iFin = ModalManager.int(heure_fin);      // ex: 17:30 -> 1730
      // si période == début, on peut réserver au moins 15mn avant une réservation
      if (this.periode === 'from' && iDebut != 0)
        iDebut -= iDebut % 100 == 0 ? (40 + 15) : 15; // ex: 800 -> 745, 730 -> 715

      nbs = minutes
        .map(m => {
          const iHeure = h * 100 + +m // ex: 8 -> 845, 10 -> 1045;
          if (iHeure > iDebut && iHeure < iFin)
            disabledNbs.push(m);
        });

    });

    this.addOptions(select_minute, minutes, disabledNbs);

  }

  addOptions(select, nbs, disabledNbs = []) {
    select.options.length = 0;
    nbs.forEach(nb => {
      const // 
        n = addZeros(nb),
        option = document.createElement('option');
      option.value = n;
      option.innerText = n;
      option.disabled = disabledNbs.includes(nb)
      select.appendChild(option);
    });
  }

  getResas(dataset) {
    const heures = [];

    if (dataset.resa_999_debut)
      return this.periode === 'to' ?
        [{ debut: dataset.resa_999_debut, fin: '23:59' }] :
        [{ debut: '00:00', fin: dataset.resa_999_fin }];

    for (let prop in dataset) {
      if (/resa_[\d+]_debut/.test(prop)) {
        const [, index, period] = prop.split('_');
        heures.push({ debut: dataset[prop] || '00:00', fin: dataset[`resa_${index}_fin`] || '23:59' });
      }
    }
    return heures;
  }

  deletePseudoResas() {
    const elt = this.periode === 'from' ?
      document.querySelector('[data-resa_999_fin="23:59"]') :
      document.querySelector('[data-resa_999_debut="00:00"]');
    if (elt === null)
      return false;
    elt.removeAttribute('dataset.resa_999_debut');
    elt.removeAttribute('dataset.resa_999_fin');
  }

  createPseudoResaAvant(target) {
    this.deletePseudoResas();
    const { dataset } = target;
    let max_heure = '00:00';
    for (let prop in dataset) {
      if (/resa_[\d+]_fin/.test(prop)) {
        max_heure = ModalManager.int(dataset[prop]) > ModalManager.int(max_heure) ? dataset[prop] : max_heure;
      }
    }
    target.dataset.resa_999_debut = '00:00';
    target.dataset.resa_999_fin = max_heure;
  }

  createPseudoResaApres(target) {
    this.deletePseudoResas();
    const { dataset } = target;
    let min_heure = '23:59';
    for (let prop in dataset) {
      if (/resa_[\d+]_debut/.test(prop)) {
        min_heure = ModalManager.int(dataset[prop]) < ModalManager.int(min_heure) ? dataset[prop] : min_heure;
      }
    }
    target.dataset.resa_999_debut = min_heure;
    target.dataset.resa_999_fin = '23:59';
  }

  affiche_infos_resas(heures) {
    if (!heures.length)
      return false;

    const //
      notice = document.getElementById(`notice-resa-${this.periode}`),
      list = document.getElementById(`text-resa-${this.periode}`);

    list.innerHTML = '';
    notice.classList.remove('hidden');
    heures.forEach(({ debut, fin }) => {
      const // 
        heure_debut = debut != '00:00' ? debut : false,
        heure_fin = fin != '23:59' ? fin : false,
        li = document.createElement('li');
      if (heure_debut && heure_fin)
        li.innerText = `de ${heure_debut} à ${heure_fin}`;
      else if (heure_debut)
        li.innerText = `à partir de ${heure_debut}`;
      else
        li.innerText = `jusqu'à ${heure_fin}`;
      list.appendChild(li);
    });

  }

  static int(heure) {
    return +heure.replace(/[^\d]/g, '');
  }

  static str(heure) {
    const s = addZeros(heure, 4);
    return `${s.substring(0, 2)}:${s.substring(2, 4)}`;
  }

}