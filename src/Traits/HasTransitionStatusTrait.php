<?php

namespace TautId\Shipping\Traits;

use Exception;
use Illuminate\Support\Facades\DB;

trait HasTransitionStatusTrait
{
    /**
     * Need to put $stateConfigs in models
     * Ex:
     * public $stateConfigs = [ModelStatus => ToModelStatusClass];
     */
    public static function bootHasTransitionStatusTrait(): void
    {
        static::updating(function (self $model) {
            $model->statusTransform();
        });
    }

    private function getStatesConfigs(): array
    {
        return $this->stateConfigs;
    }

    private function statusTransform()
    {
        if (! $this->isDirty('status')) {
            return;
        }

        try {
            DB::beginTransaction();

            $states = $this->getStatesConfigs();
            $class = data_get($states, $this->status, null);

            if (empty($class)) {
                throw new Exception('Status is invanlid!');
            }

            $instance = app($class);

            $instance->handle($this);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }
    }
}
