<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password — SI HIMA Informatika</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="w-full max-w-md px-4">

        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4"
                 style="background-color: #6B1124;">
                <span class="text-white text-2xl">🔒</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Ganti Password</h1>
            <p class="text-gray-500 text-sm mt-1">
                Demi keamanan, ganti password default kamu sebelum melanjutkan.
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-8">

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-4 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('mahasiswa.password.update') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                                  focus:outline-none focus:ring-2"
                           placeholder="Minimal 8 karakter">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                                  focus:outline-none focus:ring-2"
                           placeholder="Ulangi password baru">
                </div>

                <button type="submit"
                        class="w-full py-2.5 rounded-lg text-white font-semibold text-sm
                               transition hover:opacity-90"
                        style="background-color: #6B1124;">
                    Simpan & Lanjutkan
                </button>
            </form>
        </div>
    </div>
</body>
</html>
