<?php

namespace App\Http\Controllers\Vehiculos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Vehiculo\Vehiculo;
use App\Models\Refrigeracion\sistemarefrigeracion;
use App\Models\Mantenimiento\Mantenimiento;
use App\Models\User;

use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DataTablesController extends Controller
{
    use ValidatesRequests;

    public function index()
    {
        $user = Auth::user();
        $vehiculos = Vehiculo::with('sistemarefrigeracion')
            ->when(!$user->hasRole('Administrador de Sistema'), function ($query) use ($user) {
                return $query->where('agencia', $user->agencia);
            })
            ->get();

        $users = User::when(!$user->hasRole('Administrador de Sistema'), function ($query) use ($user) {
            return $query->where('agencia', $user->agencia);
        })->get();

        if (request()->ajax()) {
            return DataTables::of($vehiculos)->toJson();
        }

        return view('vehiculo.dataTables', compact('vehiculos', 'users'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $vehiculo = Vehiculo::with('sistemaRefrigeracion')
            ->when(!$user->hasRole('Administrador de Sistema'), function ($query) use ($user) {
                return $query->where('agencia', $user->agencia);
            })
            ->findOrFail($id);
        return view('vehiculo.edit', compact('vehiculo'));
    }

    public function update(Request $request, $id)
    {
        Log::info('Iniciando actualización de vehículo', ['id' => $id, 'datos' => $request->all()]);

        $request->validate([
            'agencia' => 'required|max:50',
            'vehiculo' => 'required|max:255',
            'placa' => 'required|max:20|unique:vehiculos,placa,'.$id,
            'marca' => 'required|in:Freightliner,Isuzu,Hino,International',
            'modelo' => 'required|max:50',
            'anio' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'dimension_pies' => 'required|numeric|max:99.99',
            'estado' => 'required|in:Activo,En mantenimiento,Fuera de servicio',
            'tipo_temperatura' => 'nullable|in:Mixto,Mixto 3 Temp,Seco,Seco (Merma),Refrigerado',
            'tipo_refrigeracion' => 'nullable|in:T1000S,T1000R,SUPRA 950 MT,T1080R,T600R,T680R',
        ]);

        try {
            DB::beginTransaction();

            $vehiculo = Vehiculo::findOrFail($id);
            Log::info('Vehículo encontrado', ['vehiculo' => $vehiculo->toArray()]);

            $vehiculo->update($request->only([
                'agencia', 'vehiculo', 'placa', 'marca', 'modelo', 'anio', 'dimension_pies', 'estado'
            ]));

            Log::info('Vehículo actualizado', ['vehiculo' => $vehiculo->fresh()->toArray()]);

            if ($request->filled('tipo_temperatura') || $request->filled('tipo_refrigeracion')) {
                $vehiculo->sistemaRefrigeracion()->updateOrCreate(
                    ['vehiculo_id' => $vehiculo->id],
                    $request->only(['tipo_temperatura', 'tipo_refrigeracion'])
                );
                Log::info('Sistema de refrigeración actualizado', ['sistema' => $vehiculo->sistemaRefrigeracion->fresh()->toArray()]);
            }

            DB::commit();
            Log::info('Transacción completada, vehículo actualizado correctamente');

            return response()->json(['success' => true, 'message' => 'Vehículo actualizado correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar el vehículo', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Error al actualizar el vehículo: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();
            $vehiculo = Vehiculo::when(!$user->hasRole('Administrador de Sistema'), function ($query) use ($user) {
                return $query->where('agencia', $user->agencia);
            })->findOrFail($id);
            $vehiculo->delete(); // Esto ahora realizará un soft delete
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeMantenimiento(Request $request)
    {
        try {
            $validated = $request->validate([
                'vehiculo_id' => 'required|exists:vehiculos,id',
                'tipoMantenimiento' => 'required|in:Preventivo,Correctivo',
                'tipoComponente' => 'required|in:vehiculo,sistemaRefrigeracion',
                'fechaInicial' => 'required|date|after_or_equal:today',
                'fechaFinal' => 'required|date|after_or_equal:fechaInicial',
                'usuario_id' => 'required|exists:users,id',
                'actividad' => 'required|string|max:500',
            ]);

            $user = Auth::user();
            $vehiculo = Vehiculo::when(!$user->hasRole('Administrador de Sistema'), function ($query) use ($user) {
                return $query->where('agencia', $user->agencia);
            })->findOrFail($validated['vehiculo_id']);

            // Crear el registro de mantenimiento
            $mantenimiento = new Mantenimiento($validated);

            // Determinar el nuevo estado basado en el tipo de componente y el tipo de mantenimiento
            $nuevoEstado = $validated['tipoComponente'] == 'vehiculo' ?
                'Vehiculo ' . $validated['tipoMantenimiento'] :
                'Refrigeracion ' . $validated['tipoMantenimiento'];

            // Si el componente es sistema de refrigeración, asignar el ID correspondiente
            if ($validated['tipoComponente'] == 'sistemaRefrigeracion') {
                if ($vehiculo->sistemaRefrigeracion) {
                    $mantenimiento->sistemarefrigeracion_id = $vehiculo->sistemaRefrigeracion->id;
                    $vehiculo->sistemaRefrigeracion->update(['estado' => $nuevoEstado]);
                } else {
                    return response()->json(['success' => false, 'message' => 'Este vehículo no tiene un sistema de refrigeración asociado.'], 422);
                }
            }

            $mantenimiento->save();

            // Actualizar el estado del vehículo solo si no tiene otros mantenimientos programados
            $otrosMantenimientos = Mantenimiento::where('vehiculo_id', $vehiculo->id)
                ->where('id', '!=', $mantenimiento->id)
                ->where('estado', '!=', 'aprobado')
                ->exists();

            if (!$otrosMantenimientos) {
                $vehiculo->update(['estado' => $nuevoEstado]);
            }

            return response()->json(['success' => true, 'message' => 'Mantenimiento agendado correctamente']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al agendar el mantenimiento: ' . $e->getMessage()], 500);
        }
    }
/*
    public function getMantenimientoReport($id)
    {
        try {
            $user = Auth::user();
            $vehiculo = Vehiculo::with(['mantenimientos.usuario', 'mantenimientos.materiales'])
                ->when(!$user->hasRole('Administrador de Sistema'), function ($query) use ($user) {
                    return $query->where('agencia', $user->agencia);
                })
                ->findOrFail($id);

            if ($vehiculo->mantenimientos->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'vehiculo' => $vehiculo->vehiculo,
                        'placa' => $vehiculo->placa,
                        'mantenimientosRealizados' => [],
                        'mantenimientosProgramados' => [],
                        'mensaje' => 'Este vehículo no tiene mantenimientos registrados.'
                    ]
                ]);
            }

            $mantenimientos = $vehiculo->mantenimientos->map(function ($m) {
                $componente = $m->sistemarefrigeracion_id ? 'Sistema de Refrigeración' : 'Vehículo';
                return [
                    'id' => $m->id,
                    'tipo' => $m->tipoMantenimiento,
                    'componente' => $componente,
                    'fechaInicial' => $m->fechaInicial,
                    'fechaFinal' => $m->fechaFinal,
                    'tecnico' => $m->usuario->name,
                    'observaciones' => $m->observaciones,
                    'diagnostico_inicial' => $m->diagnostico_inicial, // Añadido
                    'acciones_realizadas' => $m->pruebas_realizadas, // Añadido
                    'foto_url' => $m->foto_evidencia ? Storage::url($m->foto_evidencia) : null,
                    'materiales' => $m->materiales->map(function ($material) {
                        return [
                            'nombre' => $material->nombre,
                            'cantidad' => $material->pivot->cantidad_usada,
                        ];
                    }),
                ];
            });

            $data = [
                'vehiculo' => $vehiculo->vehiculo,
                'placa' => $vehiculo->placa,
                'mantenimientosRealizados' => $mantenimientos->where('fechaFinal', '<', now())->values(),
                'mantenimientosProgramados' => $mantenimientos->where('fechaFinal', '>=', now())->values(),
            ];

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error en getMantenimientoReport: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener el reporte de mantenimientos: ' . $e->getMessage()], 500);
        }
    }
*/
    public function getMantenimientoReport($id)
    {
        try {
            $user = Auth::user();
            $vehiculo = Vehiculo::with(['mantenimientos.fases.materiales', 'mantenimientos.usuario'])
                ->when(!$user->hasRole('Administrador de Sistema'), function ($query) use ($user) {
                    return $query->where('agencia', $user->agencia);
                })
                ->findOrFail($id);

            if ($vehiculo->mantenimientos->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'vehiculo' => $vehiculo->vehiculo,
                        'placa' => $vehiculo->placa,
                        'mantenimientosRealizados' => [],
                        'mantenimientosProgramados' => [],
                        'mensaje' => 'Este vehículo no tiene mantenimientos registrados.'
                    ]
                ]);
            }

            $mantenimientos = $vehiculo->mantenimientos->map(function ($m) {
                return [
                    'id' => $m->id,
                    'tipo' => $m->tipoMantenimiento,
                    'fechaInicial' => $m->fechaInicial,
                    'fechaFinal' => $m->fechaFinal,
                    'tecnico' => $m->usuario->name,
                    'fases' => $m->fases->map(function ($fase) {
                        return [
                            'tipo_fase' => $fase->tipo_fase,
                            'hora_inicio' => $fase->hora_inicio,
                            'hora_fin' => $fase->hora_fin,
                            'descripcion' => $fase->descripcion,
                            'acciones_realizadas' => $fase->acciones_realizadas,
                            'observaciones' => $fase->observaciones,
                            'foto_url' => $fase->foto_evidencia ? Storage::url($fase->foto_evidencia) : null,
                            'materiales' => $fase->materiales->map(function ($material) {
                                return [
                                    'nombre' => $material->nombre,
                                    'cantidad' => $material->pivot->cantidad_usada,
                                ];
                            }),
                        ];
                    }),
                ];
            });

            $data = [
                'vehiculo' => $vehiculo->vehiculo,
                'placa' => $vehiculo->placa,
                'mantenimientosRealizados' => $mantenimientos->where('fechaFinal', '<', now())->values(),
                'mantenimientosProgramados' => $mantenimientos->where('fechaFinal', '>=', now())->values(),
            ];

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error en getMantenimientoReport: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener el reporte de mantenimientos: ' . $e->getMessage()], 500);
        }
    }

    public function getVehiculoInfo($id)
    {
        $user = Auth::user();
        $vehiculo = Vehiculo::with('sistemaRefrigeracion')
            ->when(!$user->hasRole('Administrador de Sistema'), function ($query) use ($user) {
                return $query->where('agencia', $user->agencia);
            })
            ->findOrFail($id);
        return response()->json($vehiculo);
    }

    public function desasignarVehiculo(Request $request)
    {
        $request->validate([
            'vehiculo_id' => 'required|exists:vehiculos,id',
        ]);

        $user = Auth::user();
        $vehiculo = Vehiculo::when(!$user->hasRole('Administrador de Sistema'), function ($query) use ($user) {
            return $query->where('agencia', $user->agencia);
        })->findOrFail($request->vehiculo_id);

        // Desasignar el vehículo (asumiendo que tienes un campo user_id en la tabla de vehículos)
        $vehiculo->user_id = null;
        $vehiculo->save();

        return response()->json(['success' => true, 'message' => 'Vehículo desasignado exitosamente.']);
    }

    public function getTecnicosDisponibles(Request $request)
    {
        $fechaInicial = $request->input('fecha_inicial');
        $fechaFinal = $request->input('fecha_final');

        $user = Auth::user();
        $query = User::role('Tecnico');

        if (!$user->hasRole('Administrador de Sistema')) {
            $query->where('agencia', $user->agencia);
        }

        $tecnicosDisponibles = $query->get();

        return response()->json($tecnicosDisponibles);
    }

    public function getTecnicosAsignados($id)
    {
        $vehiculo = Vehiculo::findOrFail($id);
        $tecnicos = $vehiculo->tecnicos;
        return response()->json($tecnicos);
    }

    public function getTecnicosDisponiblesYAsignados(Request $request)
    {
        Log::info('Datos recibidos:', $request->all());

        $vehiculoId = $request->input('vehiculo_id');
        $tipoComponente = $request->input('tipo_componente');

        $vehiculo = Vehiculo::findOrFail($vehiculoId);

        // Obtener técnicos asignados al vehículo
        $tecnicosAsignados = $vehiculo->tecnicos()->pluck('users.id')->toArray();

        // Si no hay técnicos asignados, obtener técnicos de la agencia
        if (empty($tecnicosAsignados)) {
            $tecnicosAsignados = User::role('Tecnico')
                ->where('agencia', $vehiculo->agencia)
                ->pluck('id')
                ->toArray();
            $mensaje = 'No hay técnicos asignados a este vehículo. Se muestran los técnicos de la agencia.';
        } else {
            $mensaje = null;
        }

        $tecnicos = User::role('Tecnico')
            ->whereIn('id', $tecnicosAsignados)
            ->get();

        Log::info('Técnicos disponibles:', $tecnicos->pluck('id')->toArray());

        return response()->json([
            'tecnicos' => $tecnicos,
            'mensaje' => $tecnicos->isEmpty() ? 'No hay técnicos disponibles para este vehículo o agencia' : $mensaje
        ]);
    }
}
