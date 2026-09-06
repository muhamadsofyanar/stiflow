<?php
require __DIR__."/vendor/autoload.php";
$app = require __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo "===USER ROLE PROMOTOR===\n";
echo "COUNT=".App\Models\User::where("role", "promotor")->count()."\n";
echo "\n===PROMOTER PROFILES LIST===\n";
$all = App\Models\PromoterProfile::with(["user","sponsor"])->orderBy("level_depth")->orderBy("stifin_code")->get();
echo "TOTAL PROFILES=".$all->count()."\n";
foreach($all as $p){
  $sponsor = $p->sponsor ? $p->sponsor->stifin_code : "ROOT";
  $mail = $p->user ? $p->user->email : "NO_USER";
  echo sprintf("%-12s | L=%d | Sponsor=%-12s | %-35s | %s\n",$p->stifin_code,(int)$p->level_depth,$sponsor,$mail,$p->verification_status);
}
echo "\nDONE";
unlink(__FILE__);
