<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class XenditService
{
    protected string $secretKey;
    protected string $webhookToken;
    protected string $baseUrl = 'https://api.xendit.co/v2';

    public function __construct()
    {
        $this->secretKey = env('XENDIT_SECRET_KEY', '');
        $this->webhookToken = env('XENDIT_WEBHOOK_VERIFICATION_TOKEN', '');
    }

    protected function isConfigured(): bool
    {
        return !empty($this->secretKey);
    }

    /**
     * Create an invoice link for payment.
     */
    public function createInvoice(Order $order): string
    {
        /*
        if ($this->isConfigured()) {
            try {
                $response = Http::withBasicAuth($this->secretKey, '')
                    ->post($this->baseUrl . '/invoices', [
                        'external_id' => $order->order_reference,
                        'amount' => $order->grand_total,
                        'description' => 'Pembayaran Order ' . $order->order_reference . ' di Elvara E-Commerce',
                        'customer' => [
                            'given_names' => $order->user->name,
                            'email' => $order->user->email,
                        ],
                        'success_redirect_url' => route('customer.order.tracking', $order->order_reference),
                        'failure_redirect_url' => route('customer.order.tracking', $order->order_reference),
                    ]);

                if ($response->successful()) {
                    return $response->json()['invoice_url'] ?? '';
                }
                Log::error('Xendit createInvoice error: ' . $response->body());
            } catch (Exception $e) {
                Log::error('Xendit createInvoice exception: ' . $e->getMessage());
            }
        }
        */

        // Fallback / Mock Mode: redirect to a local simulation route
        return route('mock.xendit.payment', ['reference' => $order->order_reference]);
    }

    /**
     * Verify the webhook authenticity.
     */
    public function verifyWebhook(string $incomingToken): bool
    {
        if (empty($this->webhookToken)) {
            return true; // If not set in local, auto approve
        }

        return $incomingToken === $this->webhookToken;
    }
}
