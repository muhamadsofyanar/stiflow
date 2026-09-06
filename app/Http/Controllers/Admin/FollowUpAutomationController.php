<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AutomationTriggerType;
use App\Http\Controllers\Controller;
use App\Models\AutomationFlow;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FollowUpAutomationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:automations.manage');
    }

    public function index(Request $request): View
    {
        $sequenceDays = [1, 3, 7, 14, 30];

        $defaultTemplates = [
            1 => [
                'day' => 1,
                'name' => 'Follow Up D+1 - Welcome & Introduction',
                'subject' => 'Selamat! Selamat datang di keluarga kami',
                'delay_hours' => 24,
                'channel' => 'email',
                'template_body' => $this->getTemplateBody(1),
                'active' => true,
                'goal' => 'Pengenalan produk & kepercayaan awal',
                'open_rate_target' => 40,
            ],
            3 => [
                'day' => 3,
                'name' => 'Follow Up D+3 - Value & Edukasi',
                'subject' => 'Rahasia keberhasilan member sebelumnya...',
                'delay_hours' => 24 * 3,
                'channel' => 'email_whatsapp',
                'template_body' => $this->getTemplateBody(3),
                'active' => true,
                'goal' => 'Edukasi manfaat produk (social proof)',
                'open_rate_target' => 35,
            ],
            7 => [
                'day' => 7,
                'name' => 'Follow Up D+7 - Testimonial & Success',
                'subject' => '[VIDEO] Kisah sukses member sama seperti Anda',
                'delay_hours' => 24 * 7,
                'channel' => 'whatsapp',
                'template_body' => $this->getTemplateBody(7),
                'active' => true,
                'goal' => 'Dorong keputusan dengan bukti sosial',
                'open_rate_target' => 45,
            ],
            14 => [
                'day' => 14,
                'name' => 'Follow Up D+14 - Case Study & Deep Dive',
                'subject' => 'Studi kasus: Dari 0 ke 10 Juta dalam 30 hari',
                'delay_hours' => 24 * 14,
                'channel' => 'email',
                'template_body' => $this->getTemplateBody(14),
                'active' => true,
                'goal' => 'Upsell / Cross-sell penawaran lanjutan',
                'open_rate_target' => 30,
            ],
            30 => [
                'day' => 30,
                'name' => 'Follow Up D+30 - Loyalty & Referral',
                'subject' => 'Bonus khusus untuk Anda - ajak teman dapat komisi!',
                'delay_hours' => 24 * 30,
                'channel' => 'email_whatsapp',
                'template_body' => $this->getTemplateBody(30),
                'active' => true,
                'goal' => 'Loyalty & dorongan referral program',
                'open_rate_target' => 35,
            ],
        ];

        $flows = AutomationFlow::query()
            ->where('trigger_type', AutomationTriggerType::LeadCreated->value)
            ->orWhere('trigger_type', 'LIKE', '%LeadCreated%')
            ->with('steps')
            ->latest()
            ->limit(10)
            ->get();

        $stats = [
            'active_flows' => AutomationFlow::query()->where('status', \App\Enums\AutomationFlowStatus::Active->value)->count(),
            'total_triggered_today' => \App\Models\AutomationRun::query()->whereDate('started_at', now()->toDateString())->count(),
            'avg_open_rate' => 38.5,
            'avg_click_rate' => 6.2,
        ];

        return view('admin.automations.follow-ups', compact(
            'sequenceDays',
            'defaultTemplates',
            'flows',
            'stats'
        ));
    }

    public function toggleStage(Request $request, int $day): RedirectResponse
    {
        $validDays = [1, 3, 7, 14, 30];
        if (! in_array($day, $validDays, true)) {
            return redirect()->back()->with('error', 'Stage follow-up tidak valid.');
        }

        cache()->put("followup_stage_{$day}_active", !(bool) cache("followup_stage_{$day}_active", true));

        return redirect()->back()->with('success', "Stage D+{$day} status berhasil diubah.");
    }

    public function runSequenceTest(Request $request): RedirectResponse
    {
        $contactId = $request->input('contact_id');
        if (! $contactId) {
            return redirect()->back()->with('error', 'Contact ID diperlukan untuk test.');
        }

        try {
            \App\Jobs\FollowUpSequenceDispatchJob::dispatch(
                (int) $contactId,
                AutomationTriggerType::Manual->value
            );

            return redirect()->back()->with('success', "Test follow-up untuk contact {$contactId} dijadwalkan.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal menjadwalkan test: ' . $e->getMessage());
        }
    }

    private function getTemplateBody(int $day): string
    {
        return match ($day) {
            1 => "Hi {{first_name}},\n\nSelamat bergabung di {{brand_name}}! Kami sangat senang Anda ada di sini.\n\n"
                . "Untuk memulai, silakan:\n"
                . "1. Login member area: {{member_area_url}}\n"
                . "2. Lengkapi data profil\n"
                . "3. Ikuti grup WA komunitas: {{wa_group_link}}\n\n"
                . "Jika ada pertanyaan, balas email ini saja ya.\n\nSalam,\nTim {{brand_name}}",

            3 => "Halo {{first_name}} 👋\n\nSudah 3 hari sejak Anda bergabung. Sudah mencoba akses produknya?\n\n"
                . "Kami ingin berbagi kisah inspiratif dari member lama:\n"
                . "💡 \"Setelah ikut program ini, omset saya naik 2x lipat dalam 1 bulan!\"\n\n"
                . "Anda juga bisa lho! Semangat terus!\n\nCC: Tim {{brand_name}}",

            7 => "Assalamualaikum {{first_name}} 🤲\n\n"
                . "Video singkat buat Anda: [LINK_VIDEO_TESTIMONI]\n\n"
                . "Ini adalah salah satu member premium kami yang merasakan manfaat produknya secara langsung.\n\n"
                . "Tertarik upgrade? Reply WA kami ya.\n\nTerima kasih,\nTim Support",

            14 => "Hi {{first_name}},\n\n"
                . "Studi kasus terbaru: Bagaimana Budiman, 32 tahun, dari 0 menghasilkan lebih dari Rp 10.000.000 dalam 30 hari dengan mengikuti program kami step-by-step.\n\n"
                . "Rahasianya? Konsisten + bimbingan mentor pribadi.\n\n"
                . "Tunggu apalagi? Tingkatkan paket Anda sekarang juga!\n\nPromo terbatas:\n{{upsell_cta}}",

            30 => "Dear {{first_name}},\n\n"
                . "Terima kasih sudah menjadi bagian dari kami selama 1 bulan ini! 🎉\n\n"
                . "Sebagai bentuk apresiasi, kami punya program spesial untuk Anda:\n"
                . "💼 Referral Exclusive: Ajak teman bergabung, dapatkan 10% komisi!\n\n"
                . "Klik link referral Anda:\n{{referral_link}}\n\n"
                . "Semoga berkah! 🤲\n\nSalam hangat,\nTim {{brand_name}}",

            default => "",
        };
    }
}
