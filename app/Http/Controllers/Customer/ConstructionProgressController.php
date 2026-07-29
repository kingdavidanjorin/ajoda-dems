<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ConstructionProgressUpdate;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class ConstructionProgressController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $updates = ConstructionProgressUpdate::query()
            ->where('customer_visible', true)
            ->where(
                function (Builder $query) use ($user): void {
                    $query
                        ->whereHas(
                            'purchase',
                            fn(Builder $purchaseQuery) =>
                            $purchaseQuery->where(
                                'customer_id',
                                $user->id,
                            ),
                        )
                        ->orWhereHas(
                            'propertyAllocation',
                            fn(Builder $allocationQuery) =>
                            $allocationQuery->where(
                                'customer_id',
                                $user->id,
                            ),
                        );
                },
            )
            ->with([
                'house',
                'purchase',
                'propertyAllocation',
            ])
            ->latest('occurred_at')
            ->paginate(
                (int) config(
                    'dems.pagination.per_page',
                    15,
                ),
            );

        return view(
            'customer.construction.index',
            compact('updates'),
        );
    }
}
