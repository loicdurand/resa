export default () => {

    const // 

        debounce = (func, delay) => {
            let timeoutId;
            return function (...args) {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    func.apply(this, args);
                }, delay);
            };
        },

        fetchAutocompleteData = debounce(async (e) => {
            const term = input.value.trim();
            if (term.length < 2) {
                suggestionsList.innerHTML = ''; // Vide les suggestions si saisie trop courte
                return;
            }

            try {
                const response = await fetch(`/resa971/autocomplete/candidat?term=${encodeURIComponent(term)}`);
                const suggestions = await response.json();

                // Vide la liste actuelle (hors choix dans la datalist)
                if (e instanceof InputEvent)
                    suggestionsList.innerHTML = '';

                // Remplit la list avec les nouvelles suggestions
                suggestions.forEach(suggestion => {
                    const listElt = document.createElement('li');
                    listElt.dataset.value = suggestion.value; // Valeur insérée dans l'input
                    listElt.textContent = suggestion.label; // Texte affiché (ex: "Doe (12345)")
                    suggestionsList.appendChild(listElt);
                });

            } catch (error) {
                console.error('Erreur lors de la récupération des suggestions:', error);
                suggestionsList.innerHTML = '';
            }
        }, 450);

    if (document.getElementById('suggest-candidat-list') === null)
        return false;

    const input = document.getElementById('suggest-candidat');
    const suggestionsList = document.getElementById('suggest-candidat-list');
    const output = document.getElementById('form-field--user');
    let data;

    const // 
        select = document.getElementById('select-user'),
        champ_suggest = document.getElementById('input-group-suggest-candidat');

    select.addEventListener('change', ({ target }) => {
        const value = target.options[target.options.selectedIndex].value;

        if (value === 'other')
            champ_suggest.classList.remove('fr-hidden');
        else
            champ_suggest.classList.add('fr-hidden');
    });

    input.addEventListener('input', async e => fetchAutocompleteData(e));

    suggestionsList.addEventListener('click', (e) => {
        // actions déclenchées lors du choix dans la datalist
        const target = e.target;

        const [nigend, displayname, mail] = target.textContent.split(' - ');
        data = { nigend, displayname, mail };
        input.value = target.textContent;
        suggestionsList.innerHTML = '';
        console.log('item selected: ' + nigend);
        output.value = nigend.trim();

    });

    console.log('done');

}