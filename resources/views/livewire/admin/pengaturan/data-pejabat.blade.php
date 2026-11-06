<div>
    <x-admin.topbar judul="Pengaturan Data Pejabat" sub="Kelola nama dan NIP pejabat untuk dokumen resmi dan PDF" />

    @if (session('sukses'))
        <div class="alert sukses" style="margin-bottom:24px">
            <i class="bi bi-check-circle"></i> {{ session('sukses') }}
        </div>
    @endif

    <form wire:submit="simpan">
        <div class="card" style="max-width:800px">
            <h3 style="margin-bottom:8px">👤 Kepala Dinas</h3>
            <p style="color:var(--mut);font-size:14px;margin-bottom:20px">
                Kepala Dinas Kepemudaan dan Olahraga, Kebudayaan dan Pariwisata Kabupaten Jember
            </p>

            <div class="mb">
                <div class="fd">
                    <label for="kepala_dinas_nama">Nama Lengkap <span style="color:red">*</span></label>
                    <input type="text" id="kepala_dinas_nama" wire:model="kepalaDinasNama" placeholder="Contoh: BOBBY ARIE SANDY, S.STP., M.M." required>
                    @error('kepalaDinasNama') <span class="err">{{ $message }}</span> @enderror
                </div>
                <div class="fd">
                    <label for="kepala_dinas_nip">NIP <span style="color:red">*</span></label>
                    <input type="text" id="kepala_dinas_nip" wire:model="kepalaDinasNip" placeholder="18 digit NIP" maxlength="18" required>
                    @error('kepalaDinasNip') <span class="err">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="card" style="max-width:800px;margin-top:24px">
            <h3 style="margin-bottom:8px">👤 Kepala Bidang Kebudayaan</h3>
            <p style="color:var(--mut);font-size:14px;margin-bottom:20px">
                Penanda tangan dokumen cetak data kesenian dan dokumen kebudayaan lainnya
            </p>

            <div class="mb">
                <div class="fd">
                    <label for="kabid_kebudayaan_nama">Nama Lengkap <span style="color:red">*</span></label>
                    <input type="text" id="kabid_kebudayaan_nama" wire:model="kabidKebudayaanNama" placeholder="Nama lengkap dengan gelar" required>
                    @error('kabidKebudayaanNama') <span class="err">{{ $message }}</span> @enderror
                </div>
                <div class="fd">
                    <label for="kabid_kebudayaan_nip">NIP <span style="color:red">*</span></label>
                    <input type="text" id="kabid_kebudayaan_nip" wire:model="kabidKebudayaanNip" placeholder="18 digit NIP" maxlength="18" required>
                    @error('kabidKebudayaanNip') <span class="err">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="act" style="justify-content:flex-end;margin-top:20px">
                <button type="submit" class="btn p">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </form>

    <div class="card" style="max-width:800px;margin-top:24px;background:var(--bg-alt);border:1px solid var(--border)">
        <h4 style="margin-bottom:8px">ℹ️ Informasi</h4>
        <ul style="margin:0;padding-left:20px;line-height:1.8;font-size:14px;color:var(--mut)">
            <li>Data pejabat digunakan untuk penanda tangan dokumen PDF (cetak data kesenian, kartu induk, dll)</li>
            <li>Setelah disimpan, semua dokumen yang dicetak akan otomatis menggunakan data terbaru</li>
            <li>Ketika pejabat berganti, cukup edit di halaman ini sekali saja</li>
            <li>Tidak perlu edit file <code>.env</code> atau akses server</li>
        </ul>
    </div>
</div>
