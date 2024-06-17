<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, CategoryController, GroupController, ExpenseController, GroupInvitationController, GroupPaymentController, NoteController, RecordController, ReportController, userController};
use App\Models\Group;

Route::prefix('auth')->group(function () {
    // Auth Routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // Google Auth Routes

});

Route::middleware('auth:sanctum')->group(function () {
    // User Routes
    Route::get('/user', [UserController::class, 'user']);
    Route::get('/user/groups', [UserController::class, 'groups']);
    Route::post('/user/friend', [UserController::class, 'addFriend']);
    Route::delete('/user/friend', [UserController::class, 'removeFriend']);
    Route::get('/user/friends', [UserController::class, 'friends']);
    Route::get('/user/balance', [UserController::class, 'balance']);
    

    // Group Routes
    Route::post('/group', [GroupController::class, 'create']);
    Route::delete('/group/{id}/leave', [GroupController::class, 'leave']);
    Route::patch('/group/{id}/edit', [GroupController::class, 'editGroupName'])->middleware('admin');
    Route::patch('/group/{id}/addAdmin', [GroupController::class, 'addAdmin'])->middleware('admin');
    Route::patch('/group/{id}/removeAdmin', [GroupController::class, 'removeAdmin'])->middleware('admin');
    Route::get('/group/{id}/members', [GroupController::class, 'members']);
    Route::get('/group/{id}/admins', [GroupController::class, 'admins']);
    Route::get('/group/{id}/balances', [GroupController::class, 'balances']);
    
    Route::post('/group/{id}/fake_member',[GroupController::class, 'addFictitiousMember'])->middleware('admin');

    // Category Routes
    Route::post('/group/{id}/category', [CategoryController::class, 'addCategory'])->middleware('admin');
    Route::get('/group/{id}/categories', [CategoryController::class, 'categories']);
    Route::patch('/category/{id}/addMember', [CategoryController::class, 'addMember']);
    Route::patch('/category/{id}/removeMember', [CategoryController::class, 'removeMember']);
    Route::patch('/category/{id}/reassignMembers', [CategoryController::class, 'reassignMembers']);
    Route::get('/category/{id}/members', [CategoryController::class, 'members']);

    // Expense Routes
    Route::post('/expense', [ExpenseController::class, 'create']);
    Route::delete('/expense', [ExpenseController::class, 'delete']);
    Route::get('/group/{id}/expenses', [ExpenseController::class, 'expenses']);
    Route::patch('/expense/{id}', [ExpenseController::class, 'editExpense']);

    // Group Invitation Routes
    Route::post('/group/{id}/add', [GroupController::class, 'addMember'])->middleware('admin');
    Route::get('/invitations', [GroupInvitationController::class, 'invitations']);
    Route::post('/invitation/response', [GroupInvitationController::class, 'response']);

    // Group Payment Routes
    Route::post('/group/{id}/payment', [GroupPaymentController::class, 'recordPayment']);
    Route::get('/payments/pending', [GroupPaymentController::class, 'pendingPayments']);
    Route::post('/payment/response', [GroupPaymentController::class, 'response']);

    // Note Routes
    Route::post('/group/{id}/note', [NoteController::class, 'create']);
    Route::get('/group/{id}/notes', [NoteController::class, 'notes']);
    Route::patch('/note/{id}', [NoteController::class, 'edit']);
    Route::delete('/note/{id}', [NoteController::class, 'delete']);

    // Record Routes
    Route::get('/group/{id}/record/expenses', [RecordController::class, 'groupExpenses']);
    Route::get('/group/{id}/record/payments', [RecordController::class, 'groupPayments']);
    Route::get('/record/expenses', [RecordController::class, 'allExpenses']);
    Route::get('/record/payments', [RecordController::class, 'allPayments']);

    // Report Routes
    Route::get('/group/{id}/report/category', [ReportController::class, 'categoryReport']);
    Route::get('/group/{id}/report/time', [ReportController::class, 'timeReport']);
});