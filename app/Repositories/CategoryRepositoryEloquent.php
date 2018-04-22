<?php namespace App\Repositories;


use App\Models\Category;
use App\Models\User;
use Sentinel;

class CategoryRepositoryEloquent implements CategoryRepository
{
    /**
     * @var Category
     */
    private $model;
    private $user;


    /**
     * CategoryRepositoryEloquent constructor.
     */
    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $models = $this->model;
//        ->whereHas('user', function ($q) use ($user) {
//            $q->where(function ($query) use ($user) {
//                $query
//                    ->orWhere('id', $user->parent->id)
//                    ->orWhere('users.user_id', $user->parent->id);
//            });
//        });

        return $models;
    }

    public function create(array $data)
    {
	    $user = User::find(Sentinel::getUser()->id);
        $company = $user->categories()->create($data);
        return $company;
    }
}