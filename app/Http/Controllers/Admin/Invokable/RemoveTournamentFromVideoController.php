<?php

namespace App\Http\Controllers\Admin\Invokable;

use App\Http\Controllers\Controller;
use App\Models\Admin\Tournament\Tournament;
use App\Models\Admin\Video\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class RemoveTournamentFromVideoController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Tournament $tournament, Video $video): RedirectResponse
    {
        // TODO: Реализовать проверку прав доступа. Примеры:
        // $this->authorize('update', $tournament);
        // $this->authorize('update', $tournament);
        // $this->authorize('manage tournament relationships');
//        if (!auth()->user()?->can('manage content')) { // Пример
//            abort(403, 'У вас нет прав для изменения связей видео и секций.');
//        }

        try {
            // Выполняем отсоединение.
            // Вариант 1: Отсоединяем видео от турнира
            $detached = $tournament->videos()->detach($video->id);
            // Вариант 2: Отсоединяем турнир от видео
            // $detached = $video->tournaments()->detach($tournament->id);

            if ($detached) {
                Log::info('Видео успешно удалено из турнира', [
                    'tournament_id' => $tournament->id,
                    'tournament_name' => $tournament->name,
                    'video_id' => $video->id,
                    'video_title' => $video->title,
                    'user_id' => auth()->id()
                ]);
                return back()->with('success', "Видео '{$video->title}' успешно отсоединено от турнира '{$tournament->name}'.");
            } else {
                Log::warning('Попытался отделить видео от турнира, но связи не было.', [
                    'tournament_id' => $tournament->id,
                    'tournament_name' => $tournament->name,
                    'video_id' => $video->id,
                    'video_title' => $video->title,
                    'user_id' => auth()->id()
                ]);
                return back()->with('info', "Видео '{$video->title}' уже было отсоединено от турнира '{$tournament->name}'.");
            }

        } catch (Throwable $e) {
            Log::error("Ошибка при отсоединении видео {$video->id} от турнира {$tournament->id}: " . $e->getMessage(), [
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'Произошла ошибка при отсоединении видео от турнира.');
        }
    }
}
