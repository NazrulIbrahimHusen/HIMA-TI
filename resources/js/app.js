import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Chart.js hanya dimuat di halaman yang memerlukannya (dashboard admin)
if (document.getElementById('chartKegiatan')) {
    import('chart.js/auto').then(({ default: Chart }) => {
        window.Chart = Chart;
        document.dispatchEvent(new Event('chart-siap'));
    });
}
