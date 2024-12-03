// STYLES
import '/node_modules/@gouvfr/dsfr/dist/dsfr.css';
import "/node_modules/@gouvfr/dsfr/dist/utility/icons/icons.main.min.css";
import './styles/app.scss';

// JAVASCRIPTS
import "/node_modules/@gouvfr/dsfr/dist/dsfr/dsfr.module";
import Router from '@bleckert/router';

new Router('/', {
  '/reserver/*': () => {
    import('./js/accueil/reserver');
  },
  '^$': () => {
    import('./js/accueil/index');
  }
});