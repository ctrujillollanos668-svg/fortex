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
            . "⚡ <i>Revisa el comprobante y apruébalo desde el panel de administración.</i>";

        try {
            // Si hay archivo de comprobante válido en el disco, enviarlo como foto con el caption
            if ($proofAbsolutePath && file_exists($proofAbsolutePath) && is_readable($proofAbsolutePath)) {
                $fileContent = file_get_contents($proofAbsolutePath);
                $fileName = basename($proofAbsolutePath);

                $response = Http::timeout(12)
                    ->attach('photo', $fileContent, $fileName)
                    ->post("https://api.telegram.org/bot{$token}/sendPhoto", [
                        'chat_id' => $chatId,
                        'caption' => $caption,
                        'parse_mode' => 'HTML',
                    ]);

                if ($response->successful()) {
                    return true;
                }
            }

            // Si no hay foto o falló el envío de foto, enviar como mensaje de texto
            return self::sendMessage($caption);
        } catch (\Throwable $e) {
            Log::error('TelegramService notifyNewDeposit Error: ' . $e->getMessage());
            // Fallback de texto seguro
            return self::sendMessage($caption);
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
