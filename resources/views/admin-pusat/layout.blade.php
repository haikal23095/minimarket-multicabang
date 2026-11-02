<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Admin Pusat Minimarket</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">
    <div class="min-h-screen lg:flex">
        <aside class="flex w-full flex-col gap-8 bg-slate-900 p-6 text-white lg:sticky lg:top-0 lg:h-screen lg:w-72 lg:shrink-0 lg:self-start">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-300">Minimarket</p>
                <h1 class="mt-2 text-2xl font-bold">Admin Pusat</h1>
            </div>

            <nav class="flex flex-wrap gap-2 lg:flex-col" aria-label="Navigasi admin pusat">
                <a href="{{ route('admin.pusat.dashboard') }}"
                    @class([
                        'rounded-lg px-4 py-3 text-sm font-medium hover:bg-white/10 hover:text-white',
                        'bg-white/10 font-semibold text-white' => request()->routeIs('admin.pusat.dashboard'),
                        'text-slate-300' => ! request()->routeIs('admin.pusat.dashboard'),
                    ])>
                    Dashboard
                </a>
                <a href="{{ route('admin.pusat.produk.index') }}"
                    @class([
                        'rounded-lg px-4 py-3 text-sm font-medium hover:bg-white/10 hover:text-white',
                        'bg-white/10 font-semibold text-white' => request()->routeIs('admin.pusat.produk.*'),
                        'text-slate-300' => ! request()->routeIs('admin.pusat.produk.*'),
                    ])>
                    Master Produk
                </a>
                <a href="{{ route('admin.pusat.kategori.index') }}"
                    @class([
                        'rounded-lg px-4 py-3 text-sm font-medium hover:bg-white/10 hover:text-white',
                        'bg-white/10 font-semibold text-white' => request()->routeIs('admin.pusat.kategori.*'),
                        'text-slate-300' => ! request()->routeIs('admin.pusat.kategori.*'),
                    ])>
                    Master Kategori
                </a>
            </nav>

            <div class="mt-auto hidden rounded-xl border border-white/10 bg-white/5 p-4 lg:block">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Node aktif</p>
                <p class="mt-2 text-lg font-semibold">Pusat</p>
            </div>
        </aside>

        <main class="min-w-0 flex-1">
            <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 bg-white px-6 py-5 sm:px-8">
                <div>
                    <p class="text-sm text-slate-500">Panel administrasi / Pusat</p>
                    <h2 class="mt-1 text-2xl font-bold">@yield('heading')</h2>
                </div>

                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-sm font-semibold">{{ auth()->user()->username }}</p>
                        <p class="text-xs text-slate-500">Admin Pusat</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold hover:bg-slate-50">
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 px-6 py-8 sm:px-8">
                @if (session('status'))
                    <div role="status" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <ul class="list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>

</html>
