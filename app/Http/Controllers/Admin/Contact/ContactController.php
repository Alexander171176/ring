<?php

namespace App\Http\Controllers\Admin\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Contact\ContactRequest;
use App\Http\Resources\Admin\Contact\ContactResource;
use App\Models\Admin\Contact\Contact;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

        $contacts = Cache::store('redis')->remember('contacts.all', $cacheTime, function () {
            return Contact::all();
        });

        return Inertia::render('Admin/Contacts/Index', [
            'contacts' => ContactResource::collection($contacts),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $contact = Cache::store('redis')->remember("contact.$id", $cacheTime, function () use ($id) {
            $contact = Contact::findOrFail($id);

            // Проверка, есть ли изображение, и формирование правильного пути к нему
            if ($contact->image_url) {
                $contact->image_url = Storage::url($contact->image_url);
            }

            return $contact;
        });

        return Inertia::render('Admin/Contacts/Edit', [
            'contact' => new ContactResource($contact),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContactRequest $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $contact = Contact::findOrFail($id);

        $data = $request->validated();

        // Удаляем изображение, если это указано в запросе
        if ($request->input('remove_image') == true) {
            if ($contact->image) {
                Storage::disk('public')->delete($contact->image); // Удаляем старое изображение
            }
            $data['image'] = ''; // Устанавливаем пустую строку вместо null
        }

        // Проверка на новое загруженное изображение
        if ($request->hasFile('image')) {
            if ($contact->image) {
                Storage::disk('public')->delete($contact->image); // Удаляем старое изображение
            }
            $data['image'] = $request->file('image')->store('contacts_images', 'public'); // Сохраняем новое изображение
        }

        // Обновляем запись
        $contact->update($data);

        // Очищаем кэш
        $this->clearCache(['contacts.all', "contact.$id"]);

        return redirect()->route('contacts.index')->with('success', 'Контакт успешно обновлен');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

}
