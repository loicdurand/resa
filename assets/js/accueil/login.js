const //
  select = document.getElementsByName('select-nigend')[0],
  input = document.getElementById('user_nigend');

select.addEventListener('change', ({ target: { value } }) => {
  input.value = value;
});