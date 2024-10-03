<?php

namespace App\Http\Controllers\Vehiculos;

use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Mantenimiento\Mantenimiento;
use App\Models\Mantenimiento\Material;
use App\Models\Mantenimiento\FaseMantenimiento;

use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CalendarioController extends Controller
{
    public function index()
    {
        return view('calendario.index');
    }

    public function getEventos(Request $request)
    {
        try {
            $start = $request->input('start');
            $end = $request->input('end');
            $usuario = Auth::user();

            $query = Mantenimiento::with('vehiculo')
                ->where(function($q) use ($start, $end) {
                    $q->whereBetween('fechaInicial', [$start, $end])
                      ->orWhereBetween('fechaFinal', [$start, $end])
                      ->orWhere(function($subQ) use ($start, $end) {
                          $subQ->where('fechaInicial', '<=', $start)
                               ->where('fechaFinal', '>=', $end);
                      });
                });

            if ($usuario->hasRole('Tecnico')) {
                $query->where('usuario_id', $usuario->id);
            } elseif ($usuario->hasRole('Administrador de Agencia')) {
                $query->whereHas('vehiculo', function($q) use ($usuario) {
                    $q->where('agencia', $usuario->agencia);
                });
            }
            // Para Administrador de Sistema, no aplicamos filtro adicional

            $mantenimientos = $query->get();

            $eventos = [];

            foreach ($mantenimientos as $mantenimiento) {
                $placa = $mantenimiento->vehiculo ? $mantenimiento->vehiculo->placa : 'Vehículo eliminado';

                $eventos[] = [
                    'id' => $mantenimiento->id,
                    'title' => $mantenimiento->tipoMantenimiento . ' - ' . $placa,
                    'start' => $mantenimiento->fechaInicial,
                    'end' => $mantenimiento->fechaFinal,
                    'color' => $this->getColorByEstado($mantenimiento->estado),
                    'vehiculo' => $mantenimiento->vehiculo->vehiculo,
                    'placa' => $mantenimiento->vehiculo->placa, 
                    'tipo' => strtolower($mantenimiento->tipoMantenimiento),
                    'actividad' => $mantenimiento->actividad,
                    'categoria' => $mantenimiento->sistemarefrigeracion_id ? 'refrigeracion' : 'vehiculo',
                    'description' => 'Mantenimiento ' . $mantenimiento->tipoMantenimiento . ' para ' . $placa,
                    'estado' => $mantenimiento->estado,
                    'rechazo_razon' => $mantenimiento->rechazo_razon,
                    'canApproveOrReject' => $usuario->hasAnyRole(['Administrador de Sistema', 'Administrador de Agencia']),
                    'canRegisterMaterial' => $mantenimiento->estado !== 'aprobado'
                ];
            }

            return response()->json($eventos);
        } catch (\Exception $e) {
            Log::error('Error al obtener eventos del calendario: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener los eventos'], 500);
        }
    }

    private function getColorByEstado($estado)
    {
        switch ($estado) {
            case 'programado':
                return '#dc3545'; // Rojo
            case 'en_progreso':
                return '#ffc107'; // Amarillo
            case 'realizado':
                return '#28a745'; // Verde
            case 'aprobado':
                return '#007bff'; // Azul
            case 'rechazado':
                return '#6c757d'; // Gris
            default:
                return '#6c757d'; // Gris por defecto
        }
    }

    public function getMateriales()
    {
        $materiales = Material::all()->map(function ($material) {
            return [
                'id' => $material->id,
                'text' => $material->nombre
            ];
        });

        return response()->json($materiales);
    }

public function registrarMantenimiento(Request $request)
{
    $request->validate([
        'eventoId' => 'required|exists:mantenimientos,id',
        'fase' => 'required|in:diagnostico,ejecucion,entrega',
        'fecha_hora' => 'required|date',
        'descripcion' => 'required|string',
        'acciones' => 'required|string',
        'materiales' => 'nullable|array',
        'materiales.*' => 'exists:materiales,id',
        'cantidades' => 'nullable|array',
        'cantidades.*' => 'required_with:materiales|integer|min:1',
        'observaciones' => 'nullable|string',
        'foto' => 'nullable|image|max:8192',
    ]);

    try {
        DB::beginTransaction();

        $mantenimiento = Mantenimiento::findOrFail($request->eventoId);

        // Buscar si ya existe una fase del mismo tipo
        $fase = $mantenimiento->fases()->where('tipo_fase', $request->fase)->first();

        if ($fase) {
            // Si la fase existe, actualizarla
            $fase->update([
                'fecha_hora' => $request->fecha_hora,
                'descripcion' => $request->descripcion,
                'acciones_realizadas' => $request->acciones,
                'observaciones' => $request->observaciones,
            ]);
        } else {
            // Si no existe, crear una nueva fase
            $fase = new FaseMantenimiento([
                'tipo_fase' => $request->fase,
                'fecha_hora' => $request->fecha_hora,
                'descripcion' => $request->descripcion,
                'acciones_realizadas' => $request->acciones,
                'observaciones' => $request->observaciones,
            ]);
            $mantenimiento->fases()->save($fase);
        }

        if ($request->has('materiales') && $request->has('cantidades')) {
            $materiales = array_combine($request->materiales, $request->cantidades);
            foreach ($materiales as $materialId => $cantidad) {
                $existingMaterial = $fase->materiales()->where('material_id', $materialId)->first();
                if ($existingMaterial) {
                    // Si el material ya existe, actualiza la cantidad
                    $existingMaterial->pivot->cantidad_usada += $cantidad;
                    $existingMaterial->pivot->save();
                } else {
                    // Si el material no existe, crea un nuevo registro
                    $fase->materiales()->attach($materialId, ['cantidad_usada' => $cantidad]);
                }
            }
        }

        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('fotos_mantenimiento', 'public');
            $fase->foto_evidencia = $fotoPath;
            $fase->save();
        }

        // Actualizar el estado del mantenimiento
        if ($request->fase === 'entrega') {
            $mantenimiento->estado = 'realizado';
        } elseif ($mantenimiento->estado === 'programado') {
            $mantenimiento->estado = 'en_progreso';
        }
        $mantenimiento->save();

        DB::commit();

        return response()->json(['success' => true, 'message' => 'Fase de mantenimiento registrada con éxito']);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'message' => 'Error al registrar la fase de mantenimiento: ' . $e->getMessage()], 500);
    }
}

    public function aprobarMantenimiento($id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Administrador de Sistema', 'Administrador de Agencia'])) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para aprobar mantenimientos'], 403);
        }

        try {
            $mantenimiento = Mantenimiento::findOrFail($id);
            $mantenimiento->estado = 'aprobado';
            $mantenimiento->save();

            // Actualizar el estado del vehículo a "Activo"
            $vehiculo = $mantenimiento->vehiculo;
            if ($vehiculo) {
                $vehiculo->estado = 'Activo';
                $vehiculo->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Mantenimiento aprobado con éxito y vehículo activado',
                'estado' => $mantenimiento->estado,
                'vehiculo_estado' => $vehiculo ? $vehiculo->estado : null
            ]);
        } catch (\Exception $e) {
            Log::error('Error al aprobar mantenimiento: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al aprobar el mantenimiento: ' . $e->getMessage()], 500);
        }
    }

    public function rechazarMantenimiento(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Administrador de Sistema', 'Administrador de Agencia'])) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para rechazar mantenimientos'], 403);
        }

        $request->validate([
            'rechazo_razon' => 'required|string|max:1000',
        ]);

        try {
            $mantenimiento = Mantenimiento::findOrFail($id);
            $mantenimiento->estado = 'rechazado'; // Asegúrate de que esto sea una cadena
            $mantenimiento->rechazo_razon = $request->rechazo_razon;
            $mantenimiento->save();

            return response()->json([
                'success' => true,
                'message' => 'Mantenimiento rechazado con éxito',
                'estado' => $mantenimiento->estado
            ]);
        } catch (\Exception $e) {
            Log::error('Error al rechazar mantenimiento: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al rechazar el mantenimiento: ' . $e->getMessage()], 500);
        }
    }

    private function procesarYGuardarFoto($fotoBase64)
    {
        $image = Image::make($fotoBase64);
        $fileName = 'mantenimiento_' . time() . '.jpg';
        $path = 'fotos_mantenimiento/' . $fileName;

        Storage::disk('public')->put($path, $image->stream());

        return $path;
    }

}
