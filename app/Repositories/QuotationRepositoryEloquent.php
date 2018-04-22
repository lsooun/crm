<?php namespace App\Repositories;


use App\Models\Quotation;
use App\Models\User;
use Sentinel;

class QuotationRepositoryEloquent implements QuotationRepository
{
    /**
     * @var Quotation
     */
    private $model;
    /**
     * QuotationRepositoryEloquent constructor.
     * @param Quotation $model
     */
    public function __construct(Quotation $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $models = $this->model;
//        ->whereHas('user', function ($q) use ($user) {
//            $q->where(function ($query)  use ($user){
//                $query
//                    ->orWhere('id', $user->parent->id)
//                    ->orWhere('users.user_id', $user->parent->id);
//            });
//        });

        return $models;
    }



    public function getAllToday()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $models = $this->model
//        ->whereHas('user', function ($q) use ($user) {
            ->where('date', strtotime(date('Y-m-d')));
//                ->where(function ($query) use ($user) {
//                    $query
//                        ->orWhere('id', $user->parent->id)
//                        ->orWhere('users.user_id', $user->parent->id);
//                });
//        });

        return $models;
    }

    public function getAllYesterday()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $models = $this->model
//	        ->whereHas('user', function ($q) use ($user) {
            ->where('date', strtotime(date('Y-m-d', strtotime("-1 days"))));
//                ->where(function ($query) use ($user) {
//                    $query
//                        ->orWhere('id', $user->parent->id)
//                        ->orWhere('users.user_id', $user->parent->id);
//                });
//        });

        return $models;
    }

    public function getAllWeek()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $models = $this->model
//	        ->whereHas('user', function ($q) use ($user) {
            ->whereBetween('date',
                array(strtotime((date('D') != 'Mon') ? date('Y-m-d', strtotime('last Monday')) : date('Y-m-d')),
                    strtotime((date('D') != 'Sat') ? date('Y-m-d', strtotime('next Saturday')) : date('Y-m-d'))));
//                ->where(function ($query) use ($user) {
//                    $query
//                        ->orWhere('id', $user->parent->id)
//                        ->orWhere('users.user_id', $user->parent->id);
//                });
//        });
        return $models;
    }

    public function getAllMonth()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $models = $this->model
//	        ->whereHas('user', function ($q) use ($user) {
            ->whereBetween('date',
                array(date('d-m-Y', strtotime('first day of this month')),
                    date('d-m-Y', strtotime('last day of this month'))));
//                ->where(function ($query) use ($user) {
//                    $query
//                        ->orWhere('id', $user->parent->id)
//                        ->orWhere('users.user_id', $user->parent->id);
//                });
//        });
        return $models;
    }


    public function getAllForCustomer($customer_id)
    {
        $models = $this->model->whereHas('customer', function ($q) use ($customer_id) {
            $q->where('customer_id', $customer_id);
        });

        return $models;
    }
}