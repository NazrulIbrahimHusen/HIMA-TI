<?php

return [
    // Batas percobaan kirim absensi per menit per IP
    'rate_limit' => (int) env('ABSENSI_RATE_LIMIT', 5),
];
