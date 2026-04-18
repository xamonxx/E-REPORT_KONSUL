<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Account;
use App\Models\NeedsCategory;
use App\Models\StatusCategory;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Super Admin ────────────────────────────────────
        User::create([
            'name' => 'Ramon',
            'email' => 'superadmin@pc.com',
            'password' => Hash::make('123321'),
            'role' => UserRole::SuperAdmin,
            'account_id' => null,
        ]);

        // ─── Akun & Admin (Putra Corporation) ───────────────
        // Format: [nama_akun, nama_admin, email_admin]
        // Satu admin bisa pegang beberapa akun dengan login berbeda
        $data = [
            ['HOME INTERIOR BANDUNG',        'HASAN',       'hasan@homeinteriorbdg.com'],
            ['INTERHOUSE ID',                'ANDIKA',      'andika@interhouseid.com'],
            ['ZODIAK INTERIOR',              'BILAL',       'bilal@zodiakinterior.com'],
            ['AKBAR INTERIOR',               'YONAS',       'yonas@akbarinterior.com'],
            ['PARTNER INTERIOR',             'YANWAR',      'yanwar@partnerinterior.com'],
            ['ELVAN INTERIOR',               'YANWAR',      'yanwar@elvaninterior.com'],
            ['MEWAH INTERIOR',               'NENG SRI',    'nengsri@mewahinterior.com'],
            ['MEDIAN INTERIOR',              'LISA',        'lisa@medianinterior.com'],
            ['ARGO INTERIOR',                'ADI',         'adi@argointerior.com'],
            ['SAVOY INTERIOR',               'AZAM',        'azam@savoyinterior.com'],
            ['FURNITURE CIMAHI',             'ADI',         'adi@furniturecimahi.com'],
            ['DEKOR INTERIOR',               'ADI',         'adi@dekorinterior.com'],
            ['NISCALA INTERIOR',             'RIVALDI',     'rivaldi@niscalainterior.com'],
            ['INTERIOR CUSTOM',              'DIAN GARUT',  'diangarut@interiorcustom.com'],
            ['INTERIOR BANDUNG',             'FIKRI ACENG', 'fikriaceng@interiorbandung.com'],
            ['INTERIOR MODERN',              'FIKRI ACENG', 'fikriaceng@interiormodern.com'],
            ['BROTO INTERIOR',               null,          null],
            ['KITCHENSET SOLUTION BANDUNG',  'LISA',        'lisa@kitchensetsolution.com'],
            ['GIBRAN INTERIOR',              'LISA',        'lisa@gibraninterior.com'],
            ['HOME SAVOY INTERIOR',          'NENG SRI',    'nengsri@homesavoy.com'],
            ['LAVENTIA',                     'ANO',         'ano@laventia.com'],
            ['PUTRO INTERIOR',               null,          null],
            ['PUSAT INTERIOR',               null,          null],
            ['KAMARSET',                     null,          null],
            ['HEYA INTERIOR',                'YASID',       'yasid@heyainterior.com'],
            ['KURNIA INTERIOR',              'YASID',       'yasid@kurniainterior.com'],
            ['KEJORA INTERIOR',              null,          null],
            ['PORTO INTERIOR',               'AGIL',        'agil@portointerior.com'],
            ['ANEKA INTERIOR',               'RAMDAN',      'ramdan@anekainterior.com'],
            ['RADEA INTERIOR',               'AGIL',        'agil@radeainterior.com'],
            ['ELVAN FURNITURE',              'RAMDAN',      'ramdan@elvanfurniture.com'],
            ['PUTRA MOULDING',               'LISA',        'lisa@putramoulding.com'],
        ];

        $password = Hash::make('123321');

        foreach ($data as $row) {
            $account = Account::create([
                'name' => $row[0],
                'description' => 'PUTRA CORPORATION',
            ]);

            if ($row[1] !== null) {
                User::create([
                    'name' => $row[1],
                    'email' => $row[2],
                    'password' => $password,
                    'role' => UserRole::Admin,
                    'account_id' => $account->id,
                ]);
            }
        }

        // ─── Needs Categories ───────────────────────────────
        $needsNames = [
            'Aluminium', 'Backdrop TV', 'Bench', 'Cabinet', 'Kitchen Set',
            'Wardrobe', 'Apartment', 'Kanopi', 'Kusen', 'Partisi',
            'Railing Tangga', 'Pintu', 'Meja Kerja', 'Rak Buku',
            'Renovasi', 'Interior Full',
        ];

        foreach ($needsNames as $name) {
            NeedsCategory::create(['name' => $name]);
        }

        // ─── Status Categories ──────────────────────────────
        $statuses = [
            ['name' => 'Hanya Tanya Tanya', 'color' => '#eab308', 'css_class' => 'chip-hanya-tanya', 'sort_order' => 1],
            ['name' => 'Masuk Survey', 'color' => '#8582ff', 'css_class' => 'chip-masuk-survey', 'sort_order' => 2],
            ['name' => 'Kendala Anggaran', 'color' => '#9f403d', 'css_class' => 'chip-kendala-anggaran', 'sort_order' => 3],
            ['name' => 'Tidak Ada Respon', 'color' => '#737c7f', 'css_class' => 'chip-tidak-ada-respon', 'sort_order' => 4],
            ['name' => 'Selesai/Deal', 'color' => '#006d4a', 'css_class' => 'chip-selesai-deal', 'sort_order' => 5],
        ];

        foreach ($statuses as $s) {
            StatusCategory::create($s);
        }
    }
}
