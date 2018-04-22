<?php namespace App\Repositories;


use App\Models\Qtemplate;
use App\Models\User;
use Sentinel;

class QuotationTemplateRepositoryEloquent implements QuotationTemplateRepository
{
    /**
     * @var Qtemplate
     */
    private $model;

	/**
	 * QuotationTemplateRepositoryEloquent constructor.
	 * @param Qtemplate $model
	 */
    public function __construct(Qtemplate $model)
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
        $model = $user->qtemplates()->create($data);
        return $model;
    }
}