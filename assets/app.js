import '@gouvfr/dsfr/dist/dsfr.css';
import "@gouvfr/dsfr/dist/utility/icons/icons.main.min.css";
import "@gouvfr/dsfr/dist/dsfr/dsfr.module";

import './styles/app.scss';

const //
  scrollLen = 160,
  onReady = async selector => {
    while (document.querySelector(selector) === null)
      await new Promise(resolve => requestAnimationFrame(resolve))
    return document.querySelector(selector);
  };

// bouton "retour en haut de page"
onReady('#hideOnScroll').then(elt => {

  elt.addEventListener('click', () => {
    let i = document.body.scrollTop || document.documentElement.scrollTop;
    while (i > scrollLen) {
      (function (i) {
        setTimeout(function () {
          document.body.scrollTop -= 1;
          document.documentElement.scrollTop -= 1;
        }, i / 100)
      })(i--)
    }
  });

  window.addEventListener('scroll', () => {
    if (document.body.scrollTop > scrollLen || document.documentElement.scrollTop > scrollLen) {
      elt.classList.add('hiddenOnScroll');
    } else {
      elt.classList.remove('hiddenOnScroll');
    }
  });


});