import axios from 'axios';

console.log('=== validation ===');

const // 

  btns = {
    valid_resa: [...document.getElementsByClassName('valid-resa')]
  };

btns.valid_resa.forEach(btn => btn.addEventListener('click', ({ target: { dataset } }) => {
  axios.post('/validation/valid', dataset)
    .then((response) => {
      console.log(response);
    })
    .catch(console.log);
}));