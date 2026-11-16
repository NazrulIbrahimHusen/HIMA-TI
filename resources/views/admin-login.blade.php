<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — SI HIMA Informatika</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --maroon: #6B1124; --maroon-dark: #4A0012; }
    </style>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="w-full max-w-md">

        {{-- Logo & Judul --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4"
                 style="background-color: #6B1124;">
                <span class="text-white text-2xl font-bold">HI</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">SI HIMA Informatika</h1>
            <p class="text-gray-500 text-sm mt-1">Panel Admin</p>
        </div>

        {{-- Card Login --}}
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-6">Masuk sebagai Admin</h2>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-4 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-3 mb-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                                  focus:outline-none focus:ring-2 focus:border-transparent
                                  @error('email') border-red-400 @enderror"
                           style="--tw-ring-color: #6B1124;"
                           placeholder="">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                                  focus:outline-none focus:ring-2 focus:border-transparent"
                           placeholder="••••••••">
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center mb-6">
                    <input type="checkbox" name="remember" id="remember"
                           class="w-4 h-4 rounded border-gray-300 text-red-800">
                    <label for="remember" class="ml-2 text-sm text-gray-600">Ingat saya</label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full py-2.5 rounded-lg text-white font-semibold text-sm
                               transition hover:opacity-90 active:scale-95"
                        style="background-color: #6B1124;">
                    Masuk
                </button>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('mahasiswa.login') }}"
                   class="text-xs text-gray-400 hover:underline">
                    Login sebagai mahasiswa →
                </a>
            </div>
        </div>
    </div>
</body>
</html>
