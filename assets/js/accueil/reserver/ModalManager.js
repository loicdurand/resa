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
    let class_bloquante = !classes_non_cliquables.find(cls => target.classList.contains(cls));
    return class_bloquante || target.classList.contains('striked_debut') || target.classList.contains('striked_fin');
  }

  open(trigger) {
    trigger.setAttribute('data-fr-opened', 'true');
  }

  close(trigger) {
    trigger.setAttribute('data-fr-opened', 'false');
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

  manage_select(target) {
    const // 
      { dataset } = target,
      {
        ref,
        date
      } = dataset,
      th = document.getElementById(`th-${ref}`),
      {
        dataset: {
          horaires
        }
      } = th,
      heures = horaires.split(','),
      select_heures = document.getElementById(`select-${this.periode}-heure`),
      select_minutes = document.getElementById(`select-${this.periode}-minute`),
      is_striked = target.classList.contains('striked');
    console.log(heures);

    // CAS FACILE: pas de réservation
    if (!is_striked) {
      this.addOptions(select_heures, heures);
    } else {
      const //
        reservations = this.getResas(dataset);
      console.log({ reservations });
      this.affiche_infos_resas(reservations);
    }

  }

  addOptions(select, nbs) {
    select.options.length = 0;
    nbs.forEach(nb => {
      const // 
        n = addZeros(nb),
        option = document.createElement('option');
      option.value = n;
      option.innerText = n;
      select.appendChild(option);
    })
  }

  getResas(dataset) {
    const heures = [];
    for (let prop in dataset) {
      if (/resa_[\d+]_debut/.test(prop)) {
        const [, index, period] = prop.split('_');
        heures.push({ debut: dataset[prop] || '00:00', fin: dataset[`resa_${index}_fin`] || '23:59' });
      }
    }
    return heures;
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

}