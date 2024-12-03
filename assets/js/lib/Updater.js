import { addZeros, time } from './utils';

const { Y, M, D, H, m, s } = time()

class Emitter {

  evtEmitter = document.createElement('div');

  constructor() {
    return this;
  }

  addEventListener(listenerName, cb) {
    this.evtEmitter.addEventListener(listenerName, cb);
  }
};


export default class Updater extends Emitter {

  is_init = false;
  is_date_filtered = false;

  heures_ouverture = [];

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

  ref_debut = `${Y}-${M}-${D} ${H}:00:00`;
  ref_fin = '';

  update = new Event("update");

  constructor() {

    super();
    this.evtEmitter.dataset.debut = "*";
    this.evtEmitter.dataset.fin = "*";

    let // 
      base = 5,
      tmp_value = 4;
    ['subtract', 'add'].forEach(field => {
      const // 
        input = document.getElementById('input--nb-places'),
        btn = document.getElementById(`cs-nb-places--button-${field}`),
        min = 2,
        max = 9;
      btn.addEventListener('click', e => {

        if (!this.is_init)
          this.is_init = this.init();

        if (input.value !== 'Indifférent') {
          if (field === 'add') {
            tmp_value = tmp_value + 1;
            if (tmp_value <= max) {
              input.value = tmp_value;
            } else {
              input.value = 'Indifférent';
            }
          } else {
            tmp_value = tmp_value - 1;
            if (tmp_value >= min) {
              input.value = tmp_value;
            } else {
              input.value = 'Indifférent';
            }
          }
        } else {
          input.value = base;
          tmp_value = base;
        }
        this.evtEmitter.dataset.nbplaces = input.value === 'Indifférent' ? '*' : input.value;
        this.evtEmitter.dispatchEvent(this.update);
      });
    });

    [...document.querySelectorAll('#fr-modal--categorie [data-categorie]')].forEach((btn, i, btns) => {
      btn.addEventListener('click', ({ currentTarget: target }) => {

        if (!this.is_init)
          this.is_init = this.init();

        btns.forEach(btn => btn.classList.remove('selected'));
        target.classList.add('selected');
        this.evtEmitter.dataset.categorie = target.dataset.categorie;
        this.evtEmitter.dispatchEvent(this.update);
      })
    });

    ['serigraphie', 'transmission'].forEach(field => {
      [...document.getElementsByName(`radio--${field}`)].forEach(radio => {
        radio.addEventListener('change', ({ target: { value } }) => {

          if (!this.is_init)
            this.is_init = this.init();

          this.evtEmitter.dataset[field] = value;
          this.evtEmitter.dispatchEvent(this.update);
        });
      });
    });


    return this;

  }

  init() {

    const  // 
      lastOption = this.modale.fin.date.options[this.modale.fin.date.selectedIndex],
      last_date = lastOption.value,
      last_periode = (lastOption.dataset.pm || lastOption.dataset.am),
      [, last_heure] = last_periode.split(/-/);

    this.ref_fin = `${last_date} ${last_heure}:00`;

    let //
      starts_auj = true,
      CSAG_ouvert_auj = true,
      CSAG_horaire_depasse = false,
      option = this.modale.debut.date.querySelector('option');

    while (option.disabled) {
      starts_auj = false;
      CSAG_ouvert_auj = false;
      option.removeAttribute('selected');
      option = option.nextElementSibling;
    }

    const // 
      heures_ouverture = this.get_heures_ouverture(option),
      min = Math.min(...heures_ouverture.debut, ...heures_ouverture.fin),
      max = Math.max(...heures_ouverture.debut, ...heures_ouverture.fin),
      now_hors_horaires_CSAG = (!starts_auj || +H < min) ? heures_ouverture[0] : +H > max;

    // ex: je suis sur le site à 22H. Le CSAG était ouvert aujourd'hui, pourtant il est fermé
    if (now_hors_horaires_CSAG) {
      option.disabled = true;
      return new Updater();
    }

    ['debut', 'fin'].forEach(creneau => {
      this.modale[creneau].heure.length = 0;
      const heures = creneau === 'debut' ? heures_ouverture.debut : this.get_heures_ouverture(lastOption).fin;

      for (let i = heures[0]; i <= heures[heures.length - 1] && i < 50; i++) {
        const opt = document.createElement('option');
        opt.value = i;
        opt.innerText = addZeros(i, 2);
        CSAG_horaire_depasse = creneau === 'debut' ? CSAG_ouvert_auj && i <= +H : false;
        if (!heures.includes(i) || CSAG_horaire_depasse || (!now_hors_horaires_CSAG && i < +H)) {
          opt.disabled = true;
          opt.title = "En dehors des horaires d'ouverture du CSAG";
        }

        if (creneau === 'fin' && i === heures[heures.length - 1])
          opt.selected = true;

        this.modale[creneau].heure.appendChild(opt);
      }

    });

    // PATCH
    if (this.modale['debut'].heure.selectedIndex < 0)
      this.modale['debut'].heure.options[0].setAttribute('selected', 'selected')

    option.setAttribute('selected', 'selected');

    this.set_ref_debut();
    this.set_ref_fin();

    ['date', 'heure'].forEach(part => {

      this.modale.debut[part].addEventListener('change', e => {
        this.is_date_filtered = true;
        this.set_ref_debut();
        this.addDays(e);
        this.removeErrorText();
        this.evtEmitter.dataset.debut = this.get_ref_debut();
        this.evtEmitter.dispatchEvent(this.update);
      });

      this.modale.fin[part].addEventListener('change', e => {
        this.is_date_filtered = true;
        this.removeErrorText();
        this.set_ref_fin('show_error');
        this.addDays(e);
        this.evtEmitter.dataset.fin = this.get_ref_fin();
        this.evtEmitter.dispatchEvent(this.update);
      });

    });

    return 'done';

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

    this.update_filtres('debut', text, short, heure);

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
      let // 
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

    this.update_filtres('fin', text, short, heure);

    return this;
  }

  update_filtres(DEBUT_OU_FIN, text, short, heure) {
    if (this.is_date_filtered) {
      this.filtres[DEBUT_OU_FIN].date.children[0].innerText = text;
      this.filtres[DEBUT_OU_FIN].date.children[1].innerText = short;
      this.filtres[DEBUT_OU_FIN].heure.innerText = heure;
      this.evtEmitter.dataset.debut = this.get_ref_debut();
      this.evtEmitter.dataset.fin = this.get_ref_fin();
    }
    ['categorie', 'serigraphie', 'nbplaces', 'transmission'].forEach(field => {
      this.evtEmitter.dataset[field] = '*';
    });
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