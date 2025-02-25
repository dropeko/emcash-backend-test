<?php

/**
 * @OA\Info(
 *      title="User Management API",
 *      version="2"
 * ),
 * @OA\Server(
 *      url="http://localhost:8000",
 * ),
 */

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;
use App\Infra\Swagger\Swagger;
use Illuminate\Http\JsonResponse;

class SwaggerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api-docs",
     *     summary="Retorna a documentação da API em formato JSON",
     *     tags={"Swagger"},
     *     @OA\Response(
     *          response="200",
     *          description="OK",
     *          content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="openapi",
     *                         type="string",
     *                     ),
     *                     @OA\Property(
     *                         property="info",
     *                         type="integer",
     *                     ),
     *                     @OA\Property(
     *                         property="servers",
     *                         type="string",
     *                     ),
     *                     @OA\Property(
     *                         property="paths",
     *                         type="string",
     *                     )
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function docs(): JsonResponse
    {
        try {
            $docBlocksPath = base_path('app/Http/Controllers');

            $swagger = (new Swagger())->setDocBlocksPath($docBlocksPath);
            $documentation = $swagger->generateDocumentation();

            return response()->json($documentation);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}