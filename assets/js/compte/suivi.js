import axios from 'axios';

console.log('=== suivi ===');

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
        const txtVehicule = row.children[3].dataset.vl.toLowerCase();
        const txtUser = row.children[5].innerText.toLowerCase();

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

const checkboxes = document.querySelectorAll('#restrictions-form [type="checkbox"]');

if (checkboxes.length) {
    document.body.classList.remove('container');
    [...document.getElementsByClassName('fr-container')].forEach(elt => elt.classList.add('plus-large'));
}

checkboxes.forEach(checkbox => {
    checkbox.addEventListener('change', ({ currentTarget: { checked, id } }) => {
        const checkeds = [...checkboxes].filter(chx => chx.checked).map(chx => chx.id.replace(/.*-/g, ''));
        const rows = document.querySelectorAll('tbody tr:not(.no-result)');
        rows.forEach(row => {
            if (!checkeds.length || checkeds.includes(row.dataset.restriction)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        })

    })
});

const modale_suivi = document.getElementById('cs-modale-suivi');
const beneficiaire = document.getElementById('modale-suivi--beneficiaire');
const attrs = ['id', 'observation', 'user', 'demandeur', 'img'];
const edit_btns = [...document.getElementsByClassName('cs-btn--icon-on-hover')];

const input_obs = document.getElementById('input-observ_valideur');
const submit_obs = document.getElementById('submit-observ_valideur');
const loader = document.getElementById('submit-observ_valideur--loader');

edit_btns.forEach(btn => {
    btn.addEventListener('click', e => {
        beneficiaire.classList.remove('fr-hidden');
        const { currentTarget: { dataset } } = e;

        attrs.forEach(attr => {
            const elt = modale_suivi.querySelector(`#modale-suivi--${attr}`);
            if (attr == 'id') {
                input_obs.dataset.id = dataset[attr];
            } else if (attr != 'img') {
                if (attr == 'demandeur' && dataset[attr] == '')
                    beneficiaire.classList.add('fr-hidden');
                elt.innerText = dataset[attr];
            } else {
                elt.setAttribute('src', dataset[attr]);
            }
        });
    });
});



input_obs.addEventListener('keyup', e => {
    const { target: { value } } = e;
    if (value === '')
        submit_obs.disabled = true;
    else
        submit_obs.disabled = false;
});

submit_obs.addEventListener('click', e => {
    loader.classList.remove('hidden');
    loader.classList.add('loading');
    const msg = input_obs.value;
    const id = input_obs.dataset.id;
    if (msg != "") {
        console.log({ id, msg });
        axios.post('/resa971/validation/changementobservation', { id, msg })
            .then(({ status, data = {} }) => {
                console.log({ status });
                if (status != 200)
                    location.reload();
                input_obs.value = '';
                submit_obs.disabled = true;
                if (msg.length >= 23)
                    document.getElementById(`observation-resa-${id}`).outerHTML = `<abbr id="observation-resa-${id}" class="clip" title="${msg}">${msg}</abbr>`;
                else
                    document.getElementById(`observation-resa-${id}`).outerHTML = `<span id="observation-resa-${id}">${msg}</span>`;
                close_modale();
            })
            .catch(err => {
                loader.classList.remove('loading');
                location.reload();
            });
    }
});

function close_modale(modal_name = 'cs-modale-suivi') {
    const // 
        modal = document.getElementById(`${modal_name}`),
        triggers = document.querySelectorAll('[data-fr-opened="true"]');
    modal.setAttribute('open', 'false');
    modal.classList.remove('fr-modal--opened');
    triggers.forEach(trigger => trigger.setAttribute('data-fr-opened', 'false'));
    loader.classList.add('hidden');
    loader.classList.remove('loading');
}
