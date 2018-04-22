<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Thumbnail;
use App\Http\Controllers\UserController;
use App\Http\Requests\CompanyRequest;
use App\Models\Call;
use App\Models\City;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Email;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Meeting;
use App\Models\Quotation;
use App\Models\Saleorder;
use App\Models\State;
use App\Repositories\CompanyRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\SalesOrderRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;
use Sentinel;

class CompanyController extends UserController
{
    /**
     * @var CompanyRepository
     */
    private $companyRepository;
    /**
     * @var SalesTeamRepository
     */
    private $salesTeamRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;
    /**
     * @var QuotationRepository
     */
    private $quotationRepository;
    /**
     * @var SalesOrderRepository
     */
    private $salesOrderRepository;

    public function __construct(CompanyRepository $companyRepository,
                                SalesTeamRepository $salesTeamRepository,
                                UserRepository $userRepository,
                                InvoiceRepository $invoiceRepository,
                                QuotationRepository $quotationRepository,
                                SalesOrderRepository $salesOrderRepository)
    {
        parent::__construct();

        $this->middleware('authorized:contacts.read', ['only' => ['index', 'data']]);
        $this->middleware('authorized:contacts.write', ['only' => ['create', 'store', 'update', 'edit']]);
        $this->middleware('authorized:contacts.delete', ['only' => ['delete']]);

        $this->companyRepository = $companyRepository;
        $this->salesTeamRepository = $salesTeamRepository;
        $this->userRepository = $userRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->quotationRepository = $quotationRepository;
        $this->salesOrderRepository = $salesOrderRepository;

        view()->share('type', 'company');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('company.companies');
        return view('user.company.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('company.new');

        $this->generateParams();

        return view('user.company.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(CompanyRequest $request)
    {
        if ($request->hasFile('company_avatar_file')) {
            $file = $request->file('company_avatar_file');
            $file = $this->companyRepository->uploadAvatar($file);

            $request->merge([
                'company_avatar' => $file->getFileInfo()->getFilename(),
            ]);
            $this->generateThumbnail($file);
        }

        $this->companyRepository->create($request->except('company_avatar_file'));

        return redirect("company");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Company $company
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company)
    {
        $title = trans('company.edit');

        $states = State::where('country_id', $company->country_id)->orderBy("name", "asc")->pluck('name', 'id');
        $cities = City::where('state_id', $company->state_id)->orderBy("name", "asc")->pluck('name', 'id');


        $this->generateParams();

        return view('user.company.edit', compact('title', 'company','cities','states'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Company $company
     * @return \Illuminate\Http\Response
     */
    public function update(CompanyRequest $request, Company $company)
    {
        if ($request->hasFile('company_avatar_file')) {
            $file = $request->file('company_avatar_file');
            $file = $this->companyRepository->uploadAvatar($file);

            $request->merge([
                'company_avatar' => $file->getFileInfo()->getFilename(),
            ]);
            $this->generateThumbnail($file);
        }

        $company->update($request->except('company_avatar_file'));

        return redirect("company");
    }

    public function show(Company $company)
    {
        $title = trans('company.details');
        $action = 'show';

        $agent_id = $company->customerCompany->pluck('user_id','user_id');
        $open_invoices = round(Invoice::where('status',trans('invoice.open_invoice'))->whereIn('customer_id',$agent_id)->sum('final_price'), 3);
        $overdue_invoices = round(Invoice::where('status',trans('invoice.overdue_invoice'))->whereIn('customer_id',$agent_id)->sum('unpaid_amount'), 3);
        $paid_invoices = round(Invoice::onlyPaidLists()->get()->whereIn('customer_id',$agent_id)->sum('final_price'),3);
        $total_invoices = round(InvoicePayment::all()->where('is_delete_list',0)->whereIn('customer_id',$agent_id)->sum('final_price'),3);

        $quotations_total = round(Quotation::all()->whereIn('customer_id',$agent_id)->sum('final_price'), 3);;
        $salesorder_total = round(Saleorder::all()->whereIn('customer_id',$agent_id)->sum('final_price'), 3);;

        $salesorder =  Saleorder::all()->whereIn('customer_id',$agent_id)->count();

        $invoices =  Invoice::where([
            ['status','!=',trans('invoice.paid_invoice')]
        ])->whereIn('customer_id',$agent_id)->count();


        $quotations =  Quotation::all()->whereIn('customer_id',$agent_id)->count();

        $calls = Call::where('company_id',$company->id)->get()->count();

        $meeting = Meeting::where('company_name',$company->id)->get()->count();

        $emails = Email::whereIn('to',$agent_id)->get()->count();

        $contracts = Contract::where('company_id',$company->id)->get()->count();


        return view('user.company.delete', compact('title', 'company','action','total_invoices','open_invoices','paid_invoices',
            'quotations_total','salesorder','quotations','invoices','calls','meeting','emails','contracts','overdue_invoices',
            'salesorder_total'));
    }

    public function delete(Company $company)
    {
        $title = trans('company.delete');

        $agent_id = $company->customerCompany->pluck('user_id','user_id');
        $open_invoices = round(Invoice::where('status',trans('invoice.open_invoice'))->whereIn('customer_id',$agent_id)->sum('final_price'), 3);
        $overdue_invoices = round(Invoice::where('status',trans('invoice.overdue_invoice'))->whereIn('customer_id',$agent_id)->sum('unpaid_amount'), 3);
        $paid_invoices = round(Invoice::onlyPaidLists()->get()->whereIn('customer_id',$agent_id)->sum('final_price'),3);
        $total_invoices = round(InvoicePayment::all()->whereIn('customer_id',$agent_id)->sum('final_price'),3);

        $quotations_total = round(Quotation::all()->whereIn('customer_id',$agent_id)->sum('final_price'), 3);;
        $salesorder_total = round(Saleorder::all()->whereIn('customer_id',$agent_id)->sum('final_price'), 3);;

        $salesorder =  Saleorder::all()->whereIn('customer_id',$agent_id)->count();

        $invoices =  Invoice::where([
            ['status','!=',trans('invoice.paid_invoice')]
        ])->whereIn('customer_id',$agent_id)->count();


        $quotations =  Quotation::all()->whereIn('customer_id',$agent_id)->count();

        $calls = Call::where('company_name',$company->name)->get()->count();

        $meeting = Meeting::where('company_name',$company->id)->get()->count();

        $emails = Email::whereIn('to',$agent_id)->get()->count();

        $contracts = Contract::where('company_id',$company->id)->get()->count();

        return view('user.company.delete', compact('title', 'company','action','total_invoices','open_invoices','paid_invoices',
            'quotations_total','salesorder','quotations','invoices','calls','meeting','emails','contracts','overdue_invoices',
            'salesorder_total'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Company $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        $company->delete();
        return redirect('company');
    }

    public function data(Datatables $datatables)
    {
        $company = $this->companyRepository->getAll()
            ->with('contactPerson','opportunityCompany')
            ->get()
            ->map(function ($comp) {
            return [
                'id' => $comp->id,
                'name' => $comp->name,
                'website' => $comp->website,
//                'customer' => isset($comp->contactPerson) ?$comp->contactPerson->full_name : '--',
                'phone' => $comp->phone,
                'count_uses' => $comp->customerCompany->count()+
                    $comp->opportunityCompany->count()
            ];
        });

        return $datatables->collection($company)

            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'contacts.write\']) || Sentinel::inRole(\'admin\'))
                                    <a href="{{ url(\'company/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning "></i> </a>
                                    @endif
                                    <a href="{{ url(\'company/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                    @if(Sentinel::getUser()->hasAccess([\'contacts.delete\']) && $count_uses==0 || Sentinel::inRole(\'admin\') && $count_uses==0)
                                    <a href="{{ url(\'company/\' . $id . \'/delete\' ) }}"  title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                       @endif')

            ->removeColumn('id')
            ->removeColumn('count_uses')
            ->escapeColumns( [ 'actions' ] )->make();
    }

    private function generateParams()
    {
        $countries = Country::orderBy("name", "asc")->pluck('name', 'id')
	                     ->prepend(trans('company.select_country'), '');
        $states = State::orderBy("name", "asc")->pluck('name', 'id')
            ->prepend(trans('company.select_state'), '');
        $cities = City::orderBy("name", "asc")->pluck('name', 'id')
            ->prepend(trans('company.select_city'), '');
        $salesteams = $this->salesTeamRepository->getAll()
	                      ->pluck('salesteam', 'id')
	                      ->prepend(trans('dashboard.select_sales_team'), '');
        $customers = $this->userRepository->getCustomers()->pluck('full_name', 'id')
                                                 ->prepend(trans('dashboard.select_customer'), '');

        view()->share('salesteams', $salesteams);
        view()->share('customers', $customers);
        view()->share('countries', $countries);
        view()->share('states', $states);
        view()->share('cities', $cities);
    }
    /**
     * @param $file
     */
    private function generateThumbnail($file)
    {
        Thumbnail::generate_image_thumbnail(public_path() . '/uploads/company/' . $file->getFileInfo()->getFilename(),
            public_path() . '/uploads/company/' . 'thumb_' . $file->getFileInfo()->getFilename());
    }

}
