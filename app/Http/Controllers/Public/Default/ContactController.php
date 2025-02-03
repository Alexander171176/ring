<?php

namespace App\Http\Controllers\Public\Default;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Contact\ContactResource;
use App\Http\Resources\Admin\Page\PageResource;
use App\Models\Admin\Contact\Contact;
use App\Models\Admin\Page\Page;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ContactController extends Controller
{
    use CacheTimeTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $contactsPage = Cache::store('redis')->remember('pages.contacts', $cacheTime, function () {
            return Page::where('url', 'contacts')->first();
        });

        $contacts = Cache::store('redis')->remember('contacts.all', $cacheTime, function () {
            return Contact::all();
        });

        return Inertia::render('Templates/Default/pages/Contacts', [
            'page' => $contactsPage ? new PageResource($contactsPage) : null, // Передаём страницу contacts, если она существует
            'contacts' => ContactResource::collection($contacts),
        ]);
    }

}
