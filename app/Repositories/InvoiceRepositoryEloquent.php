<?php namespace App\Repositories;


use App\Models\Invoice;
use App\Models\User;
use Sentinel;

class InvoiceRepositoryEloquent implements InvoiceRepository
{
    /**
     * @var Invoice
     */
    private $model;
    /**
     * InvoiceRepositoryEloquent constructor.
     * @param Invoice $model
     */
    public function __construct(Invoice $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $models = $this->model;
//        ->whereHas('user', function ($q) {
//            $q->where(function ($query) {
//                $query
//                    ->orWhere('id', Sentinel::getUser()->parent->id)
//                    ->orWhere('users.user_id', Sentinel::getUser()->parent->id);
//            });
//        });

        return $models;
    }

    public function create(array $data)
    {
	    $user = User::find(Sentinel::getUser()->id);
        $model = Sentinel::getUser()->invoiceReceivePayments()->create($data);
        return $model;
    }

    public function getAllOpen()
    {
	    $user = User::find(Sentinel::getUser()->id);
        $models = $this->model//->whereHas('user', function ($q) {
        ->where('invoices.status', 'Open Invoice');
//                ->where(function ($query) {
//                    $query
//                        ->orWhere('id', Sentinel::getUser()->parent->id)
//                        ->orWhere('users.user_id', Sentinel::getUser()->parent->id);
//                });
//        });

        return $models;
    }

    public function getAllOverdue()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $models = $this->model//->whereHas('user', function ($q) {
        ->where('invoices.status', 'Overdue Invoice');
//                ->where(function ($query) {
//                    $query
//                        ->orWhere('id', Sentinel::getUser()->parent->id)
//                        ->orWhere('users.user_id', Sentinel::getUser()->parent->id);
//                });
//        });

        return $models;
    }

    public function getAllPaid()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $models = $this->model
//	        ->whereHas('user', function ($q) {
            ->where('invoices.status', 'Paid Invoice');
//                ->where(function ($query) {
//                    $query
//                        ->orWhere('id', Sentinel::getUser()->parent->id)
//                        ->orWhere('users.user_id', Sentinel::getUser()->parent->id);
//                });
//        });

        return $models;
    }

    public function getAllForCustomer($customer_id)
    {
        $models = $this->model->whereHas('user', function ($q) use ($customer_id) {
            $q->where('customer_id', $customer_id);
        });

        return $models;
    }

    public function getAllOpenForCustomer($customer_id)
    {
        $models = $this->model->whereHas('user', function ($q) use ($customer_id) {
            $q->where('invoices.status', 'Open Invoice')
                ->where('customer_id', $customer_id);
        });

        return $models;
    }

    public function getAllOverdueForCustomer($customer_id)
    {
        $models = $this->model->whereHas('user', function ($q) use ($customer_id) {
            $q->where('invoices.status', 'Overdue Invoice')
                ->where('customer_id', $customer_id);
        });

        return $models;
    }

    public function getAllPaidForCustomer($customer_id)
    {
        $models = $this->model->whereHas('user', function ($q) use ($customer_id) {
            $q->where('invoices.status', 'Paid Invoice')
                ->where('customer_id', $customer_id);
        });

        return $models;
    }

    public function getAllOpenMonth()
    {
	    $user = User::find(Sentinel::getUser()->id);
        $models = $this->model
//	        ->whereHas('user', function ($q) {
            ->where('invoices.status', 'Open Invoice')
            ->where('invoice_date', 'LIKE', date('Y-m') . '%');
//                ->where(function ($query) {
//                    $query
//                        ->orWhere('id', Sentinel::getUser()->parent->id)
//                        ->orWhere('users.user_id', Sentinel::getUser()->parent->id);
//                });
//        });

        return $models;
    }

    public function getAllOverdueMonth()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $models = $this->model
//	        ->whereHas('user', function ($q) {
            ->where('invoices.status', 'Overdue Invoice')
            ->where('invoice_date', 'LIKE', date('Y-m') . '%');
//                ->where(function ($query) {
//                    $query
//                        ->orWhere('id', Sentinel::getUser()->parent->id)
//                        ->orWhere('users.user_id', Sentinel::getUser()->parent->id);
//                });
//        });

        return $models;
    }

    public function getAllPaidMonth()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $models = $this->model
//	        ->whereHas('user', function ($q) use ($user) {
            ->where('invoices.status', 'Paid Invoice')
            ->where('invoice_date', 'LIKE', date('Y-m') . '%');
//                ->where(function ($query) use ($user) {
//                    $query
//                        ->orWhere('id', $user->parent->id)
//                        ->orWhere('users.user_id', $user->parent->id);
//                });
//        });
        return $models;
    }
}