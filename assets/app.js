// STYLES
import '/node_modules/@gouvfr/dsfr/dist/dsfr.css';
import "/node_modules/@gouvfr/dsfr/dist/utility/icons/icons.main.min.css";
import './styles/app.scss';

// JAVASCRIPTS
import "/node_modules/@gouvfr/dsfr/dist/dsfr/dsfr.module";
import Router from '@bleckert/router';

new Router('/', {
  '/resa971/connexion': () => {
    import('./js/accueil/login')
  },
  '/resa971/reserver': () => {
    import('./js/accueil/reserver/reserver');
  },
  '/resa971/historique': () => {
    import('./js/historique/historique');
  },
  '/resa971/parc/tdb': () => {
    import('./js/parc/tdb');
  },
  '/resa971/parc': () => {
    import('./js/parc/parc');
  },
  '/resa971/compte': () => {
    import('./js/compte/compte');
  },
  '/resa971/validation': () => {
    import('./js/compte/validation');
  },
  '/resa971/suivi': () => {
    import('./js/compte/suivi');
  },
  '/resa971': () => {
    import('./js/accueil/index/index');
  }
});