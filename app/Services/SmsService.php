<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http; // Re-introduced Laravel's HTTP Client

class SmsService
{
    private $url;
    private $token;
    private $to;
    private $from;
    private $sender;
    private $message;
    private $callback;
    /**
     * Send OTP via Afromessage API using Laravel's HTTP Client (POST method).
     */
    public function sendViaApi(string $phone ,string $message): void
    {
        // Load these from .env
        $this->url = env('AFRO_MESSAGE_URL');
        $this->token = env('AFRO_MESSAGE_TOKEN');

        // Prepend '251' to the phone number if it doesn't already start with it or '+251'
        if (!preg_match('/^(251|\+251)/', $phone)) {
            $this->to = '251' . ltrim($phone, '0'); // Remove leading zero if present and add 251
        } else {
            $this->to = $phone;
        }

        // Set 'from' and 'sender' to null if their env variables are not set,
        // allowing the API to use defaults for beta testers.
        $this->from = env('AFRO_MESSAGE_FROM', null);
        $this->sender = env('AFRO_MESSAGE_SENDER', null);
        $this->message = $message;
        $this->callback = env('AFRO_MESSAGE_CALLBACK', null);

        // Prepare the request body as an array
        $requestBody = [
            "from" => $this->from,
            "sender" => $this->sender,
            "to" => $this->to,
            "message" => $this->message,
            "callback" => $this->callback
        ];

        // Log the exact JSON payload being sent for debugging
        Log::debug("Sending Afromessage API Request - URL: {$this->url}, Payload: " . json_encode($requestBody));

        try {
            // Make the POST request using Laravel's HTTP client
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ])
                ->withoutVerifying() // Disable SSL verification for development (NOT recommended for production)
                ->post($this->url, $requestBody);
                
                // dd($response);
            // Handle the API response
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['acknowledge']) && $data['acknowledge'] == 'success') {
                    Log::info("Sent successfully to {$this->to}. API Acknowledge: success. Response: " . $response->body());
                } else {
                    Log::error("Failed to send to {$this->to}. API Acknowledge: failure. Response: " . $response->body());
                }
            } else {
                Log::error("Failed to send to {$this->to}. HTTP Code: {$response->status()}. Response: " . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error("HTTP Client Error while sending OTP to {$this->to}: " . $e->getMessage());
        }
    }
}
