{{-- Satu pintu /dashboard: isi halaman dipilih berdasarkan peran pengguna. --}}
@if (auth()->user()->isAdminPlatform())
    @include('dashboard.admin')
@elseif (auth()->user()->isSellerAdmin())
    @include('dashboard.seller')
@else
    @include('dashboard.pembeli')
@endif
