<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RecordController extends Controller
{
    public function allExpenses(Request $request)
    {
        $userExpenses = $request->user()->expenses()
            ->join('groups', 'expenses.group_id', '=', 'groups.id')
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->join('users', 'expenses.user_id', '=', 'users.id')
            ->select('expenses.amount', 'expenses.name', 'expenses.split_type', 'expenses.created_at', 'groups.name as group_name', 'categories.name as category_name', 'users.name as user_name')
            ->orderBy('expenses.created_at', 'desc')
            ->get();
        return response()->json(['expenses' => $userExpenses]);
    }

    public function allPayments(Request $request)
    {
        $userPayments = $request->user()->allGroupPayments()
            
            ->join('groups', 'group_payments.group_id', '=', 'groups.id')
            ->join('users as payer', 'group_payments.payer_id', '=', 'payer.id')
            ->join('users as recipient', 'group_payments.recipient_id', '=', 'recipient.id')
            ->select('group_payments.amount', 'group_payments.created_at', 'groups.name as group_name', 'payer.name as payer_name', 'recipient.name as recipient_name','status as status')
            ->orderBy('group_payments.created_at', 'desc')
            ->get();
        
        return response()->json(['payments' => $userPayments]);
    }

    public function groupExpenses(Request $request, $id)
    {
        $group = $request->user()->groups()->where('group_id', $id)->first();
        if (!$group) {
            return response()->json(['error' => 'Group not found'], 404);
        }
        $groupExpenses = $group->expenses()
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->join('users', 'expenses.user_id', '=', 'users.id')
            ->select('expenses.amount', 'expenses.name', 'expenses.split_type', 'expenses.created_at', 'categories.name as category_name', 'users.name as user_name')
            ->orderBy('expenses.created_at', 'desc')
            ->get();
        return response()->json(['expenses' => $groupExpenses]);
    }

    public function groupPayments(Request $request, $id)
    {
        $group = $request->user()->groups()->where('group_id', $id)->first();
        if (!$group) {
            return response()->json(['error' => 'Group not found'], 404);
        }
        $groupPayments = $group->groupPayments()
            ->where('status', 'accepted')
            ->join('users as payer', 'group_payments.payer_id', '=', 'payer.id')
            ->join('users as recipient', 'group_payments.recipient_id', '=', 'recipient.id')
            ->select('group_payments.amount', 'group_payments.created_at', 'payer.name as payer_name', 'recipient.name as recipient_name')
            ->orderBy('group_payments.created_at', 'desc')
            ->get();
        return response()->json(['payments' => $groupPayments]);
    }
}
