console.log('=== parc/tdb ===');

const // 
  select_affichage = document.getElementById('select-type-affichage'),
  first_table = document.querySelector('table:first-of-type'),
  row_days = first_table.querySelector('thead tr.cs-row-days'),
  jours_affiches_dans_premier_tableau = first_table.querySelectorAll('td:not(:empty):not([colspan])'),
  len = jours_affiches_dans_premier_tableau.length,
  table_suivante = document.querySelector('table:nth-of-type(2)');

if (table_suivante !== null) {
  const //
    target = table_suivante.querySelector('tbody tr'),
    first_td = target.children[0],
    nb_cases_avant = first_td.getAttribute('colspan');

  if (len < 7 && nb_cases_avant !== null) {
    let i = nb_cases_avant;
    const parent = jours_affiches_dans_premier_tableau[0].parentElement;
    first_td.outerHTML = '';
    console.log({ parent });
    while (i--)
      target.prepend(parent.children[i]);

    table_suivante.querySelector('thead').append(row_days);
    first_table.outerHTML = '';
  }
}

select_affichage.addEventListener('change', ({ target: { value } }) => location.href = value);
