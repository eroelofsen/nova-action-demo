<?php

namespace App\Nova;

use App\Nova\Actions\ClaimOrder;
use App\Nova\Actions\OrderContinueProcess;
use App\Nova\Actions\OrderExport;
use App\Nova\Actions\OrderExportFiltered;
use App\Nova\Metrics\OrdersPerDay;
use App\Nova\Metrics\OrdersPerStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\ActionRequest;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Order
 *
 * @property int $id
 * @property string $currency
 * @property string $status
 */
class Order extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Order::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'reference';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'reference',
        'status',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            ID::make(__('ID'), 'id')
                ->hideFromIndex(),

            Text::make('Reference')
                ->rules('required'),

            Text::make('Currency')
                ->rules('required')
                ->hideFromIndex(),

            Badge::make('Status')->map([
                '' => 'danger',
                'ordered' => 'warning',
                'claimed' => 'info',
                'shipped' => 'success',
                'delivered' => 'success',
                'returned' => 'warning',
                'refunded' => 'warning',
                'error' => 'danger',
            ])->hideWhenUpdating(),

            Select::make('Status')->options([
                'ordered' => 'Ordered',
                'claimed' => 'Claimed',
                'shipped' => 'Shipped',
                'delivered' => 'Delivered',
                'returned' => 'Returned',
                'error' => 'Error',
            ])->onlyOnForms(),

            DateTime::make('Ordered At')
                ->sortable()
                ->rules('required'),

            Number::make('Price')
                ->rules('required')
                ->min(0)
                ->max(100000)
                ->step(0.01)
                ->displayUsing(function ($value) {
                    return $this->currency . ' ' . number_format($value, 2, '.', ',');
                }),
        ];
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param NovaRequest $request
     * @param  Builder  $query
     * @return Builder
     */
    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        // Only show orders claimed by the User or unclaimed orders.
        return $query->whereNull('user_id')->orWhere('user_id', '=', $request->user()->id);
    }

    /**
     * Get the actions available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return [
            (new ClaimOrder())
                ->onlyOnTableRow()
                ->canSee(function ($request) {
                    if ($request instanceof ActionRequest) {
                        return true;
                    }

                    return $this->resource instanceof \App\Models\Order && is_null($this->resource->user);
                })->canRun(function ($request, $user) {
                    return true;
                }),
        ];
    }
}
