<?php
namespace QuanKim\LaravelCounterCache\Traits;

use Illuminate\Database\Eloquent\SoftDeletingScope;

trait CounterCache
{
    /**
     * Override boot function in eloquent model
     */
    protected static function boot()
    {
        parent::boot();
        static::created(function ($model) {
            (new self)->runCounter($model, 'increment');
        });

        static::saved(function ($model) {
            (new self)->runCounter($model);
        });

        static::deleted(function ($model) {
            (new self)->runCounter($model, 'decrement');
        });

        if (static::hasGlobalScope(SoftDeletingScope::class)) {
            static::restored(function ($model) {
                (new self)->runCounter($model, 'increment');
            });
        }
    }

    /**
     * Run counter from counter cache options in model
     *
     * @param $model
     * @param null $type
     */
    public function runCounter($model, $type = null)
    {
        foreach ($this->counterCacheOptions as $method => $counter) {
            $this->counterForRelation($model, $method, $counter, $type);
        }
    }

    /**
     * Update field
     *
     * @param $model
     * @param $method
     * @param $counter
     * @param $type
     */
    protected function counterForRelation($model, $method, $counter, $type)
    {
        foreach ($counter as $field => $option) {
            if (is_string($option) && $type) {
                $model->$method()->$type($option);
            }

            if (is_array($option)) {
                $relation = $this->loadRelation($model, $method);
                if (isset($option['conditions'])) {
                    $relation = $relation->where($option['conditions'])->first();
                }

                $counterMethod = $option['method'] ?? null;
                if ($relation) {
                    if ($counterMethod) {
                        $relation->update([
                            $field => $relation->$counterMethod(),
                        ]);
                    } elseif ($type) {
                        $relation->$type($field);
                    }
                }
            }
        }
    }

    /**
     * Build relation model from relationship
     *
     * @param $model
     * @param $method
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function loadRelation($model, $method)
    {
        $this->load($method);

        return $model->$method;
    }
}
