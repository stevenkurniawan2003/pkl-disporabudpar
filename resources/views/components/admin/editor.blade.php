{{--
    Editor teks kaya (TinyMCE) untuk form admin: tebal, miring, garis bawah, daftar, tautan, judul.
    Pakai: <x-admin.editor model="narasi" id="narasi" />  (model = nama properti Livewire).
    Script TinyMCE dimuat lewat @assets di view form; Alpine "editorKaya" ada di layouts/admin.
    wire:ignore: Livewire tidak boleh merender ulang isi editor saat form diperbarui.
--}}
@props(['model', 'id'])
<div class="editor-kaya" wire:ignore x-data="editorKaya(@js($model))">
    <textarea id="{{ $id }}" x-ref="area" aria-label="{{ $attributes->get('aria-label', 'Editor teks') }}"></textarea>
</div>
