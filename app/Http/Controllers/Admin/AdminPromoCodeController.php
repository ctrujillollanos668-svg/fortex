<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\PromoCodeRedemption;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPromoCodeController extends Controller
{
    public function index()
    {
        $promoCodes = PromoCode::withCount('redemptions')->latest()->paginate(15);
        $recentRedemptions = PromoCodeRedemption::with(['promoCode', 'user'])->latest()->take(10)->get();

        $totalCodes = PromoCode::count();
        $activeCodes = PromoCode::where('status', true)->count();
        $totalRedeemedAmount = PromoCodeRedemption::sum('reward_amount');
        $totalRedemptionsCount = PromoCodeRedemption::count();

        return view('admin.promo_codes.index', compact(
            'promoCodes',
            'recentRedemptions',
            'totalCodes',
            'activeCodes',
            'totalRedeemedAmount',
            'totalRedemptionsCount'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'nullable|string|max:50|unique:promo_codes,code',
            'reward_amount' => 'required|numeric|min:100',
            'max_uses' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date',
        ], [
            'code.unique' => 'Ya existe un código con este mismo nombre.',
            'reward_amount.required' => 'Debes especificar el monto del premio en pesos ($ COP).',
            'reward_amount.min' => 'El monto mínimo es de $100 COP.',
            'max_uses.min' => 'El límite de usos debe ser al menos 1.',
        ]);

        // Si no ingresó código manual, generar uno elegante automáticamente (ej: FORTEX-8X9Y)
        $code = !empty($request->code)
            ? strtoupper(trim(preg_replace('/[^A-Za-z0-9_-]/', '', $request->code)))
            : 'FORTEX-' . strtoupper(Str::random(6));

        PromoCode::create([
            'code' => $code,
            'reward_amount' => $request->reward_amount,
            'max_uses' => $request->max_uses,
            'used_count' => 0,
            'status' => true,
            'description' => $request->description ?? 'Código de sorteo/regalo creado por el Administrador.',
            'expires_at' => $request->expires_at ? \Carbon\Carbon::parse($request->expires_at) : null,
        ]);

        return redirect()->route('admin.promo-codes.index')
            ->with('success', "¡Código [{$code}] de $" . number_format($request->reward_amount, 0, ',', '.') . " COP creado exitosamente con límite de {$request->max_uses} uso(s)!");
    }

    public function toggle($id)
    {
        $promo = PromoCode::findOrFail($id);
        $promo->status = !$promo->status;
        $promo->save();

        $statusText = $promo->status ? 'activado' : 'desactivado / pausado';
        return back()->with('success', "El código [{$promo->code}] ha sido {$statusText}.");
    }

    public function destroy($id)
    {
        $promo = PromoCode::findOrFail($id);
        $code = $promo->code;
        $promo->delete();

        return back()->with('success', "El código [{$code}] ha sido eliminado correctamente.");
    }
}
