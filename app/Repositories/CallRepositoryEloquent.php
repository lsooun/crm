<?php namespace App\Repositories;

use App\Models\Call;
use App\Models\User;
use Sentinel;

class CallRepositoryEloquent implements CallRepository
{
    /**
     * @var Call
     */
    private $model;


    /**
     * CallRepositoryEloquent constructor.
     * @param Call $model
     */
    public function __construct(Call $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        $calls = $this->model;

        return $calls;
    }

    public function getAllLeads()
    {
//    	$user = User::find(Sentinel::getUser()->id);
        $calls = $this->model->where('call_type', 'leads');
//            ->whereHas('user', function ($q) use ($user) {
//                $q->where(function ($query) use ($user) {
//                    $query
//                        ->orWhere('id',$user->parent->id)
//                        ->orWhere('users.user_id', $user->parent->id);
//                });
//            });

        return $calls;
    }

    public function getAllOppotunity()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $calls = $this->model->where('call_type', 'opportunities');
//            ->whereHas('user', function ($q) use ($user)  {
//                $q->where(function ($query) use ($user)  {
//                    $query
//                        ->orWhere('id', $user->parent->id)
//                        ->orWhere('users.user_id', $user->parent->id);
//                });
//            });

        return $calls;
    }

    public function create(array $data)
    {
	    $user = User::find(Sentinel::getUser()->id);
        $call = $user->calls()->create($data);
        return $call;
    }


}