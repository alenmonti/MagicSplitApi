<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'max:255',
            'name' => 'required|max:255',
            'split_type' => 'required|in:equal,exact',
            'exact_amounts.*' => 'required_if:split_type,exact|numeric|min:0',
        ]);

        if ($request->split_type == 'exact') {
            $total_exact_amount = array_sum($request->exact_amounts);
            if ($total_exact_amount != $request->amount) {
                return response()->json(['message' => 'La suma de los montos exactos no coincide con el monto total del gasto'], 400);
            }
        }

        if ($request->amount <= 0) { return response()->json(['message' => 'El monto debe ser mayor a 0'], 400); }
        if (!$request->user()->groups->contains($request->group_id)) { return response()->json(['message' => 'El grupo no pertenece al usuario'], 400); }
        if (!Group::find($request->group_id)->categories->contains($request->category_id)) { return response()->json(['message' => 'La categoría no pertenece al grupo'], 400); }


        $this->updateBalances($request->group_id, $request->category_id, $request->amount, $request->split_type, $request->exact_amounts);

        $expense = Expense::create([
            'group_id' => $request->group_id,
            'category_id' => $request->category_id,
            'user_id' => $request->user()->id,
            'amount' => $request->amount,
            'description' => $request->description,
            'name' =>  $request->name,
            'split_type' => $request->split_type,
            'exact_amounts' => $request->exact_amounts ?? null,
        ]);

        return response()->json(['message' => 'Gasto creado exitosamente', 'expense' => $expense], 201);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:expenses,id',
        ]);
        $expense = Expense::find($request->id);

        if ($expense->split_type === 'exact') {
            return response()->json(['message' => 'No se puede eliminar un gasto dividido por montos exactos'], 400);
        }
        
        $usuarioIsAdmin = $request->user()->isAdminOfGroup($expense->group);
        $usuarioIsOwner = $request->user()->expenses->contains($request->id);
        if ((!$usuarioIsAdmin) && (!$usuarioIsOwner)) { return response()->json(['message' => 'El gasto no pertenece al usuario'], 400); }

        $this->updateBalances($expense->group->id, $expense->category->id, -$expense->amount, $expense->split_type, []); //no funciona aún para eliminar gastos divididos por montos exactos.
        
        $expense->delete();
        
        return response()->json(['message' => 'Gasto eliminado exitosamente'], 200);
    }

    private function updateBalances($group_id, $category_id, $amount, $split_type, $exact_amounts){
        $group = Group::find($group_id);
        $category = Category::find($category_id);
        foreach ($group->users as $user) {
            if (in_array($user->id, $category->users->pluck('id')->toArray())) {
                if ($split_type == 'equal') {
                    if ($user->id == Auth::user()->id) $user->pivot->balance += $amount;
                    $user->pivot->balance -= $amount / count($category->users);
                } else {
                    if ($user->id == Auth::user()->id) {
                        $user->pivot->balance += $amount - ($exact_amounts[$user->id] ?? 0);
                    } else {
                       $user->pivot->balance -= $exact_amounts[$user->id] ?? 0;
                    }
                }
                $user->pivot->save();
            }
        }
    }

    public function expenses(Request $request, $id)
    {
        $expenses = Expense::where('group_id', $id)->orderBy('created_at', 'desc')->get();
        if (!$expenses) { return response()->json(['message' => 'Gastos no existentes en el grupo '.$id], 404); }

        if (!$request->user()->groups->contains($id)) { return response()->json(['message' => 'El grupo no pertenece al usuario'], 400); }

        return response()->json(['expenses' => $expenses], 200);
    }

    public function editExpense(Request $request, $id)
    {
        $rules = [
            'category_id' => 'exists:categories,id',
            'amount' => 'numeric',
            'description' => 'max:255',
            'name' => 'max:255',
        ];

        $request->validate($rules);

        $expense = Expense::find($id);

        if (!$expense) {
            return response()->json(['message' => 'Gasto no encontrado'], 404);
        }

        $usuarioIsAdmin = $request->user()->isAdminOfGroup($expense->group);
        $usuarioIsOwner = $request->user()->expenses->contains($id);

        if ((!$usuarioIsAdmin) && (!$usuarioIsOwner)) return response()->json(['message' => 'No tienes permisos para realizar esta acción'], 403);

        if ($request->has('category_id')) {
            $expense->category_id = $request->category_id;
        }

        if ($request->has('amount')) {
            $expense->amount = $request->amount;
        }

        if ($request->has('description')) {
            $expense->description = $request->description;
        }

        if ($request->has('name')) {
            $expense->name = $request->name;
        }

        $expense->save();

        return response()->json(['message' => 'Gasto actualizado exitosamente', 'expense' => $expense], 200);
    }


}
