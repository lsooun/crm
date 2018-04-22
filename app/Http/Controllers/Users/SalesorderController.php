<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Common;
use App\Http\Controllers\UserController;
use App\Http\Requests\QuotationMailRequest;
use App\Http\Requests\SaleorderRequest;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Email;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\Qtemplate;
use App\Models\QtemplateProduct;
use App\Models\Saleorder;
use App\Models\SaleorderProduct;
use App\Models\Salesteam;
use App\Models\User;
use App\Repositories\CompanyRepository;
use App\Repositories\OptionRepository;
use App\Repositories\ProductRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\QuotationTemplateRepository;
use App\Repositories\SalesOrderRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Efriandika\LaravelSettings\Facades\Settings;
use Sentinel;
use Yajra\Datatables\Datatables;

class SalesorderController extends UserController
{
    /**
     * @var QuotationRepository
     */
    private $salesOrderRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var SalesTeamRepository
     */
    private $salesTeamRepository;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var CompanyRepository
     */
    private $companyRepository;
    /**
     * @var QuotationTemplateRepository
     */
    private $quotationTemplateRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * @param SalesOrderRepository $salesOrderRepository
     * @param UserRepository $userRepository
     * @param SalesTeamRepository $salesTeamRepository
     * @param ProductRepository $productRepository
     * @param CompanyRepository $companyRepository
     * @param QuotationTemplateRepository $quotationTemplateRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(SalesOrderRepository $salesOrderRepository,
                                UserRepository $userRepository,
                                SalesTeamRepository $salesTeamRepository,
                                ProductRepository $productRepository,
                                CompanyRepository $companyRepository,
                                QuotationTemplateRepository $quotationTemplateRepository,
                                OptionRepository $optionRepository)
    {

        $this->middleware('authorized:sales_orders.read', ['only' => ['index', 'data']]);
        $this->middleware('authorized:sales_orders.write', ['only' => ['create', 'store', 'update', 'edit']]);
        $this->middleware('authorized:sales_orders.delete', ['only' => ['delete']]);

        parent::__construct();

        $this->salesOrderRepository = $salesOrderRepository;
        $this->userRepository = $userRepository;
        $this->salesTeamRepository = $salesTeamRepository;
        $this->productRepository = $productRepository;
        $this->companyRepository = $companyRepository;
        $this->quotationTemplateRepository = $quotationTemplateRepository;
        $this->optionRepository = $optionRepository;

        view()->share('type', 'sales_order');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('sales_order.sales_orders');
        return view('user.sales_order.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('sales_order.create');

        $this->generateParams();

        return view('user.sales_order.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SaleorderRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(SaleorderRequest $request)
    {
        $sales_orders = DB::table('sales_orders')->get()->count();
        if($sales_orders == 0){
            $total_fields = 0;
        }else{
            $total_fields = DB::table('sales_orders')->orderBy('id','desc')->first()->id;
        }
        $start_number = Settings::get('sales_start_number');
        $sale_no = Settings::get('sales_prefix') . (is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields : 0) + 1);
        $exp_date = date(Settings::get('date_format'), strtotime(' + ' . isset($request->payment_term)?$request->payment_term:Settings::get('opportunities_reminder_days') . ' days'));

        $saleorder = new Saleorder($request->only('customer_id', 'qtemplate_id', 'date',
            'exp_date', 'payment_term', 'sales_person_id', 'sales_team_id', 'terms_and_conditions', 'status', 'total',
            'tax_amount', 'grand_total','discount','final_price'));
        $saleorder->sale_number = $sale_no;
        $saleorder->exp_date  = isset($request->exp_date)?$request->exp_date:strtotime($exp_date);
        $saleorder->user_id = Sentinel::getUser()->id;
        $saleorder->save();

        if (!empty($request->product_id)) {
            foreach ($request->product_id as $key => $item) {
                if ($item != "" && $request->product_name[$key] != "" &&
                    $request->quantity[$key] != "" && $request->price[$key] != "" && $request->sub_total[$key] != ""
                ) {
                    $saleorderProduct = new SaleorderProduct();
                    $saleorderProduct->order_id = $saleorder->id;
                    $saleorderProduct->product_id = $item;
                    $saleorderProduct->product_name = $request->product_name[$key];
                    $saleorderProduct->description = $request->description[$key];
                    $saleorderProduct->quantity = $request->quantity[$key];
                    $saleorderProduct->price = $request->price[$key];
                    $saleorderProduct->sub_total = $request->sub_total[$key];
                    $saleorderProduct->save();
                }
            }
        }
        return redirect("sales_order");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Saleorder $saleorder
     * @return \Illuminate\Http\Response
     * @internal param QuotationSaleorder $quotation
     */
    public function edit(Saleorder $saleorder)
    {
        $title = trans('sales_order.edit');

        $this->generateParams();
        $this->emailRecipients($saleorder->customer_id);
        $sales_team = Salesteam::where('id',$saleorder->sales_team_id)->pluck('team_leader','id');
        $sales_team_members=Salesteam::where('id',$saleorder->sales_team_id)->pluck('team_members','id');
        $sales_team_members = $sales_team_members[$saleorder->sales_team_id];
        $main_staff = User::whereIn('id', $sales_team->merge($sales_team_members))->get()->pluck('full_name','id');
        return view('user.sales_order.edit', compact('title', 'saleorder','main_staff'));
    }

    /**
     * Update the specified resource in storage.
     * @param SaleorderRequest $request
     * @param Saleorder $saleorder
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(SaleorderRequest $request, Saleorder $saleorder)
    {
        $saleorder->update($request->only('customer_id', 'qtemplate_id', 'date',
            'exp_date', 'payment_term', 'sales_person_id', 'sales_team_id', 'terms_and_conditions', 'status', 'total',
            'tax_amount', 'grand_total','discount','final_price'));

        SaleorderProduct::where('order_id', $saleorder->id)->delete();
        if (!empty($request->product_id)) {
            foreach ($request->product_id as $key => $item) {
                if ($item != "" && $request->product_name[$key] != "" &&
                    $request->quantity[$key] != "" && $request->price[$key] != "" && $request->sub_total[$key] != ""
                ) {
                    $saleorderProduct = new SaleorderProduct();
                    $saleorderProduct->order_id = $saleorder->id;
                    $saleorderProduct->product_id = $item;
                    $saleorderProduct->product_name = $request->product_name[$key];
                    $saleorderProduct->description = $request->description[$key];
                    $saleorderProduct->quantity = $request->quantity[$key];
                    $saleorderProduct->price = $request->price[$key];
                    $saleorderProduct->sub_total = $request->sub_total[$key];
                    $saleorderProduct->save();
                }
            }
        }
        if ($request->status == trans('sales_order.draft_salesorder')){
            return redirect("sales_order/draft_salesorders");
        }else{
            return redirect("sales_order");
        }
    }

    public function show(Saleorder $saleorder)
    {
        $title = trans('sales_order.show');
        $this->generateParams();
        $this->emailRecipients($saleorder->customer_id);
        $action = 'show';
        return view('user.sales_order.show', compact('title', 'saleorder','action'));
    }

    public function delete(Saleorder $saleorder)
    {
        $title = trans('sales_order.delete');
        $this->generateParams();
        return view('user.sales_order.delete', compact('title', 'saleorder'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Saleorder $saleorder
     * @return \Illuminate\Http\Response
     * @throws \Exception
     * @internal param QuotationSaleorder $quotation
     */
    public function destroy(Saleorder $saleorder)
    {
        $saleorder->update(['is_delete_list' => 1]);
        return redirect('sales_order');
    }

    public function data(Datatables $datatables)
    {
        $sales_order = $this->salesOrderRepository->getAll()
            ->where('status',trans('sales_order.send_salesorder'))
            ->with('user', 'customer')
            ->get()
            ->map(function ($saleOrder) {
                return [
                    'id' => $saleOrder->id,
                    'sale_number' => $saleOrder->sale_number,
                    'customer' => isset($saleOrder->customer) ?$saleOrder->customer->full_name : '',
                    'final_price' => $saleOrder->final_price,
                    'date' => $saleOrder->date,
                    'exp_date' => $saleOrder->exp_date,
                    'payment_term' => $saleOrder->payment_term,
                    'status' => $saleOrder->status
                ];
            });
        return $datatables->collection($sales_order)
            ->addColumn(
                'expired',
                '@if(strtotime(date("m/d/Y"))>strtotime("+".$payment_term." ",strtotime($exp_date)))
                                        <i class="fa fa-bell-slash text-danger" title="{{trans(\'sales_order.salesorder_expired\')}}"></i> 
                                     @else
                                      <i class="fa fa-bell text-warning" title="{{trans(\'sales_order.salesorder_will_expire\')}}"></i> 
                                     @endif'
            )
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'sales_orders.write\']) || Sentinel::inRole(\'admin\') )
                                        
                                     @endif
                                     @if(Sentinel::getUser()->hasAccess([\'sales_orders.read\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'sales_order/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                     <a href="{{ url(\'sales_order/\' . $id . \'/print_quot\' ) }}" title="{{ trans(\'table.print\') }}">
                                            <i class="fa fa-fw fa-print text-primary "></i>  </a>
                                    @endif
                                     @if(Sentinel::getUser()->hasAccess([\'sales_orders.delete\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'sales_order/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                     @endif')
            ->removeColumn('id')
            ->escapeColumns( [ 'actions' ] )->make();
    }

    public function draftIndex(){
        $title=trans('sales_order.draft_salesorder');
        return view('user.sales_order.draft_salesorders', compact('title'));
    }
    public function draftSalesOrders(Datatables $datatables)
    {
        $sales_order = $this->salesOrderRepository->getAll()
            ->where('status',trans('sales_order.draft_salesorder'))
            ->with('user', 'customer')
            ->get()
            ->map(function ($saleOrder) {
                return [
                    'id' => $saleOrder->id,
                    'sale_number' => $saleOrder->sale_number,
                    'customer' => isset($saleOrder->customer) ?$saleOrder->customer->full_name : '',
                    'final_price' => $saleOrder->final_price,
                    'date' => $saleOrder->date,
                    'exp_date' => $saleOrder->exp_date,
                    'payment_term' => $saleOrder->payment_term,
                    'status' => $saleOrder->status
                ];
            });
        return $datatables->collection($sales_order)
            ->addColumn(
                'expired',
                '@if(strtotime(date("m/d/Y"))>strtotime("+".$payment_term." ",strtotime($exp_date)))
                                        <i class="fa fa-bell-slash text-danger" title="{{trans(\'sales_order.salesorder_expired\')}}"></i> 
                                     @else
                                      <i class="fa fa-bell text-warning" title="{{trans(\'sales_order.salesorder_will_expire\')}}"></i> 
                                     @endif'
            )
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'sales_orders.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'sales_order/\' . $id . \'/edit\' ) }}"  title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning "></i>  </a>
                                     @endif
                                     @if(Sentinel::getUser()->hasAccess([\'sales_orders.read\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'sales_order/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                     <a href="{{ url(\'sales_order/\' . $id . \'/print_quot\' ) }}" title="{{ trans(\'table.print\') }}">
                                            <i class="fa fa-fw fa-print text-primary "></i>  </a>
                                    @endif
                                     @if(Sentinel::getUser()->hasAccess([\'sales_orders.delete\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'sales_order/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                     @endif')
            ->removeColumn('id')
            ->escapeColumns( [ 'actions' ] )->make();
    }

    /**
     * @param Qtemplate $qtemplate
     */
    public function ajaxQtemplatesProducts(Qtemplate $qtemplate)
    {
        return QtemplateProduct::where('qtemplate_id', $qtemplate->id)->get();
    }


    public function printQuot(Saleorder $saleorder)
    {
        $filename = 'SalesOrder-' . $saleorder->sale_number;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4','landscape');
        $pdf->loadView('saleorder_template.'.Settings::get('saleorder_template'), compact('saleorder'));
        return $pdf->download($filename . '.pdf');
    }

    public function ajaxCreatePdf(Saleorder $saleorder)
    {
        $filename = 'SalesOrder-' . $saleorder->sale_number;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4','landscape');
        $pdf->loadView('saleorder_template.'.Settings::get('saleorder_template'), compact('saleorder'));
        $pdf->save('./pdf/' . $filename . '.pdf');
        $pdf->stream();
        echo url("pdf/" . $filename . ".pdf");

    }

    public function sendSaleorder(Request $request)
    {
        $saleorder_id = $request->saleorder_id;
        $email_subject = $request->email_subject;
	    $to_customers = Customer::whereIn('user_id', $request->recipients)->get();
        $email_body = $request->message_body;
        $message_body = Common::parse_template($email_body);
        $saleorder_pdf = $request->saleorder_pdf;
        if (!empty($to_customers) && !filter_var(Settings::get('site_email'), FILTER_VALIDATE_EMAIL) === false) {
            foreach ($to_customers as $item) {
                if (!filter_var($item->user->email, FILTER_VALIDATE_EMAIL) === false) {
                    Mail::send('emails.quotation', array('message_body' => $message_body), function ($message)
                    use ($item, $email_subject, $saleorder_pdf) {
                        $message->from(Settings::get('site_email'), Settings::get('site_name'));
                        $message->to($item->user->email)->subject($email_subject);
                        $message->attach(url('/pdf/' . $saleorder_pdf));
                    });
                }
                $email = new Email();
                $email->assign_customer_id = $item->id;
                $email->to = $item->user_id;
                $email->from = Sentinel::getUser()->id;
                $email->subject = $email_subject;
                $email->message = $message_body;
                $email->save();
            }
            echo '<div class="alert alert-success">' . trans('sales_order.success') . '</div>';
        }
        else {
            echo '<div class="alert alert-danger">' . trans('invoice.error') . '</div>';
        }
    }

    public function makeInvoice(Saleorder $saleorder)
    {
        $invoices = DB::table('invoices')->get()->count();
        if($invoices == 0){
            $total_fields = 0;
        }else{
            $total_fields = DB::table('invoices')->orderBy('id','desc')->first()->id;
        }
        $start_number = Settings::get('invoice_start_number');
        $invoice_number = Settings::get('invoice_prefix') . (is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields : 0) + 1);
        $saleorder->update(['is_invoice_list' => 1]);

        $invoice_details = [
            'order_id' => $saleorder->id,
            'customer_id' => $saleorder->customer_id,
            'sales_person_id' => $saleorder->sales_person_id,
            'sales_team_id' => $saleorder->sales_team_id,
            'invoice_number' => $invoice_number,
            'invoice_date' =>date(Settings::get('date_format')),
            'due_date' => $saleorder->exp_date,
            'payment_term' => isset($saleorder->payment_term)?$saleorder->payment_term:0,
            'status' => 'Open Invoice',
            'total' => $saleorder->total,
            'tax_amount' => $saleorder->tax_amount,
            'grand_total' => $saleorder->grand_total,
            'unpaid_amount' => $saleorder->final_price,
            'discount' => $saleorder->discount,
            'final_price' => $saleorder->final_price,
            'user_id' => Sentinel::getUser()->id,
            'qtemplate_id' => $saleorder->qtemplate_id,
        ];
        $invoice = Invoice::create($invoice_details);

        $products = $saleorder->products;
        if ($products->count()>0) {
            foreach ($products as $item) {
                $product_add = [
                    'invoice_id' => $invoice->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'sub_total' => $item->sub_total
                ];
                InvoiceProduct::create($product_add);
            }
        }

        return redirect('invoice');
    }


    private function generateParams()
    {
        $products = $this->productRepository->getAll()->orderBy("id", "desc")->get();

        $qtemplates = $this->quotationTemplateRepository->getAll()
	            ->pluck('quotation_template', 'id')
	            ->prepend(trans('dashboard.select_template'), '');

        $companies = $this->companyRepository->getAll()->orderBy("name", "asc")
	            ->pluck('name', 'id')
	            ->prepend(trans('dashboard.select_company'), '');

        $staffs = $this->userRepository->getStaff()
	            ->pluck('full_name', 'id')
	            ->prepend(trans('dashboard.select_staff'), '');

        $salesteams = $this->salesTeamRepository->getAll()
	            ->pluck('salesteam', 'id')
	            ->prepend(trans('dashboard.select_sales_team'), '');

        $customers = $this->userRepository->getParentCustomers()
	            ->pluck('full_name', 'id')
	            ->prepend(trans('dashboard.select_customer'), '');

        $companies_mail = $this->userRepository->getAll()->get()->filter(function ($user) {
            return $user->inRole('customer');
        })->pluck('full_name', 'id');

        $statuses = $this->optionRepository->getAll()
            ->where('category', 'sales_order_status')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title', 'value')->prepend(trans('quotation.status'), '');

        view()->share('statuses', $statuses);
        view()->share('products', $products);
        view()->share('qtemplates', $qtemplates);
        view()->share('companies', $companies);
        view()->share('staffs', $staffs);
        view()->share('salesteams', $salesteams);
        view()->share('customers', $customers);
        view()->share('companies_mail', $companies_mail);
    }

    private function emailRecipients($customer_id){
        $email_recipients = $this->userRepository->getParentCustomers()->where('id',$customer_id)->pluck('full_name','id');
        view()->share('email_recipients', $email_recipients);
    }
}
