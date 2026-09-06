<?php

namespace Database\Seeders;

use App\Enums\CommissionRuleType;
use App\Enums\ContactStatus;
use App\Enums\IntegrationConnectionStatus;
use App\Enums\LandingPageStatus;
use App\Enums\MessageChannel;
use App\Enums\PermissionGroups;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\PromoterVerificationStatus;
use App\Enums\ProviderCategory;
use App\Enums\UserRole;
use App\Models\BranchSetting;
use App\Models\BrandSetting;
use App\Models\CommissionPlan;
use App\Models\CommissionRule;
use App\Models\Contact;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\IntegrationConnection;
use App\Models\LandingPage;
use App\Models\Lesson;
use App\Models\MessageTemplate;
use App\Models\Permission;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Product;
use App\Models\PromoterProfile;
use App\Models\Tag;
use App\Models\User;
use App\Models\VoucherProductConfig;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use LogicException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new LogicException('DatabaseSeeder berisi data demo dan dilarang dijalankan di production.');
        }

        DB::transaction(function () {
            $admin = User::query()->firstOrCreate(
                ['email' => 'admin@jml62.stiflow.test'],
                [
                    'name' => 'Admin Cabang JML-CAB-62',
                    'password' => Hash::make('password'),
                    'role' => UserRole::Admin,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            $promotorUser = User::query()->firstOrCreate(
                ['email' => 'promotor@jml62.stiflow.test'],
                [
                    'name' => 'Promotor JML 62',
                    'password' => Hash::make('password'),
                    'role' => UserRole::Promotor,
                    'status' => 'active',
                    'email_verified_at' => now(),
                    'phone' => '0812-JML-CAB62',
                ]
            );

            $promoterProfile = PromoterProfile::query()->firstOrCreate(
                ['user_id' => $promotorUser->id],
                [
                    'stifin_code' => 'PRO-JML-001',
                    'verification_status' => PromoterVerificationStatus::Verified,
                    'verified_at' => now(),
                    'referral_slug' => 'promotor-jml62-'.Str::random(6),
                    'sponsor_promoter_profile_id' => null,
                    'upline_path_root_promoter_profile_id' => null,
                    'level_depth' => 0,
                ]
            );
            if ($promoterProfile->upline_path_root_promoter_profile_id === null) {
                $promoterProfile->upline_path_root_promoter_profile_id = $promoterProfile->id;
                $promoterProfile->save();
            }
            $contactOwnerPromotor = $promoterProfile;

            $staff = User::query()->firstOrCreate(
                ['email' => 'staff@jml62.stiflow.test'],
                [
                    'name' => 'Staff Cabang JML-CAB-62',
                    'password' => Hash::make('password'),
                    'role' => UserRole::Staff,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            $promotorPool = [];

            $bksUser = User::query()->firstOrCreate(
                ['email' => 'bks-hra-40@jml62.stiflow.test'],
                [
                    'name' => 'Promotor BKS HRA 40',
                    'password' => Hash::make('password'),
                    'role' => UserRole::Promotor,
                    'status' => 'active',
                    'email_verified_at' => now(),
                    'phone' => '0813-BKS-HRA40',
                ]
            );
            $bksProfile = PromoterProfile::query()->firstOrCreate(
                ['user_id' => $bksUser->id],
                [
                    'stifin_code' => 'BKS-HRA-40',
                    'verification_status' => PromoterVerificationStatus::Verified,
                    'verified_at' => now(),
                    'referral_slug' => 'bks-hra-40-'.Str::random(6),
                    'sponsor_promoter_profile_id' => null,
                    'upline_path_root_promoter_profile_id' => null,
                    'level_depth' => 0,
                ]
            );
            if ($bksProfile->upline_path_root_promoter_profile_id === null) {
                $bksProfile->upline_path_root_promoter_profile_id = $bksProfile->id;
                $bksProfile->save();
            }
            $promotorPool['BKS-HRA-40'] = ['user' => $bksUser, 'profile' => $bksProfile];

            for ($i = 1; $i <= 10; $i++) {
                $num = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
                $stifinCode = 'KHU-ABU-'.$num;
                $email = 'khu-abu-'.strtolower($num).'@jml62.stiflow.test';
                $khuUser = User::query()->firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => 'Promotor KHU ABU '.$num,
                        'password' => Hash::make('password'),
                        'role' => UserRole::Promotor,
                        'status' => 'active',
                        'email_verified_at' => now(),
                        'phone' => '0814-KHU-ABU'.$num,
                    ]
                );

                if ($i <= 5) {
                    $sponsorProfile = $bksProfile;
                    $level = 1;
                } else {
                    $pairNum = $i - 5;
                    $pairCode = 'KHU-ABU-'.str_pad((string) $pairNum, 2, '0', STR_PAD_LEFT);
                    $sponsorProfile = $promotorPool[$pairCode]['profile'];
                    $level = 2;
                }

                $khuProfile = PromoterProfile::query()->firstOrCreate(
                    ['user_id' => $khuUser->id],
                    [
                        'stifin_code' => $stifinCode,
                        'verification_status' => PromoterVerificationStatus::Verified,
                        'verified_at' => now(),
                        'referral_slug' => 'khu-abu-'.strtolower($num).'-'.Str::random(6),
                        'sponsor_promoter_profile_id' => $sponsorProfile->id,
                        'upline_path_root_promoter_profile_id' => $bksProfile->id,
                        'level_depth' => $level,
                    ]
                );
                $promotorPool[$stifinCode] = ['user' => $khuUser, 'profile' => $khuProfile];
            }

            BranchSetting::query()->firstOrCreate(
                ['branch_code' => 'JML-CAB-62'],
                [
                    'brand_name' => 'STIFLOW Cabang JML-CAB-62',
                    'contact' => '0812-JML-CAB62',
                    'address' => 'Kantor Cabang JML-CAB-62 STIFIN',
                    'bank_name' => 'BCA',
                    'bank_account' => '7654321098',
                    'bank_account_name' => 'PT. STIFLOW Cabang JML-CAB-62',
                    'locale' => 'id_ID',
                    'timezone' => 'Asia/Jakarta',
                    'currency' => 'IDR',
                ]
            );

            BrandSetting::query()->firstOrCreate(
                ['id' => 1],
                [
                    'footer_text' => '© '.date('Y').' STIFLOW Cabang JML-CAB-62. All rights reserved.',
                ]
            );

            $voucherProduct = Product::query()->firstOrCreate(
                ['slug' => 'voucher-stifin-satuan'],
                [
                    'type' => 'voucher',
                    'name' => 'Voucher STIFIN Satuan',
                    'status' => 'active',
                    'visibility' => 'login_only',
                    'price' => 100000.00,
                    'commission_eligible' => false,
                    'description' => 'Voucher STIFIN per unit. Dapat digunakan untuk layanan STIFIN.',
                ]
            );

            VoucherProductConfig::query()->firstOrCreate(
                ['product_id' => $voucherProduct->id],
                [
                    'unit_price' => 100000.00,
                    'min_qty' => 1,
                    'max_qty' => 100,
                    'presets_json' => [1, 5, 10, 25, 50],
                    'free_units_rule_json' => null,
                ]
            );
            $voucherProduct->update([
                'status' => ProductStatus::Active->value,
                'visibility' => 'public',
                'is_published' => true,
                'published_at' => now(),
                'sort_order' => 7,
                'is_catalog_visible' => true,
                'featured_image_path' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=Voucher+STIFIN+Kartu+Merah+Emas+Ilustrasi+Premium&image_size=landscape_16_9',
                'meta_description' => 'Voucher STIFIN satuan untuk pembelian layanan tes STIFIN personal.',
                'landing_page_slug' => 'voucher-stifin',
                'package_code' => 'VCH-SATUAN-001',
            ]);

            $testimonialPromotorNames = [
                'KHU ABU 01', 'KHU ABU 02', 'KHU ABU 03', 'BKS HRA 40',
                'KHU ABU 05', 'KHU ABU 07', 'KHU ABU 09', 'KHU ABU 10',
            ];

            $productsList = [
                [
                    'slug' => 'produk-umum-stifin',
                    'name' => 'Paket Produk Umum STIFIN',
                    'type' => ProductType::Digital->value,
                    'price' => 149000.00,
                    'commission_eligible' => true,
                    'points_eligible' => true,
                    'sort_order' => 1,
                    'package_code' => 'UMUM-001',
                    'description' => 'Paket STIFIN produk umum untuk personal dan keluarga. Akses report dasar, video edukasi, dan group konsultasi.',
                    'meta_json' => [
                        'summary' => 'Produk umum STIFIN dasar - cocok untuk personal development',
                        'features' => [
                            'Akses 1x Tes STIFIN Personal',
                            'Report Minat Bakat & Kecerdasan',
                            'Konsultasi Online 1x 60 Menit',
                            'Ebook Panduan Karir STIFIN',
                            'Akses Group Komunitas 1 Tahun',
                        ],
                        'faq' => [
                            ['q' => 'Berapa lama report jadi?', 'a' => 'Report diterima H+1 setelah pembayaran diverifikasi.'],
                            ['q' => 'Berlaku berapa lama?', 'a' => 'Konsultasi berlaku 30 hari setelah aktivasi.'],
                        ],
                    ],
                    'landing_title' => 'Paket Produk Umum STIFIN — Personal Development',
                    'landing_slug' => 'produk-umum-stifin',
                    'hero_subtitle' => 'Temukan minat bakat & kelebihanmu dalam 1x Tes STIFIN. Report akurat + konselor berpengalaman.',
                    'hero_cta' => 'Dapatkan Sekarang — Rp 149.000',
                ],
                [
                    'slug' => 'tes-stifin-personal',
                    'name' => 'Tes STIFIN Personal (1x Tes + Report + Konsultasi)',
                    'type' => ProductType::Service->value,
                    'price' => 250000.00,
                    'commission_eligible' => true,
                    'points_eligible' => true,
                    'sort_order' => 2,
                    'package_code' => 'TSP-001',
                    'stifin_test_type_code' => 'PERSONAL-FULL',
                    'description' => 'Tes STIFIN Personal 1 sesi penuh. Sesi tes online mandiri, report 16 halaman, dan 1-on-1 konsultasi 90 menit.',
                    'meta_json' => [
                        'summary' => 'Tes personal lengkap STIFIN — tes 90 menit + report 16 halaman + konselor 1:1 90 menit',
                        'features' => [
                            'Tes STIFIN Personal 90 Menit',
                            'Report STIFIN 16 Halaman (Cetak & PDF)',
                            'Konsultasi 1-on-1 Bersertifikat 90 Menit',
                            'Rekomendasi Karir & Jurusan Kuliah',
                            'Grafik Kecerdasan Ganda & Gaya Belajar',
                            'Tanya Jawab Follow Up via WhatsApp 7 Hari',
                        ],
                        'faq' => [
                            ['q' => 'Apakah bisa tes dari rumah?', 'a' => 'BISA. Tes online 24/7 via browser HP atau laptop.'],
                            ['q' => 'Usia minimal berapa?', 'a' => 'Usia 8 tahun s/d dewasa 60 tahun bisa mengikuti Tes STIFIN Personal.'],
                            ['q' => 'Berapa kali bisa konsultasi ulang?', 'a' => 'Follow up WA 7 hari, sesi 1:1 tambahan ada paket upgrade.'],
                        ],
                    ],
                    'landing_title' => 'Tes STIFIN Personal — Report 16 Halaman + Konselor 1:1',
                    'landing_slug' => 'tes-stifin-personal',
                    'hero_subtitle' => 'Kenali dirimu SEBENARNYA: minat, bakat, gaya belajar, kecerdasan dominan, dan peta karir. Ribuan orang sudah terbukti.',
                    'hero_cta' => 'Ambil Tes Sekarang — Rp 250.000',
                ],
                [
                    'slug' => 'workshop-stifin-level-1',
                    'name' => 'Workshop STIFIN Level 1 (WSL-1) — Sertifikasi Dasar Konselor',
                    'type' => ProductType::Course->value,
                    'price' => 950000.00,
                    'commission_eligible' => true,
                    'points_eligible' => true,
                    'sort_order' => 3,
                    'package_code' => 'WSL-1-STIFIN',
                    'include_wsl_license_access' => true,
                    'wsl_level' => 1,
                    'description' => 'Workshop Sertifikasi Level 1 untuk menjadi Konselor STIFIN. Materi 12 Modul, Ujian, dan Sertifikat Resmi.',
                    'meta_json' => [
                        'summary' => 'Workshop Sertifikasi STIFIN Level 1 — jadi konselor bersertifikat bisa baca report & konsultasi',
                        'features' => [
                            '12 Modul Teori + Video Praktik',
                            'Group Mentoring 4x bersama Master Konselor',
                            'Ujian Teori & Praktik Bacaan Report',
                            'Sertifikat Konselor STIFIN Level 1',
                            'Bahan Bacaan 300+ Slide + Template Report',
                            'Akses LMS Update 1 Tahun',
                            'Kuota Jual Voucher dengan Margin Promotor',
                        ],
                        'faq' => [
                            ['q' => 'Apakah harus lulus psikologi?', 'a' => 'TIDAK. Terbuka untuk semua latar belakang asal lulus seleksi administrasi.'],
                            ['q' => 'Berapa lama masa berlaku sertifikat?', 'a' => '2 tahun, bisa diperpanjang dengan upgrade ke WSL-2.'],
                        ],
                    ],
                    'landing_title' => 'Workshop STIFIN Level 1 — Jadilah Konselor STIFIN Bersertifikat',
                    'landing_slug' => 'workshop-stifin-level-1',
                    'hero_subtitle' => 'Ubah hobi konsultasi jadi PROFESI. Buka peluang income JUTAAN RUPIAH per bulan sebagai Konselor STIFIN Resmi.',
                    'hero_cta' => 'Daftar WSL 1 — Rp 950.000',
                ],
                [
                    'slug' => 'workshop-stifin-level-2',
                    'name' => 'Workshop STIFIN Level 2 (WSL-2) — Sertifikasi Master + Trainer',
                    'type' => ProductType::Course->value,
                    'price' => 1950000.00,
                    'commission_eligible' => true,
                    'points_eligible' => true,
                    'sort_order' => 4,
                    'package_code' => 'WSL-2-STIFIN',
                    'include_wsl_license_access' => true,
                    'wsl_level' => 2,
                    'description' => 'Lanjutan WSL-1: Master pembacaan report kompleks, Trainer untuk bikin Workshop sendiri, akses case study VIP.',
                    'meta_json' => [
                        'summary' => 'WSL-2 Lanjutan: Master konselor + Train the Trainer. Bisa jalankan workshop sendiri komersial.',
                        'features' => [
                            'Lulus WSL-1 = Syarat Utama',
                            '18 Modul Lanjutan + 10 Case Study Kompleks',
                            'Sesi Private 1:1 Master Trainer 4x',
                            'Train The Trainer: Bahan Slide Workshop Sendiri',
                            'Sertifikat Master Konselor + Trainer STIFIN',
                            'Izin Komersial Adakan Workshop Sendiri',
                            'Akses Database 5.000+ Sample Report',
                        ],
                        'faq' => [
                            ['q' => 'Wajib lulus WSL-1 dulu?', 'a' => 'YA. WSL-2 tidak bisa diambil tanpa bukti sertifikat WSL-1 aktif.'],
                            ['q' => 'Ada job placement?', 'a' => 'Prioritas direkomendasikan ke jaringan cabang STIFIN seluruh Indonesia.'],
                        ],
                    ],
                    'landing_title' => 'Workshop STIFIN Level 2 — Master & Trainer Resmi',
                    'landing_slug' => 'workshop-stifin-level-2',
                    'hero_subtitle' => 'Naik level jadi MASTER: tangani kasus sulit, latih calon konselor baru, dan adakan workshop sendiri BERBAYAR.',
                    'hero_cta' => 'Upgrade ke WSL 2 — Rp 1.950.000',
                ],
                [
                    'slug' => 'id-card-scanner-stifin',
                    'name' => 'Paket ID Card + Scanner STIFIN (Reseller Konselor Kit)',
                    'type' => ProductType::Digital->value,
                    'price' => 475000.00,
                    'commission_eligible' => true,
                    'points_eligible' => true,
                    'sort_order' => 5,
                    'package_code' => 'ID-SCAN-KIT-001',
                    'include_id_card_license' => true,
                    'include_scanner' => true,
                    'stifin_test_type_code' => 'CARD-SCANNER-ACCESS',
                    'description' => 'Konselor pemula: ID Card Identitas Konselor STIFIN (print & desain) + akses fitur Scanner App untuk baca QR Report digital klien.',
                    'meta_json' => [
                        'summary' => 'KIT ID Card Cetak + Scanner App untuk konselor pemula: identitas profesional + tool baca QR report digital',
                        'features' => [
                            'ID Card Konselor STIFIN (PVC Premium, 2 sisi, nama + foto + kode ID)',
                            'Lanyard & ID Holder Premium STIFIN',
                            'Aktivasi Scanner App (1 tahun) untuk baca QR Report digital klien',
                            'Verifikasi Online Status Konselor via QR',
                            'Stiker Konselor (isi 10 lembar)',
                            'Template Desain Kartu Nama (Editable via Canva)',
                            'Akses Portal Scan Client Report Mobile',
                        ],
                        'faq' => [
                            ['q' => 'Apakah ID Card dikirim fisik?', 'a' => 'YA. Gratis ongkir wilayah Indonesia via JNE REG.'],
                            ['q' => 'Berapa lama Scanner berlaku?', 'a' => '1 tahun, renew Rp 99.000/tahun.'],
                        ],
                    ],
                    'landing_title' => 'ID Card + Scanner STIFIN — Konselor Kit Profesional',
                    'landing_slug' => 'id-card-scanner-stifin',
                    'hero_subtitle' => 'Tingkatkan kepercayaan klien dengan ID Card resmi + Scanner App untuk validasi report digital 1 ketuk.',
                    'hero_cta' => 'Pesan Konselor Kit — Rp 475.000',
                ],
                [
                    'slug' => 'paket-promotor-internal',
                    'name' => 'Paket Promotor Internal STIFIN (Bundle Resmi: Voucher 10 + WSL1 + Scanner + ID)',
                    'type' => ProductType::Membership->value,
                    'price' => 2990000.00,
                    'commission_eligible' => true,
                    'points_eligible' => true,
                    'sort_order' => 6,
                    'package_code' => 'PP-INTERNAL-001',
                    'include_scanner' => true,
                    'include_id_card_license' => true,
                    'include_wsl_license_access' => true,
                    'wsl_level' => 1,
                    'description' => 'PAKET HEMAT khusus calon promotor internal: 10 Voucher, WSL-1, ID Card, Scanner, Bahan Sosmed, Bimbingan 1 Bulan.',
                    'meta_json' => [
                        'summary' => 'Promotor Internal Bundle: 10 Voucher + WSL1 + ID Card + Scanner + Mentoring 30 Hari Resmi',
                        'features' => [
                            '10 Voucher STIFIN Satuan (senilai Rp 1.000.000)',
                            'Workshop Sertifikasi WSL-1 FULL (senilai Rp 950.000)',
                            'ID Card + Scanner Kit Konselor 1 Tahun',
                            'Mentoring Penjualan via Group WA 30 Hari',
                            '100+ Template Konten Sosmed (Canva Editable)',
                            'Akses ke Referral Link Unik & Dashboard',
                            'Komisi Hingga Level 5 sesuai Plan Standard',
                            'Meeting Rutin 2x Sebulan dengan Leader',
                        ],
                        'faq' => [
                            ['q' => 'Apa untung jadi Promotor Internal?', 'a' => 'Dapat akses harga khusus voucher, komisi 5 level, mentoring penjualan 30 HARI, dan jaringan nasional.'],
                            ['q' => 'Voucher bisa dijual sendiri?', 'a' => 'BISA. Dijual langsung ke prospek, margin disesuaikan dengan harga konsumen.'],
                        ],
                    ],
                    'landing_title' => 'Paket Promotor Internal STIFIN — Resmi Langsung Jualan',
                    'landing_slug' => 'paket-promotor-internal',
                    'hero_subtitle' => 'BUNDLE PALING LARIS: Langsung punya stok voucher 10 unit + sertifikat konselor + tool kit + mentoring 30 hari. Tinggal JUAL.',
                    'hero_cta' => 'Gabung Promotor — Rp 2.990.000',
                ],
            ];

            $insertedProducts = collect([$voucherProduct]);
            foreach ($productsList as $pConfig) {
                $p = Product::query()->firstOrCreate(
                    ['slug' => $pConfig['slug']],
                    [
                        'type' => $pConfig['type'],
                        'name' => $pConfig['name'],
                        'status' => ProductStatus::Active->value,
                        'visibility' => 'public',
                        'price' => $pConfig['price'],
                        'commission_eligible' => $pConfig['commission_eligible'],
                        'points_eligible' => $pConfig['points_eligible'],
                        'description' => $pConfig['description'],
                        'is_published' => true,
                        'published_at' => now(),
                        'is_catalog_visible' => true,
                        'sort_order' => $pConfig['sort_order'],
                        'meta_json' => $pConfig['meta_json'],
                        'package_code' => $pConfig['package_code'],
                        'stifin_test_type_code' => $pConfig['stifin_test_type_code'] ?? null,
                        'wsl_level' => $pConfig['wsl_level'] ?? null,
                        'include_scanner' => $pConfig['include_scanner'] ?? false,
                        'include_wsl_license_access' => $pConfig['include_wsl_license_access'] ?? false,
                        'include_id_card_license' => $pConfig['include_id_card_license'] ?? false,
                        'landing_page_slug' => $pConfig['landing_slug'],
                        'meta_description' => $pConfig['meta_json']['summary'] ?? null,
                        'featured_image_path' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt='.
                            urlencode($pConfig['name'].' Ilustrasi produk STIFIN profesional berwarna biru emas modern').
                            '&image_size=landscape_16_9',
                    ]
                );
                $insertedProducts->push($p);
            }

            $buildLandingBlocks = function (array $cfg) use ($testimonialPromotorNames) {
                $faqs = $cfg['meta_json']['faq'] ?? [
                    ['q' => 'Apakah ada garansi?', 'a' => 'Ada garansi 100% uang kembali sesuai syarat berlaku.'],
                    ['q' => 'Bagaimana cara bayar?', 'a' => 'Transfer Manual BCA 7654321098 a/n PT STIFLOW Cabang JML-CAB-62, lalu upload bukti.'],
                ];
                $features = $cfg['meta_json']['features'] ?? ['Fitur utama produk ini.'];
                $bulletsKeuntungan = [
                    'Support Tim Cabang JML-CAB-62 Resmi',
                    'Pembayaran Aman via Manual Transfer bank',
                    'Akses Cepat H+1 setelah verifikasi',
                    'Garansi Kepuasan & Komplain Channel Resmi',
                    'Bisa Request Invoice atas Nama Perusahaan',
                    'Laporan Pajak & Faktur Pajak Tersedia (PPN)',
                ];
                shuffle($testimonialPromotorNames);
                $tesNama = array_slice($testimonialPromotorNames, 0, 4);
                $tesKata = [
                    'Terbantu banget, order cepat masuk. Report jelas!',
                    'Klien saya puas hasil tes STIFINnya. Recommended.',
                    'Konselornya sabar, ngasih saran detail buat jurusan kuliah anak saya.',
                    'Sejak jadi promotor, komisi masuk tiap minggu. Terima kasih STIFIN!',
                    'WSL-2 upgrade langsung buka workshop sendiri. ROI cepet balik.',
                    'ID Card + Scanner bikin klien langsung percaya di pertemuan pertama.',
                ];
                shuffle($tesKata);
                $testimonial = [];
                foreach ($tesNama as $idx => $nm) {
                    $testimonial[] = ['name' => $nm, 'words' => $tesKata[$idx] ?? $tesKata[0], 'jabatan' => 'Promotor STIFIN Cabang JML-CAB-62'];
                }

                $blocks = [
                    ['type' => 'hero', 'config' => [
                        'title' => $cfg['landing_title'],
                        'subtitle' => $cfg['hero_subtitle'] ?? '',
                        'primary_cta' => ['label' => $cfg['hero_cta'], 'url' => url('/checkout/'.$cfg['slug'])],
                        'secondary_cta' => ['label' => 'Lihat Katalog Lainnya', 'url' => url('/katalog')],
                        'bg_image' => $cfg['featured_image_path'] ?? '',
                        'badge' => 'Produk Resmi Cabang JML-CAB-62',
                    ]],
                    ['type' => 'bullets', 'config' => ['title' => 'Kenapa Pilih Paket Ini?', 'items' => $bulletsKeuntungan, 'columns' => 3]],
                    ['type' => 'features', 'config' => ['title' => 'Apa yang Kamu Dapatkan?', 'subtitle' => 'Semua paket sudah lengkap, tinggal gunakan. Tambahan biaya NOL.', 'items' => $features, 'icon' => 'check-circle']],
                    ['type' => 'pricing', 'config' => ['title' => 'Harga Paket', 'currency' => 'IDR', 'items' => [
                        ['name' => $cfg['name'], 'price_monthly' => null, 'price_once' => $cfg['price'], 'features' => array_slice($features, 0, 7), 'cta' => ['label' => 'Beli Sekarang', 'url' => url('/checkout/'.$cfg['slug'])], 'highlight' => true],
                    ]]],
                    ['type' => 'cta', 'config' => ['title' => 'Siap Memulai?', 'subtitle' => 'Order sekarang dan dapatkan akses H+1 setelah pembayaran diverifikasi Admin.', 'button' => ['label' => $cfg['hero_cta'], 'url' => url('/checkout/'.$cfg['slug'])], 'variant' => 'solid-primary']],
                    ['type' => 'testimonial', 'config' => ['title' => 'Kata Mereka yang Sudah Pakai', 'subtitle' => 'Testimonial asli dari promotor dan klien cabang JML-CAB-62.', 'items' => $testimonial]],
                    ['type' => 'faq', 'config' => ['title' => 'Pertanyaan yang Sering Diajukan (FAQ)', 'items' => $faqs]],
                    ['type' => 'text', 'config' => ['title' => 'Cara Kerja Pemesanan — 4 Langkah Mudah', 'content' => '<ol><li>Klik tombol BELI, isi data diri di halaman checkout.</li><li>Transfer ke Rek BCA 7654321098 a/n PT. STIFLOW Cabang JML-CAB-62 sesuai nominal total.</li><li>Upload bukti transfer di halaman order atau WA Admin.</li><li>Tunggu 1x24 jam — Admin verifikasi, lalu akses produk KIRIM via email & WhatsApp.</li></ol>']],
                    ['type' => 'image', 'config' => ['title' => $cfg['name'], 'image_url' => $cfg['featured_image_path'] ?? '', 'alt' => $cfg['name']]],
                    ['type' => 'countdown', 'config' => ['title' => 'Promo Flash Sale Berakhir Dalam', 'ends_at' => now()->addDays(30)->toDateTimeString(), 'after_text' => 'Harga akan kembali normal setelah countdown habis.']],
                    ['type' => 'leadform', 'config' => ['title' => 'Mau Konsultasi Dulu Sebelum Beli?', 'subtitle' => 'Isi form di bawah, tim Admin akan menghubungi via WhatsApp maks 1 jam kerja.', 'fields' => ['nama', 'whatsapp', 'kota', 'catatan'], 'submit_button_label' => 'Kirim & Minta Dihubungi Admin']],
                    ['type' => 'social-proof', 'config' => ['title' => 'Orang Lain Sedang Melihat Produk Ini', 'type' => 'recent_purchases', 'window_hours' => 48, 'product_slug' => $cfg['slug'], 'limit' => 8, 'anonymize' => true]],
                    ['type' => 'video', 'config' => ['title' => 'Video Demo Produk & Cara Penggunaan', 'provider' => 'youtube', 'video_url' => 'https://youtube.com/watch?v=stifin-demo-'.($cfg['package_code'] ?? 'umum'), 'poster_image' => $cfg['featured_image_path'] ?? '']],
                    ['type' => 'footer', 'config' => ['brand_name' => 'STIFLOW Cabang JML-CAB-62', 'contact_wa' => '0812-JML-CAB62', 'address' => 'Kantor Cabang JML-CAB-62 STIFIN', 'social_media' => ['instagram' => '@stifin.jml62', 'facebook' => 'stiflow.jml62', 'tiktok' => '@stifin.jml62'], 'copyright' => '© '.date('Y').' STIFLOW Cabang JML-CAB-62.']],
                ];

                return $blocks;
            };

            $voucherLandingCfg = [
                'slug' => 'voucher-stifin',
                'name' => 'Voucher STIFIN Satuan',
                'price' => 100000.00,
                'landing_title' => 'Voucher STIFIN Satuan — Beli 1 Unit, Untuk 1x Tes',
                'hero_subtitle' => 'Voucher resmi untuk layanan Tes STIFIN personal. Bisa dipakai sendiri atau dijual kembali ke prospek.',
                'hero_cta' => 'Beli Voucher — Rp 100.000',
                'featured_image_path' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=Voucher+STIFIN+Kartu+Merah+Emas+Ilustrasi+Premium&image_size=landscape_16_9',
                'meta_json' => [
                    'summary' => 'Voucher STIFIN 1 unit — untuk 1x Tes Personal, bisa ditukarkan kapan saja dalam 1 tahun.',
                    'features' => [
                        '1 Voucher Unit = 1x Tes STIFIN Personal Full',
                        'Masa Berlaku 12 Bulan Sejak Pembelian',
                        'Dapat Dijual Kembali ke Prospek Sendiri (Promotor)',
                        'Auto Generate Kode Voucher Digital via Email',
                        'Tukarkan via Dashboard Member atau Konselor',
                        'Bonus Ebook Panduan Tes STIFIN PDF',
                    ],
                    'faq' => [
                        ['q' => 'Berapa voucher minimal pembelian?', 'a' => 'Minimal 1 unit, ada paket hemat 5 / 10 / 25 / 50 unit dengan harga lebih murah.'],
                        ['q' => 'Bisa untuk hadiah kado ulang tahun?', 'a' => 'Bisa banget. Kirim nama penerima, voucher bisa di-print dan dikemas kado oleh admin (add on packing Rp 25rb).'],
                    ],
                ],
            ];

            $voucherLandingPage = LandingPage::query()->firstOrCreate(
                ['slug' => $voucherLandingCfg['slug']],
                [
                    'title' => $voucherLandingCfg['landing_title'],
                    'status' => LandingPageStatus::Published->value,
                    'published_at' => now(),
                    'blocks_json' => $buildLandingBlocks($voucherLandingCfg),
                    'meta_json' => ['product_slug' => 'voucher-stifin-satuan', 'template' => 'product-sales'],
                    'created_by_user_id' => $admin->id,
                ]
            );
            foreach ($productsList as $pCfg) {
                $featured = 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt='.
                    urlencode($pCfg['name'].' Ilustrasi produk STIFIN profesional berwarna biru emas modern').
                    '&image_size=landscape_16_9';
                LandingPage::query()->firstOrCreate(
                    ['slug' => $pCfg['landing_slug']],
                    [
                        'title' => $pCfg['landing_title'],
                        'status' => LandingPageStatus::Published->value,
                        'published_at' => now(),
                        'blocks_json' => $buildLandingBlocks(array_merge($pCfg, ['featured_image_path' => $featured])),
                        'meta_json' => ['product_slug' => $pCfg['slug'], 'package_code' => $pCfg['package_code'], 'template' => 'product-sales'],
                        'created_by_user_id' => $admin->id,
                    ]
                );
            }

            $permissions = [
                ['key' => 'users.manage', 'name' => 'Kelola Pengguna', 'group' => PermissionGroups::Admin->value, 'description' => 'Mengelola data pengguna sistem'],
                ['key' => 'promoters.view', 'name' => 'Lihat Promotor', 'group' => PermissionGroups::Promotor->value, 'description' => 'Melihat daftar profil promotor'],
                ['key' => 'promoters.manage', 'name' => 'Kelola Promotor', 'group' => PermissionGroups::Promotor->value, 'description' => 'Mengelola data promotor'],
                ['key' => 'promoters.verify', 'name' => 'Verifikasi Promotor', 'group' => PermissionGroups::Promotor->value, 'description' => 'Melakukan verifikasi promotor baru'],
                ['key' => 'contacts.view_all', 'name' => 'Lihat Semua Kontak', 'group' => PermissionGroups::Crm->value, 'description' => 'Melihat seluruh kontak milik semua promotor'],
                ['key' => 'contacts.manage_all', 'name' => 'Kelola Semua Kontak', 'group' => PermissionGroups::Crm->value, 'description' => 'Mengelola seluruh kontak milik semua promotor'],
                ['key' => 'contacts.reassign', 'name' => 'Pindahkan Pemilik Kontak', 'group' => PermissionGroups::Crm->value, 'description' => 'Memindahkan kepemilikan kontak antar promotor'],
                ['key' => 'orders.view', 'name' => 'Lihat Pesanan', 'group' => PermissionGroups::Orders->value, 'description' => 'Melihat daftar pesanan'],
                ['key' => 'orders.manage', 'name' => 'Kelola Pesanan', 'group' => PermissionGroups::Orders->value, 'description' => 'Mengelola pesanan (ubah status, dll)'],
                ['key' => 'payments.verify', 'name' => 'Verifikasi Pembayaran', 'group' => PermissionGroups::Payments->value, 'description' => 'Memverifikasi bukti pembayaran'],
                ['key' => 'payments.refund', 'name' => 'Proses Pengembalian Dana', 'group' => PermissionGroups::Payments->value, 'description' => 'Memproses refund pembayaran'],
                ['key' => 'vouchers.view', 'name' => 'Lihat Voucher', 'group' => PermissionGroups::Vouchers->value, 'description' => 'Melihat daftar voucher'],
                ['key' => 'vouchers.review', 'name' => 'Review Voucher', 'group' => PermissionGroups::Vouchers->value, 'description' => 'Melakukan review klaim voucher'],
                ['key' => 'vouchers.retry_confirmed', 'name' => 'Retry Voucher Terkonfirmasi', 'group' => PermissionGroups::Vouchers->value, 'description' => 'Mengulang proses voucher yang sudah terkonfirmasi'],
                ['key' => 'products.manage', 'name' => 'Kelola Produk', 'group' => PermissionGroups::Catalog->value, 'description' => 'Mengelola katalog produk'],
                ['key' => 'courses.manage', 'name' => 'Kelola Kursus', 'group' => PermissionGroups::Courses->value, 'description' => 'Mengelola kursus LMS'],
                ['key' => 'affiliate.manage', 'name' => 'Kelola Afiliasi', 'group' => PermissionGroups::Affiliate->value, 'description' => 'Mengelola program afiliasi dan referral'],
                ['key' => 'payouts.approve', 'name' => 'Setujui Pencairan', 'group' => PermissionGroups::Payouts->value, 'description' => 'Menyetujui permintaan payout komisi'],
                ['key' => 'campaigns.manage', 'name' => 'Kelola Kampanye', 'group' => PermissionGroups::Campaigns->value, 'description' => 'Mengelola kampanye pemasaran'],
                ['key' => 'campaigns.send', 'name' => 'Kirim Kampanye', 'group' => PermissionGroups::Campaigns->value, 'description' => 'Mengirim atau menjadwalkan kampanye'],
                ['key' => 'integrations.manage', 'name' => 'Kelola Integrasi', 'group' => PermissionGroups::Integrations->value, 'description' => 'Mengelola koneksi integrasi sistem'],
                ['key' => 'settings.manage', 'name' => 'Kelola Pengaturan', 'group' => PermissionGroups::Settings->value, 'description' => 'Mengelola pengaturan sistem'],
                ['key' => 'audit.view', 'name' => 'Lihat Audit Trail', 'group' => PermissionGroups::Audit->value, 'description' => 'Melihat log audit aktivitas sistem'],
                ['key' => 'member.manage', 'name' => 'Kelola Member', 'group' => PermissionGroups::Member->value, 'description' => 'Mengelola member dan keanggotaan'],
            ];

            foreach ($permissions as $perm) {
                Permission::query()->firstOrCreate(
                    ['key' => $perm['key']],
                    [
                        'name' => $perm['name'],
                        'group' => $perm['group'],
                        'description' => $perm['description'],
                    ]
                );
            }

            $pipeline = Pipeline::query()->firstOrCreate(
                ['name' => 'STIFIN Sales'],
                [
                    'description' => 'Pipeline default untuk proses penjualan STIFIN',
                    'is_default' => true,
                    'owner_user_id' => $admin->id,
                ]
            );

            $stages = [
                ['name' => 'Lead', 'slug' => 'lead', 'position' => 0, 'color_hex' => '#94a3b8', 'is_won_stage' => false, 'is_lost_stage' => false],
                ['name' => 'Contacted', 'slug' => 'contacted', 'position' => 1, 'color_hex' => '#38bdf8', 'is_won_stage' => false, 'is_lost_stage' => false],
                ['name' => 'Qualified', 'slug' => 'qualified', 'position' => 2, 'color_hex' => '#22c55e', 'is_won_stage' => false, 'is_lost_stage' => false],
                ['name' => 'Proposal', 'slug' => 'proposal', 'position' => 3, 'color_hex' => '#f59e0b', 'is_won_stage' => false, 'is_lost_stage' => false],
                ['name' => 'Client', 'slug' => 'client', 'position' => 4, 'color_hex' => '#8b5cf6', 'is_won_stage' => false, 'is_lost_stage' => false],
                ['name' => 'Won/Lost', 'slug' => 'won-lost', 'position' => 5, 'color_hex' => '#ef4444', 'is_won_stage' => true, 'is_lost_stage' => true],
            ];

            foreach ($stages as $stage) {
                PipelineStage::query()->firstOrCreate(
                    ['pipeline_id' => $pipeline->id, 'slug' => $stage['slug']],
                    [
                        'name' => $stage['name'],
                        'position' => $stage['position'],
                        'color_hex' => $stage['color_hex'],
                        'is_won_stage' => $stage['is_won_stage'],
                        'is_lost_stage' => $stage['is_lost_stage'],
                    ]
                );
            }

            $commissionPlan = CommissionPlan::query()->firstOrCreate(
                ['name' => 'STIFLOW Standard Plan'],
                [
                    'description' => 'Paket komisi standar STIFLOW dengan skema 5 level',
                    'max_levels' => 5,
                    'is_active' => true,
                    'is_default' => true,
                    'minimum_commission_per_entry' => 0,
                    'minimum_payout_total' => 100000,
                ]
            );

            $commissionRules = [
                ['level' => 1, 'value' => 10.0000],
                ['level' => 2, 'value' => 3.0000],
                ['level' => 3, 'value' => 2.0000],
                ['level' => 4, 'value' => 1.0000],
                ['level' => 5, 'value' => 0.5000],
            ];

            foreach ($commissionRules as $rule) {
                CommissionRule::query()->firstOrCreate(
                    [
                        'commission_plan_id' => $commissionPlan->id,
                        'product_id' => null,
                        'level' => $rule['level'],
                    ],
                    [
                        'product_type_filter' => null,
                        'rule_type' => CommissionRuleType::Percentage->value,
                        'value' => $rule['value'],
                        'cap_per_unit_max' => null,
                    ]
                );
            }

            $courseBasic = Course::query()->firstOrCreate(
                ['slug' => 'stifin-dasar-level-1'],
                [
                    'title' => 'Stifin Dasar Level 1',
                    'summary' => 'Kursus dasar memahami konsep STIFIN untuk pemula',
                    'description' => 'Materi pembelajaran dasar STIFIN meliputi pengenalan, konsep dasar, dan implementasi awal.',
                    'difficulty_level' => 'beginner',
                    'estimated_minutes' => 300,
                    'is_published' => true,
                    'published_at' => now(),
                    'is_free' => true,
                    'author_user_id' => $admin->id,
                ]
            );

            $moduleBasic = CourseModule::query()->firstOrCreate(
                ['course_id' => $courseBasic->id, 'position' => 0],
                [
                    'title' => 'Modul Pengenalan',
                    'summary' => 'Modul pengenalan STIFIN Dasar',
                    'is_unlocked_by_default' => true,
                ]
            );

            for ($i = 1; $i <= 10; $i++) {
                Lesson::query()->firstOrCreate(
                    ['course_module_id' => $moduleBasic->id, 'position' => $i - 1],
                    [
                        'title' => "Pelajaran {$i}: Dasar STIFIN Bagian {$i}",
                        'lesson_type' => 'text',
                        'content_text' => "Materi pelajaran nomor {$i} untuk kursus Stifin Dasar Level 1.",
                        'duration_minutes' => 30,
                        'is_preview_allowed' => $i <= 2,
                        'is_published' => true,
                    ]
                );
            }

            Course::query()->firstOrCreate(
                ['slug' => 'pro-member-stifin'],
                [
                    'title' => 'Pro Member STIFIN',
                    'summary' => 'Kursus eksklusif untuk anggota Pro Member STIFIN',
                    'description' => 'Materi lanjutan dan konten eksklusif untuk Pro Member STIFIN.',
                    'difficulty_level' => 'intermediate',
                    'estimated_minutes' => 600,
                    'is_published' => true,
                    'published_at' => now(),
                    'is_free' => false,
                    'author_user_id' => $admin->id,
                ]
            );

            $tags = [
                ['name' => 'VIP', 'color_hex' => '#fbbf24', 'category' => 'tier'],
                ['name' => 'Prospect Baru', 'color_hex' => '#06b6d4', 'category' => 'status'],
                ['name' => 'Hot Lead', 'color_hex' => '#ef4444', 'category' => 'priority'],
                ['name' => 'Perlu Follow Up', 'color_hex' => '#8b5cf6', 'category' => 'action'],
            ];

            foreach ($tags as $tag) {
                Tag::query()->firstOrCreate(
                    ['name' => $tag['name']],
                    [
                        'color_hex' => $tag['color_hex'],
                        'category' => $tag['category'],
                    ]
                );
            }

            $messageTemplates = [
                [
                    'name' => 'Voucher Masuk',
                    'channel' => MessageChannel::WhatsApp->value,
                    'template_type' => 'transactional',
                    'subject_line' => null,
                    'content_body' => 'Halo {nama}, voucher STIFIN Anda sudah masuk. Kode voucher Anda adalah {kode_voucher}. Silakan gunakan sesuai ketentuan berlaku. Terima kasih!',
                    'language_code' => 'id',
                    'placeholders_json' => ['nama', 'kode_voucher'],
                    'created_by_user_id' => $admin->id,
                    'is_active' => true,
                ],
                [
                    'name' => 'Follow Up',
                    'channel' => MessageChannel::WhatsApp->value,
                    'template_type' => 'general',
                    'subject_line' => null,
                    'content_body' => 'Halo {nama}, saya {nama_promotor} dari STIFIN. Ingin follow up mengenai penawaran STIFIN yang kita bahas sebelumnya. Apakah Anda punya waktu untuk diskusi singkat?',
                    'language_code' => 'id',
                    'placeholders_json' => ['nama', 'nama_promotor'],
                    'created_by_user_id' => $admin->id,
                    'is_active' => true,
                ],
                [
                    'name' => 'Invoice Order',
                    'channel' => MessageChannel::Email->value,
                    'template_type' => 'transactional',
                    'subject_line' => 'Invoice Pesanan #{nomor_order}',
                    'content_body' => '<p>Halo {nama},</p><p>Terima kasih atas pesanan Anda. Berikut adalah rincian invoice:</p><ul><li>Nomor Order: {nomor_order}</li><li>Tanggal: {tanggal}</li><li>Total: {total}</li></ul><p>Silakan selesaikan pembayaran sesuai instruksi. Terima kasih!</p>',
                    'language_code' => 'id',
                    'placeholders_json' => ['nama', 'nomor_order', 'tanggal', 'total'],
                    'created_by_user_id' => $admin->id,
                    'is_active' => true,
                ],
            ];

            foreach ($messageTemplates as $tpl) {
                MessageTemplate::query()->firstOrCreate(
                    ['name' => $tpl['name'], 'channel' => $tpl['channel']],
                    [
                        'template_type' => $tpl['template_type'],
                        'subject_line' => $tpl['subject_line'],
                        'content_body' => $tpl['content_body'],
                        'language_code' => $tpl['language_code'],
                        'placeholders_json' => $tpl['placeholders_json'],
                        'created_by_user_id' => $tpl['created_by_user_id'],
                        'has_approved_external_template' => false,
                        'is_active' => $tpl['is_active'],
                    ]
                );
            }

            $contactSamples = [
                ['full_name' => 'Prospek A', 'first_name' => 'Prospek', 'last_name' => 'A', 'phone' => '0812-0000-0001', 'whatsapp' => '0812-0000-0001'],
                ['full_name' => 'Prospek B', 'first_name' => 'Prospek', 'last_name' => 'B', 'phone' => '0812-0000-0002', 'whatsapp' => '0812-0000-0002'],
                ['full_name' => 'Prospek C', 'first_name' => 'Prospek', 'last_name' => 'C', 'phone' => '0812-0000-0003', 'whatsapp' => '0812-0000-0003'],
            ];

            $firstStage = PipelineStage::query()->where('pipeline_id', $pipeline->id)->orderBy('position')->first();

            foreach ($contactSamples as $sample) {
                Contact::query()->firstOrCreate(
                    ['owner_promoter_profile_id' => $promoterProfile->id, 'full_name' => $sample['full_name']],
                    [
                        'status' => ContactStatus::Prospect->value,
                        'first_name' => $sample['first_name'],
                        'last_name' => $sample['last_name'],
                        'phone' => $sample['phone'],
                        'whatsapp' => $sample['whatsapp'],
                        'owner_user_id' => $promotorUser->id,
                        'pipeline_id' => $pipeline->id,
                        'stage_id' => $firstStage?->id,
                        'source_channel' => 'seed',
                    ]
                );
            }

            IntegrationConnection::query()->firstOrCreate(
                ['provider_category' => ProviderCategory::StifinApi->value, 'provider_type' => 'stifin_api', 'is_primary' => true],
                [
                    'provider_name' => 'STIFIN API',
                    'display_name' => 'STIFIN Default API',
                    'encrypted_credentials' => [
                        'username' => env('STIFIN_USERNAME', 'stifin_default_user'),
                        'password' => env('STIFIN_PASSWORD', 'stifin_default_pass'),
                        'user_id' => env('STIFIN_USER_ID', 'STIFIN-LOCAL'),
                        'base_url' => env('STIFIN_BASE_URL', 'https://apro.stifin.id/api'),
                        'timeout_connect_seconds' => 15,
                        'timeout_total_seconds' => 30,
                    ],
                    'config_json' => null,
                    'status' => IntegrationConnectionStatus::Configured->value,
                    'is_active' => true,
                    'is_primary' => true,
                    'owned_by_user_id' => $admin->id,
                ]
            );
        });
    }
}
