<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Services\EntityService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class EntitiesController extends Controller
{
    private EntityService $entityService;

    public function __construct(EntityService $entityService)
    {
        $this->entityService = $entityService;
    }
    public function pickup(): \Inertia\Response
    {
        $entities = $this->entityService->getEntitiesForPickup();

        return Inertia::render('Dashboard', [
            'entities' => $entities,
        ]);
    }

    public function index(): \Inertia\Response
    {
        $entities = $this->entityService->getAllForUser();

        return Inertia::render('Entities', [
            'entities' => $entities,
        ]);
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('EditEntity', [
            'entityData' => null,
            'status' => session('status'),
        ]);
    }


    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        // フォームリクエスト 作ってもいいけどコレで
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string'
        ]);

        $this->entityService->create($validatedData);

        return redirect()->route('entities.index');
    }


    public function show(Entity $entity): Entity
    {
        return $this->entityService->getWithDays($entity);
    }


    public function edit(Entity $entity): \Inertia\Response
    {
        return Inertia::render('EditEntity', [
            'entityData' => $entity,
            'status' => session('status'),
        ]);
    }


    public function update(Request $request, Entity $entity): \Illuminate\Http\RedirectResponse
    {
        // フォームリクエスト 作ってもいいけどコレで
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $this->entityService->update($entity, $validatedData);

        return redirect()->route('entities.index');
    }


    public function destroy(Entity $entity): \Illuminate\Http\RedirectResponse
    {
        $this->entityService->delete($entity);

        return redirect()->route('entities.index');
    }
}
