<?php

namespace App\Http\Controllers\Api\Tutorial;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tutorial\TutorialRequest;
use App\Http\Resources\Admin\Tutorial\TutorialResource;
use App\Models\Admin\Tutorial\Tutorial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

/**
 * @OA\Info(
 *     title="Tutorial API",
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
 *     schema="Tutorial",
 *     type="object",
 *     title="Tutorial",
 *     description="Tutorial model",
 *     properties={
 *         @OA\Property(property="id", type="integer", description="Tutorial ID"),
 *         @OA\Property(property="title", type="string", description="Tutorial title"),
 *         @OA\Property(property="url", type="string", description="Tutorial URL"),
 *         @OA\Property(property="image_url", type="string", description="Tutorial image URL"),
 *         @OA\Property(property="description", type="string", description="Tutorial description"),
 *         @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", description="Last update date")
 *     }
 * )
 */
class ApiTutorialController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tutorials",
     *     operationId="getTutorials",
     *     tags={"Tutorials"},
     *     summary="Get list of tutorials",
     *     description="Returns list of all tutorials",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Tutorial")
     *         )
     *     )
     * )
     */
    public function index(): \Inertia\Response
    {
        $tutorials = Tutorial::all();
        $tutorialsCount = Tutorial::count();

        return Inertia::render('Admin/Tutorials/Index', [
            'tutorials' => TutorialResource::collection($tutorials),
            'tutorialsCount' => $tutorialsCount,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/tutorials",
     *     operationId="createTutorial",
     *     tags={"Tutorials"},
     *     summary="Create a new tutorial",
     *     description="Creates a new tutorial",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Tutorial")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tutorial created successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    public function store(TutorialRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image_url')) {
            $data['image_url'] = $request->file('image_url')->store('tutorial_images', 'public');
        }

        $tutorial = Tutorial::create($data);

        // Log::info('API - Tutorial created: ', $tutorial->toArray());

        return redirect()->route('tutorials.index')->with('success', 'Tutorial created successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/tutorials/{id}",
     *     operationId="getTutorialById",
     *     tags={"Tutorials"},
     *     summary="Get tutorial by ID",
     *     description="Returns a single tutorial",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Tutorial")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tutorial not found"
     *     )
     * )
     */
    public function edit(string $id): \Inertia\Response
    {
        $tutorial = Tutorial::findOrFail($id);

        if ($tutorial->image_url) {
            $tutorial->image_url = Storage::url($tutorial->image_url);
        }

        return Inertia::render('Admin/Tutorials/Edit', [
            'tutorial' => new TutorialResource($tutorial),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/tutorials/{id}",
     *     operationId="updateTutorial",
     *     tags={"Tutorials"},
     *     summary="Update an existing tutorial",
     *     description="Updates a tutorial",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Tutorial")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tutorial updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tutorial not found"
     *     )
     * )
     */
    public function update(TutorialRequest $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $tutorial = Tutorial::findOrFail($id);

        $data = $request->validated();

        if ($request->hasFile('image_url')) {
            if ($tutorial->image_url) {
                Storage::disk('public')->delete($tutorial->image_url);
            }
            $data['image_url'] = $request->file('image_url')->store('tutorial_images', 'public');
        } else {
            $data['image_url'] = $tutorial->image_url;
        }

        $tutorial->update($data);

        // Log::info('API - Tutorial updated: ', $tutorial->toArray());

        return redirect()->route('tutorials.index')->with('success', 'Tutorial updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/tutorials/{id}",
     *     operationId="deleteTutorial",
     *     tags={"Tutorials"},
     *     summary="Delete a tutorial",
     *     description="Deletes a tutorial",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Tutorial deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tutorial not found"
     *     )
     * )
     */
    public function destroy(string $id): \Illuminate\Http\RedirectResponse
    {
        $tutorial = Tutorial::findOrFail($id);

        if ($tutorial->image_url) {
            Storage::disk('public')->delete($tutorial->image_url);
        }
        $tutorial->delete();

        // Log::info('API - Tutorial deleted: ', $tutorial->toArray());

        return back();
    }

    /**
     * @OA\Post(
     *     path="/api/tutorials/bulk-destroy",
     *     operationId="bulkDestroyTutorials",
     *     tags={"Tutorials"},
     *     summary="Bulk delete tutorials",
     *     description="Deletes multiple tutorials by IDs",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tutorials deleted successfully"
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
            'ids.*' => 'exists:tutorials,id',
        ]);

        $tutorialIds = $validated['ids'];

        Tutorial::whereIn('id', $tutorialIds)->each(function ($tutorial) {
            if ($tutorial->image_url) {
                Storage::disk('public')->delete($tutorial->image_url);
            }
            $tutorial->delete();
        });

        // Log::info('API - Tutorials deleted: ', $tutorialIds);

        return response()->json(['success' => true]);
    }

    /**
     * @OA\Patch(
     *     path="/api/tutorials/{id}/activity",
     *     operationId="updateTutorialActivity",
     *     tags={"Tutorials"},
     *     summary="Update tutorial activity",
     *     description="Updates the activity status of a tutorial",
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
     *             @OA\Property(property="activity", type="boolean", description="Tutorial activity status")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tutorial activity updated successfully"
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

        $tutorial = Tutorial::findOrFail($id);
        $tutorial->activity = $validated['activity'];
        $tutorial->save();

        // Log::info("API - Tutorial activity updated for ID: $id", $validated);

        return response()->json(['success' => true]);
    }

    /**
     * @OA\Patch(
     *     path="/api/tutorials/{id}/sort",
     *     operationId="updateTutorialSort",
     *     tags={"Tutorials"},
     *     summary="Update tutorial sort order",
     *     description="Updates the sort order of a tutorial",
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
     *         description="Tutorial sort order updated successfully"
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

        $tutorial = Tutorial::findOrFail($id);
        $tutorial->sort = $validated['sort'];
        $tutorial->save();

        // Log::info("API - Tutorial sort order updated for ID: $id", $validated);

        return response()->json(['success' => true]);
    }

    /**
     * @OA\Post(
     *     path="/api/tutorials/{id}/clone",
     *     operationId="cloneTutorial",
     *     tags={"Tutorials"},
     *     summary="Clone a tutorial",
     *     description="Clones an existing tutorial",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tutorial cloned successfully"
     *     )
     * )
     */
    public function clone(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        $tutorial = Tutorial::findOrFail($id);

        $clonedTutorial = $tutorial->replicate();
        $clonedTutorial->title = $tutorial->title . ' 2';
        $clonedTutorial->url = $tutorial->url . '-2';

        if ($tutorial->image_url) {
            $clonedTutorial->image_url = $tutorial->image_url;
        }

        $clonedTutorial->save();

        // Log::info('API - Tutorial cloned: ', $clonedTutorial->toArray());

        return redirect()->route('tutorials.index')->with('success', 'Tutorial cloned successfully');
    }
}
