// STYLES
import '/node_modules/@gouvfr/dsfr/dist/dsfr.css';
import "/node_modules/@gouvfr/dsfr/dist/utility/icons/icons.main.min.css";
import './styles/app.scss';

// JAVASCRIPTS
import "/node_modules/@gouvfr/dsfr/dist/dsfr/dsfr.module";
import Router from '@bleckert/router';

new Router('/', {
  '/connexion': () => {
    import('./js/accueil/login')
  },
  '/reserver': () => {
    import('./js/accueil/reserver/reserver');
  },
  '/parc': () => {
    import('./js/parc/parc');
  },
  '/compte':()=>{
    import('./js/compte/compte');
  },
  '/validation':()=>{
    import('./js/compte/validation');
  },
  '^$': () => {
    import('./js/accueil/index/index');
  }
});