<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Str;

/**
 * Teks kaya (rich text) dari editor TinyMCE: narasi wisata, narasi hotel, dan isi berita.
 * Semua HTML dibersihkan dengan HTML Purifier (hanya tag format yang diizinkan), jadi aman
 * ditampilkan dengan {!! !!}. Data lama berupa teks biasa otomatis diubah menjadi paragraf.
 */
class TeksKaya
{
    /** Tag dan atribut yang boleh dipakai. Selebihnya (script, style, onclick, dll.) dibuang. */
    private const DIIZINKAN = 'p,br,strong,b,em,i,u,s,sub,sup,h2,h3,h4,blockquote,ul,ol,li,a[href|title|target]';

    private static ?HTMLPurifier $pembersih = null;

    /** HTML bersih yang siap ditampilkan. Teks biasa (tanpa tag) dipecah per baris kosong menjadi <p>. */
    public static function html(?string $teks): string
    {
        $teks = trim((string) $teks);

        if ($teks === '') {
            return '';
        }

        if (! self::berisiHtml($teks)) {
            return collect(preg_split('/\R{2,}/', $teks))
                ->map('trim')
                ->filter(fn ($p) => $p !== '')
                ->map(fn ($p) => '<p>'.nl2br(e($p), false).'</p>')
                ->implode("\n");
        }

        // HTML tanpa tag blok (misalnya teks lama yang hanya berisi <b>): pecah dulu per baris kosong.
        if (! preg_match('/<\s*(p|ul|ol|h[2-4]|blockquote)\b/i', $teks)) {
            $teks = collect(preg_split('/\R{2,}/', $teks))
                ->map('trim')
                ->filter(fn ($p) => $p !== '')
                ->map(fn ($p) => '<p>'.nl2br($p, false).'</p>')
                ->implode("\n");
        }

        return trim(self::pembersih()->purify($teks));
    }

    /** Teks polos tanpa tag, untuk cuplikan dan popup peta. */
    public static function polos(?string $teks, ?int $batas = null): string
    {
        $polos = html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '<br/>', '<br />', '</li>'], ' ', (string) $teks)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $polos = trim(preg_replace('/\s+/u', ' ', str_replace("\u{00A0}", ' ', $polos)));

        return $batas ? Str::limit($polos, $batas) : $polos;
    }

    /** Kosong setelah tag dibuang? (editor mengirim "<p></p>" kalau admin menghapus semua isi) */
    public static function kosong(?string $teks): bool
    {
        return self::polos($teks) === '';
    }

    private static function berisiHtml(string $teks): bool
    {
        return (bool) preg_match('/<\s*\/?\s*[a-z][a-z0-9]*[^>]*>/i', $teks);
    }

    private static function pembersih(): HTMLPurifier
    {
        if (! self::$pembersih) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', self::DIIZINKAN);
            $config->set('Attr.AllowedFrameTargets', ['_blank']);
            $config->set('HTML.TargetNoopener', true);
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
            $config->set('AutoFormat.RemoveEmpty', true);

            $folder = storage_path('framework/cache/purifier');
            if (! is_dir($folder)) {
                @mkdir($folder, 0755, true);
            }
            $config->set('Cache.SerializerPath', $folder);

            self::$pembersih = new HTMLPurifier($config);
        }

        return self::$pembersih;
    }
}
