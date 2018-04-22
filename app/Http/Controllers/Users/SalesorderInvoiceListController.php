<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Models\Invoice;
use App\Models\Saleorder;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class SalesorderInvoiceListController extends UserController
{
    public function __construct()
    {
        parent::__construct();


        view()->share('type', 'salesorder_invoice_list');
    }

    public function index()
    {
        $title = trans('sales_order.salesorder_invoice_list');
        return view('user.sales_order.salesorder_invoice_list',compact('title'));
    }


    public function data(Datatables $datatables)
    {
        $salesOrderDeleteList = Saleorder::onlyInvoiceConvertedLists()->get()
            ->map(function ($salesOrderDeleteList) {
                return [
                    'id' => $salesOrderDeleteList->id,
                    'sale_number' => $salesOrderDeleteList->sale_number,
                    'date' => $salesOrderDeleteList->date,
                    'customer' => isset($salesOrderDeleteList->customer) ?$salesOrderDeleteList->customer->full_name : '',
                    'person' => isset($salesOrderDeleteList->user) ?$salesOrderDeleteList->user->full_name : '',
                    'final_price' => $salesOrderDeleteList->final_price,
                    'status' => $salesOrderDeleteList->status
                ];
            });

        return $datatables->collection($salesOrderDeleteList)
            ->addColumn('actions', '
                                    <a href="{{ url(\'salesorder_invoice_list/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>')
            ->removeColumn('id')
            ->escapeColumns( [ 'actions' ] )->make();
    }
    public function invoiceList($id)
    {
        $invoice_id = Invoice::where('order_id', $id)->get()->first();
        if(isset($invoice_id)){
            return redirect('invoice/' . $invoice_id->id . '/show');
        }else{
            return redirect('salesorder_invoice_list')->withErrors(trans('quotation.converted_invoice'));
        }
    }
}
