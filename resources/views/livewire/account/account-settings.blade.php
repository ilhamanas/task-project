<div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Pengaturan Akun</h1>
            <p class="text-secondary mb-0 small">Kelola foto profil dan ubah kata sandi akun Anda.</p>
        </div>
    </div>

    <div class="row g-4">
        {{-- Kartu Foto Profil --}}
        <div class="col-12 col-lg-5">
            <div class="card-dasher p-4 h-100">
                <h2 id="heading-photo" class="h6 fw-bold mb-3">Foto Profil</h2>

                <div class="d-flex align-items-center gap-3 pb-3 mb-3 border-bottom">
                    @if($photo && ! $errors->has('photo') && $photo->isPreviewable())
                        <img
                            src="{{ $photo->temporaryUrl() }}"
                            alt="Pratinjau foto profil baru"
                            class="rounded-circle border"
                            width="80"
                            height="80"
                            style="width: 80px; height: 80px; object-fit: cover; flex-shrink: 0;">
                    @elseif($user->avatarUrl())
                        <img
                            src="{{ $user->avatarUrl() }}"
                            alt="Foto profil {{ $user->nama }}"
                            class="rounded-circle border"
                            width="80"
                            height="80"
                            style="width: 80px; height: 80px; object-fit: cover; flex-shrink: 0;">
                    @else
                        <span
                            class="rounded-circle bg-primary-subtle-ds fw-bold d-inline-flex align-items-center justify-content-center"
                            style="width: 80px; height: 80px; font-size: 1.75rem; flex-shrink: 0;"
                            aria-hidden="true">
                            {{ $user->initials() }}
                        </span>
                    @endif

                    <div class="min-w-0">
                        <div class="fw-semibold text-truncate">{{ $user->nama }}</div>
                        <div class="text-secondary small text-truncate">{{ $user->email }}</div>
                        <span class="badge bg-primary-subtle-ds mt-1">{{ $user->roleLabel() }}</span>
                    </div>
                </div>

                @if($photo && ! $errors->has('photo'))
                    <div class="alert alert-info py-2 px-3 small mb-3" role="status">
                        Pratinjau foto siap disimpan. Klik <strong>Simpan Foto</strong> untuk menerapkan.
                    </div>
                @endif

                <form wire:submit="updatePhoto" aria-labelledby="heading-photo">
                    <div class="mb-3">
                        <label for="account-photo" class="form-label small fw-semibold">Pilih Foto Baru</label>
                        <div id="account-photo-help" class="form-text mt-0 mb-2">Format JPG, PNG, atau WebP. Ukuran maksimal 2 MB.</div>
                        <input
                            id="account-photo"
                            name="photo"
                            type="file"
                            wire:model="photo"
                            accept="image/png,image/jpeg,image/webp"
                            class="form-control @error('photo') is-invalid @enderror"
                            aria-describedby="account-photo-help @error('photo') account-photo-error @enderror"
                            @error('photo') aria-invalid="true" @enderror>
                        @error('photo')
                            <div id="account-photo-error" class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div wire:loading wire:target="photo" class="small text-secondary mt-2" role="status">
                            Memuat pratinjau foto...
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button
                            type="submit"
                            class="btn btn-primary fw-semibold"
                            wire:loading.attr="disabled"
                            wire:target="photo,updatePhoto">
                            <span wire:loading.remove wire:target="updatePhoto">Simpan Foto</span>
                            <span wire:loading wire:target="updatePhoto">Menyimpan...</span>
                        </button>

                        @if($user->avatar_path)
                            <button
                                type="button"
                                wire:click="removePhoto"
                                class="btn btn-outline-danger"
                                wire:loading.attr="disabled"
                                wire:target="photo,updatePhoto,removePhoto">
                                Hapus Foto
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Kartu Ubah Kata Sandi --}}
        <div class="col-12 col-lg-7">
            <div class="card-dasher p-4 h-100">
                <h2 id="heading-password" class="h6 fw-bold mb-3">Ubah Kata Sandi</h2>

                @if(session('password_success'))
                    <div class="alert alert-success alert-dismissible fade show" role="status">
                        {{ session('password_success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif

                <form wire:submit="updatePassword" aria-labelledby="heading-password" novalidate>
                    <div class="mb-3">
                        <label for="current-password" class="form-label small fw-semibold">
                            Kata Sandi Saat Ini <span class="text-danger" aria-hidden="true">*</span>
                        </label>
                        <div class="input-group">
                            <input
                                id="current-password"
                                name="current_password"
                                type="password"
                                wire:model="current_password"
                                autocomplete="current-password"
                                required
                                class="form-control @error('current_password') is-invalid @enderror"
                                @error('current_password') aria-invalid="true" aria-describedby="current-password-error" @enderror>
                            <button
                                type="button"
                                class="btn btn-light border px-3"
                                data-password-toggle="current-password"
                                aria-pressed="false">
                                <span data-toggle-text>Tampilkan</span><span class="visually-hidden"> kata sandi saat ini</span>
                            </button>
                        </div>
                        @error('current_password')
                            <div id="current-password-error" class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="new-password" class="form-label small fw-semibold">
                            Kata Sandi Baru <span class="text-danger" aria-hidden="true">*</span>
                        </label>
                        <div id="new-password-help" class="form-text mt-0 mb-2">Minimal 6 karakter dan berbeda dari kata sandi saat ini.</div>
                        <div class="input-group">
                            <input
                                id="new-password"
                                name="password"
                                type="password"
                                wire:model="password"
                                autocomplete="new-password"
                                minlength="6"
                                required
                                class="form-control @error('password') is-invalid @enderror"
                                aria-describedby="new-password-help @error('password') new-password-error @enderror"
                                @error('password') aria-invalid="true" @enderror>
                            <button
                                type="button"
                                class="btn btn-light border px-3"
                                data-password-toggle="new-password"
                                aria-pressed="false">
                                <span data-toggle-text>Tampilkan</span><span class="visually-hidden"> kata sandi baru</span>
                            </button>
                        </div>
                        @error('password')
                            <div id="new-password-error" class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="new-password-confirmation" class="form-label small fw-semibold">
                            Konfirmasi Kata Sandi Baru <span class="text-danger" aria-hidden="true">*</span>
                        </label>
                        <div class="input-group">
                            <input
                                id="new-password-confirmation"
                                name="password_confirmation"
                                type="password"
                                wire:model="password_confirmation"
                                autocomplete="new-password"
                                minlength="6"
                                required
                                class="form-control">
                            <button
                                type="button"
                                class="btn btn-light border px-3"
                                data-password-toggle="new-password-confirmation"
                                aria-pressed="false">
                                <span data-toggle-text>Tampilkan</span><span class="visually-hidden"> konfirmasi kata sandi baru</span>
                            </button>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary fw-semibold"
                        wire:loading.attr="disabled"
                        wire:target="updatePassword">
                        <span wire:loading.remove wire:target="updatePassword">Simpan Kata Sandi</span>
                        <span wire:loading wire:target="updatePassword">Menyimpan...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-password-toggle]');
        if (!button) return;

        const input = document.getElementById(button.dataset.passwordToggle);
        if (!input) return;

        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(show));

        const labelEl = button.querySelector('[data-toggle-text]');
        if (labelEl) {
            labelEl.textContent = show ? 'Sembunyikan' : 'Tampilkan';
        }
    });
</script>
@endpush
