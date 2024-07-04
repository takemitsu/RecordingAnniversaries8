<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class EntitiesController extends Controller
{
    public function pickup()
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

    public function index()
    {
        $entities = Entity::where('user_id', auth()->user()->id)
            ->with('days')
            ->orderBy('created_at', 'asc')
            ->get();

        return Inertia::render('Entities', [
            'entities' => $entities,
        ]);
    }

    public function create()
    {
        return Inertia::render('Entity', [
            'entityData' => null,
            'status' => session('status'),
        ]);
    }


    public function store(Request $request)
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


    public function show(Entity $entity)
    {
        return $entity->load('days');
    }


    public function edit(Entity $entity)
    {
        return Inertia::render('Entity', [
            'entityData' => $entity,
            'status' => session('status'),
        ]);
    }


    public function update(Request $request, Entity $entity)
    {
        // フォームリクエスト 作ってもいいけどコレで
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string'
        ]);

        $entity->name = $validatedData['name'];
        $entity->desc = $validatedData['desc'] ?? null;
        $entity->save();

        return redirect()->route('entities.index');
    }


    public function destroy(Entity $entity)
    {
        $entity->delete();

        return redirect()->route('entities.index');
    }
}
