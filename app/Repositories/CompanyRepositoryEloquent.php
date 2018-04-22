<?php namespace App\Repositories;

use App\Models\Company;
use App\Models\User;
use Sentinel;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CompanyRepositoryEloquent implements CompanyRepository
{
    /**
     * @var Company
     */
    private $model;

    /**
     * CompanyRepositoryEloquent constructor.
     * @param Company $model
     */
    public function __construct(Company $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $companies = $this->model;
//        ->whereHas('user', function ($q) use ($user) {
//            $q->where(function ($query) use ($user) {
//                $query
//                    ->orWhere('id', $user->parent->id)
//                    ->orWhere('users.user_id', $user->parent->id);
//            });
//        });

        return $companies;
    }

    public function create(array $data)
    {
	    $user = User::find(Sentinel::getUser()->id);
        $company = $user->companies()->create($data);
        return $company;
    }


    public function uploadAvatar(UploadedFile $file)
    {
        $destinationPath = public_path() . '/uploads/company/';
        $extension = $file->getClientOriginalExtension() ?: 'png';
        $fileName = str_random(10) . '.' . $extension;
        return $file->move($destinationPath, $fileName);
    }
    public function uploadCustomerAvatar(UploadedFile $file)
    {
        $destinationPath = public_path() . '/uploads/avatar/';
        $extension = $file->getClientOriginalExtension() ?: 'png';
        $fileName = str_random(10) . '.' . $extension;
        return $file->move($destinationPath, $fileName);
    }

}