<?php

namespace App\Filament\Resources\MaterialIntakes\Pages;

use App\Filament\Resources\MaterialIntakes\MaterialIntakeResource;
use App\Models\Material;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMaterialIntakes extends ListRecords
{
    protected static string $resource = MaterialIntakeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('All'),
        ];

        foreach (Material::query()->orderBy('name')->get() as $material) {
            $tabs["material_{$material->id}"] = Tab::make($material->name)
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where(
                        'material_id',
                        $material->id
                    )
                );
        }

        return $tabs;
    }
}
