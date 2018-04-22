<?php namespace App\Repositories;

use App\Models\User;
use Sentinel;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserRepositoryEloquent implements UserRepository
{
    private $user;
    /**
     * @var User
     */
    private $model;

    /**
     * UserRepositoryEloquent constructor.
     * @param User $model
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function getStaff()
    {
	    $user = User::find(Sentinel::getUser()->id);
        if (Sentinel::inRole('staff')) {
            return $user->users->filter(function ($user) {
                return $user->inRole('staff');
            })->add($user);
        } else if ($user->inRole('admin')) {
            $staffs = new Collection([]);
            $user
                ->users()
                ->with('users.users')
                ->get()
                ->each(function ($user) use ($staffs) {
                    foreach ($user->users as $u) {
                        $staffs->push($u);
                    }
                    //$staffs->push($user);
                });

            $staffs = $staffs->filter(function ($user) {
                return $user->inRole('staff');
            });
            return $staffs;
        }

    }

    public function getCustomers()
    {
	    $user = User::find(Sentinel::getUser()->id);
        if (Sentinel::inRole('staff')) {
            return $user->users->filter(function ($user) {
                return $user->inRole('customer');
            });
        } else if ($user->inRole('admin')) {
            $staffs = new Collection([]);
            $user
                ->users()
                ->with('users.users')
                ->get()
                ->each(function ($user) use ($staffs) {
                    foreach ($user->users as $u) {
                        $staffs->push($u);
                    }
                    //$staffs->push($user);
                });

            $staffs = $staffs->filter(function ($user) {
                return $user->inRole('customer');
            });
            return $staffs;
        }
    }

    public function getParentStaff()
    {
        $staffs = new Collection([]);
	    $user = User::find(Sentinel::getUser()->id);
	    $user->parent->users()
            ->with('users.users')
            ->get()
            ->each(function ($user) use ($staffs) {
                foreach ($user->users as $u) {
                    $staffs->push($u);
                }
                //$staffs->push($user);
            });

        $staffs = $staffs->filter(function ($user) {
            return $user->inRole('staff');
        });
        return $staffs;
    }

    public function getParentCustomers()
    {
        $staffs = new Collection([]);
	    $user = User::find(Sentinel::getUser()->id);
        $user
            ->parent->users()
            ->with('users.users')
            ->get()
            ->each(function ($user) use ($staffs) {
                foreach ($user->users as $u) {
                    $staffs->push($u);
                }
                // $staffs->push($user);
            });

        $staffs = $staffs->filter(function ($user) {
            return $user->inRole('customer');
        });
        return $staffs;
    }

    public function getAll()
    {
	    $user = User::find(Sentinel::getUser()->id);
        $models = $this->model->whereHas('user', function ($q) use ($user) {
            $q->where(function ($query) use ($user) {
                $query
                    ->orWhere('user_id', $user->parent->id)
                    ->orWhere('users.user_id', $user->parent->id);
            });
        });

        return $models;
    }

    public function getAllNew()
    {
	    $models = $this->model;
        return $models;
    }

    public function getAllForCustomer()
    {
        $models = $this->model;

        return $models;
    }
    public function getUsersAndStaffs()
    {
        return $this->model->get()->filter(
            function ($user) {
                return ($user->inRole('admin') || $user->inRole('staff'));
            }
        );
    }

    public function uploadAvatar(UploadedFile $file)
    {
        $destinationPath = public_path() . '/uploads/avatar/';
        $extension = $file->getClientOriginalExtension() ?: 'png';
        $fileName = str_random(10) . '.' . $extension;
        return $file->move($destinationPath, $fileName);
    }

    public function create(array $data, $activate = false)
    {
	    $user = User::find(Sentinel::getUser()->id);
        $userNew = Sentinel::registerAndActivate($data, $activate);
	    $user->users()->save($userNew);
        return $userNew;
    }

    public function assignRole(User $user, $roleName)
    {
        $role = Sentinel::getRoleRepository()->findByName($roleName);
        $role->users()->attach($user);
    }

}