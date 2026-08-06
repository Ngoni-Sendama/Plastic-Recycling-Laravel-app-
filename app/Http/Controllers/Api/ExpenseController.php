<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Services\DocumentNumberGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExpenseController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return ExpenseResource::collection(
            Expense::with(['category', 'recordedByUser'])->latest('date')->get(),
        );
    }

    public function show(Expense $expense): ExpenseResource
    {
        $expense->load(['category', 'recordedByUser']);

        return new ExpenseResource($expense);
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $data = $request->validated();

        $expenseNumber = DocumentNumberGenerator::generate(new Expense, 'expense_number', 'EXP', $data['date']);

        $expense = Expense::create([
            ...$data,
            'expense_number' => $expenseNumber,
            'recorded_by_user_id' => $request->user()->id,
        ]);

        $expense->load(['category', 'recordedByUser']);

        return response()->json([
            'message' => 'Expense recorded successfully.',
            'data' => new ExpenseResource($expense),
        ], 201);
    }

    public function update(StoreExpenseRequest $request, Expense $expense): ExpenseResource
    {
        $expense->update($request->validated());
        $expense->load(['category', 'recordedByUser']);

        return new ExpenseResource($expense);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();

        return response()->json(['message' => 'Expense deleted successfully.']);
    }

    public function trashed(): AnonymousResourceCollection
    {
        return ExpenseResource::collection(
            Expense::onlyTrashed()->with(['category', 'recordedByUser'])->latest('date')->get(),
        );
    }

    public function restore(Expense $expense): JsonResponse
    {
        $expense->restore();

        return response()->json(['message' => 'Record restored successfully.']);
    }

    public function forceDelete(Expense $expense): JsonResponse
    {
        $expense->forceDelete();

        return response()->json(['message' => 'Record permanently deleted.']);
    }
}
