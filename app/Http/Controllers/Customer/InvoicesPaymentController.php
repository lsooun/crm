<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\UserController;
use App\Http\Requests\InvoiceReceivePaymentRequest;
use App\Http\Requests\InvoiceRequest;
use App\Models\Invoice;
use App\Models\InvoiceReceivePayment;
use App\Models\Option;
use App\Repositories\CompanyRepository;
use App\Repositories\InvoicePaymentRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\OptionRepository;
use App\Repositories\UserRepository;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Sentinel;
use App\Http\Requests;
use Yajra\Datatables\Datatables;

class InvoicesPaymentController extends UserController
{
    /**
     * @var InvoicePaymentRepository
     */
    private $invoicePaymentRepository;

    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * @param InvoicePaymentRepository $invoicePaymentRepository
     * @param CompanyRepository $companyRepository
     * @param InvoiceRepository $invoiceRepository
     * @param UserRepository $userRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(InvoicePaymentRepository $invoicePaymentRepository,
                                CompanyRepository $companyRepository,
                                InvoiceRepository $invoiceRepository,
                                UserRepository $userRepository,
                                OptionRepository $optionRepository)
    {
        parent::__construct();

        $this->invoicePaymentRepository = $invoicePaymentRepository;
        $this->companyRepository = $companyRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->userRepository = $userRepository;
        $this->optionRepository = $optionRepository;

        view()->share('type', 'customers/invoices_payment_log');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('invoices_payment_log.invoices_payment_log');
        return view('customers.invoices_payment_log.index', compact('title'));
    }

    public function show(InvoiceReceivePayment $invoiceReceivePayment)
    {
        $title = trans('invoices_payment_log.show');
        $action = 'show';
        return view('customers.invoices_payment_log.show', compact('title', 'action','invoiceReceivePayment'));
    }


    public function data(Datatables $datatables)
    {
        $invoice_payments = $this->invoicePaymentRepository->getAllForCustomer(Sentinel::getUser()->id)
            ->with('customer')
            ->get()->map(function ($ip) {
                return [
                    'id' => $ip->id,
                    'payment_number' =>  $ip->payment_number,
                    'payment_received' => $ip->payment_received,
                    'invoice_number' => isset($ip->invoice) ? $ip->invoice->invoice_number : '',
                    'payment_method' => $ip->payment_method,
                    'payment_date' => $ip->payment_date,
                    'customer' => (isset($ip->invoice) && isset($ip->invoice->customer)) ?$ip->customer->full_name: "",
                    'salesperson' => (isset($ip->invoice) && isset($ip->invoice->salesPerson)) ?$ip->invoice->salesPerson->full_name: ""
                ];
            });

        return $datatables->collection($invoice_payments)
            ->addColumn('actions', '<a href="{{ url(\'customers/invoices_payment_log/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}">
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>')
            ->removeColumn('id')
            ->escapeColumns( [ 'actions' ] )->make();
    }

    private function generateParams()
    {
        $payment_methods = $this->optionRepository->getAll()
            ->where('category', 'payment_methods')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title', 'value')->prepend(trans('invoice.payment_method'),'');

        view()->share('payment_methods', $payment_methods);
    }
    public function paymentLog(Request $request){
        $payment_details= Invoice::where( 'id', $request->id )->get()->first();
        return $payment_details;
    }
}
