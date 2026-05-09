<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\Company;

class CheckSubscriptionExpiry extends Command
{
    protected $signature = 'subscription:check-expiry';
    protected $description = 'Check for expiring or expired subscriptions and update company status';

    public function handle()
    {
        $this->info('Checking subscriptions...');

        $expiredSubscriptions = Subscription::active()->where('expires_at', '<=', now())->get();

        foreach ($expiredSubscriptions as $sub) {
            $sub->update(['status' => 'expired']);
            $sub->company->update(['status' => 'expired']);
            $this->info("Subscription #{$sub->id} expired for company {$sub->company->name}");
            
            // TODO: Send expiry email to company admin
        }

        $expiringSubscriptions = Subscription::active()
            ->where('expires_at', '<=', now()->addDays(7))
            ->where('renewal_reminder_sent', false)
            ->get();

        foreach ($expiringSubscriptions as $sub) {
            $sub->update(['renewal_reminder_sent' => true]);
            $this->info("Renewal reminder needed for subscription #{$sub->id} - Company: {$sub->company->name}");
            
            // TODO: Send renewal email to company admin
        }

        $this->info('Subscription check completed.');
    }
}
