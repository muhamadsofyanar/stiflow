<?php

namespace App\Services\Landing;

class SimpleBlockRendererService
{
    public function render(array $blocks): string
    {
        if (empty($blocks)) {
            return <<<'HTML'
<main class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 to-emerald-50 p-6">
  <div class="bg-white border border-slate-200 rounded-3xl p-10 shadow-xl max-w-2xl w-full text-center">
    <h1 class="text-4xl font-black text-slate-900 mb-4">Landing Page</h1>
    <p class="text-slate-600 mb-6">Belum ada blok yang ditambahkan. Silakan edit dari Admin Panel.</p>
  </div>
</main>
HTML;
        }

        $html = '<div class="landing-page-content min-h-screen">';
        foreach ($blocks as $block) {
            $type = strtolower($block['type'] ?? 'text');
            $html .= $this->renderBlock($type, $block);
        }
        $html .= '</div>';

        return $html;
    }

    private function renderBlock(string $type, array $block): string
    {
        $title = e($block['title'] ?? '');
        $content = e($block['content'] ?? $block['description'] ?? $block['subtitle'] ?? '');

        $textBlockTitleHtml = $title ? "<h2 class=\"text-3xl font-black text-slate-900 mb-4\">{$title}</h2>" : '';
        $textBlockContentHtml = $content ? "<div class=\"text-slate-600 leading-relaxed prose prose-slate max-w-none\">{$content}</div>" : '';
        $imageBlockTitleHtml = $title ? "<h2 class=\"text-2xl font-bold text-slate-900 mb-4 text-center\">{$title}</h2>" : '';
        $videoBlockTitleHtml = $title ? "<h2 class=\"text-2xl font-bold text-slate-900 mb-4 text-center\">{$title}</h2>" : '';
        $defaultBlockTitleHtml = $title ? "<h3 class=\"text-xl font-bold text-slate-900 mb-2\">{$title}</h3>" : '';
        $defaultBlockContentHtml = $content ? "<p class=\"text-slate-600\">{$content}</p>" : '';

        return match ($type) {
            'hero' => $this->renderHero($block),
            'features' => $this->renderFeatures($block),
            'testimonial' => $this->renderTestimonial($block),
            'pricing' => $this->renderPricing($block),
            'faq' => $this->renderFaq($block),
            'cta' => $this->renderCta($block),
            'text' => <<<HTML
<section class="py-16 px-6 bg-white">
  <div class="max-w-4xl mx-auto">
    {$textBlockTitleHtml}
    {$textBlockContentHtml}
  </div>
</section>
HTML,
            'image' => <<<HTML
<section class="py-12 px-6 bg-slate-50">
  <div class="max-w-5xl mx-auto">
    {$imageBlockTitleHtml}
    <div class="aspect-video bg-gradient-to-br from-indigo-100 to-emerald-100 rounded-3xl flex items-center justify-center text-6xl text-slate-400">
      🖼️
    </div>
  </div>
</section>
HTML,
            'video' => <<<HTML
<section class="py-12 px-6 bg-white">
  <div class="max-w-4xl mx-auto">
    {$videoBlockTitleHtml}
    <div class="aspect-video bg-slate-900 rounded-3xl flex items-center justify-center text-6xl text-white">▶️</div>
  </div>
</section>
HTML,
            'leadform' => $this->renderLeadForm($block),
            'countdown' => $this->renderCountdown($block),
            'social-proof' => $this->renderSocialProofBlock($block),
            'bullets' => $this->renderBullets($block),
            'footer' => $this->renderFooter($block),
            default => <<<HTML
<section class="py-10 px-6 bg-slate-50 border border-slate-200 rounded-2xl mx-6 my-4">
  <p class="text-sm text-slate-500 uppercase tracking-wide font-bold mb-1">Block: {$type}</p>
  {$defaultBlockTitleHtml}
  {$defaultBlockContentHtml}
</section>
HTML,
        };
    }

    private function renderHero(array $b): string
    {
        $t = e($b['title'] ?? 'Temukan Solusi Terbaik');
        $s = e($b['subtitle'] ?? 'Deskripsi singkat penawaran Anda');
        $btn = e($b['button_text'] ?? 'Mulai Sekarang');
        $url = e($b['button_url'] ?? '#');
        return <<<HTML
<section class="relative py-24 px-6 bg-gradient-to-br from-indigo-600 via-indigo-700 to-emerald-600 text-white overflow-hidden">
  <div class="max-w-5xl mx-auto text-center relative z-10">
    <h1 class="text-5xl sm:text-6xl font-black tracking-tight mb-6 leading-tight">{$t}</h1>
    <p class="text-xl text-indigo-100 mb-10 max-w-2xl mx-auto leading-relaxed">{$s}</p>
    <a href="{$url}" class="inline-block px-10 py-4 bg-white text-indigo-700 font-black text-lg rounded-2xl shadow-2xl hover:scale-105 transition-transform">
      {$btn} →
    </a>
  </div>
</section>
HTML;
    }

    private function renderFeatures(array $b): string
    {
        $t = e($b['title'] ?? 'Fitur Unggulan');
        $features = $b['features'] ?? [];
        $cards = '';
        $icons = ['✨', '🚀', '🔒', '⚡', '🎯', '💡'];
        $i = 0;
        foreach ($features as $f) {
            $ic = e($f['icon'] ?? $icons[$i % count($icons)]);
            $ft = e($f['title'] ?? 'Fitur');
            $fd = e($f['desc'] ?? $f['description'] ?? '');
            $cards .= <<<HTML
<div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-xl transition-shadow">
  <div class="text-4xl mb-4">{$ic}</div>
  <h3 class="font-bold text-xl text-slate-900 mb-2">{$ft}</h3>
  <p class="text-slate-600 leading-relaxed">{$fd}</p>
</div>
HTML;
            $i++;
        }
        if (empty($cards)) {
            $cards = '<p class="text-slate-500 text-center col-span-full py-8">Belum ada fitur.</p>';
        }
        return <<<HTML
<section class="py-20 px-6 bg-slate-50">
  <div class="max-w-6xl mx-auto">
    <h2 class="text-4xl font-black text-center text-slate-900 mb-4">{$t}</h2>
    <div class="grid md:grid-cols-3 gap-6 mt-12">{$cards}</div>
  </div>
</section>
HTML;
    }

    private function renderTestimonial(array $b): string
    {
        $t = e($b['title'] ?? 'Apa Kata Mereka');
        $name = e($b['name'] ?? 'Pengguna');
        $role = e($b['role'] ?? $b['position'] ?? '');
        $quote = e($b['quote'] ?? $b['content'] ?? 'Sangat puas dengan layanan ini!');
        $stars = str_repeat('⭐', (int) ($b['rating'] ?? 5));
        $roleHtml = $role ? "<p class=\"text-slate-500 text-sm\">{$role}</p>" : '';
        $nameFirstChar = strtoupper($name[0] ?? 'U');
        return <<<HTML
<section class="py-20 px-6 bg-white">
  <div class="max-w-4xl mx-auto">
    <h2 class="text-4xl font-black text-center text-slate-900 mb-12">{$t}</h2>
    <div class="bg-gradient-to-br from-indigo-50 to-emerald-50 border border-slate-200 rounded-3xl p-10 shadow-lg">
      <div class="text-2xl mb-4">{$stars}</div>
      <p class="text-2xl text-slate-800 leading-relaxed mb-6 italic">"{$quote}"</p>
      <div class="flex items-center gap-4">
        <div class="h-14 w-14 rounded-full bg-gradient-to-br from-indigo-500 to-emerald-500 flex items-center justify-center text-white font-black text-xl">
          {$nameFirstChar}
        </div>
        <div>
          <p class="font-bold text-slate-900">{$name}</p>
          {$roleHtml}
        </div>
      </div>
    </div>
  </div>
</section>
HTML;
    }

    private function renderPricing(array $b): string
    {
        $t = e($b['title'] ?? 'Paket Harga');
        $price = e($b['price'] ?? $b['amount'] ?? '99.000');
        $per = e($b['period'] ?? '/ paket');
        $btn = e($b['button_text'] ?? 'Pilih Paket');
        $url = e($b['button_url'] ?? '#');
        $features = $b['features'] ?? [];
        $list = '';
        foreach ($features as $f) {
            $ft = e(is_array($f) ? ($f['text'] ?? json_encode($f)) : $f);
            $list .= "<li class=\"flex items-start gap-2\"><span class=\"text-emerald-600 mt-0.5\">✓</span><span class=\"text-slate-700\">{$ft}</span></li>";
        }
        return <<<HTML
<section class="py-20 px-6 bg-slate-50">
  <div class="max-w-2xl mx-auto">
    <h2 class="text-4xl font-black text-center text-slate-900 mb-12">{$t}</h2>
    <div class="bg-white border-2 border-indigo-600 rounded-3xl p-10 shadow-2xl relative">
      <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-5 py-1.5 bg-gradient-to-r from-indigo-600 to-emerald-600 text-white rounded-full text-xs font-black uppercase tracking-wider">
        Paling Populer
      </div>
      <div class="text-center mb-8">
        <p class="text-6xl font-black text-slate-900 mb-1">Rp{$price}</p>
        <p class="text-slate-500">{$per}</p>
      </div>
      <ul class="space-y-3 mb-10">{$list}</ul>
      <a href="{$url}" class="block w-full text-center py-4 bg-gradient-to-r from-indigo-600 to-emerald-600 text-white font-bold text-lg rounded-2xl shadow-lg hover:shadow-xl transition-shadow">
        {$btn}
      </a>
    </div>
  </div>
</section>
HTML;
    }

    private function renderFaq(array $b): string
    {
        $t = e($b['title'] ?? 'Pertanyaan Umum');
        $items = $b['items'] ?? $b['faqs'] ?? [];
        $html = '';
        foreach ($items as $item) {
            $q = e($item['question'] ?? $item['q'] ?? '');
            $a = e($item['answer'] ?? $item['a'] ?? '');
            if ($q) {
                $html .= <<<HTML
<details class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm group">
  <summary class="font-bold text-slate-900 cursor-pointer flex justify-between items-center list-none">
    <span>{$q}</span>
    <span class="text-indigo-600 group-open:rotate-45 transition-transform text-2xl leading-none">+</span>
  </summary>
  <p class="mt-4 text-slate-600 leading-relaxed">{$a}</p>
</details>
HTML;
            }
        }
        return <<<HTML
<section class="py-20 px-6 bg-white">
  <div class="max-w-3xl mx-auto">
    <h2 class="text-4xl font-black text-center text-slate-900 mb-12">{$t}</h2>
    <div class="space-y-4">{$html}</div>
  </div>
</section>
HTML;
    }

    private function renderCta(array $b): string
    {
        $t = e($b['title'] ?? 'Siap Memulai?');
        $s = e($b['subtitle'] ?? $b['content'] ?? 'Jangan tunggu lagi. Daftar hari ini juga!');
        $btn = e($b['button_text'] ?? 'Daftar Sekarang');
        $url = e($b['button_url'] ?? '#');
        return <<<HTML
<section class="py-20 px-6 bg-gradient-to-r from-slate-900 via-indigo-900 to-slate-900 text-white">
  <div class="max-w-4xl mx-auto text-center">
    <h2 class="text-4xl sm:text-5xl font-black mb-4">{$t}</h2>
    <p class="text-xl text-indigo-200 mb-10 leading-relaxed">{$s}</p>
    <a href="{$url}" class="inline-block px-10 py-4 bg-gradient-to-r from-emerald-500 to-emerald-600 text-slate-900 font-black text-lg rounded-2xl shadow-2xl hover:scale-105 transition-transform">
      {$btn} →
    </a>
  </div>
</section>
HTML;
    }

    private function renderLeadForm(array $b): string
    {
        $t = e($b['title'] ?? 'Dapatkan Akses Eksklusif');
        $s = e($b['subtitle'] ?? $b['content'] ?? 'Isi form di bawah ini.');
        $btn = e($b['button_text'] ?? 'Kirim');
        $action = e($b['action'] ?? route('lead-capture.store'));
        return <<<HTML
<section class="py-20 px-6 bg-slate-50">
  <div class="max-w-xl mx-auto">
    <h2 class="text-3xl font-black text-center text-slate-900 mb-2">{$t}</h2>
    <p class="text-center text-slate-600 mb-8">{$s}</p>
    <form method="POST" action="{$action}" class="bg-white border border-slate-200 rounded-3xl p-8 shadow-xl space-y-4">
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <div>
        <label class="block text-sm font-bold text-slate-700 mb-1.5">Nama Lengkap</label>
        <input type="text" name="name" required class="w-full rounded-xl border-slate-300">
      </div>
      <div>
        <label class="block text-sm font-bold text-slate-700 mb-1.5">Email</label>
        <input type="email" name="email" required class="w-full rounded-xl border-slate-300">
      </div>
      <div>
        <label class="block text-sm font-bold text-slate-700 mb-1.5">No. WhatsApp</label>
        <input type="tel" name="phone" class="w-full rounded-xl border-slate-300">
      </div>
      <button type="submit" class="w-full py-4 bg-gradient-to-r from-indigo-600 to-emerald-600 text-white font-bold text-lg rounded-2xl shadow-lg hover:shadow-xl transition-shadow">
        {$btn}
      </button>
    </form>
  </div>
</section>
HTML;
    }

    private function renderCountdown(array $b): string
    {
        $t = e($b['title'] ?? '⏰ Penawaran Segera Berakhir');
        $end = e($b['end_at'] ?? '');
        return <<<HTML
<section class="py-12 px-6 bg-gradient-to-r from-rose-500 via-amber-500 to-rose-500 text-white">
  <div class="max-w-4xl mx-auto text-center">
    <h2 class="text-2xl sm:text-3xl font-black mb-6">{$t}</h2>
    <div class="grid grid-cols-4 gap-3 sm:gap-6 max-w-xl mx-auto" data-countdown="{$end}">
      <div class="bg-white/20 backdrop-blur rounded-2xl p-4"><p class="text-3xl sm:text-5xl font-black font-mono" data-days>00</p><p class="text-xs uppercase tracking-wide mt-1 opacity-80">Hari</p></div>
      <div class="bg-white/20 backdrop-blur rounded-2xl p-4"><p class="text-3xl sm:text-5xl font-black font-mono" data-hours>00</p><p class="text-xs uppercase tracking-wide mt-1 opacity-80">Jam</p></div>
      <div class="bg-white/20 backdrop-blur rounded-2xl p-4"><p class="text-3xl sm:text-5xl font-black font-mono" data-mins>00</p><p class="text-xs uppercase tracking-wide mt-1 opacity-80">Menit</p></div>
      <div class="bg-white/20 backdrop-blur rounded-2xl p-4"><p class="text-3xl sm:text-5xl font-black font-mono" data-secs>00</p><p class="text-xs uppercase tracking-wide mt-1 opacity-80">Detik</p></div>
    </div>
  </div>
</section>
HTML;
    }

    private function renderSocialProofBlock(array $b): string
    {
        $t = e($b['title'] ?? '🔥 Terbukti Banyak yang Beli');
        $purchases = $b['purchases'] ?? [
            ['name' => 'Budi S.', 'city' => 'Jakarta', 'product' => 'Paket Starter', 'ago' => '5 menit lalu'],
            ['name' => 'Siti R.', 'city' => 'Surabaya', 'product' => 'Paket Pro', 'ago' => '12 menit lalu'],
            ['name' => 'Andi P.', 'city' => 'Bandung', 'product' => 'Paket Starter', 'ago' => '27 menit lalu'],
        ];
        $html = '';
        foreach ($purchases as $p) {
            $n = e($p['name'] ?? 'Pengguna');
            $c = e($p['city'] ?? '');
            $pr = e($p['product'] ?? $p['item'] ?? '');
            $a = e($p['ago'] ?? $p['time_ago'] ?? '');
            $avatar = strtoupper($n[0] ?? 'U');
            $html .= <<<HTML
<div class="flex items-center gap-3 bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
  <div class="h-12 w-12 shrink-0 rounded-full bg-gradient-to-br from-indigo-500 to-emerald-500 flex items-center justify-center text-white font-bold">{$avatar}</div>
  <div class="min-w-0 flex-1">
    <p class="font-bold text-slate-900">{$n} <span class="text-xs text-slate-400 font-normal">{$c}</span></p>
    <p class="text-sm text-emerald-700 font-semibold">✓ Membeli {$pr}</p>
    <p class="text-xs text-slate-400">{$a}</p>
  </div>
</div>
HTML;
        }
        return <<<HTML
<section class="py-16 px-6 bg-white">
  <div class="max-w-2xl mx-auto">
    <h2 class="text-3xl font-black text-center text-slate-900 mb-8">{$t}</h2>
    <div class="space-y-3">{$html}</div>
  </div>
</section>
HTML;
    }

    private function renderBullets(array $b): string
    {
        $t = e($b['title'] ?? '');
        $items = $b['items'] ?? $b['bullets'] ?? [];
        $list = '';
        foreach ($items as $it) {
            $tx = e(is_array($it) ? ($it['text'] ?? json_encode($it)) : $it);
            $list .= <<<HTML
<li class="flex items-start gap-3">
  <span class="mt-1 h-6 w-6 shrink-0 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">✓</span>
  <span class="text-lg text-slate-700">{$tx}</span>
</li>
HTML;
        }
        $bulletsTitleHtml = $t ? "<h2 class=\"text-3xl font-black text-slate-900 mb-8\">{$t}</h2>" : '';
        return <<<HTML
<section class="py-16 px-6 bg-slate-50">
  <div class="max-w-3xl mx-auto">
    {$bulletsTitleHtml}
    <ul class="space-y-4">{$list}</ul>
  </div>
</section>
HTML;
    }

    private function renderFooter(array $b): string
    {
        $text = e($b['copyright'] ?? $b['text'] ?? '© STIFLOW — All rights reserved.');
        return <<<HTML
<footer class="py-10 px-6 bg-slate-900 text-slate-300">
  <div class="max-w-6xl mx-auto text-center">
    <p class="mb-4 text-sm">{$text}</p>
    <div class="flex justify-center gap-6 text-sm">
      <a href="#" class="hover:text-white">Kebijakan Privasi</a>
      <a href="#" class="hover:text-white">Syarat & Ketentuan</a>
      <a href="#" class="hover:text-white">Kontak</a>
    </div>
  </div>
</footer>
HTML;
    }
}
