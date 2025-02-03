<?php

namespace App\Http\Controllers\Admin\Invokable;

use App\Http\Controllers\Controller;
use App\Models\Admin\Guide\Guide;
use App\Models\Admin\Tutorial\Tutorial;

class RemoveGuideFromTutorialController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Guide $guide, Tutorial $tutorial): \Illuminate\Http\RedirectResponse
    {
        // Удаляем связь статьи с рубрикой
        $guide->tutorials()->detach($tutorial->id);

        return back()->with('success', 'Гайд успешно удален из руководства');
    }
}
