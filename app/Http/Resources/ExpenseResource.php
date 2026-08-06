<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'expense_number' => $this->expense_number,
            'date' => $this->date?->toDateString(),
            'expense_category_id' => $this->expense_category_id,
            'category' => $this->whenLoaded('category', fn () => new ExpenseCategoryResource($this->category)),
            'description' => $this->description,
            'amount' => (float) $this->amount,
            'payment_method' => $this->payment_method,
            'recorded_by_user_id' => $this->recorded_by_user_id,
            'recorded_by' => $this->whenLoaded('recordedByUser', fn () => $this->recordedByUser->name),
            'lock_version' => $this->lock_version,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
