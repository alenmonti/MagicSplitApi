<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function categoryReport(Request $request, $id)
    {
        $group = Group::find($id);
        if(!$group) return response()->json(['error' => 'Group not found'], 404);

        $categories = $group->categories;
        $data = [];
        foreach ($categories as $category) {
            $categoryData = [
                'category' => $category->name,
                'total' => $category->expenses->sum('amount'),
                // 'members' => []
            ];
            // foreach ($category->users as $member) {
            //     $categoryData['members'][] = [
            //         'name' => $member->name,
            //         'total' => $member->expenses->where('category_id', $category->id)->sum('amount')
            //     ];
            // }
            $data[] = $categoryData;
        }
        return response()->json(["report" => $data]);
    }

    public function timeReport(Request $request, $id)
    {
        $request->validate([
            'start' => 'date',
            'end' => 'date'
        ]);

        $group = Group::find($id);
        if(!$group) return response()->json(['error' => 'Group not found'], 404);

        $expenses = $group->expenses();
        if($request->start) $expenses->where('created_at', '>=', $request->start);
        if($request->end) $expenses->where('created_at', '<=', $request->end . ' 23:59:59');
        $expenses = $expenses->get()->groupBy(function($date) {
            return \Carbon\Carbon::parse($date->created_at)->format('Y-m-d');
        });

        $data = [];
        foreach ($expenses as $day => $expensesByDay) {
            $dayData = [
                'day' => $day,
                'total' => $expensesByDay->sum('amount')
            ];
            $data[] = $dayData;
        }

        return response()->json(["report" => $data]);
    }
}
