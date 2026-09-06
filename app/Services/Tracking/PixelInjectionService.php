<?php

namespace App\Services\Tracking;

use App\Models\EventTrackingPixel;
use Illuminate\Support\Facades\Cache;

class PixelInjectionService
{
    private const CACHE_KEY = 'stiflow.pixels.active';

    public function getActivePixels()
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return EventTrackingPixel::query()
                ->active()
                ->get(['id', 'provider', 'pixel_id', 'script_head', 'script_body']);
        });
    }

    public function renderHeadScripts(): string
    {
        $pixels = $this->getActivePixels();
        if ($pixels->isEmpty()) {
            return '';
        }

        $html = '';
        foreach ($pixels as $pixel) {
            if (! empty($pixel->script_head)) {
                $html .= "<!-- {$pixel->provider->value}: {$pixel->pixel_id} -->\n";
                $html .= $pixel->script_head."\n";

                continue;
            }

            $html .= $this->defaultHeadScript($pixel);
        }

        return $html;
    }

    public function renderBodyScripts(): string
    {
        $pixels = $this->getActivePixels();
        if ($pixels->isEmpty()) {
            return '';
        }

        $html = '';
        foreach ($pixels as $pixel) {
            if (! empty($pixel->script_body)) {
                $html .= "<!-- {$pixel->provider->value} body: {$pixel->pixel_id} -->\n";
                $html .= $pixel->script_body."\n";

                continue;
            }

            $html .= $this->defaultBodyScript($pixel);
        }

        return $html;
    }

    private function defaultHeadScript($pixel): string
    {
        $pid = e($pixel->pixel_id);
        switch ($pixel->provider->value) {
            case 'MetaPixel':
                return <<<HTML
<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{$pid}');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id={$pid}&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->
HTML;

            case 'GoogleAnalytics4':
                return <<<HTML
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$pid}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{$pid}');
</script>
HTML;

            case 'TikTokPixel':
                return <<<HTML
<!-- TikTok Pixel -->
<script>
!function (w, d, t) {
  w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","setMeta"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};
  for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e};ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i={},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{sandbox:"SANDBOX"in w?w.SANDBOX:!!n};var r=d.createElement("script");r.type="text/javascript",r.async=!0,r.src=i+"?sdkid="+e+"&lib="+t;var a=d.getElementsByTagName("script")[0];a.parentNode.insertBefore(r,a)};
  ttq.load('{$pid}');
  ttq.page();
}(window, document, 'ttq');
</script>
HTML;

            case 'LinkedInInsight':
                return <<<HTML
<!-- LinkedIn Insight Tag -->
<script type="text/javascript">
_linkedin_partner_id = "{$pid}";
window._linkedin_data_partner_ids = window._linkedin_data_partner_ids || [];
window._linkedin_data_partner_ids.push(_linkedin_partner_id);
</script><script type="text/javascript">
(function(l) {
if (!l){window.lintrk = function(a,b){window.lintrk.q.push([a,b])};
window.lintrk.q=[]}
var s = document.getElementsByTagName("script")[0];
var b = document.createElement("script");
b.type = "text/javascript";b.async = true;
b.src = "https://snap.licdn.com/li.lms-analytics/insight.min.js";
s.parentNode.insertBefore(b, s);})(window.lintrk);
</script>
<noscript>
<img height="1" width="1" style="display:none;" alt="" src="https://px.ads.linkedin.com/collect/?pid={$pid}&fmt=gif" />
</noscript>
HTML;

            default:
                return "<!-- pixel: {$pid} -->\n";
        }
    }

    private function defaultBodyScript($pixel): string
    {
        return '';
    }
}
