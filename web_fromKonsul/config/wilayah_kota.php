<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mapping Kota/Kabupaten -> Provinsi (Indonesia)
    |--------------------------------------------------------------------------
    |
    | Data referensi untuk auto-fill provinsi berdasarkan kota.
    | Akses via: config('wilayah_kota.mapping')
    |
    */
    'mapping' => [
        // Aceh
        'Banda Aceh' => 'Aceh', 'Sabang' => 'Aceh', 'Lhokseumawe' => 'Aceh',
        'Langsa' => 'Aceh', 'Subulussalam' => 'Aceh', 'Aceh Besar' => 'Aceh',
        'Aceh Barat' => 'Aceh', 'Aceh Selatan' => 'Aceh', 'Aceh Timur' => 'Aceh',
        'Aceh Utara' => 'Aceh', 'Aceh Tengah' => 'Aceh', 'Meulaboh' => 'Aceh',

        // Sumatera Utara
        'Medan' => 'Sumatera Utara', 'Binjai' => 'Sumatera Utara',
        'Pematangsiantar' => 'Sumatera Utara', 'Tebing Tinggi' => 'Sumatera Utara',
        'Tanjungbalai' => 'Sumatera Utara', 'Sibolga' => 'Sumatera Utara',
        'Padangsidimpuan' => 'Sumatera Utara', 'Gunungsitoli' => 'Sumatera Utara',
        'Deli Serdang' => 'Sumatera Utara', 'Serdang Bedagai' => 'Sumatera Utara',
        'Langkat' => 'Sumatera Utara', 'Karo' => 'Sumatera Utara',
        'Simalungun' => 'Sumatera Utara', 'Tapanuli Utara' => 'Sumatera Utara',

        // Sumatera Barat
        'Padang' => 'Sumatera Barat', 'Bukittinggi' => 'Sumatera Barat',
        'Payakumbuh' => 'Sumatera Barat', 'Solok' => 'Sumatera Barat',
        'Sawahlunto' => 'Sumatera Barat', 'Padang Panjang' => 'Sumatera Barat',
        'Pariaman' => 'Sumatera Barat',

        // Riau
        'Pekanbaru' => 'Riau', 'Dumai' => 'Riau', 'Kampar' => 'Riau',
        'Bengkalis' => 'Riau', 'Siak' => 'Riau', 'Indragiri Hilir' => 'Riau',

        // Jambi
        'Jambi' => 'Jambi', 'Sungai Penuh' => 'Jambi',

        // Sumatera Selatan
        'Palembang' => 'Sumatera Selatan', 'Lubuklinggau' => 'Sumatera Selatan',
        'Prabumulih' => 'Sumatera Selatan', 'Pagar Alam' => 'Sumatera Selatan',

        // Bengkulu
        'Bengkulu' => 'Bengkulu',

        // Lampung
        'Bandar Lampung' => 'Lampung', 'Metro' => 'Lampung',
        'Lampung Selatan' => 'Lampung', 'Lampung Tengah' => 'Lampung',

        // Kep. Bangka Belitung
        'Pangkalpinang' => 'Kep. Bangka Belitung',

        // Kep. Riau
        'Tanjungpinang' => 'Kep. Riau', 'Batam' => 'Kep. Riau',

        // DKI Jakarta
        'Jakarta' => 'DKI Jakarta', 'Jakarta Pusat' => 'DKI Jakarta',
        'Jakarta Utara' => 'DKI Jakarta', 'Jakarta Barat' => 'DKI Jakarta',
        'Jakarta Selatan' => 'DKI Jakarta', 'Jakarta Timur' => 'DKI Jakarta',

        // Jawa Barat
        'Bandung' => 'Jawa Barat', 'Bekasi' => 'Jawa Barat',
        'Bogor' => 'Jawa Barat', 'Depok' => 'Jawa Barat',
        'Cimahi' => 'Jawa Barat', 'Cirebon' => 'Jawa Barat',
        'Sukabumi' => 'Jawa Barat', 'Tasikmalaya' => 'Jawa Barat',
        'Karawang' => 'Jawa Barat', 'Subang' => 'Jawa Barat',
        'Garut' => 'Jawa Barat', 'Sumedang' => 'Jawa Barat',
        'Cianjur' => 'Jawa Barat', 'Purwakarta' => 'Jawa Barat',
        'Indramayu' => 'Jawa Barat', 'Majalengka' => 'Jawa Barat',
        'Kuningan' => 'Jawa Barat', 'Bandung Barat' => 'Jawa Barat',
        'Pangandaran' => 'Jawa Barat', 'Ciamis' => 'Jawa Barat',

        // Jawa Tengah
        'Semarang' => 'Jawa Tengah', 'Surakarta' => 'Jawa Tengah',
        'Solo' => 'Jawa Tengah', 'Tegal' => 'Jawa Tengah',
        'Pekalongan' => 'Jawa Tengah', 'Salatiga' => 'Jawa Tengah',
        'Magelang' => 'Jawa Tengah', 'Kendal' => 'Jawa Tengah',
        'Demak' => 'Jawa Tengah', 'Klaten' => 'Jawa Tengah',
        'Boyolali' => 'Jawa Tengah', 'Purwokerto' => 'Jawa Tengah',
        'Banyumas' => 'Jawa Tengah', 'Cilacap' => 'Jawa Tengah',
        'Kebumen' => 'Jawa Tengah', 'Kudus' => 'Jawa Tengah',
        'Jepara' => 'Jawa Tengah', 'Brebes' => 'Jawa Tengah',
        'Pemalang' => 'Jawa Tengah', 'Batang' => 'Jawa Tengah',
        'Wonogiri' => 'Jawa Tengah', 'Sragen' => 'Jawa Tengah',
        'Karanganyar' => 'Jawa Tengah', 'Sukoharjo' => 'Jawa Tengah',
        'Blora' => 'Jawa Tengah', 'Rembang' => 'Jawa Tengah',
        'Pati' => 'Jawa Tengah', 'Grobogan' => 'Jawa Tengah',
        'Temanggung' => 'Jawa Tengah', 'Wonosobo' => 'Jawa Tengah',
        'Purbalingga' => 'Jawa Tengah', 'Banjarnegara' => 'Jawa Tengah',

        // DI Yogyakarta
        'Yogyakarta' => 'DI Yogyakarta', 'Jogja' => 'DI Yogyakarta',
        'Sleman' => 'DI Yogyakarta', 'Bantul' => 'DI Yogyakarta',
        'Gunungkidul' => 'DI Yogyakarta', 'Kulon Progo' => 'DI Yogyakarta',

        // Jawa Timur
        'Surabaya' => 'Jawa Timur', 'Malang' => 'Jawa Timur',
        'Sidoarjo' => 'Jawa Timur', 'Gresik' => 'Jawa Timur',
        'Mojokerto' => 'Jawa Timur', 'Kediri' => 'Jawa Timur',
        'Blitar' => 'Jawa Timur', 'Madiun' => 'Jawa Timur',
        'Pasuruan' => 'Jawa Timur', 'Probolinggo' => 'Jawa Timur',
        'Batu' => 'Jawa Timur', 'Lamongan' => 'Jawa Timur',
        'Tuban' => 'Jawa Timur', 'Bojonegoro' => 'Jawa Timur',
        'Jombang' => 'Jawa Timur', 'Nganjuk' => 'Jawa Timur',
        'Ponorogo' => 'Jawa Timur', 'Trenggalek' => 'Jawa Timur',
        'Tulungagung' => 'Jawa Timur', 'Pacitan' => 'Jawa Timur',
        'Magetan' => 'Jawa Timur', 'Ngawi' => 'Jawa Timur',
        'Lumajang' => 'Jawa Timur', 'Jember' => 'Jawa Timur',
        'Banyuwangi' => 'Jawa Timur', 'Bondowoso' => 'Jawa Timur',
        'Situbondo' => 'Jawa Timur', 'Bangkalan' => 'Jawa Timur',
        'Pamekasan' => 'Jawa Timur', 'Sumenep' => 'Jawa Timur',
        'Sampang' => 'Jawa Timur',

        // Banten
        'Serang' => 'Banten', 'Cilegon' => 'Banten',
        'Tangerang' => 'Banten', 'Tangerang Selatan' => 'Banten',
        'Tangsel' => 'Banten', 'Lebak' => 'Banten', 'Pandeglang' => 'Banten',

        // Bali
        'Denpasar' => 'Bali', 'Badung' => 'Bali', 'Gianyar' => 'Bali',
        'Tabanan' => 'Bali', 'Klungkung' => 'Bali', 'Bangli' => 'Bali',
        'Karangasem' => 'Bali', 'Buleleng' => 'Bali', 'Jembrana' => 'Bali',
        'Singaraja' => 'Bali', 'Ubud' => 'Bali', 'Kuta' => 'Bali',
        'Seminyak' => 'Bali', 'Sanur' => 'Bali', 'Nusa Dua' => 'Bali',

        // Nusa Tenggara Barat
        'Mataram' => 'Nusa Tenggara Barat', 'Bima' => 'Nusa Tenggara Barat',
        'Lombok' => 'Nusa Tenggara Barat', 'Sumbawa' => 'Nusa Tenggara Barat',

        // Nusa Tenggara Timur
        'Kupang' => 'Nusa Tenggara Timur', 'Ende' => 'Nusa Tenggara Timur',
        'Maumere' => 'Nusa Tenggara Timur',

        // Kalimantan Barat
        'Pontianak' => 'Kalimantan Barat', 'Singkawang' => 'Kalimantan Barat',

        // Kalimantan Tengah
        'Palangkaraya' => 'Kalimantan Tengah',

        // Kalimantan Selatan
        'Banjarmasin' => 'Kalimantan Selatan', 'Banjarbaru' => 'Kalimantan Selatan',

        // Kalimantan Timur
        'Samarinda' => 'Kalimantan Timur', 'Balikpapan' => 'Kalimantan Timur',
        'Bontang' => 'Kalimantan Timur',

        // Kalimantan Utara
        'Tarakan' => 'Kalimantan Utara', 'Tanjung Selor' => 'Kalimantan Utara',

        // Sulawesi Utara
        'Manado' => 'Sulawesi Utara', 'Bitung' => 'Sulawesi Utara',
        'Tomohon' => 'Sulawesi Utara', 'Kotamobagu' => 'Sulawesi Utara',

        // Sulawesi Tengah
        'Palu' => 'Sulawesi Tengah',

        // Sulawesi Selatan
        'Makassar' => 'Sulawesi Selatan', 'Parepare' => 'Sulawesi Selatan',
        'Palopo' => 'Sulawesi Selatan', 'Maros' => 'Sulawesi Selatan',
        'Gowa' => 'Sulawesi Selatan', 'Bone' => 'Sulawesi Selatan',

        // Sulawesi Tenggara
        'Kendari' => 'Sulawesi Tenggara', 'Baubau' => 'Sulawesi Tenggara',

        // Gorontalo
        'Gorontalo' => 'Gorontalo',

        // Sulawesi Barat
        'Mamuju' => 'Sulawesi Barat',

        // Maluku
        'Ambon' => 'Maluku', 'Tual' => 'Maluku',

        // Maluku Utara
        'Ternate' => 'Maluku Utara', 'Tidore' => 'Maluku Utara',

        // Papua
        'Jayapura' => 'Papua',

        // Papua Barat
        'Manokwari' => 'Papua Barat',

        // Papua Selatan
        'Merauke' => 'Papua Selatan',

        // Papua Tengah
        'Nabire' => 'Papua Tengah',

        // Papua Pegunungan
        'Wamena' => 'Papua Pegunungan',

        // Papua Barat Daya
        'Sorong' => 'Papua Barat Daya',
    ],
];
