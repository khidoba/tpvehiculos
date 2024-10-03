<?php

namespace App\Http\Controllers\Vehiculos;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

use App\Models\Mantenimiento\Mantenimiento;
use App\Models\Mantenimiento\Material;
use Illuminate\Support\Facades\DB;


class MaterialController extends Controller
{

    public function index()
    {
        $materiales = Material::all();
        return response()->json($materiales);
    }

    public function obtenerMateriales()
    {
        //$materiales = Material::all(['id', 'nombre']);
        //return response()->json($materiales);
        return Material::all();
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'eventoId' => 'required|integer|exists:mantenimientos,id',
            'materiales' => 'required|array',
            'materiales.*' => 'required|integer|exists:materiales,id',
            'cantidades' => 'required|array',
            'cantidades.*' => 'required|integer|min:1',
            'fotoBase64' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $mantenimiento = Mantenimiento::findOrFail($request->eventoId);

            foreach ($request->materiales as $index => $materialId) {
                $material = Material::findOrFail($materialId);
                $cantidad = $request->cantidades[$index];

                // Guardar la relación de material con el mantenimiento
                $mantenimiento->materiales()->attach($materialId, ['cantidad' => $cantidad]);

                // Reducir la cantidad de material en stock
                $material->cantidad -= $cantidad;
                $material->save();
            }

            // Guardar la foto si existe
            if ($request->has('fotoBase64') && $request->fotoBase64) {
                $fotoData = $request->fotoBase64;
                $fotoPath = $this->guardarFoto($fotoData);
                $mantenimiento->foto_material = $fotoPath;
                $mantenimiento->save();
            }

            DB::commit();

            return response()->json(['message' => 'Materiales registrados con éxito'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al registrar los materiales: ' . $e->getMessage()], 500);
        }
    }

    private function guardarFoto($fotoBase64)
    {
        // Generar un nombre único para la foto
        $filename = 'materiales/' . Str::uuid() . '.jpg';

        // Decodificar la imagen y guardarla en el almacenamiento
        $fotoData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $fotoBase64));
        Storage::disk('public')->put($filename, $fotoData);

        return $filename;
    }

}
