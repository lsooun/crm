<?php namespace App\Repositories;

use App\Models\Option;
use Sentinel;

class OptionRepositoryEloquent implements OptionRepository
{
    /**
     * @var Option
     */
    private $model;

    /**
     * OptionRepositoryEloquent constructor.
     * @param Option $model
     */
    public function __construct(Option $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }
}