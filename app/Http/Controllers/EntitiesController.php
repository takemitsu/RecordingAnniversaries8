<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntityRequest;
use App\Http\Requests\UpdateEntityRequest;
use App\Http\Resources\EntityResource;
use App\Models\Entity;
use App\Services\EntityService;
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
            'entities' => EntityResource::collection($entities),
        ]);
    }

    public function index(): \Inertia\Response
    {
        $entities = $this->entityService->getAllForUser();

        return Inertia::render('Entities', [
            'entities' => EntityResource::collection($entities),
        ]);
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('EditEntity', [
            'entityData' => null,
            'status' => session('status'),
        ]);
    }

    public function store(StoreEntityRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->entityService->create($request->validated());

        return redirect()->route('entities.index');
    }

    public function show(Entity $entity)
    {
        $this->authorize('view', $entity);
        $entity = $this->entityService->getWithDays($entity);

        return new EntityResource($entity);
    }

    public function edit(Entity $entity): \Inertia\Response
    {
        $this->authorize('update', $entity);

        return Inertia::render('EditEntity', [
            'entityData' => $entity,
            'status' => session('status'),
        ]);
    }

    public function update(UpdateEntityRequest $request, Entity $entity): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $entity);
        $this->entityService->update($entity, $request->validated());

        return redirect()->route('entities.index');
    }

    public function destroy(Entity $entity): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $entity);
        $this->entityService->delete($entity);

        return redirect()->route('entities.index');
    }
}
