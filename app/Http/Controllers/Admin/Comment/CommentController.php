<?php

namespace App\Http\Controllers\Admin\Comment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Comment\CommentResource;
use App\Models\Admin\Comment\Comment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class CommentController extends Controller
{
    /**
     * Display a listing of the comments.
     */
    public function index(): Response
    {
        $comments = Comment::with(['user', 'article', 'section'])->get();
        $commentsCount = DB::table('comments')->count();

        return Inertia::render('Admin/Comments/Index', [
            'comments'      => CommentResource::collection($comments),
            'commentsCount' => $commentsCount,
        ]);
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        Log::info('Комментарий удален: ', $comment->toArray());

        return back();
    }

    /**
     * Удаление выбранных комментариев.
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:comments,id',
        ]);

        $commentIds = $validated['ids'];

        Comment::whereIn('id', $commentIds)->each(function ($comment) {
            $comment->delete();
        });

        Log::info('Комментарии удалены: ', $commentIds);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Toggle the activity of the comment.
     * @throws AuthorizationException
     */
    public function updateActivity(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'activity' => 'required|boolean',
        ]);

        $comment = Comment::findOrFail($id);
        $comment->activity = $validated['activity'];
        $comment->save();

        Log::info("Обновлено activity комментария с ID: $id с данными: ", $validated);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Approve the comment.
     *
     * @param $id
     * @return JsonResponse
     */
    public function approve($id): JsonResponse
    {
        $comment = Comment::findOrFail($id);
        $comment->status = true;
        $comment->save();

        return response()->json(['message' => 'Комментарий успешно одобрен']);
    }
}
