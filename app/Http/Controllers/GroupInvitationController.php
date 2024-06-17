<?php

namespace App\Http\Controllers;

use App\Models\GroupInvitation;
use Illuminate\Http\Request;

class GroupInvitationController extends Controller
{
    public function invitations(Request $request)
    {
        $invitations = $request->user()->groupInvitations()->where('status', 'pending')->with('group')->get();
        if($invitations->isEmpty()) return response()->json(['message' => 'No tienes invitaciones pendientes'], 404);
        return response()->json(['invitations' => $invitations], 200);
    }
    
    public function response(Request $request)
    {
        $request->validate([
            'invitation_id' => 'required|exists:group_invitations,id',
            'response' => 'required|in:accepted,rejected',
        ]);

        $groupInvitation = GroupInvitation::find($request->invitation_id);
        if(!$groupInvitation->isPending()) return response()->json(['message' => 'Invitación ya fue aceptada o rechazada'], 400);
        if($groupInvitation->user_id !== $request->user()->id) return response()->json(['message' => 'No tienes permiso para aceptar o rechazar esta invitación'], 403);

        $groupInvitation->status = $request->response;
        $groupInvitation->save();

        if($request->response === 'accepted') $groupInvitation->group->users()->attach($groupInvitation->user_id);

        return response()->json(['message' => 'Invitación '. $groupInvitation->status], 200);
    }
}
