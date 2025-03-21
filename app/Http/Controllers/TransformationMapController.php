<?php

namespace App\Http\Controllers;

use App\Models\TransformationMap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TransformationMapController extends Controller
{
    /**
     * Exibe todos os registros de TransformationMap.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $transformationMaps = TransformationMap::all();
        return response()->json($transformationMaps);
    }

    /**
     * Exibe um registro específico de TransformationMap.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $transformationMap = TransformationMap::findOrFail($id);

        return response()->json($transformationMap);
    }

    /**
     * Cria um novo registro de TransformationMap.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'from_type' => 'required|string|max:255',
            'to_type' => 'required|string|max:255',
            'rules' => 'required|array',
        ]);

        $transformationMap = TransformationMap::create($request->only([
            'name', 'from_type', 'to_type', 'rules'
        ]));

        return response()->json($transformationMap, 201);
    }

    /**
     * Atualiza um registro existente de TransformationMap.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $transformationMap = TransformationMap::findOrFail($id);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'from_type' => 'nullable|string|max:255',
            'to_type' => 'nullable|string|max:255',
            'rules' => 'nullable|array',
        ]);

        $transformationMap->update($request->only([
            'name', 'from_type', 'to_type', 'rules'
        ]));

        return response()->json($transformationMap);
    }

    /**
     * Deleta um registro de TransformationMap.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $transformationMap = TransformationMap::findOrFail($id);

        $transformationMap->delete();

        return response()->json(['message' => 'TransformationMap deleted successfully']);
    }
}
