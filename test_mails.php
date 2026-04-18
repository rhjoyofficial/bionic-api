<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Mail;

$email = 'professionalbusinessmanager49@gmail.com';

echo "1. Sending Welcome Email to {$email}...\n";
$user = new User;
$user->id = 9999;
$user->name = 'Joy';
$user->email = $email;

$coupon = \App\Domains\Coupon\Models\Coupon::make([
    'code' => 'WELCOME10',
    'type' => 'percentage',
    'value' => 10,
    'end_date' => now()->addDays(30),
]);

try {
    Mail::mailer('smtp')->to($email)->send(new \App\Mail\WelcomeMail($user, $coupon));
    echo "   -> Welcome HTML mail sent successfully!\n\n";
} catch (\Exception $e) {
    echo '   -> Error sending welcome mail: '.$e->getMessage()."\n\n";
}
