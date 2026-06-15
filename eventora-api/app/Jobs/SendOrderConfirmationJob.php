<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\Order;
use App\Mail\OrderConfirmationMail;

class SendOrderConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $organization = $this->order->event->organization;

        if ($organization->smtp_host) {
            $mailer = Mail::build([
                'transport' => 'smtp',
                'host' => $organization->smtp_host,
                'port' => $organization->smtp_port,
                'encryption' => 'tls',
                'username' => $organization->smtp_username,
                'password' => $organization->smtp_password,
                'timeout' => null,
            ]);

            // Set From address if available
            $fromAddress = $organization->smtp_from_email ?? config('mail.from.address');
            $fromName = $organization->smtp_from_name ?? config('mail.from.name');

            $mailer->alwaysFrom($fromAddress, $fromName);

            $mailer->to($this->order->buyer_email)->send(new OrderConfirmationMail($this->order));
        } else {
            // Use default mailer
            Mail::to($this->order->buyer_email)->send(new OrderConfirmationMail($this->order));
        }
    }
}
