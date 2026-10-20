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

// Unduh bagan struktur sebagai PNG (pustaka dimuat hanya saat tombol diklik)
const tombolBagan = document.getElementById('unduh-bagan');
if (tombolBagan) {
    tombolBagan.addEventListener('click', async () => {
        tombolBagan.disabled = true;
        try {
            const { toPng } = await import('html-to-image');
            const gelap = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const url = await toPng(document.getElementById('bagan'), {
                pixelRatio: 2,
                backgroundColor: gelap ? '#120d0e' : '#FAF6F5',
            });
            const a = document.createElement('a');
            a.href = url;
            a.download = 'struktur-pengurus.png';
            a.click();
        } finally {
            tombolBagan.disabled = false;
        }
    });
}

// Saat mencetak / menyimpan PDF, paksa tampilan terang agar hasilnya hemat tinta dan mudah dibaca
let sudahTerang = false;
window.addEventListener('beforeprint', () => {
    sudahTerang = document.documentElement.classList.contains('paksa-terang');
    document.documentElement.classList.add('paksa-terang');
});
window.addEventListener('afterprint', () => {
    if (!sudahTerang) document.documentElement.classList.remove('paksa-terang');
});
