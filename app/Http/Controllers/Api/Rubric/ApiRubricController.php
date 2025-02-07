<?php

namespace App\Http\Controllers\Api\Rubric;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Rubric\RubricRequest;
use App\Http\Resources\Admin\Rubric\RubricResource;
use App\Models\Admin\Rubric\Rubric;

/**
 * @OA\Info(
 *     title="API Documentation",
 *     version="1.0.0",
 *     @OA\Contact(
 *         email="support@example.com"
 *     )
 * )
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Rubric",
 *     type="object",
 *     title="Rubric",
 *     description="Rubric model",
 *     properties={
 *         @OA\Property(property="id", type="integer", description="Rubric ID"),
 *         @OA\Property(property="title", type="string", description="Rubric title"),
 *         @OA\Property(property="url", type="string", description="Rubric URL"),
 *         @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", description="Last update date")
 *     }
 * )
 */
class ApiRubricController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/rubrics",
     *     operationId="getRubrics",
     *     tags={"Rubrics"},
     *     summary="Display a listing of the rubrics",
     *     description="Get a list of rubrics",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Rubric")
     *         )
     *     )
     * )
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $rubrics = Rubric::all();
        $rubricsCount = Rubric::count();

        return response()->json([
            'rubrics' => RubricResource::collection($rubrics),
            'rubricsCount' => $rubricsCount,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/rubrics",
     *     operationId="storeRubric",
     *     tags={"Rubrics"},
     *     summary="Store a newly created rubric",
     *     description="Create a new rubric",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Rubric")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Rubric")
     *     )
     * )
     */
    public function store(RubricRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $rubric = Rubric::create($data);

        return response()->json(new RubricResource($rubric), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/rubrics/{id}",
     *     operationId="getRubric",
     *     tags={"Rubrics"},
     *     summary="Display the specified rubric",
     *     description="Get a specific rubric by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Rubric")
     *     )
     * )
     */
    public function show(Rubric $rubric): \Illuminate\Http\JsonResponse
    {
        return response()->json(new RubricResource($rubric));
    }

    /**
     * @OA\Put(
     *     path="/api/rubrics/{id}",
     *     operationId="updateRubric",
     *     tags={"Rubrics"},
     *     summary="Update the specified rubric",
     *     description="Update a specific rubric by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Rubric")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Rubric")
     *     )
     * )
     */
    public function update(RubricRequest $request, Rubric $rubric): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $rubric->update($data);

        return response()->json(new RubricResource($rubric));
    }

    /**
     * @OA\Delete(
     *     path="/api/rubrics/{id}",
     *     operationId="deleteRubric",
     *     tags={"Rubrics"},
     *     summary="Remove the specified rubric",
     *     description="Delete a specific rubric by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation"
     *     )
     * )
     */
    public function destroy(Rubric $rubric): \Illuminate\Http\JsonResponse
    {
        $rubric->delete();

        return response()->json(null, 204);
    }
}
