<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 248 desa/kelurahan di 31 kecamatan Kabupaten Jember (226 desa + 22 kelurahan).
 * Jalankan setelah KecamatanSeeder. Aman dijalankan ulang (insertOrIgnore).
 */
class DesaSeeder extends Seeder
{
    public const DESA = [
        'Ajung' => ['Ajung', 'Klompangan', 'Mangaran', 'Pancakarya', 'Rowoindah', 'Sukamakmur', 'Wirowongso'],
        'Ambulu' => ['Ambulu', 'Andongsari', 'Karang Anyar', 'Pontang', 'Sabrang', 'Sumberejo', 'Tegalsari'],
        'Arjasa' => ['Arjasa', 'Biting', 'Candijati', 'Darsono', 'Kamal', 'Kemuning Lor'],
        'Balung' => ['Balung Kidul', 'Balung Kulon', 'Balung Lor', 'Curahlele', 'Gumelar', 'Karangduren', 'Karang Semanding', 'Tutul'],
        'Bangsalsari' => ['Badean', 'Bangsalsari', 'Banjarsari', 'Curahkalong', 'Gambirono', 'Karangsono', 'Langkap', 'Petung', 'Sukorejo', 'Tisnogambar', 'Tugusari'],
        'Gumukmas' => ['Bagorejo', 'Gumukmas', 'Karangrejo', 'Kepanjen', 'Mayangan', 'Menampu', 'Purwoasri', 'Tembokrejo'],
        'Jelbuk' => ['Jelbuk', 'Panduman', 'Sucopangepok', 'Sugerkidul', 'Sukojember', 'Sukowiryo'],
        'Jenggawah' => ['Cangkring', 'Jatimulyo', 'Jatisari', 'Jenggawah', 'Kemuningsari Kidul', 'Kertonegoro', 'Sruni', 'Wonojati'],
        'Jombang' => ['Jombang', 'Keting', 'Ngampelrejo', 'Padomasan', 'Sarimulyo', 'Wringinagung'],
        'Kalisat' => ['Ajung', 'Gambiran', 'Glagahwero', 'Gumuksari', 'Kalisat', 'Patempuran', 'Plalangan', 'Sebanen', 'Sukoreno', 'Sumberjeruk', 'Sumberkalong', 'Sumberketempa'],
        'Kaliwates' => ['Jember Kidul', 'Kaliwates', 'Kebon Agung', 'Kepatihan', 'Mangli', 'Sempusari', 'Tegal Besar'],
        'Kencong' => ['Cakru', 'Kencong', 'Kraton', 'Paseban', 'Wonorejo'],
        'Ledokombo' => ['Karangpaiton', 'Ledokombo', 'Lembengan', 'Slateng', 'Sukogidri', 'Sumberanget', 'Sumberbulus', 'Sumberlesung', 'Sumbersalak', 'Suren'],
        'Mayang' => ['Mayang', 'Mrawan', 'Seputih', 'Sidomukti', 'Sumberkejayan', 'Tegalrejo', 'Tegalwaru'],
        'Mumbulsari' => ['Karang Kedawung', 'Kawangrejo', 'Lampeji', 'Lengkong', 'Mumbulsari', 'Suco', 'Tamansari'],
        'Pakusari' => ['Bedadung', 'Jatian', 'Kertosari', 'Pakusari', 'Patemon', 'Subo', 'Sumberpinang'],
        'Panti' => ['Glagahwero', 'Kemiri', 'Kemuningsari Lor', 'Pakis', 'Panti', 'Serut', 'Suci'],
        'Patrang' => ['Banjarsengon', 'Baratan', 'Bintoro', 'Gebang', 'Jemberlor', 'Jumerto', 'Patrang', 'Slawu'],
        'Puger' => ['Bagon', 'Grenden', 'Jambearum', 'Kasiyan', 'Kasiyan Timur', 'Mlokorejo', 'Mojomulyo', 'Mojosari', 'Puger Kulon', 'Puger Wetan', 'Wonosari', 'Wringintelu'],
        'Rambipuji' => ['Curahmalang', 'Gugut', 'Kaliwining', 'Nogosari', 'Pecoro', 'Rambigundam', 'Rambipuji', 'Rowotamtu'],
        'Semboro' => ['Pondokdalem', 'Pondokjoyo', 'Rejoagung', 'Semboro', 'Sidomekar', 'Sidomulyo'],
        'Silo' => ['Garahan', 'Harjomulyo', 'Karangharjo', 'Mulyorejo', 'Pace', 'Sempolan', 'Sidomulyo', 'Silo', 'Sumberjati'],
        'Sukorambi' => ['Dukuhmencek', 'Jubung', 'Karangpring', 'Klungkung', 'Sukorambi'],
        'Sukowono' => ['Arjasa', 'Balet Baru', 'Dawuhanmangli', 'Mojogemi', 'Pocangan', 'Sukokerto', 'Sukorejo', 'Sukosari', 'Sukowono', 'Sumberdanti', 'Sumberwaru', 'Sumberwringin'],
        'Sumberbaru' => ['Gelang', 'Jambesari', 'Jamintoro', 'Jatiroto', 'Kaliglagah', 'Karangbayat', 'Pringgowirawan', 'Rowotengah', 'Sumberagung', 'Yosorati'],
        'Sumberjambe' => ['Cumedak', 'Gunungmalang', 'Jambearum', 'Plerean', 'Pringgondani', 'Randuagung', 'Rowosari', 'Sumberjambe', 'Sumberpakem'],
        'Sumbersari' => ['Antirogo', 'Karangrejo', 'Kebonsari', 'Kranjingan', 'Sumbersari', 'Tegalgede', 'Wirolegi'],
        'Tanggul' => ['Darungan', 'Klatakan', 'Kramat Sukoharjo', 'Manggisan', 'Patemon', 'Selodakon', 'Tanggul Kulon', 'Tanggul Wetan'],
        'Tempurejo' => ['Andongrejo', 'Curahnongko', 'Curahtakir', 'Pondokrejo', 'Sanenrejo', 'Sidodadi', 'Tempurejo', 'Wonoasri'],
        'Umbulsari' => ['Gadingrejo', 'Gunungsari', 'Mundurejo', 'Paleran', 'Sidorejo', 'Sukoreno', 'Tanjungsari', 'Tegalwangi', 'Umbulrejo', 'Umbulsari'],
        'Wuluhan' => ['Ampel', 'Dukuhdempok', 'Glundengan', 'Kesilir', 'Lojejer', 'Tamansari', 'Tanjungrejo'],
    ];

    public function run(): void
    {
        $kecamatan = DB::table('kecamatan')->pluck('id', 'nama');

        $baris = [];

        foreach (self::DESA as $namaKecamatan => $daftarDesa) {
            if (! isset($kecamatan[$namaKecamatan])) {
                continue;
            }

            foreach ($daftarDesa as $nama) {
                $baris[] = ['kecamatan_id' => $kecamatan[$namaKecamatan], 'nama' => $nama];
            }
        }

        DB::table('desa')->insertOrIgnore($baris);
    }
}
