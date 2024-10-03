<?php

namespace App\Http\Controllers\Vehiculos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehiculo\Vehiculo;
use App\Models\Refrigeracion\sistemarefrigeracion;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    use ValidatesRequests;

    public function __construct()
    {
        $this->middleware('permission:registrarVehiculo');
    }

    public function index()
    {
        $agencia = Auth::user()->agencia;
        return view('vehiculo.register', compact('agencia'));
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'agencia' => 'required|max:50',
            'vehiculo' => 'required',
            'placa' => 'required|unique:vehiculos|max:7',
            'marca' => 'required|max:50',
            'modelo' => 'required|max:50',
            'anio' => 'required|numeric|max:2050|min:1980',
            'dimension_pies' => 'required|numeric|max:40|min:10',
            'estado' => 'required|max:20',
            'tipo_temperatura' => 'required|in:Mixto,Mixto 3 Temp,Seco,Seco (Merma),Refrigerado',
            'tipo_refrigeracion' => 'required|string|max:255',
            'agencia' => 'required|max:50|in:' . Auth::user()->agencia,
        ]);

        try {
            DB::transaction(function () use ($validatedData) {
                $user_id = Auth::id();
                $vehiculo = Vehiculo::create(array_merge($validatedData, ['user_id' => $user_id]));
                $vehiculo->sistemaRefrigeracion()->create([
                    'user_id' => $user_id,
                    'tipo_temperatura' => $validatedData['tipo_temperatura'],
                    'tipo_refrigeracion' => $validatedData['tipo_refrigeracion'],
                ]);
            });

            return view('vehiculo.register');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al registrar el vehículo: ' . $e->getMessage()], 500);
        }
    }
}
