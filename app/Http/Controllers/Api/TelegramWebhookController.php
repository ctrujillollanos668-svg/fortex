<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    /**
     * Webhook de Telegram para procesar clics en los botones interactivos
     */
    /**
     * Webhook de Telegram para procesar clics en los botones interactivos o mensajes de texto con montos
     */
    public function handleTelegramWebhook(Request $request)
    {
        try {
            $data = $request->all();
            $authorizedChatId = env('TELEGRAM_CHAT_ID', '6992027483');

            // ============================================================
            // CASO 1: MENSAJE DE TEXTO DIRECTO (ej: "25000", "/aprobar 25000", "/rechazar")
            // ============================================================
            if (isset($data['message']['text'])) {
                $textMsg = trim((string) $data['message']['text']);
                $fromId = $data['message']['from']['id'] ?? null;
                $chatId = $data['message']['chat']['id'] ?? null;

                if ((string) $fromId !== (string) $authorizedChatId && (string) $chatId !== (string) $authorizedChatId) {
                    return response()->json(['status' => 'unauthorized'], 403);
                }

                // 1. Verificar si el usuario respondió (reply_to_message) a una foto específica
                $reply = $data['message']['reply_to_message'] ?? null;
                $targetDeposit = null;
                $targetMsgId = null;

                if ($reply) {
                    $targetMsgId = $reply['message_id'] ?? null;
                    $replyText = ($reply['caption'] ?? '') . ' ' . ($reply['text'] ?? '');
                    if (preg_match('/#DEP-(\d+)/', $replyText, $m)) {
                        $targetDeposit = Deposit::with('user')->find((int) $m[1]);
                    }
                    if (!$targetDeposit && $targetMsgId) {
                        $targetDeposit = Deposit::with('user')->where('admin_notes', 'like', "%[TG:{$targetMsgId}]%")->first();
                    }
                }

                // 2. Si no respondió a un mensaje específico, tomar el último pendiente
                if (!$targetDeposit) {
                    $targetDeposit = Deposit::with('user')->where('status', 'pending')->latest()->first();
                }

                if (!$targetDeposit) {
                    TelegramService::sendMessage("ℹ️ No hay ningún comprobante pendiente por procesar en este momento.");
                    return response()->json(['status' => 'no_pending']);
                }

                if ($targetDeposit->status !== 'pending') {
                    TelegramService::sendMessage("ℹ️ El comprobante #DEP-{$targetDeposit->id} de {$targetDeposit->user->name} ya fue procesado ({$targetDeposit->status}).");
                    return response()->json(['status' => 'already_processed']);
                }

                // Si es comando /rechazar
                if (strtolower($textMsg) === '/rechazar' || strtolower($textMsg) === 'rechazar') {
                    return $this->executeRejection($targetDeposit, $targetDeposit->user, $chatId, $targetMsgId, null);
                }

                // Detectar si el mensaje contiene un monto numérico ej: "18000", "/aprobar 18000", "$18.000"
                $cleanNum = preg_replace('/[^0-9]/', '', $textMsg);
                if (is_numeric($cleanNum) && (int) $cleanNum >= 1000) {
                    $customAmount = (float) $cleanNum;
                    return $this->executeApproval($targetDeposit, $targetDeposit->user, $customAmount, $chatId, $targetMsgId, null);
                }

                if ($textMsg === '/start' || $textMsg === '/ayuda') {
                    TelegramService::sendMessage(
                        "👋 <b>Panel de Control Fortex en Telegram</b>\n\n"
                        . "• Cuando recibas un comprobante, puedes tocar los botones de montos rápidos.\n"
                        . "• O escribir cualquier número directamente (ej: <code>25000</code> o <code>/aprobar 25000</code>) para aprobar esa cantidad exacta al instante.\n"
                        . "• Escribe <code>/rechazar</code> para rechazar el comprobante pendiente."
                    );
                    return response()->json(['status' => 'help_sent']);
                }

                return response()->json(['status' => 'ignored']);
            }

            // ============================================================
            // CASO 2: CLIC EN BOTÓN INTERACTIVO (callback_query)
            // ============================================================
            if (!isset($data['callback_query'])) {
                return response()->json(['status' => 'ignored']);
            }

            $callbackQuery = $data['callback_query'];
            $callbackId = $callbackQuery['id'];
            $callbackData = $callbackQuery['data'] ?? '';
            $fromId = $callbackQuery['from']['id'] ?? null;
            $message = $callbackQuery['message'] ?? [];
            $messageId = $message['message_id'] ?? null;
            $chatId = $message['chat']['id'] ?? null;

            // Seguridad: Solo el Administrador autorizado puede aprobar/rechazar
            if ((string) $fromId !== (string) $authorizedChatId && (string) $chatId !== (string) $authorizedChatId) {
                TelegramService::answerCallbackQuery($callbackId, '⛔ No tienes permisos para realizar esta acción.', true);
                return response()->json(['status' => 'unauthorized'], 403);
            }

            // Parsear datos del callback: ej: "approve:15:50000" o "reject:15"
            $parts = explode(':', $callbackData);
            $action = $parts[0] ?? '';
            $depositId = (int) ($parts[1] ?? 0);
            $amount = isset($parts[2]) ? (float) $parts[2] : null;

            $deposit = Deposit::with('user')->find($depositId);
            if (!$deposit) {
                TelegramService::answerCallbackQuery($callbackId, '⚠️ Depósito no encontrado.', true);
                return response()->json(['status' => 'not_found']);
            }

            if ($deposit->status !== 'pending') {
                TelegramService::answerCallbackQuery($callbackId, "ℹ️ Este comprobante ya fue procesado ({$deposit->status}).", true);
                return response()->json(['status' => 'already_processed']);
            }

            $user = $deposit->user;
            if (!$user) {
                TelegramService::answerCallbackQuery($callbackId, '⚠️ Usuario asociado no existe.', true);
                return response()->json(['status' => 'no_user']);
            }

            if ($action === 'approve') {
                $finalAmount = ($amount && $amount > 0) ? $amount : (float) $deposit->amount;
                if ($finalAmount <= 0) {
                    TelegramService::answerCallbackQuery($callbackId, '⚠️ Especifica un monto válido mayor a 0.', true);
                    return response()->json(['status' => 'invalid_amount']);
                }
                return $this->executeApproval($deposit, $user, $finalAmount, $chatId, $messageId, $callbackId);
            }

            if ($action === 'reject') {
                return $this->executeRejection($deposit, $user, $chatId, $messageId, $callbackId);
            }

            return response()->json(['status' => 'unknown_action']);

        } catch (\Throwable $e) {
            Log::error('TelegramWebhookController Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Lógica unificada para procesar y acreditar una recarga aprobada
     */
    protected function executeApproval($deposit, $user, float $finalAmount, $chatId, $messageId = null, $callbackId = null)
    {
        DB::transaction(function () use ($deposit, $user, $finalAmount) {
            // 1. Actualizar depósito
            $deposit->update([
                'amount' => $finalAmount,
                'status' => 'approved',
                'admin_notes' => 'Aprobado con éxito vía Telegram por el Administrador el ' . now()->format('d/m/Y H:i'),
            ]);

            // 2. Sumar saldo al usuario
            $user->balance += $finalAmount;
            $user->save();

            // 3. Registrar transacción
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $finalAmount,
                'balance_after' => $user->balance,
                'description' => 'Recarga de saldo aprobada vía Telegram ($' . number_format($finalAmount, 0, ',', '.') . ' COP)',
            ]);

            // 4. Premiar con +3 giros al cliente
            $user->increment('roulette_spins', 3);
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit_spins',
                'amount' => 0,
                'balance_after' => $user->balance,
                'description' => '🎁 Recompensa por Recarga: +3 Giros de Ruleta VIP',
            ]);

            // 5. Premiar con +2 giros al patrocinador si tiene
            if ($user->sponsor) {
                $user->sponsor->increment('roulette_spins', 2);
                Transaction::create([
                    'user_id' => $user->sponsor->id,
                    'type' => 'referral_spins',
                    'amount' => 0,
                    'balance_after' => $user->sponsor->balance,
                    'description' => '🎁 Recompensa: +2 Giros de Ruleta por recarga de tu invitado (' . $user->name . ')',
                ]);
            }
        });

        $amountFormatted = number_format($finalAmount, 0, ',', '.');

        if ($callbackId) {
            TelegramService::answerCallbackQuery($callbackId, "✅ ¡Recarga de \${$amountFormatted} COP aprobada exitosamente!");
        }

        $updatedCaption = "✅ <b>¡RECARGA APROBADA EXITOSAMENTE!</b>\n\n"
            . "👤 <b>Cliente:</b> {$user->name}\n"
            . "📱 <b>Teléfono:</b> <code>" . ($user->phone ?: 'Sin teléfono') . "</code>\n"
            . "💰 <b>Monto Acreditado:</b> <b>\${$amountFormatted} COP</b>\n"
            . "🎰 <b>Giros Otorgados:</b> +3 Giros VIP\n"
            . "🕒 <b>Aprobado el:</b> " . now()->setTimezone('America/Bogota')->format('d/m/Y - h:i A') . "\n\n"
            . "🎉 <i>El cliente ha sido notificado automáticamente por WhatsApp.</i>";

        if ($messageId) {
            TelegramService::editMessageCaption($chatId, $messageId, $updatedCaption, [
                'inline_keyboard' => [
                    [['text' => '🌐 Ver en Panel de Control', 'url' => 'https://fortex.wasmer.app/admin/deposits']]
                ]
            ]);
        } else {
            TelegramService::sendMessage($updatedCaption);
        }

        // Notificar al cliente por WhatsApp si tiene teléfono
        if ($user->phone) {
            $waMessage = "🎉 *¡Hola {$user->name}! Buenas noticias:*\n\n"
                . "Tu recarga de *$" . $amountFormatted . " COP* ha sido verificada y aprobada exitosamente. ✅\n\n"
                . "💵 Tu nuevo saldo ya se encuentra disponible en tu cuenta:\n"
                . "👉 https://fortex.wasmer.app/deposit\n\n"
                . "🎟️ *¡Además recibiste +3 Giros Extras para la Ruleta VIP!* Gira ahora y gana bonos en efectivo:\n"
                . "👉 https://fortex.wasmer.app/dashboard\n\n"
                . "¡Gracias por confiar en Fortex! Si tienes alguna duda, aquí sigo atenta para ayudarte. 😊";

            TelegramService::sendWhatsAppMessage($user->phone, $waMessage);
        }

        return response()->json(['status' => 'approved', 'amount' => $finalAmount]);
    }

    /**
     * Lógica unificada para procesar el rechazo de un comprobante
     */
    protected function executeRejection($deposit, $user, $chatId, $messageId = null, $callbackId = null)
    {
        $deposit->update([
            'status' => 'rejected',
            'admin_notes' => 'Comprobante rechazado vía Telegram por el Administrador el ' . now()->format('d/m/Y H:i'),
        ]);

        if ($callbackId) {
            TelegramService::answerCallbackQuery($callbackId, '❌ Comprobante rechazado.');
        }

        $updatedCaption = "❌ <b>COMPROBANTE RECHAZADO</b>\n\n"
            . "👤 <b>Cliente:</b> {$user->name}\n"
            . "📱 <b>Teléfono:</b> <code>" . ($user->phone ?: 'Sin teléfono') . "</code>\n"
            . "🕒 <b>Rechazado el:</b> " . now()->setTimezone('America/Bogota')->format('d/m/Y - h:i A') . "\n\n"
            . "⚠️ <i>El cliente ha sido informado por WhatsApp para que verifique su pago.</i>";

        if ($messageId) {
            TelegramService::editMessageCaption($chatId, $messageId, $updatedCaption, [
                'inline_keyboard' => [
                    [['text' => '🌐 Ver en Panel de Control', 'url' => 'https://fortex.wasmer.app/admin/deposits']]
                ]
            ]);
        } else {
            TelegramService::sendMessage($updatedCaption);
        }

        if ($user->phone) {
            $waRejectMsg = "Hola {$user->name}, verificamos el comprobante de pago enviado pero no encontramos la transferencia recibida en nuestra cuenta bancaria o los datos no coinciden. ⚠️\n\n"
                . "Por favor verifica desde tu app bancaria que el dinero haya sido debitado y comunícate con nosotros por este medio para ayudarte a validar tu pago con gusto.";

            TelegramService::sendWhatsAppMessage($user->phone, $waRejectMsg);
        }

        return response()->json(['status' => 'rejected']);
    }

    /**
     * Endpoint invocado por el bot de WhatsApp (Python) cuando el cliente envía un comprobante
     */
    public function reportDepositFromBot(Request $request)
    {
        try {
            $phone = trim((string) $request->input('client_phone', ''));
            $imageB64 = $request->input('image_b64', '');
            $suggestedAmount = (float) $request->input('suggested_amount', 0);
            $userEmail = trim((string) $request->input('email', ''));

            if (empty($phone) && empty($userEmail)) {
                return response()->json(['success' => false, 'message' => 'Teléfono o correo requerido'], 422);
            }

            // Normalizar teléfono
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            $last10 = substr($cleanPhone, -10);

            // Buscar usuario en la base de datos
            $user = null;
            if (!empty($userEmail)) {
                $user = User::where('email', $userEmail)->first();
            }
            if (!$user && strlen($last10) >= 7) {
                $user = User::where('phone', $cleanPhone)
                    ->orWhere('phone', $last10)
                    ->orWhere('phone', 'like', "%{$last10}")
                    ->first();
            }

            // Si aún no está registrado, avisarle al bot para que le pida el correo
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'user_found' => false,
                    'message' => 'El número de WhatsApp no coincide con ninguna cuenta registrada en Fortex.',
                ]);
            }

            // Si se pasa un nombre de cliente específico (ej: "Cristian"), actualizarlo
            $clientName = trim((string) $request->input('client_name', ''));
            if (!empty($clientName)) {
                $user->name = $clientName;
                $user->save();
            }

            // Guardar imagen del comprobante en disco
            $fileName = 'wa_proof_' . time() . '_' . uniqid() . '.jpg';
            $destDir = public_path('uploads/proofs');
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0777, true);
            }
            $absolutePath = $destDir . DIRECTORY_SEPARATOR . $fileName;

            if ($imageB64) {
                file_put_contents($absolutePath, base64_decode($imageB64));
            }

            $relPath = 'uploads/proofs/' . $fileName;

            // Crear depósito en estado pendiente
            $deposit = Deposit::create([
                'user_id' => $user->id,
                'amount' => $suggestedAmount > 0 ? $suggestedAmount : 0,
                'payment_method' => 'WhatsApp / Nequi / Bancolombia',
                'transaction_hash' => 'WA_' . strtoupper(uniqid()),
                'proof_image' => $relPath,
                'status' => 'pending',
                'admin_notes' => 'Comprobante recibido por el bot de WhatsApp el ' . now()->format('d/m/Y H:i'),
            ]);

            // Notificar a Telegram con la foto y los botones de aprobación
            TelegramService::notifyWhatsAppReceipt($deposit, $user, file_exists($absolutePath) ? $absolutePath : null, $suggestedAmount);

            return response()->json([
                'success' => true,
                'user_found' => true,
                'user_name' => $user->name,
                'deposit_id' => $deposit->id,
                'message' => 'Comprobante registrado y enviado al administrador para aprobación.',
            ]);

        } catch (\Throwable $e) {
            Log::error('reportDepositFromBot Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
