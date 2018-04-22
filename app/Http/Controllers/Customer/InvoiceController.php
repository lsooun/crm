<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\UserController;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Repositories\InvoiceRepository;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Sentinel;
use Yajra\Datatables\Datatables;

class InvoiceController extends UserController
{
    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;

    public function __construct(InvoiceRepository $invoiceRepository)
    {
        parent::__construct();

        view()->share('type', 'customers/invoice');
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $open_invoice_total = round($this->invoiceRepository->getAllOpenForCustomer($this->user->id)->sum('grand_total'), 3);
        $overdue_invoices_total = round(InvoicePayment::all()->where('status','Overdue Invoice')->where('customer_id',Sentinel::getUser()->id)->sum('unpaid_amount'),3);
        $paid_invoices_total = round(InvoicePayment::all()->where('status','Paid Invoice')->where('customer_id',Sentinel::getUser()->id)->sum('grand_total'),3);
        $invoices_total_collection = round(InvoicePayment::all()->where('customer_id',Sentinel::getUser()->id)->sum('grand_total'), 3);

        $title = trans('invoice.invoices');
        return view('customers.invoice.index', compact('title','open_invoice_total','overdue_invoices_total',
            'paid_invoices_total','invoices_total_collection'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Invoice $invoice
     * @return \Illuminate\Http\Response
     */
    public function show(Invoice $invoice)
    {
        $title = trans('invoice.show') . ' ' . $invoice->invoice_number;
        return view('customers.invoice.show', compact('title','invoice'));
    }

    public function data(Datatables $datatables)
    {
        $invoices = $this->invoiceRepository->getAllForCustomer(Sentinel::getUser()->id)
            ->with('customer')
            ->get()
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
                    'count_payment' => $invoice->receivePayment->count()
                ];
            });
        return Datatables::of($invoices)
            ->addColumn('expired', '@if(strtotime(date("m/d/Y"))>strtotime("+".$payment_term."",strtotime($due_date)))
                                        <i class="fa fa-bell-slash text-danger" title="{{trans(\'invoice.invoice_expired\')}}"></i>
                                     @else
                                      <i class="fa fa-bell text-warning" title="{{trans(\'invoice.invoice_will_expire\')}}"></i>
                                     @endif')
            ->addColumn('actions', '<a href="{{ url(\'customers/invoice/\' . $id . \'/show\' ) }}"  title={{ trans("table.details")}}>
                                            <i class="fa fa-fw fa-eye text-primary"></i>  </a>')
            ->removeColumn('id')
            ->removeColumn('count_payment')
            ->removeColumn('payment_term')
            ->escapeColumns( [ 'actions' ] )->make();
    }

    /**
     * @param Invoice $invoice
     * @return mixed
     */
    public function printQuot(Invoice $invoice)
    {
        $filename = 'Invoice-' . $invoice->invoice_number;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4', 'landscape');
        $pdf->loadView('invoice_template.'.Settings::get('invoice_template'), compact('invoice'));
        return $pdf->download($filename . '.pdf');
    }

    /**
     * @param Invoice $invoice
     */
    public function ajaxCreatePdf(Invoice $invoice)
    {
        $filename = 'Invoice-' . Str::slug($invoice->invoice_number);
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4','landscape');
        $pdf->loadView('invoice_template.'.Settings::get('invoice_template'), compact('invoice'));
        $pdf->save('./pdf/' . $filename . '.pdf');
        $pdf->stream();
        echo url("pdf/" . $filename . ".pdf");

    }
}
