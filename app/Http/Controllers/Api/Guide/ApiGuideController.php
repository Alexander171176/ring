<?php

namespace App\Http\Controllers\Api\Guide;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Guide\GuideRequest;
use App\Http\Resources\Admin\Guide\GuideResource;
use App\Models\Admin\Guide\Guide;
use App\Models\Admin\Tutorial\Tutorial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Info(
 *     title="Guide API",
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
 *     schema="Guide",
 *     type="object",
 *     title="Guide",
 *     description="Guide model",
 *     properties={
 *         @OA\Property(property="id", type="integer", description="Guide ID"),
 *         @OA\Property(property="title", type="string", description="Guide title"),
 *         @OA\Property(property="url", type="string", description="Guide URL"),
 *         @OA\Property(property="image_url", type="string", description="Guide image URL"),
 *         @OA\Property(property="description", type="string", description="Guide description"),
 *         @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", description="Last update date")
 *     }
 * )
 */
class ApiGuideController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/guides",
     *     operationId="getGuides",
     *     tags={"Guides"},
     *     summary="Get list of guides",
     *     description="Returns list of all guides",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Guide")
     *         )
     *     )
     * )
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $guides = Guide::with('tutorials')->get();
        $guidesCount = Guide::count();

        return response()->json([
            'guides' => GuideResource::collection($guides),
            'guidesCount' => $guidesCount,
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/guides",
     *     operationId="createGuide",
     *     tags={"Guides"},
     *     summary="Create a new guide",
     *     description="Creates a new guide",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Guide")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Guide created successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    public function store(GuideRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image_url')) {
            $data['image_url'] = $request->file('image_url')->store('guide_images', 'public');
        }

        $tutorialIds = [];
        if ($request->has('tutorials')) {
            $tutorialTitles = array_column($request->input('tutorials'), 'title');
            $tutorialIds = Tutorial::whereIn('title', $tutorialTitles)->pluck('id')->toArray();
        }

        $guide = Guide::create($data);

        if ($tutorialIds) {
            $guide->tutorials()->sync($tutorialIds);
        }

        // Log::info('API - Guide created: ', $guide->toArray());

        return response()->json(new GuideResource($guide), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/guides/{id}",
     *     operationId="getGuideById",
     *     tags={"Guides"},
     *     summary="Get guide by ID",
     *     description="Returns a single guide",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Guide")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Guide not found"
     *     )
     * )
     */
    public function show(string $id): \Illuminate\Http\JsonResponse
    {
        $guide = Guide::with('tutorials')->findOrFail($id);

        if ($guide->image_url) {
            $guide->image_url = Storage::url($guide->image_url);
        }

        return response()->json(new GuideResource($guide), 200);
    }

    /**
     * @OA\Put(
     *     path="/api/guides/{id}",
     *     operationId="updateGuide",
     *     tags={"Guides"},
     *     summary="Update an existing guide",
     *     description="Updates a guide",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Guide")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Guide updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Guide not found"
     *     )
     * )
     */
    public function update(GuideRequest $request, string $id): \Illuminate\Http\JsonResponse
    {
        $guide = Guide::findOrFail($id);

        $data = $request->validated();

        if ($request->hasFile('image_url')) {
            if ($guide->image_url) {
                Storage::disk('public')->delete($guide->image_url);
            }
            $data['image_url'] = $request->file('image_url')->store('guide_images', 'public');
        } else {
            $data['image_url'] = $guide->image_url;
        }

        if ($request->has('tutorials')) {
            $tutorialTitles = array_column($request->input('tutorials'), 'title');
            $tutorialIds = Tutorial::whereIn('title', $tutorialTitles)->pluck('id')->toArray();
            $guide->tutorials()->sync($tutorialIds);
        }

        $guide->update($data);

        // Log::info('API - Guide updated: ', $guide->toArray());

        return response()->json(new GuideResource($guide), 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/guides/{id}",
     *     operationId="deleteGuide",
     *     tags={"Guides"},
     *     summary="Delete a guide",
     *     description="Deletes a guide",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Guide deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Guide not found"
     *     )
     * )
     */
    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        $guide = Guide::findOrFail($id);

        if ($guide->image_url) {
            Storage::disk('public')->delete($guide->image_url);
        }
        $guide->delete();

        // Log::info('API - Guide deleted: ', $guide->toArray());

        return response()->json(null, 204);
    }

    /**
     * @OA\Post(
     *     path="/api/guides/bulk-destroy",
     *     operationId="bulkDestroyGuides",
     *     tags={"Guides"},
     *     summary="Bulk delete guides",
     *     description="Deletes multiple guides by IDs",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Guides deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    public function bulkDestroy(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:guides,id',
        ]);

        $guideIds = $validated['ids'];

        Guide::whereIn('id', $guideIds)->each(function ($guide) {
            if ($guide->image_url) {
                Storage::disk('public')->delete($guide->image_url);
            }
            $guide->delete();
        });

        // Log::info('API - Guides deleted: ', $guideIds);

        return response()->json(['success' => true], 200);
    }

    /**
     * @OA\Patch(
     *     path="/api/guides/{id}/activity",
     *     operationId="updateGuideActivity",
     *     tags={"Guides"},
     *     summary="Update guide activity",
     *     description="Updates the activity status of a guide",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="activity", type="boolean", description="Guide activity status")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Guide activity updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    public function updateActivity(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'activity' => 'required|boolean',
        ]);

        $guide = Guide::findOrFail($id);
        $guide->activity = $validated['activity'];
        $guide->save();

        // Log::info("API - Guide activity updated for ID: $id", $validated);

        return response()->json(['success' => true], 200);
    }

    /**
     * @OA\Patch(
     *     path="/api/guides/{id}/sort",
     *     operationId="updateGuideSort",
     *     tags={"Guides"},
     *     summary="Update guide sort order",
     *     description="Updates the sort order of a guide",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="sort", type="integer", description="Sort order")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Guide sort order updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    public function updateSort(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'sort' => 'required|integer',
        ]);

        $guide = Guide::findOrFail($id);
        $guide->sort = $validated['sort'];
        $guide->save();

        // Log::info("API - Guide sort order updated for ID: $id", $validated);

        return response()->json(['success' => true], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/guides/{id}/clone",
     *     operationId="cloneGuide",
     *     tags={"Guides"},
     *     summary="Clone a guide",
     *     description="Clones an existing guide",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Guide cloned successfully"
     *     )
     * )
     */
    public function clone(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $guide = Guide::findOrFail($id);

        $clonedGuide = $guide->replicate();
        $clonedGuide->title = $guide->title . ' 2';
        $clonedGuide->url = $guide->url . '-2';
        $clonedGuide->save();

        $tutorialIds = $guide->tutorials->pluck('id')->toArray();
        if ($tutorialIds) {
            $clonedGuide->tutorials()->sync($tutorialIds);
        }

        // Log::info('API - Guide cloned: ', $clonedGuide->toArray());

        return response()->json(new GuideResource($clonedGuide), 200);
    }
}
