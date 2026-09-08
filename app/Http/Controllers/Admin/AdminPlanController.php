<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class AdminPlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('userPlans')->latest()->get();
        return view('admin.plans.index', compact('plans'));
    }

    public function store(Request $request)
    {
        // Normalizar valores monetarios y porcentajes (soporte de comas, puntos y símbolos)
        $price = str_replace(['.', '$', ' ', ','], ['', '', '', '.'], $request->price ?? '');
        $maxReturn = str_replace(['.', '$', ' ', ','], ['', '', '', '.'], $request->max_return ?? '');
        $dailyPercentage = str_replace(',', '.', $request->daily_percentage ?? '');

        $request->merge([
            'price' => $price,
            'max_return' => $maxReturn,
            'daily_percentage' => $dailyPercentage,
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:1',
            'daily_percentage' => 'required|numeric|min:0.01|max:1000',
            'duration_days' => 'required|integer|min:1',
            'max_return' => 'required|numeric|min:1',
            'badge' => 'nullable|string|max:100',
            'stock' => 'nullable|integer|min:0',
        ], [
            'name.required' => 'El nombre del plan es obligatorio.',
            'price.required' => 'El precio en COP es obligatorio.',
            'daily_percentage.required' => 'El porcentaje diario es obligatorio.',
            'daily_percentage.min' => 'El porcentaje diario mínimo es 0.01%.',
            'daily_percentage.max' => 'El porcentaje diario no puede superar el 1000%.',
            'duration_days.required' => 'La duración en días es obligatoria.',
            'max_return.required' => 'El tope máximo de retorno es obligatorio.',
            'stock.integer' => 'El límite de unidades debe ser un número entero.',
            'stock.min' => 'El límite de unidades no puede ser menor a 0.',
        ]);

        Plan::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'daily_percentage' => $request->daily_percentage,
            'duration_days' => $request->duration_days,
            'max_return' => $request->max_return,
            'badge' => $request->badge,
            'stock' => $request->filled('stock') ? (int) $request->stock : null,
            'status' => $request->has('status') ? true : true, // Activo por defecto para que aparezca de inmediato a admin y cliente
            'show_on_home' => $request->has('show_on_home') ? true : false,
        ]);

        return back()->with('success', '🎉 ¡Nuevo Plan VIP creado correctamente y activado en el sistema!');
    }

    public function update(Request $request, $id)
    {
        $plan = Plan::findOrFail($id);

        $price = str_replace(['.', '$', ' ', ','], ['', '', '', '.'], $request->price ?? '');
        $maxReturn = str_replace(['.', '$', ' ', ','], ['', '', '', '.'], $request->max_return ?? '');
        $dailyPercentage = str_replace(',', '.', $request->daily_percentage ?? '');

        $request->merge([
            'price' => $price,
            'max_return' => $maxReturn,
            'daily_percentage' => $dailyPercentage,
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:1',
            'daily_percentage' => 'required|numeric|min:0.01|max:1000',
            'duration_days' => 'required|integer|min:1',
            'max_return' => 'required|numeric|min:1',
            'badge' => 'nullable|string|max:100',
            'stock' => 'nullable|integer|min:0',
        ], [
            'name.required' => 'El nombre del plan es obligatorio.',
            'price.required' => 'El precio en COP es obligatorio.',
            'daily_percentage.required' => 'El porcentaje diario es obligatorio.',
            'daily_percentage.min' => 'El porcentaje diario mínimo es 0.01%.',
            'daily_percentage.max' => 'El porcentaje diario no puede superar el 1000%.',
            'duration_days.required' => 'La duración en días es obligatoria.',
            'max_return.required' => 'El tope máximo de retorno es obligatorio.',
            'stock.integer' => 'El límite de unidades debe ser un número entero.',
            'stock.min' => 'El límite de unidades no puede ser menor a 0.',
        ]);

        $plan->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'daily_percentage' => $request->daily_percentage,
            'duration_days' => $request->duration_days,
            'max_return' => $request->max_return,
            'badge' => $request->badge,
            'stock' => $request->filled('stock') ? (int) $request->stock : null,
            'status' => $request->has('status') ? true : false,
            'show_on_home' => $request->has('show_on_home') ? true : false,
        ]);

        return back()->with('success', '¡Plan VIP actualizado correctamente!');
    }

    public function toggle($id)
    {
        $plan = Plan::findOrFail($id);
        $plan->status = ! $plan->status;
        $plan->save();

        $state = $plan->status ? 'activado' : 'pausado';
        return back()->with('success', "El plan {$plan->name} fue {$state}.");
    }

    public function toggleHome($id)
    {
        $plan = Plan::findOrFail($id);
        $plan->show_on_home = ! $plan->show_on_home;
        $plan->save();

        $state = $plan->show_on_home ? 'ahora se mostrará en la pantalla de Inicio' : 'se ocultó de Inicio (solo se verá en el catálogo Planes)';
        return back()->with('success', "🏠 El plan {$plan->name} {$state}.");
    }

    public function destroy($id)
    {
        $plan = Plan::findOrFail($id);
        $name = $plan->name;
        $plan->delete();
        
        return back()->with('success', "🗑️ ¡El {$name} fue eliminado correctamente!");
    }
}
