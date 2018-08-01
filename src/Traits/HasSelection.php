<?php

namespace NastuzziSamy\Laravel\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * This trait add multiple scopes into model class
 * They are all usable directly by calling them (withtout the "scope" behind) when querying for items
 *
 * To work correctly, the developer must define at least this property:
 *  - `selection` as a key/value array
 *      => the developer defines as selectors as (s)he wants, but a selector is only usable if it is defined as key
 *      => each key is a selector: paginate, week, order...
 *      => each value corresponds to a default value for the selector
 *      => if a value is `null`, this means that the selector is optional
 *
 * It is also possible to customize these properties:
 *  - `paginateLimit` is the max amount of items in a page
 *  - `created_at` is the column name for the date creation
 *  - `begin_at` is the column name for the date of begining
 *  - `end_at` is the column name for the date of ending
 */
Trait HasSelection {
    /**
     * Paginate items by number of `number`
     * Auto manage page argument
     * @param  Builder $query
     * @param  int     $number (if $number > of the limit defined in the model => throw an exception)
     * @return Collection
     */
    public function scopePaginate(Builder $query, int $number) {
        if ($this->paginateLimit && $this->paginateLimit < $number)
            throw new \Exception('Only '.$this->paginateLimit.' items could be displayed in the same time');

        return $query->paginate($number);
    }

    public function scopeGetPaginate(Builder $query, int $number) {
        return $this->scopePaginate($query, $number);
    }

    /**
     * Set a precise order
     * @param  Builder $query
     * @param  string  $order enum of `latest`, `oldest` and `random`
     * @return Builder
     */
    public function scopeOrder(Builder $query, string $order) {
        $orders = [
            'latest'    => 'latest',
            'oldest'    => 'oldest',
            'random'    => 'inRandomOrder'
        ];

        if (!isset($orders[$order]))
            throw new \Exception('This order '.$order.' does not exist. Only `latest`, `oldest` and `random` are allowed');

        if ($order === 'random')
            return $query->inRandomOrder();
        else {
            return $query->{$orders[$order]}(
                $this->created_at ?? 'created_at'
            );
        }
    }

    public function scopeGetOrder(Builder $query, string $order) {
        return $this->scopeOrder($query, $order)->get();
    }

    /**
     * Show items within the day given
     * @param  Builder $query
     * @param          $date    must be compatible with Carbon or an Exception will be thrown
     * @return Builder
     */
    public function scopeDay(Builder $query, $date) {
        return $query
            ->where($this->begin_at ?? 'created_at', '>=', Carbon::parse($date))
            ->where($this->end_at ?? 'created_at', '<=', Carbon::parse($date)->addDay());
    }

    public function scopeGetDay(Builder $query, $date) {
        return $this->scopeDay($query, $date)->get();
    }

    /**
     * Show items within the week given
     * @param  Builder $query
     * @param          $date    must be compatible with Carbon or an Exception will be thrown
     * @return Builder
     */
    public function scopeWeek(Builder $query, $date) {
        return $query
            ->where($this->begin_at ?? 'created_at', '>=', Carbon::parse($date))
            ->where($this->end_at ?? 'created_at', '<=', Carbon::parse($date)->addWeek());
    }

    public function scopeGetWeek(Builder $query, $date) {
        return $this->scopeWeek($query, $date)->get();
    }

    /**
     * Show items within the month given
     * @param  Builder $query
     * @param          $date    must be compatible with Carbon or an Exception will be thrown
     * @return Builder
     */
    public function scopeMonth(Builder $query, $date) {
        return $query
            ->where($this->begin_at ?? 'created_at', '>=', Carbon::parse($date))
            ->where($this->end_at ?? 'created_at', '<=', Carbon::parse($date)->addMonth());
    }

    public function scopeGetMonth(Builder $query, $date) {
        return $this->scopeMonth($query, $date)->get();
    }

    /**
     * Show items within the year given
     * @param  Builder $query
     * @param          $date    must be compatible with Carbon or an Exception will be thrown
     * @return Builder
     */
    public function scopeYear(Builder $query, $date) {
        return $query
            ->where($this->begin_at ?? 'created_at', '>=', Carbon::parse($date))
            ->where($this->end_at ?? 'created_at', '<=', Carbon::parse($date)->addYear());
    }

    public function scopeGetYear($query, $date) {
        return $this->scopeYear($query, $date)->get();
    }

    /**
     * Get query builder to show items with the different selectors defined in the model
     * @param  Builder $query
     * @return Builder
     */
    public function scopeSelect(Builder $query) {
        if ($this->selection) {
            foreach ($this->selection as $selection => $default) {
                $param = \Request::input($selection, $default);

                if ($selection === 'paginate' || $param === null) // Paginate returns a collection
                    continue;

                $query = $this->{'scope'.ucfirst($selection)}(
                    $query,
                    $param
                );
            }

            if (isset($this->selection['paginate'])) { // Must be treated at last
                return $this->scopePaginate(
                    $query,
                    \Request::input('paginate', $this->selection['paginate'])
                );
            }
        }

        return $query;
    }

    /**
     * Show all items with the different selectors defined in the model
     * @param  Builder $query
     * @return Collection
     */
    public function scopeGetSelection(Builder $query) {
        $selection = $this->scopeSelect($query);

        if ($selection instanceof Builder)
            return $selection->get();
        else
            return $selection;
    }
}
