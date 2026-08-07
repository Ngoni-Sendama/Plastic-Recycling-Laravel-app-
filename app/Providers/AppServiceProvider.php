<?php

namespace App\Providers;

use App\Models\Buyer;
use App\Models\CashRemittance;
use App\Models\CrushingProduction;
use App\Models\Dispatch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Material;
use App\Models\MaterialIntake;
use App\Models\PalletizingProduction;
use App\Models\PalletizingReceipt;
use App\Models\PelletSale;
use App\Models\User;
use App\Observers\AuditLogObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api-login', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->string('username')->toString().'|'.$request->ip());
        });

        foreach ([User::class, Material::class, Buyer::class, MaterialIntake::class, CrushingProduction::class, Dispatch::class, PalletizingReceipt::class, PalletizingProduction::class, PelletSale::class, CashRemittance::class, Expense::class, ExpenseCategory::class] as $modelClass) {
            $modelClass::observe(AuditLogObserver::class);
        }
    }
}
