<?php namespace App\Repositories;


use App\Models\InvoiceReceivePayment;
use App\Models\User;
use Sentinel;

class InvoicePaymentRepositoryEloquent implements InvoicePaymentRepository
{
    /**
     * @var InvoiceReceivePayment
     */
    private $model;
    /**
     * InvoicePaymentRepositoryEloquent constructor.
     * @param InvoiceReceivePayment $model
     */
    public function __construct(InvoiceReceivePayment $model)
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


    public function getAllForCustomer($customer_id)
    {
        $models = $this->model->whereHas('customer', function ($q) use ($customer_id) {
            $q->where('customer_id', $customer_id);
        });

        return $models;
    }
}