<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\Entity;
use App\Http\Requests\StoreDayRequest;
use App\Http\Requests\UpdateDayRequest;
use App\Http\Resources\DayResource;
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
        $this->authorize('view', $entity);
        $days = $this->dayService->getByEntity($entity);
        return DayResource::collection($days);
    }

    public function create(Entity $entity): \Inertia\Response
    {
        $this->authorize('createDay', $entity);
        return Inertia::render('EditAnniversaryDay', [
            'entityData' => $entity,
            'dayData' => null,
            'status' => session('status'),
        ]);
    }

    public function store(StoreDayRequest $request, Entity $entity): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('createDay', $entity);
        $this->dayService->create($entity, $request->validated());

        return redirect()->route('entities.index');
    }

    public function show(Entity $entity, Day $day)
    {
        $this->authorize('view', $entity);
        $this->authorize('view', $day);
        $day = $this->dayService->get($entity, $day);
        return new DayResource($day);
    }

    public function edit(Request $request, Entity $entity, Day $day): \Inertia\Response
    {
        $this->authorize('update', $entity);
        $this->authorize('update', $day);
        return Inertia::render('EditAnniversaryDay', [
            'entityData' => $entity,
            'dayData' => $day,
            'status' => session('status'),
        ]);
    }

    public function update(UpdateDayRequest $request, Entity $entity, Day $day): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $entity);
        $this->authorize('update', $day);
        $this->dayService->update($entity, $day, $request->validated());

        return redirect()->route('entities.index');
    }

    public function destroy(Entity $entity, Day $day): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $entity);
        $this->authorize('delete', $day);
        $this->dayService->delete($entity, $day);

        return redirect()->route('entities.index');
    }
}
