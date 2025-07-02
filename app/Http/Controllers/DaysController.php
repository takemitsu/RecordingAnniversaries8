<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\Entity;
use App\Services\DayService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DaysController extends Controller
{
    private DayService $dayService;

    public function __construct(DayService $dayService)
    {
        $this->dayService = $dayService;
    }
    public function index(Entity $entity)
    {
        return $this->dayService->getByEntity($entity);
    }

    public function create(Entity $entity): \Inertia\Response
    {
        return Inertia::render('EditAnniversaryDay', [
            'entityData' => $entity,
            'dayData' => null,
            'status' => session('status'),
        ]);
    }

    public function store(Request $request, Entity $entity): \Illuminate\Http\RedirectResponse
    {
        // フォームリクエスト 作ってもいいけどコレで
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'anniv_at' => 'required|date_format:Y-m-d',
        ]);

        $this->dayService->create($entity, $validatedData);

        return redirect()->route('entities.index');
    }

    public function show(Entity $entity, Day $day): Day
    {
        return $this->dayService->get($entity, $day);
    }

    public function edit(Request $request, Entity $entity, Day $day): \Inertia\Response
    {
        return Inertia::render('EditAnniversaryDay', [
            'entityData' => $entity,
            'dayData' => $day,
            'status' => session('status'),
        ]);
    }

    public function update(Request $request, Entity $entity, Day $day): \Illuminate\Http\RedirectResponse
    {
        // フォームリクエスト 作ってもいいけどコレで
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'anniv_at' => 'required|date_format:Y-m-d',
        ]);

        $this->dayService->update($entity, $day, $validatedData);

        return redirect()->route('entities.index');
    }

    public function destroy(Entity $entity, Day $day): \Illuminate\Http\RedirectResponse
    {
        $this->dayService->delete($entity, $day);

        return redirect()->route('entities.index');
    }
}
