@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

    {{-- Filter periode --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="font-serif text-2xl font-bold text-stone-800">Halo, {{ auth()->user()->name }}</h2>
            <p class="text-sm text-stone-500">Ringkasan aktivitas HIMA dalam {{ $bulan }} bulan terakhir.</p>
        </div>
        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
            <label for="periode" class="text-sm text-stone-500">Periode</label>
            <select id="periode" name="periode" onchange="this.form.submit()"
                    class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon">
                <option value="3"  @selected($bulan === 3)>3 bulan terakhir</option>
                <option value="6"  @selected($bulan === 6)>1 semester (6 bulan)</option>
                <option value="12" @selected($bulan === 12)>12 bulan terakhir</option>
            </select>
        </form>
    </div>

    {{-- Kartu statistik --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $cards = [
                ['label' => 'Anggota Aktif',       'value' => $anggotaAktif,  'hint' => 'Terdaftar & berstatus aktif'],
                ['label' => 'Kegiatan',            'value' => $totalKegiatan, 'hint' => 'Pada periode terpilih'],
                ['label' => 'Total Kehadiran',     'value' => $totalHadir,    'hint' => 'Anggota & tamu'],
                ['label' => 'Rata-rata Keaktifan', 'value' => is_null($rataKeaktifan) ? '-' : $rataKeaktifan . '%', 'hint' => 'Khusus anggota'],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-stone-500">{{ $card['label'] }}</p>
                <p class="mt-2 font-serif text-3xl font-bold text-maroon">{{ $card['value'] }}</p>
                <p class="mt-1 text-xs text-stone-400">{{ $card['hint'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Grafik --}}
    <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm lg:col-span-2">
            <h3 class="mb-4 font-serif text-lg font-bold text-stone-800">Kegiatan per Bulan</h3>
            <div class="relative h-64"><canvas id="chartKegiatan"></canvas></div>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 font-serif text-lg font-bold text-stone-800">Distribusi Grade</h3>
            @if (array_sum($grade) > 0)
                <div class="relative h-64"><canvas id="chartGrade"></canvas></div>
            @else
                <div class="flex h-64 items-center justify-center text-center text-sm text-stone-400">
                    Belum ada data absensi anggota<br>pada periode ini.
                </div>
            @endif
        </div>
    </div>

    <div class="mb-6 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
        <h3 class="mb-4 font-serif text-lg font-bold text-stone-800">Tren Keaktifan Anggota (%)</h3>
        <div class="relative h-64"><canvas id="chartTren"></canvas></div>
    </div>

    {{-- Absensi terbaru --}}
    <div class="rounded-2xl border border-stone-200 bg-white shadow-sm">
        <div class="border-b border-stone-200 px-5 py-4">
            <h3 class="font-serif text-lg font-bold text-stone-800">Absensi Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-maroon-soft text-xs uppercase tracking-wide text-maroon">
                    <tr>
                        <th class="px-5 py-3">NIM</th>
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Kegiatan</th>
                        <th class="px-5 py-3">Peserta</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($absensiTerbaru as $a)
                        @php
                            $warna = [
                                'hadir' => 'bg-emerald-100 text-emerald-800',
                                'izin'  => 'bg-sky-100 text-sky-800',
                                'sakit' => 'bg-amber-100 text-amber-800',
                                'alpa'  => 'bg-red-100 text-red-800',
                            ][$a->status] ?? 'bg-stone-100 text-stone-700';
                        @endphp
                        <tr class="hover:bg-stone-50">
                            <td class="px-5 py-3 font-mono text-xs">{{ $a->nim }}</td>
                            <td class="px-5 py-3 font-medium">{{ $a->nama }}</td>
                            <td class="px-5 py-3 text-stone-600">{{ $a->sesi?->kegiatan?->nama ?? '-' }}</td>
                            <td class="px-5 py-3 capitalize text-stone-600">{{ $a->tipe_peserta }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $warna }}">{{ $a->status_label }}</span>
                            </td>
                            <td class="px-5 py-3 text-stone-500">{{ optional($a->waktu_absen ?? $a->created_at)->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-stone-400">Belum ada data absensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('chart-siap', () => {
        const pemantau = window.matchMedia('(prefers-color-scheme: dark)');
        const gelap = pemantau.matches;
        const maroon = gelap ? '#e63956' : '#6B1124';
        // Ganti tema perangkat saat halaman terbuka → muat ulang agar warna grafik menyesuaikan
        pemantau.addEventListener('change', () => location.reload());
        const labels = @json($bulanLabels);

        Chart.defaults.font.family = '"Plus Jakarta Sans", sans-serif';
        Chart.defaults.color = gelap ? '#bda9ae' : '#78716c';
        Chart.defaults.borderColor = gelap ? '#3d242a' : '#e7e5e4';

        new Chart(document.getElementById('chartKegiatan'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{ label: 'Kegiatan', data: @json($kegiatanPerBulan), backgroundColor: maroon, borderRadius: 6 }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });

        new Chart(document.getElementById('chartTren'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Keaktifan (%)', data: @json($trenKeaktifan),
                    borderColor: maroon, backgroundColor: gelap ? 'rgba(230,57,86,0.15)' : 'rgba(107,17,36,0.12)',
                    fill: true, tension: 0.3, spanGaps: true, pointRadius: 4,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { min: 0, max: 100, ticks: { callback: v => v + '%' } } },
            },
        });

        @if (array_sum($grade) > 0)
        new Chart(document.getElementById('chartGrade'), {
            type: 'doughnut',
            data: {
                labels: ['A (80–100%)', 'B (60–79%)', 'C (40–59%)', 'D (20–39%)', 'E (0–19%)'],
                datasets: [{
                    data: @json(array_values($grade)),
                    backgroundColor: ['#2E7D32', '#7CB342', '#F9A825', '#EF6C00', '#C62828'],
                    borderWidth: 2, borderColor: gelap ? '#1a1215' : '#fff',
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
            },
        });
        @endif
        });
    </script>
@endpush
