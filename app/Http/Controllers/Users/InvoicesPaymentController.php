<?php

namespace App\Http\Controllers\Users;

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

        view()->share('type', 'invoices_payment_log');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('invoices_payment_log.invoices_payment_log');
        return view('user.invoices_payment_log.index', compact('title'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('invoices_payment_log.new');
        $invoices = $this->invoiceRepository->getAll()
            ->where('status','Open Invoice')
            ->orWhere('status','Overdue invoice')
            ->orderBy('invoice_number', 'asc')
            ->pluck('invoice_number', 'id')->prepend(trans('invoice.invoice_number'),'');

        $this->generateParams();
        return view('user.invoices_payment_log.create', compact('title', 'invoices'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param InvoiceReceivePaymentRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(InvoiceReceivePaymentRequest $request)
    {
        $invoice = Invoice::find($request->invoice_id);

        $recive_payment = InvoiceReceivePayment::all()->count();

        if($recive_payment == 0){
            $total_fields = 0;
        }else{
            $total_fields = DB::table('invoice_receive_payments')->orderBy('id','desc')->first()->id;

        }
        $start_number = Settings::get(
            'invoice_payment_prefix'
        ) ;

        $payment_no = Settings::get('invoice_payment_prefix').(is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields : 0) + 1);

        $payment_date = date(Settings::get('date_format'), strtotime(' + ' . $request->payment_date));

        $invoiceRepository = $this->invoiceRepository->create($request->except('invoice_id'));
        $invoiceRepository->invoice()->associate($invoice);
        $invoiceRepository->payment_number = $payment_no;
        $invoiceRepository->payment_date = isset($request->payment_date) ? $request->payment_date : strtotime($payment_date);
        $invoiceRepository->customer_id = $invoice->customer_id;
        $invoiceRepository->save();

        $unpaid_amount_new = round($invoice->unpaid_amount - $request->payment_received, 2);

        if ($unpaid_amount_new <= '0') {
            $invoice_data = array(
                'unpaid_amount' => $unpaid_amount_new,
                'status' => 'Paid Invoice',
            );
        } else {
            $invoice_data = array(
                'unpaid_amount' => $unpaid_amount_new,
            );
        }

        $invoice->update($invoice_data);

        return redirect("invoices_payment_log");
    }

    public function show(InvoiceReceivePayment $invoiceReceivePayment)
    {
        $title = trans('invoices_payment_log.show');
        $action = 'show';
        return view('user.invoices_payment_log.show', compact('title', 'action','invoiceReceivePayment'));
    }

    public function delete(InvoiceReceivePayment $invoiceReceivePayment)
    {
        $title = trans('invoices_payment_log.delete');
        return view('user.invoices_payment_log.delete', compact('title', 'invoiceReceivePayment'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Invoice $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy(InvoiceReceivePayment $invoiceReceivePayment)
    {
        $invoiceReceivePayment->delete();
        return redirect('invoices_payment_log');
    }


    public function data(Datatables $datatables)
    {
        $invoice_payments = $this->invoicePaymentRepository->getAll()
            ->with('invoice.customer', 'invoice.salesPerson')
            ->get()->map(function ($ip) {
                return [
                    'id' => $ip->id,
                    'payment_number' =>  $ip->payment_number,
                    'payment_received' => $ip->payment_received,
                    'invoice_number' => isset($ip->invoice) ? $ip->invoice->invoice_number : '',
                    'payment_method' => $ip->payment_method,
                    'payment_date' => $ip->payment_date,
                    'customer' => (isset($ip->invoice) && isset($ip->invoice->customer)) ?$ip->invoice->customer->full_name: "",
                    'salesperson' => (isset($ip->invoice) && isset($ip->invoice->salesPerson)) ?$ip->invoice->salesPerson->full_name: ""
                ];
            });

        return $datatables->collection($invoice_payments)
            ->addColumn('actions', '<a href="{{ url(\'invoices_payment_log/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}">
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
