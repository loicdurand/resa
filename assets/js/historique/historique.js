import axios from 'axios';

console.log('=== historique ===');

const edit_btns = [...document.getElementsByClassName('annul-resa')];
const modale = document.getElementById('fr-modal-suppression');

const input_obs = document.getElementById('msg-demandeur--delete');
const submit = document.getElementById('annul-resa--confirm');
const loader = document.getElementById('submit-observ_valideur--loader');

edit_btns.forEach(btn => {
    btn.addEventListener('click', e => {
        const { currentTarget: { dataset: { id } } } = e;
        input_obs.dataset.id = id;
    });
});

submit?.addEventListener('click', e => {
    loader.classList.remove('hidden');
    loader.classList.add('loading');
    const msg = input_obs.value;
    const id = input_obs.dataset.id;
    console.log({ id, msg });
    console.log({ id, msg });
    axios.post('/resa971/historique/annuler', { id, msg })
        .then(({ status, data = {} }) => {
            console.log({ status });
            if (status != 200)
                location.reload();
            input_obs.value = '';
            close_modale();
        })
        .catch(err => {
            loader.classList.remove('loading');
            location.reload();
        });
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