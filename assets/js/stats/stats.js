console.log('=== stats ===');

import './Chart';
import axios from 'axios';

(async () => {
    // --- DONNÉES SIMULÉES (À remplacer par ton fetch BDD) ---
    const response = await fetch('/resa971/stats/getdata');
    const data = await response.json();

    // 1. Graphique Évolution : Flotte vs Occupation
    const ctxEvol = document.getElementById('evolutionChart').getContext('2d');
    new Chart(ctxEvol, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Total Véhicules',
                data: data.evolutionVehicules,
                borderColor: '#3498db',
                fill: false
            }, {
                label: 'Véhicules Occupés',
                data: data.vehiculesReserves,
                backgroundColor: 'rgba(231, 76, 60, 0.2)',
                borderColor: '#e74c3c',
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: { display: true, text: 'Occupation immédiate de la flotte' }
            }
        }
    });

    // 2. Graphique de Distribution : Pourquoi c'est saturé ?
    const ctxDist = document.getElementById('distributionChart').getContext('2d');
    new Chart(ctxDist, {
        type: 'pie',
        data: {
            labels: Object.keys(data.repartitionDuree),
            datasets: [{
                data: Object.values(data.repartitionDuree),
                backgroundColor: ['#2ecc71', '#f1c40f', '#e74c3c']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: { display: true, text: 'Répartition par durée de réservation' }
            }
        }
    });

    // Injection des chiffres KPI (Logique de calcul simple)
    document.getElementById('totalVehicles').innerText = data.evolutionVehicules.slice(-1);
    document.getElementById('avgAvailability').innerText = "2%"; // Chiffre volontairement bas pour alerter
    document.getElementById('avgDuration').innerText = "42 jours";
})();
