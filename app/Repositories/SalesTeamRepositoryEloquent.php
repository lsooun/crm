<?php namespace App\Repositories;

use App\Models\Salesteam;
use App\Models\User;
use Sentinel;

class SalesTeamRepositoryEloquent implements SalesTeamRepository
{
    /**
     * @var Salesteam
     */
    private $model;

    /**
     * SalesTeamRepositoryEloquent constructor.
     */
    public function __construct(Salesteam $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $salesTeams = $this->model;
//        ->whereHas('user', function ($q) use ($user) {
//            $q->where(function ($query) use ($user) {
//                $query
//                    ->orWhere('id', $user->parent->id)
//                    ->orWhere('users.user_id', $user->parent->id);
//            });
//        });
        return $salesTeams;
    }

    public function teamLeader()
    {
        return $this->model->teamLeader();
    }

    public function create(array $data)
    {
	    $user = User::find(Sentinel::getUser()->id);
        $model = $user->salesTeams()->create($data);
        return $model;
    }

    public function getAllQuotations()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $salesTeams = $this->model->where('quotations', 1);
//        ->whereHas('user', function ($q) use ($user) {
//            $q->where(function ($query) use ($user) {
//                $query
//                    ->orWhere('id', $user->parent->id)
//                    ->orWhere('users.user_id', $user->parent->id);
//            })
//                ->where('quotations',1);
//        });
        return $salesTeams;
    }

    public function getAllLeads()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $salesTeams = $this->model->where('leads', 1);
//        ->whereHas('user', function ($q) use ($user) {
//            $q->where(function ($query) use ($user) {
//                $query
//                    ->orWhere('id', $user->parent->id)
//                    ->orWhere('users.user_id', $user->parent->id);
//            })
//                ->where('leads',1);
//        });
        return $salesTeams;
    }

    public function getAllOpportunities()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $salesTeams = $this->model->where('opportunities', 1);
//        ->whereHas('user', function ($q) use ($user) {
//            $q->where(function ($query) use ($user) {
//                $query
//                    ->orWhere('id', $user->parent->id)
//                    ->orWhere('users.user_id', $user->parent->id);
//            })
//                ->where('opportunities',1);
//        });
        return $salesTeams;
    }
}