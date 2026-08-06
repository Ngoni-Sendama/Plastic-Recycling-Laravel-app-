<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreExpenseCategoryRequest;
use App\Http\Resources\ExpenseCategoryResource;
use App\Models\ExpenseCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExpenseCategoryController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return ExpenseCategoryResource::collection(
            ExpenseCategory::latest('name')->get(),
        );
    }

    public function show(ExpenseCategory $expenseCategory): ExpenseCategoryResource
    {
        return new ExpenseCategoryResource($expenseCategory);
    }

    public function store(StoreExpenseCategoryRequest $request): JsonResponse
    {
        $expenseCategory = ExpenseCategory::create($request->validated());

        return response()->json([
            'message' => 'Expense category created successfully.',
            'data' => new ExpenseCategoryResource($expenseCategory),
        ], 201);
    }

    public function update(StoreExpenseCategoryRequest $request, ExpenseCategory $expenseCategory): ExpenseCategoryResource
    {
        $expenseCategory->update($request->validated());

        return new ExpenseCategoryResource($expenseCategory);
    }

    public function destroy(ExpenseCategory $expenseCategory): JsonResponse
    {
        $expenseCategory->delete();

        return response()->json(['message' => 'Expense category deleted successfully.']);
    }

    public function trashed(): AnonymousResourceCollection
    {
        return ExpenseCategoryResource::collection(ExpenseCategory::onlyTrashed()->latest('name')->get());
    }

    public function restore(ExpenseCategory $expenseCategory): JsonResponse
    {
        $expenseCategory->restore();

        return response()->json(['message' => 'Record restored successfully.']);
    }

    public function forceDelete(ExpenseCategory $expenseCategory): JsonResponse
    {
        $expenseCategory->forceDelete();

        return response()->json(['message' => 'Record permanently deleted.']);
    }
}
