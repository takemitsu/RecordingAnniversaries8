<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class EntitiesController extends Controller
{
    public function pickup(): \Inertia\Response
    {
        $entities = Entity::where('user_id', auth()->user()->id)
            ->has('days')
            ->with('days')
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($entities as $key => $entity) {
            if (count($entity->days) == 0) {
                unset ($entities[$key]);
            } else {
                // diff_days で sort
                $sorted = array_values(Arr::sort($entity->days, function ($value) {
                    return $value['diff_days'];
                }));
                unset($entities[$key]->days);
                $entities[$key]->days = $sorted;
            }
        }

        return Inertia::render('Dashboard', [
            'entities' => $entities,
        ]);
    }

    public function index(): \Inertia\Response
    {
        $entities = Entity::where('user_id', auth()->user()->id)
            ->with('days')
            ->orderBy('created_at', 'asc')
            ->get();

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

        $entity = new Entity();
        $entity->user_id = auth()->user()->id;
        $entity->name = $validatedData['name'];
        $entity->desc = $validatedData['desc'] ?? null;
        $entity->save();

        return redirect()->route('entities.index');
    }


    public function show(Entity $entity): Entity
    {
        return $entity->load('days');
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

        $entity->name = $validatedData['name'];
        $entity->desc = $validatedData['desc'] ?? null;
        $entity->status = $validatedData['status'] ?? true;
        $entity->save();

        return redirect()->route('entities.index');
    }


    public function destroy(Entity $entity): \Illuminate\Http\RedirectResponse
    {
        $entity->delete();

        return redirect()->route('entities.index');
    }
}
