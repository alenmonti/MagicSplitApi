<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use App\Models\FictitiousUser;
use App\DTOs\Balance;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);
        $user = $request->user();

        $group = new Group;
        $group->name = $request->name;
        $group->admin_id = $user->id;
        $group->description = $request->description;
        $group->save();
        $group->users()->attach($user->id, ['admin' => true]);

        return response()->json(['message' => 'Grupo creado exitosamente', 'group' => $group], 201);
    }

    public function addMember(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        $group = Group::find($id);
        if(!$group) return response()->json(['message' => 'Grupo no encontrado'], 404);

        $userToAdd = User::where('email', $request->email)->first();
        if($group->users->contains($userToAdd)) return response()->json(['message' => 'El usuario ya forma parte del grupo'], 403);
        if($group->groupInvitations()->where('user_id', $userToAdd->id)->where('status', 'pending')->exists()) return response()->json(['message' => 'El usuario ya tiene una invitación pendiente'], 403);
        $group->groupInvitations()->create([
            'user_id' => $userToAdd->id,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Invitación enviada correctamente'], 200);
    }

    public function members(Request $request, $id)
    {
        $group = Group::find($id);
        if(!$group) return response()->json(['message' => 'Grupo no encontrado'], 404);

        return response()->json(['members' => $group->users], 200);
    }

    public function admins(Request $request, $id)
    {
        $group = Group::find($id);
        if(!$group) return response()->json(['message' => 'Grupo no encontrado'], 404);

        $admins = $group->users()->wherePivot('admin', true)->get();
    
        return response()->json(['admins' => $admins], 200);
    }

    public function editGroupName(Request $request, $id)
    {
        $request->validate(['name' => 'required|max:100']);
        $group = Group::find($id);
        if(!$group) return response()->json(['message' => 'Grupo no encontrado'], 404);

        $group->name = $request->name;
        $group->description = $request->description;
        $group->save();

        return response()->json(['message' => 'Nombre del grupo actualizado exitosamente', 'group' => $group], 200);
    }

    public function balances(Request $request, $id)
    {
        $group = Group::find($id);
        if(!$group) return response()->json(['message' => 'Grupo no encontrado'], 404);

        $balances = [];
        foreach($group->users as $user) {
            $balances[] = new Balance($user->id, $user->name, $user->pivot->balance);
        }

        return response()->json(['balances' => $balances], 200);
    }

    public function leave(Request $request, $id)
    {
        $group = Group::find($id);
        if(!$group) return response()->json(['message' => 'Grupo no encontrado'], 404);

        $user = Group::find($id)->users()->where('user_id', $request->user()->id)->first();
        if(!$user) return response()->json(['message' => 'El ususario no forma parte del grupo'], 404);
        if($group->admin_id == $user->id) return response()->json(['message' => 'El administrador no puede abandonar el grupo'], 403);
        if($user->pivot->balance != 0) return response()->json(['message' => 'El usuario no puede abandonar el grupo con deudas pendientes'], 403);

        $group->users()->detach($user->id);

        return response()->json(['message' => 'El usuario '. $user->name .' dejó el grupo'], 200);
    }
    
    
    public function addFictitiousMember(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);

        $group = Group::find($id);
        if (!$group) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }
        $fictitiousUser = FictitiousUser::create([
            'name' => $request->name,
            'email' =>  Str::uuid() . '@fictitious.com',
            'password' => bcrypt('password'),
        ]);

        $group->users()->attach($fictitiousUser->id);
        return response()->json(['message' => 'Persona ficticia agregada al grupo'], 200);
    }

    public function addAdmin(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        $group = Group::find($id);
        if(!$group) return response()->json(['message' => 'Grupo no encontrado'], 404);

        $userToAdd = $group->users()->where('user_id', $request->user_id)->first();
        if(!$userToAdd) return response()->json(['message' => 'Usuario no encontrado'], 404);
        if($group->users->contains($userToAdd)) {
            $userToAdd->pivot->admin = true;
            $userToAdd->pivot->save();
            return response()->json(['message' => 'Usuario promovido a administrador exitosamente'], 200);
        } else {
            return response()->json(['message' => 'El usuario no forma parte del grupo'], 403);
        }
    }

    public function removeAdmin(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        $group = Group::find($id);
        if(!$group) return response()->json(['message' => 'Grupo no encontrado'], 404);

        $userToRemove = $group->users()->where('user_id', $request->user_id)->first();
        if(!$userToRemove) return response()->json(['message' => 'Usuario no encontrado'], 404);
        if($userToRemove->pivot->admin) {
            $userToRemove->pivot->admin = false;
            $userToRemove->pivot->save();
            return response()->json(['message' => 'Usuario removido de administradores exitosamente'], 200);
        } else {
            return response()->json(['message' => 'El usuario no es administrador del grupo'], 403);
        }
    }
}

