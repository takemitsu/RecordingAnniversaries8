<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\Entity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DaysController extends Controller
{
    public function index(Entity $entity)
    {
        if ($entity->user->id != auth()->user()->id) {
            abort(404, 'Not Found Entity');
        }

        return $entity->days;
    }

    public function create(Entity $entity)
    {
        return Inertia::render('Anniv', [
            'entityData' => $entity,
            'dayData' => null,
            'status' => session('status'),
        ]);
    }

    public function store(Request $request, Entity $entity)
    {
        if ($entity->user->id != auth()->user()->id) {
            abort(404, 'Not Found Entity');
        }

        // フォームリクエスト 作ってもいいけどコレで
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'anniv_at' => 'required|date_format:Y-m-d',
        ]);

        $day = new Day();
        $day->entity_id = $entity->id;
        $day->name = $validatedData['name'];
        $day->desc = $validatedData['desc'] ?? null;
        $day->anniv_at = $validatedData['anniv_at'];
        $day->save();

        return redirect()->route('entities.index');
    }

    public function show(Entity $entity, Day $day)
    {
        if ($entity->user->id != auth()->user()->id) {
            abort(404, 'Not Found Entity');
        }

        return $day;
    }

    public function edit(Request $request, Entity $entity, Day $day)
    {
        return Inertia::render('Anniv', [
            'entityData' => $entity,
            'dayData' => $day,
            'status' => session('status'),
        ]);
    }

    public function update(Request $request, Entity $entity, Day $day)
    {
        if ($entity->user->id != auth()->user()->id) {
            abort(404, 'Not Found Entity');
        }

        // フォームリクエスト 作ってもいいけどコレで
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'anniv_at' => 'required|date_format:Y-m-d',
        ]);

        $day->name = $validatedData['name'];
        $day->desc = $validatedData['desc'] ?? null;
        $day->anniv_at = $validatedData['anniv_at'];
        $day->save();

        return redirect()->route('entities.index');
    }

    public function destroy(Entity $entity, Day $day)
    {
        if ($entity->user->id != auth()->user()->id) {
            abort(404, 'Not Found Entity');
        }

        $day->delete();

        return redirect()->route('entities.index');
    }
}
