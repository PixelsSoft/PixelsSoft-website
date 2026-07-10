<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\GoogleIntegration;
use App\Services\CrmLeadFromContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
            'recaptcha_token' => 'nullable|string',
        ]);

        $recaptcha = GoogleIntegration::where('service_key', 'recaptcha')->first();
        if ($recaptcha?->enabled) {
            $secret = $recaptcha->config['secret_key'] ?? null;
            if ($secret) {
                $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secret,
                    'response' => $validated['recaptcha_token'] ?? '',
                ]);

                if (!$response->json('success')) {
                    return response()->json(['message' => 'reCAPTCHA verification failed.'], 422);
                }
            }
        }

        $message = ContactMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'] ?? 'Contact Form',
            'message' => $validated['message'],
        ]);

        try {
            app(CrmLeadFromContactService::class)->createFromMessage($message);
        } catch (\Throwable) {
            // CRM lead creation is best-effort; inbox message is still saved.
        }

        return response()->json(['message' => 'Message sent successfully.']);
    }
}
