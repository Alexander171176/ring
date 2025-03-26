<?php

namespace App\Http\Controllers\Admin\Invokable;

use App\Http\Controllers\Controller;
use App\Models\Admin\Rubric\Rubric;
use App\Models\Admin\Section\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RemoveRubricFromSectionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Section $section, Rubric $rubric): RedirectResponse
    {
        // Удаляем связь секции с рубрикой
        $section->rubrics()->detach($rubric->id);

        return back()->with('success', 'Секция успешно удалена из рубрики');
    }
}
