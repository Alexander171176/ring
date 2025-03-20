<?php

namespace App\Http\Controllers\Admin\Comment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Comment\CommentResource;
use App\Models\Admin\Comment\Comment;
use App\Traits\CacheTimeTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CommentController extends Controller
{
    use CacheTimeTrait;

    /**
     * Display a listing of the comments.
     */
    public function index(): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $comments = Cache::store('redis')->remember('comments.all', $cacheTime, function () {
            // Подгружаем связанные данные о статье и пользователе
            return Comment::with(['user', 'article', 'section'])->get();
        });

        $commentsCount = Cache::store('redis')->remember('comments.count', $cacheTime, function () {
            return DB::table('comments')->count();
        });

        return Inertia::render('Admin/Comments/Index', [
            'comments' => CommentResource::collection($comments),
            'commentsCount' => $commentsCount,
        ]);
    }

    /**
     * Remove the specified comment from storage.
     * @throws AuthorizationException
     */
    public function destroy(Comment $comment): \Illuminate\Http\RedirectResponse
    {
        // Удаляем комментарий
        $comment->delete();

        // Логируем информацию об удалении
        Log::info('Комментарий удален: ', $comment->toArray());

        // Очищаем кэш
        $this->clearCache(['comments.all', 'comments.count']);

        // Возвращаем пользователя обратно
        return back();
    }

    /**
     * Удаление выбранных комментариев.
     */
    public function bulkDestroy(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:comments,id',
        ]);

        $commentIds = $validated['ids'];

        Comment::whereIn('id', $commentIds)->each(function ($comment) {
            $comment->delete();
        });

        Log::info('Комментарии удалены: ', $commentIds);

        $this->clearCache(['comments.all', 'comments.count']);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Toggle the activity of the comment.
     * @throws AuthorizationException
     */
    public function updateActivity(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'activity' => 'required|boolean',
        ]);

        $comment = Comment::findOrFail($id);
        $comment->activity = $validated['activity'];
        $comment->save();

        Log::info("Обновлено activity комментария с ID: $id с данными: ", $validated);

        $this->clearCache(['comments.all', 'comments.count']);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Approve the comment.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve($id): \Illuminate\Http\JsonResponse
    {
        $comment = Comment::findOrFail($id);
        $comment->status = true;
        $comment->save();

        return response()->json(['message' => 'Комментарий успешно одобрен']);
    }

}
