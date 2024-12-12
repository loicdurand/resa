import { addZeros } from '../lib/utils';

console.log('=== compte ===');


const // 
  [table] = document.getElementsByClassName('cs-table-horaires'),
  form = document.querySelector('form[name="horaire_ouverture"]'),
  submit = form.querySelector('[type="submit"]'),
  inputs = {};

['code_unite', 'jour', 'creneau'].forEach(field => inputs[field] = document.getElementById(`form-field--${field}`));
['debut', 'fin'].forEach(field => {
  inputs[field] = document.getElementById(`horaire_ouverture_${field}`);
  inputs[field].addEventListener('keyup', ({ target: { value } }) => {
    if (/^\d\d:\d\d$/.test(addZeros(value, 5)))
      submit.disabled = false;
    else
      submit.disabled = true;
  });

});

table.addEventListener('click', ({ target }) => {

  if (target.nodeName !== 'BUTTON' && !target.matches('[aria-controls="cs-modale-horaires"]'))
    return false;

  const //
    { creneau, jour } = target.dataset,
    tr = target.parentElement.parentElement,
    btns = [...tr.querySelectorAll('button')],
    [debut_AM, fin_AM, debut_PM, fin_PM] = btns.map(btn => btn.innerText.trim());

  inputs.jour.value = jour;
  inputs.creneau.value = creneau;
  if (creneau === 'AM') {
    inputs.debut.value = debut_AM;
    inputs.fin.value = fin_AM;
  } else {
    inputs.debut.value = debut_PM;
    inputs.fin.value = fin_PM;
  }
});


