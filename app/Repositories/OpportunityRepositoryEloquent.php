<?php namespace App\Repositories;

use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Support\Collection;
use Sentinel;

class OpportunityRepositoryEloquent implements OpportunityRepository
{
    /**
     * @var Opportunity
     */
    private $model;
    /**
     * OpportunityRepositoryEloquent constructor.
     * @param Opportunity $model
     */
    public function __construct(Opportunity $model)
    {
        $this->model = $model;
    }

    public function getAll(array $with = [])
    {
//    	$user = User::find(Sentinel::getUser()->id);
        $models = $this->model;
//        ->whereHas('user', function ($q) use ($user){
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
	    $user->opportunities()->create($data);
    }

    public function getAllForCustomer($company_id)
    {
	    return $this->model->where('customer_id', $company_id);
    }

	public function getAllForUser($user_id)
	{
		return $this->model->where('user_id', $user_id);
	}
}