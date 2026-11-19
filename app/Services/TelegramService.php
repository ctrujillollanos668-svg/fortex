<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Obtener el Token del Bot
     */
    protected static function getBotToken(): ?string
    {
        return config('services.telegram.bot_token') ?: env('TELEGRAM_BOT_TOKEN');
    }

    /**
     * Obtener el Chat ID del Administrador
     */
    protected static function getChatId(): ?string
    {
        return config('services.telegram.chat_id') ?: env('TELEGRAM_CHAT_ID');
    }

    /**
     * Enviar mensaje de texto simple a Telegram
     */
    public static function sendMessage(string $text): bool
    {
        $token = self::getBotToken();
        $chatId = self::getChatId();

        if (empty($token) || empty($chatId)) {
            Log::warning('TelegramService: Falta configurar TELEGRAM_BOT_TOKEN o TELEGRAM_CHAT_ID');
            return false;
        }

        try {
            $response = Http::timeout(6)->asForm()->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => 'false',
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('TelegramService sendMessage Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notificar una nueva recarga / depósito reportado con comprobante
     */
    public static function notifyNewDeposit($deposit, $user, ?string $proofAbsolutePath = null): bool
    {
        $token = self::getBotToken();
        $chatId = self::getChatId();

        if (empty($token) || empty($chatId)) {
            return false;
        }

        $amountFormatted = number_format($deposit->amount, 0, ',', '.');
        $appName = config('app.name', 'FORTEX');
        
        $methodFormatted = strtoupper($deposit->payment_method);
        $phoneFormatted = $user->phone ? $user->phone : 'Sin teléfono';
        $emailFormatted = $user->email;
        $hashFormatted = $deposit->transaction_hash;
        $dateFormatted = now()->setTimezone('America/Bogota')->format('d/m/Y - h:i A');

        $caption = "🚨 <b>¡NUEVA RECARGA REGISTRADA!</b> 🚨\n\n"
            . "👤 <b>Cliente:</b> {$user->name}\n"
            . "📱 <b>Teléfono:</b> <code>{$phoneFormatted}</code>\n"
            . "📧 <b>Email:</b> {$emailFormatted}\n"
            . "💰 <b>Monto:</b> <b>\${$amountFormatted} COP</b>\n"
            . "💳 <b>Método:</b> {$methodFormatted}\n"
            . "🧾 <b>Referencia/Hash:</b> <code>{$hashFormatted}</code>\n"
            . "📅 <b>Fecha:</b> {$dateFormatted}\n\n"
            . "⚡ <i>Toca un botón abajo para aprobar o rechazar inmediatamente:</i>";

        $keyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => "✅ Aprobar \${$amountFormatted} COP", 'callback_data' => "approve:{$deposit->id}:{$deposit->amount}"],
                    ['text' => "❌ Rechazar", 'callback_data' => "reject:{$deposit->id}"],
                ],
                [
                    ['text' => "🌐 Ver en Panel Web", 'url' => "https://fortex.wasmer.app/admin/deposits"]
                ]
            ]
        ]);

        try {
            // Si hay archivo de comprobante válido en el disco, enviarlo como foto con el caption y botones
            if ($proofAbsolutePath && file_exists($proofAbsolutePath) && is_readable($proofAbsolutePath)) {
                $fileContent = file_get_contents($proofAbsolutePath);
                $fileName = basename($proofAbsolutePath);

                $response = Http::timeout(12)
                    ->attach('photo', $fileContent, $fileName)
                    ->post("https://api.telegram.org/bot{$token}/sendPhoto", [
                        'chat_id' => $chatId,
                        'caption' => $caption,
                        'parse_mode' => 'HTML',
                        'reply_markup' => $keyboard,
                    ]);

                if ($response->successful()) {
                    return true;
                }
            }

            // Fallback: enviar como texto con botones
            $response = Http::timeout(8)->asForm()->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $caption,
                'parse_mode' => 'HTML',
                'reply_markup' => $keyboard,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('TelegramService notifyNewDeposit Error: ' . $e->getMessage());
            return self::sendMessage($caption);
        }
    }

    /**
     * Notificar comprobante recibido por WhatsApp con selección de montos rápidos
     */
    public static function notifyWhatsAppReceipt($deposit, $user, ?string $proofAbsolutePath = null, ?float $suggestedAmount = null): bool
    {
        $token = self::getBotToken();
        $chatId = self::getChatId();

        if (empty($token) || empty($chatId)) {
            return false;
        }

        $appName = config('app.name', 'FORTEX');
        $phoneFormatted = $user->phone ? $user->phone : 'Sin teléfono';
        $emailFormatted = $user->email;
        $dateFormatted = now()->setTimezone('America/Bogota')->format('d/m/Y - h:i A');

        $caption = "📱 <b>¡COMPROBANTE RECIBIDO POR WHATSAPP!</b> 📱\n\n"
            . "👤 <b>Cliente:</b> {$user->name}\n"
            . "📱 <b>WhatsApp:</b> <code>{$phoneFormatted}</code>\n"
            . "📧 <b>Email:</b> {$emailFormatted}\n"
            . "📅 <b>Fecha:</b> {$dateFormatted}\n\n"
            . "⚡ <i>Revisa la imagen y toca el monto exacto para acreditar al cliente:</i>";

        $keyboardButtons = [];

        // Si hay monto sugerido detectado, colocarlo primero
        if ($suggestedAmount && $suggestedAmount >= 10000) {
            $sugFmt = number_format($suggestedAmount, 0, ',', '.');
            $keyboardButtons[] = [
                ['text' => "🌟 Aprobar \${$sugFmt} COP", 'callback_data' => "approve:{$deposit->id}:{$suggestedAmount}"]
            ];
        }

        $keyboardButtons[] = [
            ['text' => '✅ $10.000', 'callback_data' => "approve:{$deposit->id}:10000"],
            ['text' => '✅ $20.000', 'callback_data' => "approve:{$deposit->id}:20000"],
            ['text' => '✅ $30.000', 'callback_data' => "approve:{$deposit->id}:30000"],
        ];
        $keyboardButtons[] = [
            ['text' => '✅ $50.000', 'callback_data' => "approve:{$deposit->id}:50000"],
            ['text' => '✅ $100.000', 'callback_data' => "approve:{$deposit->id}:100000"],
            ['text' => '✅ $200.000', 'callback_data' => "approve:{$deposit->id}:200000"],
        ];
        $keyboardButtons[] = [
            ['text' => '❌ Rechazar Comprobante', 'callback_data' => "reject:{$deposit->id}"]
        ];

        $keyboard = json_encode(['inline_keyboard' => $keyboardButtons]);

        try {
            if ($proofAbsolutePath && file_exists($proofAbsolutePath) && is_readable($proofAbsolutePath)) {
                $fileContent = file_get_contents($proofAbsolutePath);
                $fileName = basename($proofAbsolutePath);

                $response = Http::timeout(15)
                    ->attach('photo', $fileContent, $fileName)
                    ->post("https://api.telegram.org/bot{$token}/sendPhoto", [
                        'chat_id' => $chatId,
                        'caption' => $caption,
                        'parse_mode' => 'HTML',
                        'reply_markup' => $keyboard,
                    ]);

                if ($response->successful()) {
                    return true;
                }
            }

            return self::sendMessage($caption);
        } catch (\Throwable $e) {
            Log::error('TelegramService notifyWhatsAppReceipt Error: ' . $e->getMessage());
            return self::sendMessage($caption);
        }
    }

    /**
     * Responder a un clic de botón en Telegram (elimina el icono de reloj)
     */
    public static function answerCallbackQuery(string $callbackQueryId, ?string $text = null, bool $showAlert = false): bool
    {
        $token = self::getBotToken();
        try {
            $response = Http::timeout(5)->asForm()->post("https://api.telegram.org/bot{$token}/answerCallbackQuery", [
                'callback_query_id' => $callbackQueryId,
                'text' => $text,
                'show_alert' => $showAlert,
            ]);
            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Editar el pie de foto de un mensaje en Telegram
     */
    public static function editMessageCaption(string|int $chatId, int $messageId, string $newCaption, ?array $replyMarkup = null): bool
    {
        $token = self::getBotToken();
        try {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'caption' => $newCaption,
                'parse_mode' => 'HTML',
            ];
            if ($replyMarkup !== null) {
                $data['reply_markup'] = json_encode($replyMarkup);
            }
            $response = Http::timeout(6)->asForm()->post("https://api.telegram.org/bot{$token}/editMessageCaption", $data);
            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Enviar mensaje automático al WhatsApp del cliente vía WAHA
     */
    public static function sendWhatsAppMessage(string $phone, string $text): bool
    {
        $wahaUrl = env('WAHA_API_URL', 'https://waha-bot-yo25.onrender.com');
        $wahaKey = env('WAHA_API_KEY', '34ec073c9c6e45738171e5fbd14a7498');

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (!str_starts_with($cleanPhone, '57') && strlen($cleanPhone) === 10) {
            $cleanPhone = '57' . $cleanPhone;
        }
        $chatId = $cleanPhone . '@c.us';

        try {
            $response = Http::timeout(15)->withHeaders([
                'X-Api-Key' => $wahaKey,
                'Content-Type' => 'application/json',
            ])->post("{$wahaUrl}/api/sendText", [
                'session' => 'default',
                'chatId' => $chatId,
                'text' => $text,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Error enviando WhatsApp vía WAHA: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notificar una nueva solicitud de retiro
     */
    public static function notifyNewWithdrawal($withdrawal, $user): bool
    {
        $amountFormatted = number_format($withdrawal->amount, 0, ',', '.');
        $netFormatted = number_format($withdrawal->net_amount, 0, ',', '.');
        $feeFormatted = number_format($withdrawal->fee, 0, ',', '.');
        $dateFormatted = now()->setTimezone('America/Bogota')->format('d/m/Y - h:i A');
        $phoneFormatted = $user->phone ? $user->phone : 'Sin teléfono';

        $text = "📤 <b>¡NUEVA SOLICITUD DE RETIRO!</b> 📤\n\n"
            . "👤 <b>Cliente:</b> {$user->name}\n"
            . "📱 <b>Teléfono:</b> <code>{$phoneFormatted}</code>\n"
            . "💰 <b>Monto Solicitado:</b> <b>\${$amountFormatted} COP</b>\n"
            . "💸 <b>Neto a Transferir:</b> <b>\${$netFormatted} COP</b> (Comisión: \${$feeFormatted})\n"
            . "🏦 <b>Destino:</b> <code>{$withdrawal->wallet_or_account}</code>\n"
            . "📅 <b>Fecha:</b> {$dateFormatted}\n\n"
            . "⚡ <i>Entra al panel de administración para procesar o transferir este retiro.</i>";

        return self::sendMessage($text);
    }
}
