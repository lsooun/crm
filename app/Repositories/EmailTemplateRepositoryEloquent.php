<?php

namespace App\Repositories;


use App\Models\EmailTemplate;
use App\Models\User;
use Sentinel;

class EmailTemplateRepositoryEloquent implements EmailTemplateRepository
{
    /**
     * @var EmailTemplate
     */
    private $model;


    /**
     * CategoryRepositoryEloquent constructor.
     * @param EmailTemplate $model
     */
    public function __construct(EmailTemplate $model)
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
//                    ->orWhere('user_id', $user->id)
//                    ->orWhere('user_id', $user->parent->id);
//            });
//        });

        return $models;
    }

    public function create(array $data)
    {
	    $user = User::find(Sentinel::getUser()->id);
        $company = $user->emailTemplates()->create($data);
        return $company;
    }
}