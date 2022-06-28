<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class ClaimOrder extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Claim';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'Are you sure you want to claim this order?';

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models): mixed
    {
        try {
            Log::debug('Models', $models->toArray());

            if ($models->count() > 1) {
                return Action::danger($models->count() . ' Models injected into the NovaAction. You can only claim one order at a time.');
            }

            $model = $models->first();

            if (! is_null($model->user)) {
                return Action::danger('This order has already been claimed by another florist.');
            }

            $model->user_id = Auth::user()->id;
            $model->status = 'claimed';
            $model->save();

            $this->markAsFinished($model);

            return Action::message('You have claimed the order, let\'t make the receiver happy with a beautifully bouquet.');
        } catch (\Throwable $e) {
            $this->markAsFailed($model, $e->getMessage());

            return Action::danger($e->getMessage());
        }
    }
}
