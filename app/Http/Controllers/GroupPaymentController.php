<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupPayment;
use App\Models\User;
use Illuminate\Http\Request;

class GroupPaymentController extends Controller
{
    public function recordPayment(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payer_id'  => 'exists:users,id',
            'recipient_id' => 'required|exists:users,id',
        ]);

        $group = Group::find($id);
        if(!$group) return response()->json(['message' => 'Grupo no encontrado'], 404);

        $user = Group::find($id)->users()->where('user_id', $request->user()->id)->first();
        if(!$user) return response()->json(['message' => 'El usuario no forma parte del grupo'], 404);

        $payer = null;
        if($request->has('payer_id')){
            $payer = Group::find($id)->users()->where('user_id', $request->payer_id)->first();
            if(!$payer) return response()->json(['message' => 'El remitente del pago no forma parte del grupo'], 404);
        } else {
            $payer = $user;
        }

        $recipient = Group::find($id)->users()->where('user_id', $request->recipient_id)->first();
        if(!$recipient) return response()->json(['message' => 'El destinatario del pago no forma parte del grupo'], 404);

        $amountPaid = $request->amount;
        if($payer->pivot->balance >= 0) return response()->json(['message' => 'No se puede pagar sin tener deuda'], 400);
        if($amountPaid > (-$payer->pivot->balance)) return response()->json(['message' => 'El remitente no tiene suficiente saldo'], 400);


        $group->groupPayments()->create([
            'payer_id' => $payer->id,
            'recipient_id' => $recipient->id,
            'amount' => $amountPaid,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Pago registrado correctamente'], 200);
    }
    
    private function updateBalances($payer_id, $recipient_id, $amountPaid)
    {
        
        $payer = User::find($payer_id)->groups()->where('user_id', $payer_id)->first();
        $recipient = User::find($recipient_id)->groups()->where('user_id', $recipient_id)->first();
        
        $payer->pivot->balance += $amountPaid;
        $recipient->pivot->balance -= $amountPaid;
        $payer->pivot->save();
        $recipient->pivot->save();
    }
    
    public function pendingPayments(Request $request)
    {
        $request->validate([
            'group_id' => 'exists:groups,id',
        ]);

        $payments = $request->user()->groupPayments()->where('status', 'pending')->with('group');
        if($request->has('group_id')) $payments->where('group_id', $request->group_id);
        $payments = $payments->get();
        if($payments->isEmpty()) return response()->json(['message' => 'No tienes confirmación de pagos pendientes'], 404);
        return response()->json(['payments' => $payments], 200);
    }
    
    public function response(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:group_payments,id',
            'response' => 'required|in:accepted,rejected',
        ]);

        $groupPayment = GroupPayment::find($request->payment_id);
        if(!$groupPayment->isPending()) return response()->json(['message' => 'El pago ya fue aceptado o rechazado'], 400);
        if($groupPayment->recipient_id !== $request->user()->id) return response()->json(['message' => 'No tienes permiso para aceptar o rechazar este pago'], 403);

        $groupPayment->status = $request->response;
        $groupPayment->save();

        if($request->response === 'accepted') {
            $this->updateBalances($groupPayment->payer_id, $groupPayment->recipient_id, $groupPayment->amount);
        }

        return response()->json(['message' => 'Pago '. $groupPayment->status], 200);
    }
}
