<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Group;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function addCategory(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255',
            'users' => 'array',
        ]);
        $group = Group::find($id);
        if(!$group) return response()->json(['message' => 'Grupo no encontrado'], 404);

        // Create category
        $category = $group->categories()->create([
            'name' => $request->name,
        ]);
        // Attach all group members to the categorym, if users are specified, attach only those
        foreach ($group->users as $user) {
            if (!$request->users) {
                $category->users()->attach($user->id);
            } else if (in_array($user->id, $request->users)) {
                $category->users()->attach($user->id);
            }
        }
        
        return response()->json(['message' => 'Categoría creada exitosamente', 'category' => $category], 201);
    }

    public function categories(Request $request, $id)
    {
        $group = Group::find($id);
        if(!$group) return response()->json(['message' => 'Grupo no encontrado'], 404);

        return response()->json(['categories' => $group->categories], 200);
    }

    public function members(Request $request, $id)
    {
        $category = Category::find($id);
        if(!$category) return response()->json(['message' => 'Categoria no encontrada'], 404);

        return response()->json(['members' => $category->users], 200);
    }

    public function addMember(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        $category = Category::find($id);
        if(!$category) return response()->json(['message' => 'Categoria no encontrada'], 404);
        if($category->users->contains($request->user_id)) return response()->json(['message' => 'El usuario ya forma parte de la categoría'], 403);
        if(!$category->group->users->contains($request->user_id)) return response()->json(['message' => 'El usuario no forma parte del grupo'], 403);

        $category->users()->attach($request->user_id);

        return response()->json(['message' => 'Usuario añadido a la categoría exitosamente'], 200);
    }

    public function removeMember(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        $category = Category::find($id);
        if(!$category) return response()->json(['message' => 'Categoria no encontrada'], 404);
        if(!$category->users->contains($request->user_id)) return response()->json(['message' => 'El usuario no forma parte de la categoría'], 403);

        $category->users()->detach($request->user_id);

        return response()->json(['message' => 'Usuario eliminado de la categoría exitosamente'], 200);
    }

    public function reassignMembers(Request $request, $id)
    {
        $request->validate([
            'users' => 'array',
        ]);
        $category = Category::find($id);
        if(!$category) return response()->json(['message' => 'Categoria no encontrada'], 404);
        $group = $category->group;
        $category->users()->detach();
        // Attach all group members to the categorym, if users are specified, attach only those
        foreach ($group->users as $user) {
            if (!$request->users) {
                $category->users()->attach($user->id);
            } else if (in_array($user->id, $request->users)) {
                $category->users()->attach($user->id);
            }
        }

        return response()->json(['message' => 'Usuarios reasignados exitosamente'], 200);
    }
}
