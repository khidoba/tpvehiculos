<?php

namespace App\Http\Controllers\Vehiculos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehiculo\Vehiculo;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class AsignacionController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('Administrador de Sistema')) {
            $vehiculos = Vehiculo::all();
            $tecnicos = User::role('Tecnico')->get();
        } else {
            $agencia = $user->agencia;
            $vehiculos = Vehiculo::where('agencia', $agencia)->get();
            $tecnicos = User::role('Tecnico')->where('agencia', $agencia)->get();
        }

        return view('vehiculo.asignaciones', compact('vehiculos', 'tecnicos'));
    }

    public function asignar(Request $request)
    {
        $request->validate([
            'vehiculo_ids' => 'required|array',
            'vehiculo_ids.*' => 'exists:vehiculos,id',
            'tecnico_id' => 'required|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $tecnico = User::findOrFail($request->tecnico_id);

            // Obtener las asignaciones actuales
            $asignacionesActuales = $tecnico->vehiculos()->pluck('vehiculos.id')->toArray();

            // Filtrar las nuevas asignaciones para evitar duplicados
            $nuevasAsignaciones = array_diff($request->vehiculo_ids, $asignacionesActuales);

            // Añadir solo las nuevas asignaciones
            $tecnico->vehiculos()->attach($nuevasAsignaciones);

            DB::commit();

            Log::info('Vehículos asignados correctamente', [
                'tecnico_id' => $tecnico->id,
                'nuevas_asignaciones' => $nuevasAsignaciones
            ]);

            return redirect()->back()->with('success', 'Vehículos asignados correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al asignar vehículos', [
                'error' => $e->getMessage(),
                'tecnico_id' => $request->tecnico_id,
                'vehiculo_ids' => $request->vehiculo_ids
            ]);
            return redirect()->back()->with('error', 'Error al asignar vehículos: ' . $e->getMessage());
        }
    }

    public function desasignar(Request $request)
    {
        $request->validate([
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'tecnico_id' => 'required|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $tecnico = User::findOrFail($request->tecnico_id);
            $vehiculo = Vehiculo::findOrFail($request->vehiculo_id);

            $tecnico->vehiculos()->detach($vehiculo->id);

            DB::commit();

            Log::info('Vehículo desasignado correctamente', [
                'tecnico_id' => $tecnico->id,
                'vehiculo_id' => $vehiculo->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vehículo desasignado correctamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al desasignar vehículo', [
                'error' => $e->getMessage(),
                'tecnico_id' => $request->tecnico_id,
                'vehiculo_id' => $request->vehiculo_id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al desasignar vehículo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function vehiculosAsignados()
    {
        $user = Auth::user();

        if ($user->hasRole('Tecnico')) {
            $vehiculos = $user->vehiculos;
        } elseif ($user->hasRole('Administrador de Agencia')) {
            $vehiculos = \App\Models\Vehiculo\Vehiculo::where('agencia', $user->agencia)->get();
        } else {
            $vehiculos = \App\Models\Vehiculo\Vehiculo::all();
        }

        return view('vehiculo.asignados', compact('vehiculos'));
    }
/*
    public function getVehiculos(Request $request)
    {
        $search = $request->search;
        $user = Auth::user();

        $query = Vehiculo::query();

        if (!$user->hasRole('Administrador de Sistema')) {
            $query->where('agencia', $user->agencia);
        }

        // Excluir vehículos ya asignados a técnicos
        $query->whereDoesntHave('tecnicos');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('placa', 'like', "%$search%")
                  ->orWhere('modelo', 'like', "%$search%");
            });
        }

        $vehiculos = $query->get();

        return response()->json($vehiculos);
    }
*/
    public function getVehiculos(Request $request)
    {
        $search = $request->search;
        $user = Auth::user();

        $query = Vehiculo::query();

        if (!$user->hasRole('Administrador de Sistema')) {
            $query->where('agencia', $user->agencia);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('placa', 'like', "%$search%")
                  ->orWhere('modelo', 'like', "%$search%");
            });
        }

        $vehiculos = $query->get();

        return response()->json($vehiculos);
    }

    public function getVehiculosAsignados(Request $request)
    {
        $tecnico = User::findOrFail($request->tecnico_id);
        return response()->json($tecnico->vehiculos);
    }

    public function getTecnicos(Request $request)
    {
        $search = $request->search;
        $user = Auth::user();

        $query = User::role('Tecnico');

        if (!$user->hasRole('Administrador de Sistema')) {
            $query->where('agencia', $user->agencia);
        }

        if ($search) {
            $query->where('name', 'like', "%$search%");
        }

        $tecnicos = $query->limit(10)->get();

        return response()->json($tecnicos);
    }

}
