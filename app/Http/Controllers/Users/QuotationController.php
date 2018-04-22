<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Common;
use App\Http\Controllers\UserController;
use App\Http\Requests\QuotationMailRequest;
use App\Http\Requests\QuotationRequest;
use App\Models\Customer;
use App\Models\Email;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\Qtemplate;
use App\Models\QtemplateProduct;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\Saleorder;
use App\Models\SaleorderProduct;
use App\Models\Salesteam;
use App\Models\User;
use App\Repositories\CompanyRepository;
use App\Repositories\OptionRepository;
use App\Repositories\ProductRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\QuotationTemplateRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Efriandika\LaravelSettings\Facades\Settings;
use Sentinel;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Mail\SendQuotation;

class QuotationController extends UserController
{
    /**
     * @var QuotationRepository
     */
    private $quotationRepository;
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
     * QuotationController constructor.
     * @param QuotationRepository $quotationRepository
     * @param UserRepository $userRepository
     * @param SalesTeamRepository $salesTeamRepository
     * @param ProductRepository $productRepository
     * @param CompanyRepository $companyRepository
     * @param QuotationTemplateRepository $quotationTemplateRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(QuotationRepository $quotationRepository,
                                UserRepository $userRepository,
                                SalesTeamRepository $salesTeamRepository,
                                ProductRepository $productRepository,
                                CompanyRepository $companyRepository,
                                QuotationTemplateRepository $quotationTemplateRepository,
                                OptionRepository $optionRepository)
    {
        parent::__construct();

        $this->middleware('authorized:quotations.read', ['only' => ['index', 'data']]);
        $this->middleware('authorized:quotations.write', ['only' => ['create', 'store', 'update', 'edit']]);
        $this->middleware('authorized:quotations.delete', ['only' => ['delete']]);

        $this->quotationRepository = $quotationRepository;
        $this->userRepository = $userRepository;
        $this->salesTeamRepository = $salesTeamRepository;
        $this->productRepository = $productRepository;
        $this->companyRepository = $companyRepository;
        $this->quotationTemplateRepository = $quotationTemplateRepository;
        $this->optionRepository = $optionRepository;

        view()->share('type', 'quotation');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('quotation.quotations');
        return view('user.quotation.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('quotation.create');

        $this->generateParams();

        return view('user.quotation.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param QuotationRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(QuotationRequest $request)
    {
        $quotations = DB::table('quotations')->get()->count();
        if($quotations == 0){
            $total_fields = 0;
        }else{
            $total_fields = DB::table('quotations')->orderBy('id','desc')->first()->id;
        }
        $start_number = Settings::get('quotation_start_number') ;
        $quotation_no = Settings::get('quotation_prefix') . (is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields : 0) + 1);
        $exp_date = date(Settings::get('date_format'), strtotime(' + ' . isset($request->payment_term) ? $request->payment_term : Settings::get('opportunities_reminder_days') . ' days'));


        $quotation = new Quotation($request->only('customer_id', 'qtemplate_id', 'date',
            'exp_date', 'payment_term', 'sales_person_id', 'sales_team_id', 'terms_and_conditions', 'status','total','discount','grand_total','final_price'));
        $quotation->quotations_number = $quotation_no;
        $quotation->exp_date = isset($request->exp_date) ? $request->exp_date : strtotime($exp_date);
        $quotation->user_id = Sentinel::getUser()->id;
        $quotation->save();

        if (!empty($request->product_id)) {
            foreach ($request->product_id as $key => $item) {
                if ($item != "" && $request->product_name[$key] != "" &&
                    $request->quantity[$key] != "" && $request->price[$key] != "" && $request->sub_total[$key] != ""
                ) {
                    $quotationProduct = new QuotationProduct();
                    $quotationProduct->quotation_id = $quotation->id;
                    $quotationProduct->product_id = $item;
                    $quotationProduct->product_name = $request->product_name[$key];
                    $quotationProduct->description = $request->description[$key];
                    $quotationProduct->quantity = $request->quantity[$key];
                    $quotationProduct->price = $request->price[$key];
                    $quotationProduct->sub_total = $request->sub_total[$key];
                    $quotationProduct->save();
                }
            }
        }
        if ($request->status == trans('quotation.draft_quotation')){
            return redirect("quotation/draft_quotations");
        }else{
            return redirect("quotation");
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Quotation $quotation
     * @return \Illuminate\Http\Response
     */
    public function edit(Quotation $quotation)
    {
        $title = trans('quotation.edit');

        $this->generateParams();
        $this->emailRecipients($quotation->customer_id);
        $sales_team = Salesteam::where('id',$quotation->sales_team_id)->pluck('team_leader','id');
        $sales_team_members=Salesteam::where('id',$quotation->sales_team_id)->pluck('team_members','id');
        $sales_team_members = $sales_team_members[$quotation->sales_team_id];
        $main_staff = User::whereIn('id', $sales_team->merge($sales_team_members))->get()->pluck('full_name','id');

        return view('user.quotation.edit', compact('title', 'quotation','main_staff'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param QuotationRequest|Request $request
     * @param Quotation $quotation
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function update(QuotationRequest $request, Quotation $quotation)
    {
        $quotation->update($request->only('customer_id', 'qtemplate_id', 'date',
            'exp_date', 'payment_term', 'sales_person_id', 'sales_team_id', 'terms_and_conditions', 'status', 'total',
            'tax_amount', 'grand_total','discount','final_price'));

        QuotationProduct::where('quotation_id', $quotation->id)->delete();
        if (!empty($request->product_id)) {
            foreach ($request->product_id as $key => $item) {
                if ($item != "" && $request->product_name[$key] != "" &&
                    $request->quantity[$key] != "" && $request->price[$key] != "" && $request->sub_total[$key] != ""
                ) {
                    $quotationProduct = new QuotationProduct();
                    $quotationProduct->quotation_id = $quotation->id;
                    $quotationProduct->product_id = $item;
                    $quotationProduct->product_name = $request->product_name[$key];
                    $quotationProduct->description = $request->description[$key];
                    $quotationProduct->quantity = $request->quantity[$key];
                    $quotationProduct->price = $request->price[$key];
                    $quotationProduct->sub_total = $request->sub_total[$key];
                    $quotationProduct->save();
                }
            }
        }
        if ($request->status == trans('quotation.draft_quotation')){
            return redirect("quotation/draft_quotations");
        }else{
            return redirect("quotation");
        }
    }

    public function show(Quotation $quotation)
    {
        $title = trans('quotation.show');
        $action = 'show';
        $this->generateParams();
        $this->emailRecipients($quotation->customer_id);
        return view('user.quotation.show', compact('title', 'quotation','action'));
    }

    public function delete(Quotation $quotation)
    {
        $title = trans('quotation.delete');
        $this->generateParams();
        return view('user.quotation.delete', compact('title', 'quotation'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Quotation $quotation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Quotation $quotation)
    {
        $quotation->update(['is_delete_list' => 1]);
        return redirect('quotation');
    }

    /**
     * @return mixed
     */
    public function data(Datatables $datatables)
    {
        $quotations = $this->quotationRepository->getAll()
            ->where([
                ['status','!=','Draft Quotation']
            ])
            ->with('user', 'customer')
            ->get()
            ->map(function ($quotation) {
                return [
                    'id' => $quotation->id,
                    'quotations_number' => $quotation->quotations_number,
                    'customer' => isset($quotation->customer) ? $quotation->customer->full_name : '',
                    'final_price' => $quotation->final_price,
                    'date' => $quotation->date,
                    'exp_date' => $quotation->exp_date,
                    'payment_term' => $quotation->payment_term,
                    'status' => $quotation->status
                ];
            });

        return $datatables->collection($quotations)
            ->addColumn(
                'expired',
                '@if(strtotime(date("m/d/Y"))>strtotime("+".$payment_term." ",strtotime($exp_date)))
                                        <i class="fa fa-bell-slash text-danger" title="{{trans(\'quotation.quotation_expired\')}}"></i> 
                                     @else
                                      <i class="fa fa-bell text-warning" title="{{trans(\'quotation.quotation_will_expire\')}}"></i> 
                                     @endif'
            )
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'quotations.write\']) || Sentinel::inRole(\'admin\'))
                                    
                                     @endif
                                     @if(Sentinel::getUser()->hasAccess([\'quotations.read\']) || Sentinel::inRole(\'admin\'))
                                    <a href="{{ url(\'quotation/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                     <a href="{{ url(\'quotation/\' . $id . \'/print_quot\' ) }}" title="{{ trans(\'table.print\') }}">
                                            <i class="fa fa-fw fa-print text-primary "></i>  </a>
                                    @endif
                                    
                                     @if(Sentinel::getUser()->hasAccess([\'quotations.delete\']) || Sentinel::inRole(\'admin\'))
                                   <a href="{{ url(\'quotation/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                   @endif')
            ->removeColumn('id')
            ->escapeColumns( [ 'actions' ] )->make();
    }

    public function draftIndex(){
        $title=trans('quotation.draft_quotations');
        return view('user.quotation.draft_quotations', compact('title'));
    }
    public function draftQuotations(Datatables $datatables)
    {
        $quotations = $this->quotationRepository->getAll()
            ->where('status',trans('quotation.draft_quotation'))
            ->with('user', 'customer')
            ->get()
            ->map(function ($quotation) {
                return [
                    'id' => $quotation->id,
                    'quotations_number' => $quotation->quotations_number,
                    'customer' => isset($quotation->customer) ? $quotation->customer->full_name : '',
                    'final_price' => $quotation->final_price,
                    'date' => $quotation->date,
                    'exp_date' => $quotation->exp_date,
                    'payment_term' => $quotation->payment_term,
                    'status' => $quotation->status
                ];
            });

        return $datatables->collection($quotations)
            ->addColumn(
                'expired',
                '@if(strtotime(date("m/d/Y"))>strtotime("+".$payment_term." ",strtotime($exp_date)))
                                        <i class="fa fa-bell-slash text-danger" title="{{trans(\'quotation.quotation_expired\')}}"></i> 
                                     @else
                                      <i class="fa fa-bell text-warning" title="{{trans(\'quotation.quotation_will_expire\')}}"></i> 
                                     @endif'
            )
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'quotations.write\']) || Sentinel::inRole(\'admin\'))
                                    <a href="{{ url(\'quotation/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}" >
                                            <i class="fa fa-fw fa-pencil text-warning"></i>  </a>
                                     @endif
                                     @if(Sentinel::getUser()->hasAccess([\'quotations.read\']) || Sentinel::inRole(\'admin\'))
                                    <a href="{{ url(\'quotation/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                     <a href="{{ url(\'quotation/\' . $id . \'/print_quot\' ) }}" title="{{ trans(\'table.print\') }}">
                                            <i class="fa fa-fw fa-print text-primary "></i>  </a>
                                    @endif                                
                                     @if(Sentinel::getUser()->hasAccess([\'quotations.delete\']) || Sentinel::inRole(\'admin\'))
                                   <a href="{{ url(\'quotation/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                   @endif')
            ->removeColumn('id')
            ->escapeColumns( [ 'actions' ] )->make();
    }

    function confirmSalesOrder(Quotation $quotation)
    {
        $quotation->update(['is_converted_list' => 1]);
        $sales_orders = DB::table('sales_orders')->get()->count();
        if($sales_orders == 0){
            $total_fields = 0;
        }else{
            $total_fields = DB::table('sales_orders')->orderBy('id','desc')->first()->id;
        }
        $start_number = Settings::get('sales_start_number');
        $sale_no = Settings::get('sales_prefix') . (is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields : 0) + 1);

        $saleorder = Saleorder::create(array(
            'sale_number' => $sale_no,
            'customer_id' => $quotation->customer_id,
            'date' => date(Settings::get('date_format')),
            'exp_date' => $quotation->exp_date,
            'qtemplate_id' => $quotation->qtemplate_id,
            'payment_term' => isset($quotation->payment_term)?$quotation->payment_term:0,
            "sales_person_id" => $quotation->sales_person_id,
            "sales_team_id" => $quotation->sales_team_id,
            "terms_and_conditions" => $quotation->terms_and_conditions,
            "total" => $quotation->total,
            "tax_amount" => $quotation->tax_amount,
            "grand_total" => $quotation->grand_total,
            "discount" => is_null($quotation->discount)?0:$quotation->discount,
            "final_price" => $quotation->final_price,
            'status' => 'Draft sales order',
            'user_id' => Sentinel::getUser()->id,
            'quotation_id' => $quotation->id
        ));

        if (!empty($quotation->products->count() > 0)) {
            foreach ($quotation->products as $item) {
                $saleorderProduct = new SaleorderProduct();
                $saleorderProduct->order_id = $saleorder->id;
                $saleorderProduct->product_id = $item->product_id;
                $saleorderProduct->product_name = $item->product_name;
                $saleorderProduct->description = $item->description;
                $saleorderProduct->quantity = $item->quantity;
                $saleorderProduct->price = $item->price;
                $saleorderProduct->sub_total = $item->sub_total;
                $saleorderProduct->save();
            }
        }


        return redirect('sales_order/draft_salesorders');
    }

    /**
     * @param Qtemplate $qtemplate
     */
    public function ajaxQtemplatesProducts(Qtemplate $qtemplate)
    {
        return QtemplateProduct::where('qtemplate_id', $qtemplate->id)->get();
    }


    public function printQuot(Quotation $quotation)
    {
        $filename = 'Quotation-' . $quotation->quotations_number;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4','landscape');
        $pdf->loadView('quotation_template.'.Settings::get('quotation_template'), compact('quotation'));
        return $pdf->download($filename . '.pdf');
    }

    public function ajaxCreatePdf(Quotation $quotation)
    {
        $filename = 'Quotation-' . $quotation->quotations_number;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4','landscape');
        $pdf->loadView('quotation_template.'.Settings::get('quotation_template'), compact('quotation'));
        $pdf->save('./pdf/' . $filename . '.pdf');
        $pdf->stream();
        echo url("pdf/" . $filename . ".pdf");

    }

    public function sendQuotation(Request $request)
    {
        $email_subject = $request->email_subject;
        $to_customers = Customer::whereIn('user_id', $request->recipients)->get();
        $email_body = $request->message_body;
        $message_body = Common::parse_template($email_body);
        $quotation_pdf = $request->quotation_pdf;

        if (!empty($to_customers) && !filter_var(Settings::get('site_email'), FILTER_VALIDATE_EMAIL) === false) {
            foreach ($to_customers as $item) {
                 if (!filter_var($item->user->email, FILTER_VALIDATE_EMAIL) === false) {
                 Mail::send('emails.quotation',
                     ['message_body' => $message_body],
                     function ($message)
                 use ($item, $email_subject, $quotation_pdf) {
                         $message->from(Settings::get('site_email'), Settings::get('site_name'));
                         $message->to($item->user->email)->subject($email_subject);
                     $message->attach(url('/pdf/' . $quotation_pdf));
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
            echo '<div class="alert alert-success">' . trans('quotation.success') . '</div>';
        } else {
            echo '<div class="alert alert-danger">' . trans('invoice.error') . '</div>';
        }
    }

    public function makeInvoice(Quotation $quotation)
    {
        $quotation->update(['is_quotation_invoice_list' => 1]);
        $invoices = DB::table('invoices')->get()->count();
        if($invoices == 0){
            $total_fields = 0;
        }else{
            $total_fields = DB::table('invoices')->orderBy('id','desc')->first()->id;
        }
        $start_number = Settings::get('invoice_start_number');
        $invoice_number = Settings::get('invoice_prefix') . ( is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields : 0) + 1);

        $invoice_details = array(
            'order_id' => $quotation->id,
            'customer_id' => $quotation->customer_id,
            'sales_person_id' => $quotation->sales_person_id,
            'sales_team_id' => $quotation->sales_team_id,
            'invoice_number' => $invoice_number,
            'invoice_date' => date(Settings::get('date_format')),
            'due_date' => $quotation->exp_date,
            'payment_term' => isset($quotation->payment_term)?$quotation->payment_term:0,
            'status' => 'Open Invoice',
            'total' => $quotation->total,
            'tax_amount' => $quotation->tax_amount,
            'grand_total' => $quotation->grand_total,
            'unpaid_amount' => $quotation->final_price,
            'discount' => $quotation->discount,
            'final_price' => $quotation->final_price,
            'user_id' => Sentinel::getUser()->id
        );
        $invoice = Invoice::create($invoice_details);

        $products = $quotation->products;
        if (!empty($products)) {
            foreach ($products as $item) {
                $product_add = array(
                    'invoice_id' => $invoice->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'sub_total' => $item->sub_total
                );
                InvoiceProduct::create($product_add);
            }
        }

//        $quotation->delete();

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
                ->orderBy("id", "asc")
                ->pluck('salesteam', 'id')
                ->prepend(trans('quotation.sales_team_id'), '');

        $customers = $this->userRepository->getParentCustomers()
	            ->pluck('full_name', 'id')
	            ->prepend(trans('dashboard.select_customer'), '');

        $companies_mail = $this->userRepository->getAll()->get()->filter(function ($user) {
            return $user->inRole('customer');
        })->pluck('full_name', 'id');

        $statuses = $this->optionRepository->getAll()
            ->where('category', 'quotation_status')
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

    public function ajaxSalesTeamList( Request $request){
        $agent_name = Customer::where('user_id',$request->id)->get()->pluck('sales_team_id','user_id');
        $agent_name = $agent_name[$request->id];
        $sales_team = Salesteam::pluck('salesteam','id')->prepend(trans('quotation.sales_team_id'), '');
        return ['agent_name'=>$agent_name,'sales_team' => $sales_team];
    }
    private function emailRecipients($customer_id){
        $email_recipients = $this->userRepository->getParentCustomers()->where('id',$customer_id)->pluck('full_name','id');
        view()->share('email_recipients', $email_recipients);
    }
}
