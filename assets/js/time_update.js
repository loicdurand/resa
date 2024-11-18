import { addZeros, time, subMinutes } from './utils';

const { Y, M, D, H, m, s } = time()


export default class Update {

  heures_ouverture = [];

  ref_debut = `${Y}-${M}-${D} ${H}:00:00`;
  ref_fin = `${Y}-${M}-${D} ${addZeros(+H + 1, 2)}:00:00`;

  filtres = {
    debut: {
      date: document.getElementById('select-from-date--target'),
      heure: document.getElementById('select-from-heure--target')
    },
    fin: {
      date: document.getElementById('select-to-date--target'),
      heure: document.getElementById('select-to-heure--target')
    }
  };

  modale = {
    debut: {
      date: document.getElementById('select-from-date'),
      heure: document.getElementById('select-from-heure')
    },
    fin: {
      date: document.getElementById('select-to-date'),
      heure: document.getElementById('select-to-heure')
    }
  };

  constructor() {

    let //
      CSAG_ouvert_auj = true,
      CSAG_horaire_depasse = false,
      option = this.modale.debut.date.querySelector('option');
    while (option.disabled) {
      CSAG_ouvert_auj = false;
      option.removeAttribute('selected');
      option = option.nextElementSibling;
    }

    const // 
      heures_ouverture = this.get_heures_ouverture(option),
      now_apres_fermeture_CSAG = CSAG_ouvert_auj && (+H > Math.max(...heures_ouverture.debut, ...heures_ouverture.fin));

    // ex: je suis sur le site à 22H. Le CSAG était ouvert aujourd'hui, pourtant il est fermé
    if (now_apres_fermeture_CSAG) {
      option.disabled = true;
      return new Update();
    }

    ['debut', 'fin'].forEach(creneau => {
      this.modale[creneau].heure.length = 0;
      const heures = heures_ouverture[creneau];
      for (let i = heures[0]; i <= heures[heures.length - 1] && i < 50; i++) {
        const opt = document.createElement('option');
        opt.value = i;
        opt.innerText = addZeros(i, 2);
        CSAG_horaire_depasse = CSAG_ouvert_auj && i <= +H;
        if (!heures.includes(i) || CSAG_horaire_depasse) {
          opt.disabled = true;
          opt.title = "En dehors des horaires d'ouverture du CSAG";
        }
        this.modale[creneau].heure.appendChild(opt);
      }

    });

    option.setAttribute('selected', 'selected');

    this.set_ref_debut();
    this.set_ref_fin();

    ['date', 'heure'].forEach(part => {
      this.modale.debut[part].addEventListener('change', target => {
        this.set_ref_debut();
        this.addDays(target);
        this.removeErrorText();
      });
    });

    ['date', 'heure'].forEach(part => {
      this.modale.fin[part].addEventListener('change', target => {
        this.removeErrorText();
        this.set_ref_fin('show_error');
        this.addDays(target);
      });
    });

    return this;
  }

  get_ref_debut() {
    const //
      debut = this.modale.debut,
      iDate = debut.date.options[debut.date.selectedIndex].value,
      date_selectionnee = addZeros(iDate, 2),
      iHeure = debut.heure.options[debut.heure.selectedIndex].value,
      heure_selectionnee = addZeros(iHeure, 2);
    return `${date_selectionnee} ${heure_selectionnee}:00:00`;
  }

  // appelé lors d'un choix dans la modale
  set_ref_debut(str = this.get_ref_debut()) {

    this.ref_debut = str;

    const // 
      int_ref_debut = +this.ref_debut.replace(/[^\d]/g, ''),
      int_ref_fin = +this.ref_fin.replace(/[^\d]/g, '');

    if (int_ref_debut >= int_ref_fin) {
      const // 
        date_fin = this.modale.fin.date.querySelector(`option[value="${this.ref_debut.replace(/\s.*/g, '')}"]`),
        heure_fin = this.modale.fin.heure.querySelector(`option[value="${this.ref_debut.replace(/.*\s[0]?|:.*/g, '')}"]`);
      if (date_fin !== null)
        date_fin.selected = true;
      heure_fin.nextElementSibling.selected = true;
      this.set_ref_fin();
    }

    const // 
      [date, time] = this.ref_debut.split(/\s+/),
      option = this.modale.debut.date.querySelector(`[value="${date}"]`),
      { innerText, dataset: { short } } = option,
      text = innerText.trim(),
      [H, m] = time.split(/:/),
      heure = `${addZeros(H, 2)}:${addZeros(m, 2)}`;

    this.update_filtres_debut(text, short, heure);

    return this;
  }

  get_ref_fin() {
    const //
      fin = this.modale.fin,
      iDate = fin.date.options[fin.date.selectedIndex].value,
      date_selectionnee = addZeros(iDate, 2),
      iHeure = fin.heure.options[fin.heure.selectedIndex].value,
      heure_selectionnee = addZeros(iHeure, 2);
    return `${date_selectionnee} ${heure_selectionnee}:00:00`;
  }

  set_ref_fin(show_message_err = false) {

    const // 
      str = this.get_ref_fin(),
      old = this.ref_fin;
    this.ref_fin = str;

    const // 
      int_ref_debut = +this.ref_debut.replace(/[^\d]/g, ''),
      int_ref_fin = +this.ref_fin.replace(/[^\d]/g, '');

    if (int_ref_debut >= int_ref_fin) {
      const // 
        date_fin = this.modale.fin.date.querySelector(`option[value="${old.replace(/\s.*/g, '')}"]`),
        heure_fin = this.modale.fin.heure.querySelector(`option[value="${+old.replace(/.*\s[0]?|:.*/g, '')}"]`);

      date_fin.selected = true;
      heure_fin.selected = true;

      this.set_ref_fin(show_message_err);
      if (show_message_err) {
        this.showErrorText();
        setTimeout(() => {
          this.removeErrorText();
        }, 3000);
      }
    }

    const // 
      [date, time] = this.ref_fin.split(/\s/),
      option = this.modale.fin.date.querySelector(`[value="${date}"]`),
      { innerText, dataset: { short } } = option,
      text = innerText.trim(),
      [H, m] = time.split(/:/),
      heure = `${addZeros(H, 2)}:${addZeros(m, 2)}`;

    this.update_filtres_fin(text, short, heure);

    return this;
  }

  update_filtres_debut(text, short, heure) {
    this.filtres.debut.date.children[0].innerText = text;
    this.filtres.debut.date.children[1].innerText = short;
    this.filtres.debut.heure.innerText = heure
  }

  update_filtres_fin(text, short, heure) {
    this.filtres.fin.date.children[0].innerText = text;
    this.filtres.fin.date.children[1].innerText = short;
    this.filtres.fin.heure.innerText = heure
  }

  get_heures_ouverture = (option) => {
    const // 
      heures_ouverture = {
        debut: [],
        fin: []
      },
      { dataset: { am = '', pm = '' } } = option;
    [am, pm].forEach(creneau => {
      if (creneau) {
        const // 
          [debut, fin] = creneau.split(/-/),
          [h_debut] = debut.split(/:/),
          [h_fin] = fin.split(/:/);

        for (let i = +h_debut; i <= +h_fin; i++) {
          if (i < h_fin)
            heures_ouverture.debut.push(i);
          heures_ouverture.fin.push(i);
        }
      }
    });
    return heures_ouverture;
  }

  addDays({ target }) {

    const [, , part] = target.id.split(/-/);

    if (part === 'date') {
      const //
        option = target.children[target.selectedIndex],
        heures_ouverture = this.get_heures_ouverture(option);
      ['debut', 'fin'].forEach(creneau => {
        this.modale[creneau].heure.length = 0;
        const heures = heures_ouverture[creneau];
        for (let i = heures[0]; i <= heures[heures.length - 1] && i < 50; i++) {
          const opt = document.createElement('option');
          opt.value = i;
          opt.innerText = addZeros(i, 2);
          if (!heures.includes(i)) {
            opt.disabled = true;
            opt.title = "En dehors des horaires d'ouverture du CSAG";
          }
          this.modale[creneau].heure.appendChild(opt);
        }

      });
    }
    this.set_ref_debut();
    this.set_ref_fin();
  }

  removeErrorText() {
    const error_text = document.querySelector('#step-2-form-content .fr-error-text');
    if (error_text !== null)
      error_text.outerHTML = '';
  }

  showErrorText() {
    const // 
      error_text = document.querySelector('#step-2-form-content .fr-error-text');
    if (error_text !== null)
      return false;
    const p = document.createElement('p');
    ['fr-col-12', 'fr-error-text', 'fr-mt-1w'].forEach(cls => p.classList.add(cls));
    p.innerHTML = /*html*/`
    <span>
      <span class="bold">
        Changement non pris en compte:
      </span>
      &nbsp;date de fin < date de début!
    </span>
    `;
    document.getElementById('step-2-form-content').appendChild(p);
  }

}