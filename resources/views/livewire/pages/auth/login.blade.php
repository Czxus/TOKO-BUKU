<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8 space-y-2">
        <h2 class="font-serif text-3xl font-semibold">Masuk ke akun Anda</h2>
        <p class="text-sm text-gray-600">Pembeli, penjual, dan admin platform masuk dari halaman yang sama.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <!-- Email -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email" placeholder="nama@email.com" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Kata sandi -->
        <div>
            <div class="flex items-baseline justify-between">
                <x-input-label for="password" value="Kata sandi" />
                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-hijau hover:underline" href="{{ route('password.request') }}" wire:navigate>Lupa kata sandi?</a>
                @endif
            </div>
            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full" type="password" name="password" placeholder="Masukkan kata sandi" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Ingat saya -->
        <label for="remember" class="inline-flex items-center gap-2">
            <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-[#A9B5B2] text-hijau focus:ring-hijau" name="remember">
            <span class="text-sm">Ingat saya di perangkat ini</span>
        </label>

        <x-primary-button class="w-full">Masuk</x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600">
        Belum punya akun?
        <a href="{{ route('register') }}" wire:navigate class="font-semibold text-hijau hover:underline">Daftar sebagai pembeli</a>
    </p>
    <p class="mt-3 text-center text-xs text-gray-500">Ingin berjualan? Hubungi admin platform untuk pendaftaran toko mitra.</p>
</div>
