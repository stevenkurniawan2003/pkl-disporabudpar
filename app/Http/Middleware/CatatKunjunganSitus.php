<?php

namespace App\Http\Middleware;

use App\Models\KunjunganSitus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Catat pengunjung situs publik: satu sesi browser dihitung sekali per hari.
 * Hanya dipasang di route publik. Bot/crawler dan admin yang sedang login tidak dihitung.
 */
class CatatKunjunganSitus
{
    public const KUNCI_SESI = 'kunjungan_situs_tanggal';

    /** Penanda user agent bot, crawler, dan alat otomatis yang umum. */
    private const POLA_BOT = '/bot|crawl|spider|slurp|mediapartners|facebookexternalhit|embedly|preview|whatsapp|telegram|'
        .'curl|wget|python|httpclient|okhttp|java\/|go-http|axios|node-fetch|headless|phantom|lighthouse|pingdom|uptime|monitor/i';

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->perluDicatat($request)) {
            $hariIni = now()->toDateString();

            if ($request->session()->get(self::KUNCI_SESI) !== $hariIni) {
                // insertOrIgnore: aman kalau dua tab pertama dari sesi yang sama datang bersamaan.
                KunjunganSitus::query()->insertOrIgnore([
                    'session_id' => $request->session()->getId(),
                    'ip_address' => $request->ip(),
                    'user_agent' => Str::limit((string) $request->userAgent(), 252),
                    'tanggal' => $hariIni,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $request->session()->put(self::KUNCI_SESI, $hariIni);
            }
        }

        return $next($request);
    }

    private function perluDicatat(Request $request): bool
    {
        $agen = (string) $request->userAgent();

        return $request->isMethod('GET')
            && $request->hasSession()
            && ! $request->user()
            && $agen !== ''
            && ! preg_match(self::POLA_BOT, $agen);
    }
}
