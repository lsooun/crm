<?php namespace App\Repositories;


use App\Models\Meeting;
use App\Models\User;
use Sentinel;

class MeetingRepositoryEloquent implements MeetingRepository
{
    /**
     * @var Meeting
     */
    private $model;

    /**
     * MeetingRepositoryEloquent constructor.
     * @param Meeting $model
     */
    public function __construct(Meeting $model)
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
        $model = $user->meetings()->create($data);
        return $model;

    }
}