<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Saleorder;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class QuotationInvoiceListController extends UserController
{
    private $quotationRepository;

    public function __construct()
    {
        parent::__construct();


        view()->share('type', 'quotation_invoice_list');
    }

    public function index()
    {
        $title = trans('quotation.quotation_invoice_list');
        return view('user.quotation.quotation_invoice_list',compact('title'));
    }


    public function data(Datatables $datatables)
    {
        $quotationInvoiceList = Quotation::onlyQuotationInvoiceLists()->get()
            ->map(function ($quotationInvoiceList) {
                return [
                    'id' => $quotationInvoiceList->id,
                    'quotations_number' => $quotationInvoiceList->quotations_number,
                    'customer' => isset($quotationInvoiceList->customer) ? $quotationInvoiceList->customer->full_name : '',
                    'sales_team_id' => $quotationInvoiceList->salesTeam->salesteam,
                    'sales_person' => isset($quotationInvoiceList->salesPerson) ? $quotationInvoiceList->salesPerson->full_name : '',
                    'final_price' => $quotationInvoiceList->final_price,
                    'payment_term' => $quotationInvoiceList->payment_term,
                    'status' => $quotationInvoiceList->status
                ];
            });

        return $datatables->collection($quotationInvoiceList)
            ->addColumn('actions', '
                                    <a href="{{ url(\'quotation_invoice_list/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
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
            return redirect('quotation_invoice_list')->withErrors(trans('quotation.converted_invoice'));
        }
    }
}
