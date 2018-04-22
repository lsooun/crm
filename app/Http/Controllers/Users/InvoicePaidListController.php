<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class InvoicePaidListController extends UserController
{
    public function __construct()
    {
        parent::__construct();


        view()->share('type', 'paid_invoice');
    }

    public function index()
    {

        $title = trans('invoice.paid_invoice');
        return view('user.invoice.paid_invoice',compact('title'));
    }


    public function data(Datatables $datatables)
    {
        $paidList = Invoice::onlyPaidLists()->get()
            ->map(function ($paidList) {
                return [
                    'id' => $paidList->id,
                    'invoice_number' => $paidList->invoice_number,
                    'invoice_date' => $paidList->invoice_date,
                    'customer' => isset($paidList->customer) ? $paidList->customer->full_name : '',
                    'due_date' => $paidList->due_date,
                    'final_price' => $paidList->final_price,
                    'status' => $paidList->status,
                    'payment_term' => isset($paidList->payment_term)?$paidList->payment_term:0,
                    'count_payment' => $paidList->receivePayment->count(),
                ];

            });

        return $datatables->collection($paidList)
            ->removeColumn('id')
            ->removeColumn('count_payment')
            ->removeColumn('payment_term')->make();
    }
}
