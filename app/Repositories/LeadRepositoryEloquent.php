<?php namespace App\Repositories;

use App\Models\Lead;
use App\Models\User;
use Sentinel;

class LeadRepositoryEloquent implements LeadRepository
{
    /**
     * @var Lead
     */
    private $model;
    /**
     * LeadRepositoryEloquent constructor.
     * @param Lead $model
     */
    public function __construct(Lead $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $models = $this->model;
//	        ->whereHas('user', function ($q) use ($user) {
//            $q->where(function ($query) use ($user) {
//                $query
//                    ->orWhere('id', $user->parent->id)
//                    ->orWhere('users.user_id', $user->parent->id);
//            });
//        });

        return $models;
    }

    public function store(array $data)
    {
	    $user = User::find(Sentinel::getUser()->id);
        $lead = $user->leads()->create($data);
        return $lead;
    }

    public function getAllForCustomer($company_id)
    {
	    return $this->model->where('customer_id', $company_id);
    }

	public function getAllForUser($customer_id)
	{
		$models = $this->model->whereHas('user', function ($q) use ($customer_id) {
			$q->where('customer_id', $customer_id);
		});
		return $models;
	}
}