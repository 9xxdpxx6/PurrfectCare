<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Models\Breed;
use App\Models\Species;
use App\Notifications\ClientPetAddedNotification;
use App\Http\Requests\Client\Pet\StoreRequest as StorePetRequest;
use App\Http\Requests\Client\Pet\UpdateRequest as UpdatePetRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Список питомцев
     */
    public function index(Request $request): View
    {
        $query = Pet::where('client_id', Auth::id())
            ->with(['breed', 'species']);

        // Поиск по имени питомца
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $pets = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('client.profile.pets.index', compact('pets'));
    }

    /**
     * Форма добавления питомца
     */
    public function create(): View
    {
        $breeds = Breed::with('species')->orderBy('name')->get();
        $species = Species::orderBy('name')->get();
        
        return view('client.profile.pets.create', compact('breeds', 'species'));
    }

    /**
     * Сохранение нового питомца
     */
    public function store(StorePetRequest $request): RedirectResponse
    {

        $data = $request->only([
            'name', 'species_id', 'breed_id', 'birthdate', 
            'gender', 'weight', 'color', 'description'
        ]);
        $data['client_id'] = Auth::id();

        // Обработка загрузки фото
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $filename = time() . '_' . $photo->getClientOriginalName();
            $path = $photo->storeAs('pets', $filename, 'public');
            $data['photo'] = $path;
        }

        $pet = Pet::create($data);

        // Отправляем уведомление клиенту
        Auth::user()->notify(new ClientPetAddedNotification($pet));

        return redirect()->route('client.profile.pets')
            ->with('success', 'Питомец успешно добавлен!');
    }

    /**
     * Форма редактирования питомца
     */
    public function edit(Pet $pet): View
    {
        // Проверяем, что питомец принадлежит текущему пользователю
        if ($pet->client_id !== Auth::id()) {
            abort(403, 'Доступ запрещен');
        }

        $breeds = Breed::with('species')->orderBy('name')->get();
        $species = Species::orderBy('name')->get();
        
        return view('client.profile.pets.edit', compact('pet', 'breeds', 'species'));
    }

    /**
     * Обновление питомца
     */
    public function update(UpdatePetRequest $request, Pet $pet): RedirectResponse
    {
        // Проверяем, что питомец принадлежит текущему пользователю
        if ($pet->client_id !== Auth::id()) {
            abort(403, 'Доступ запрещен');
        }

        $data = $request->only([
            'name', 'species_id', 'breed_id', 'birthdate', 
            'gender', 'weight', 'color', 'description'
        ]);

        // Обработка загрузки нового фото
        if ($request->hasFile('photo')) {
            // Удаляем старое фото
            if ($pet->photo) {
                Storage::disk('public')->delete($pet->photo);
            }
            
            $photo = $request->file('photo');
            $filename = time() . '_' . $photo->getClientOriginalName();
            $path = $photo->storeAs('pets', $filename, 'public');
            $data['photo'] = $path;
        }

        $pet->update($data);

        return redirect()->route('client.profile.pets')
            ->with('success', 'Информация о питомце обновлена!');
    }

    /**
     * Удаление питомца
     */
    public function destroy(Pet $pet): RedirectResponse
    {
        // Проверяем, что питомец принадлежит текущему пользователю
        if ($pet->client_id !== Auth::id()) {
            abort(403, 'Доступ запрещен');
        }

        // Проверяем, что у питомца нет записей на прием
        if ($pet->visits()->exists()) {
            return back()->withErrors(['error' => 'Нельзя удалить питомца, у которого есть записи на прием.']);
        }

        // Удаляем фото
        if ($pet->photo) {
            Storage::disk('public')->delete($pet->photo);
        }

        $pet->delete();

        return redirect()->route('client.profile.pets')
            ->with('success', 'Питомец успешно удален!');
    }

    /**
     * Получение пород по виду
     */
    public function getBreedsBySpecies(Request $request)
    {
        $breeds = Breed::where('species_id', $request->species_id)
            ->orderBy('name')
            ->get();
        
        return response()->json($breeds);
    }
}
