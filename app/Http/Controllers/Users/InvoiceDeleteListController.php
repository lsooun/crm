<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class InvoiceDeleteListController extends UserController
{
    public function __construct()
    {
        parent::__construct();


        view()->share('type', 'invoice_delete_list');
    }
    public function index()
    {
        $title = trans('invoice.delete_list');
        return view('user.invoice_delete_list.index',compact('title'));
    }

    public function show($invoice)
    {
        $invoice = Invoice::onlyDeleteLists()->where('id',$invoice)->get()->first();
        $title = 'Show Delete List';
        $action = 'show';
        return view('user.invoice_delete_list.show', compact('title', 'invoice','action'));
    }

    public function delete($invoice){
        $invoice = Invoice::onlyDeleteLists()->where('id',$invoice)->get()->first();
        $title = 'Restore Delete List';
        $action = 'delete';
        return view('user.invoice_delete_list.restore', compact('title', 'invoice','action'));
    }

    public function restoreInvoice($invoice)
    {
        $invoice = Invoice::onlyDeleteLists()->where('id',$invoice)->get()->first();
        $invoice->update(['is_delete_list'=>0]);
        return redirect('invoice');
    }

    /**
     * @param Datatables $datatables
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Datatables $datatables)
    {
        $invoice = Invoice::onlyDeleteLists()->get()
            ->map(function ($invoice) {
                return [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'invoice_date' => $invoice->invoice_date,
                        'customer' => isset($invoice->customer) ? $invoice->customer->full_name : '',
                        'due_date' => $invoice->due_date,
                        'final_price' => $invoice->final_price,
                        'unpaid_amount' => $invoice->unpaid_amount,
                        'status' => $invoice->status,
                        'payment_term' => isset($invoice->payment_term)?$invoice->payment_term:0,
                        'count_payment' => $invoice->receivePayment->count(),
                    ];

            });

        return $datatables->collection($invoice)
            ->addColumn(
                'expired',
                '@if(strtotime(date("m/d/Y"))>strtotime("+".$payment_term."",strtotime($due_date)))
                                        <i class="fa fa-bell-slash text-danger" title="{{trans(\'invoice.invoice_expired\')}}"></i> 
                                     @else
                                      <i class="fa fa-bell text-warning" title="{{trans(\'invoice.invoice_will_expire\')}}"></i> 
                                     @endif'
            )
            ->addColumn(
                'actions',
                '
                                     @if(Sentinel::getUser()->hasAccess([\'invoices.read\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'invoice_delete_list/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>                                 
                                    @endif
                                     @if((Sentinel::getUser()->hasAccess([\'invoices.write\']) || Sentinel::inRole(\'admin\')) && $count_payment==0)
                                       <a href="{{ url(\'invoice_delete_list/\' . $id . \'/restore\' ) }}"  title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-undo text-success"></i> </a>
                                     @endif'
            )
            ->removeColumn('id')
            ->removeColumn('count_payment')
            ->removeColumn('payment_term')
            ->escapeColumns( [ 'actions' ] )->make();
    }
}
