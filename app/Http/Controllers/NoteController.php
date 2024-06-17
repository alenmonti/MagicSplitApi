<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function create(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|max:255',
        ]);
        $group = Group::find($id);
        if(!$group) return response()->json(['message' => 'Grupo no encontrado'], 404);
        if(!$group->users->contains($request->user())) return response()->json(['message' => 'No puedes crear notas en este grupo'], 403);

        $note = $group->notes()->create([
            'content' => $request->content,
            'user_id' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Nota creada exitosamente', "note" => $note], 201);
    }

    public function edit(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|max:255',
        ]);
        $note = Note::find($id);
        if(!$note) return response()->json(['message' => 'Nota no encontrada'], 404);
        if($note->user_id !== $request->user()->id) return response()->json(['message' => 'No puedes editar esta nota'], 403);

        $note->content = $request->content;
        $note->save();

        return response()->json(['message' => 'Nota editada exitosamente', "note" => $note], 200);
    }

    public function notes(Request $request, $id)
    {
        $group = Group::find($id);
        if(!$group) return response()->json(['message' => 'Grupo no encontrado'], 404);

        $notes = $group->notes()->with('user')->get();

        return response()->json(['notes' => $notes], 200);
    }

    public function delete(Request $request, $id)
    {
        $note = Note::find($id);
        if(!$note) return response()->json(['message' => 'Nota no encontrada'], 404);
        if($note->user_id !== $request->user()->id) return response()->json(['message' => 'No puedes eliminar esta nota'], 403);

        $note->delete();

        return response()->json(['message' => 'Nota eliminada exitosamente']);
    }
}
