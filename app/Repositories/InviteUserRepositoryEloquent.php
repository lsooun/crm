<?php

namespace App\Repositories;

use App\Models\InviteUser;
use App\Models\User;
use Sentinel;

class InviteUserRepositoryEloquent implements InviteUserRepository
{
    /**
     * @var InviteUser
     */
    private $model;
    /**
     * CallRepositoryEloquent constructor.
     * @param InviteUser $model
     */
    public function __construct(InviteUser $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $calls = $this->model;
//        ->whereHas('user', function ($q) use ($user) {
//            $q->where(function ($query) use ($user) {
//                $query
//                    ->orWhere('id', $user->parent->id)
//                    ->orWhere('users.user_id', $user->parent->id);
//            });
//        });

        return $calls;
    }

    public function create(array $data)
    {
	    $user = User::find(Sentinel::getUser()->id);
        $data['code'] = bin2hex(openssl_random_pseudo_bytes(16));
        return$user->invite()->create($data);
    }


}