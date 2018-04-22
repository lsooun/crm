<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Thumbnail;
use App\Helpers\ExcelfileValidator;
use App\Http\Controllers\UserController;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Opportunity;
use App\Models\Quotation;
use App\Models\Saleorder;
use App\Models\Salesteam;
use App\Models\User;
use App\Repositories\CompanyRepository;
use App\Repositories\ExcelRepository;
use App\Repositories\OptionRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\UserRepository;
use function Aws\map;
use Efriandika\LaravelSettings\Facades\Settings;
use function GuzzleHttp\Promise\all;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Sentinel;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Mail;
use Yajra\Datatables\Datatables;



class CustomerController extends UserController
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var CompanyRepository
     */
    private $companyRepository;
    /**
     * @var SalesTeamRepository
     */
    private $salesTeamRepository;
    /**
     * @var ExcelRepository
     */
    private $excelRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * CustomerController constructor.
     *
     * @param UserRepository $userRepository
     * @param CompanyRepository $companyRepository
     * @param SalesTeamRepository $salesTeamRepository
     * @param ExcelRepository $excelRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(
        UserRepository $userRepository,
        CompanyRepository $companyRepository,
        SalesTeamRepository $salesTeamRepository,
        ExcelRepository $excelRepository,
        OptionRepository $optionRepository
    )
    {
        parent::__construct();

        $this->middleware('authorized:contacts.read', ['only' => ['index', 'data']]);
        $this->middleware('authorized:contacts.write', ['only' => ['create', 'store', 'update', 'edit']]);
        $this->middleware('authorized:contacts.delete', ['only' => ['delete']]);

        $this->userRepository = $userRepository;
        $this->companyRepository = $companyRepository;
        $this->salesTeamRepository = $salesTeamRepository;
        $this->excelRepository = $excelRepository;
        $this->optionRepository = $optionRepository;

        view()->share('type', 'customer');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('customer.agent');

        $companies = $this->companyRepository->getAll()
            ->orderBy("name", "asc")
            ->get()
            ->map(function ($c) {
                return [
                    'text' => $c->name,
                    'id' => $c->id,
                ];
            })->values();

        $customers = $this->userRepository->getAll()
            ->with('customer.company')
            ->get()
            ->filter(function ($user) {
                return $user->inRole('customer', 'companies');
            });
//        return $customers;
//        $customers = Customer::all();

        return view('user.customer.index', compact('title', 'companies', 'customers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('customer.new');

        $this->generateParams();

        return view('user.customer.create', compact('title'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CustomerRequest $request)
    {
        $user = Sentinel::registerAndActivate($request->only('first_name', 'last_name', 'email', 'password','user_avatar'));
        $role = Sentinel::findRoleBySlug('customer');
        $role->users()->attach($user);

        $user = User::find($user->id);

        if ($request->hasFile('user_avatar_file')) {
            $file = $request->file('user_avatar_file');
            $file = $this->companyRepository->uploadCustomerAvatar($file);
            $request->merge([
                'company_avatar' => $file->getFileInfo()->getFilename(),
            ]);
            $user->user_avatar = basename($file);
            $this->generateThumbnail($file);
        }
        $user->phone_number = $request->phone_number; //already saving with registerAndActivate?,check above
        $user->password = bcrypt($request->password);
        $user->save();
        $customer = new Customer($request->except(  'password',
            'password_confirmation', 'user_avatar_file','email','first_name','last_name','phone_number'));
        $customer->user_id = $user->id;
        $customer->belong_user_id = Sentinel::getUser()->id;

        $customer->save();

        $subject = 'Customer login details';

        $currentUser = Sentinel::getUser();
        $currentUser->users()->save($user);

        if (!filter_var(Settings::get('site_email'), FILTER_VALIDATE_EMAIL) === false) {
            Mail::send('emails.new_customer', array('email' => $request->email,
                'password' => $request->password
            ), function ($m) use ($request, $subject) {
                $m->from(Settings::get('site_email'), Settings::get('site_name'));
                $m->to($request->email, $request->first_name . $request->last_name);
                $m->subject($subject);
            });
        }

        return redirect("customer");
    }

    public function edit( Customer $customer ) {
        $customers = $this->userRepository->getAll()
            ->with('customer.company')
            ->get()
            ->filter(function ($user) {
                return $user->inRole('customer', 'companies');
            });
        $title = trans( 'customer.edit' );
        $this->generateParams();

        return view( 'user.customer.edit', compact( 'customer', 'title' ) );
    }

    public function update(CustomerRequest $request, Customer $customer)
    {

        if ($request->hasFile('user_avatar_file')) {
            $file = $request->file('user_avatar_file');
            $file = $this->companyRepository->uploadCustomerAvatar($file);
            $request->merge([
                'company_avatar' => $file->getFileInfo()->getFilename(),
            ]);
            $this->generateThumbnail($file);
        }
        $customer->update($request->except( 'password','email',
            'password_confirmation', 'user_avatar_file','first_name','last_name','phone_number'));

        $user =collect($request->only('first_name','last_name','email','phone_number','user_avatar'));

        if ($request->password != null) {
            $user = $user->merge(['password' => bcrypt($request->password)]);
        }

        if (isset($customer->company_avatar) && $customer->company_avatar!="") {
            $user = $user->merge(['user_avatar' => $customer->company_avatar]);
        }

        $user = $user->toArray();
        User::find($customer->user_id)->update($user);
        return redirect("customer");
    }

    public function show( Customer $customer) {
        $title  = trans( 'customer.details' );
        $this->generateParams();

        $action = "show";
        $customer->load('salesTeam');


        return view( 'user.customer.show', compact( 'title', 'customer', 'action' ) );
    }

    public function delete(Customer $customer)
    {
        $title = trans('customer.delete');
        $this->generateParams();
        return view('user.customer.delete', compact('title', 'customer'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User $user
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        $customer->user()->delete();
        $customer->delete();
        return redirect('customer');
    }

    public function data(Datatables $datatables)
    {
        $customers = Customer::all()->map(function($user){
            $customerOpportunity = Opportunity::where('customer_id',$user->user_id)->get()->count();
            $deletedOpportunity = Opportunity::onlyDeleteLists()->where('customer_id',$user->user_id)->get()->count();
            $customerQuotation = Quotation::where('customer_id',$user->user_id)->get()->count();
            $deletedQuotation = Quotation::onlyDeleteLists()->where('customer_id',$user->user_id)->get()->count();
            $customerSaleorder = Saleorder::where('customer_id',$user->user_id)->get()->count();
            $deletedSaleorder = Saleorder::onlyDeleteLists()->where('customer_id',$user->user_id)->get()->count();
            $customerInvoice = Invoice::where('customer_id',$user->user_id)->get()->count();
            $deletedInvoice = Invoice::onlyDeleteLists()->where('customer_id',$user->user_id)->get()->count();
            return [
                'full_name' => isset($user->user->full_name)?$user->user->full_name:null,
                'company_id' => isset($user->company->name)?$user->company->name:null,
                'email' => isset($user->user->email)?$user->user->email:null,
                'phone_number' => isset($user->user->phone_number)?$user->user->phone_number:null,
                'id' => $user->id,
                'count_uses' => $customerOpportunity + $deletedOpportunity + $customerQuotation + $deletedQuotation
                    + $customerSaleorder + $deletedSaleorder + $customerInvoice + $deletedInvoice,
            ];
        })->values();
        return $datatables->collection($customers)
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'contacts.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'customer/\' . $id . \'/edit\' ) }}"  title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning"></i>  </a>
                                            @endif
                                     <a href="{{ url(\'customer/\' . $id . \'/show\' ) }}"  title="{{ trans(\'table.show\') }}">
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                            @if(Sentinel::getUser()->hasAccess([\'contacts.delete\']) && $count_uses==0 || Sentinel::inRole(\'admin\') && $count_uses==0)
                                            <a href="{{ url(\'customer/\' . $id . \'/delete\' ) }}"  title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>@endif')
            ->removeColumn('id')
            ->removeColumn('count_uses')
            ->escapeColumns( [ 'actions' ] )->make();
    }

    public function importExcelData(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|mimes:xlsx,xls,csv|max:5000',
        ]);

        $reader = $this->excelRepository->load($request->file('file'));

        $users = $reader->all()->map(function ($row) {
            return [
                'email' => $row->email,
                'password' => $row->password,
                'first_name' => $row->first_name,
                'last_name' => $row->last_name,
                'mobile' => $row->mobile,
                'fax' => $row->fax,
                'website' => $row->website,
            ];
        });

        foreach ($users as $userData) {
            if (!$customer = \App\Models\User::whereEmail($userData['email'])->first()) {
                $customer = $this->userRepository->create($userData);

                $customer->customer()->create($userData);
                $this->userRepository->assignRole($customer, 'customer');
            }
        }

        return response()->json([], 200);
    }

    public function downloadExcelTemplate()
    {
        return response()->download(base_path('resources/excel-templates/contacts.xlsx'));
    }

    private function generateParams()
    {

        $salesteams = $this->salesTeamRepository->getAll()->orderBy("id", "asc")
            ->pluck('salesteam', 'id')
            ->prepend(trans('dashboard.select_sales_team'), '');
        $companies = $this->companyRepository->getAll()->orderBy("name", "asc")
            ->pluck('name', 'id')
            ->prepend(trans('dashboard.select_company'), '');
        $titles = $this->optionRepository->getAll()
            ->where('category', 'titles')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value' => $title->value,
                ];
            })->pluck('title', 'value')->prepend(trans('customer.title'), '');
        view()->share('salesteams', $salesteams);
        view()->share('companies', $companies);
        view()->share('titles', $titles);
    }


    public function getImport()
    {
        $title = trans('customer.customers');

        return view('user.customer.import', compact('title'));
    }

    public function postImport(Request $request)
    {

        //~ $this->validate($request, [
        //~ 'file' => 'required|mimes:xlsx,xls,csv|max:5000',
        //~ ]);
        if (!ExcelfileValidator::validate($request)) {
            return response('invalid File or File format', 500);
        }


        $reader = $this->excelRepository->load($request->file('file'));


        $titles = $this->optionRepository->getAll()
            ->where('category', 'titles')
            ->get()
            ->map(function ($title) {
                return $title->title;
            })->values()
            ->toArray();


        $customers = $reader->all()->map(function ($row) use ($titles) {
            return [
                'first_name' => $row->first_name,
                'last_name' => $row->last_name,
                'email' => $row->email,
                'phone_number' => $row->phone,
                'title' => in_array($row->title, $titles) ? $row->title : null,
                'password' => $row->password,
                'password_confirmation' => $row->password,
                'mobile' => $row->mobile,
                'website' => $row->website,
                'fax' => $row->fax,
            ];
        });

        $companies = $this->companyRepository->getAll()->get()->map(function ($company) {
            return [
                'text' => $company->name,
                'id' => $company->id,
            ];
        })->values();

        $titles = $this->optionRepository->getAll()
            ->where('category', 'titles')
            ->get()
            ->map(function ($title) {
                return [
                    'text' => $title->title,
                    'id' => $title->value,
                ];
            })->values();

        return response()->json(compact('customers', 'companies', 'titles'), 200);
    }

    public function postAjaxStore(CustomerRequest $request)
    {
        //add user
        // $userNew = $this->userRepository->create( $request->except( 'created', 'errors', 'selected','password_confirmation','mobile','website','fax' ) );
        $userNew = $this->userRepository->create($request->only('email', 'password', 'first_name', 'last_name', 'phone_number'));

        //assign customer role to new user
        $this->userRepository->assignRole($userNew, 'customer');

        //add user to customers table
        $customer = new Customer($request->except( 'password',
            'password_confirmation', 'user_avatar_file'));
        $customer->user_id = $userNew->id;
        $customer->belong_user_id = Sentinel::getUser()->id;
        $customer->save();

        return response()->json([], 200);
    }

    /**
     * @param $file
     */
    private function generateThumbnail($file)
    {
        Thumbnail::generate_image_thumbnail(public_path() . '/uploads/avatar/' . $file->getFileInfo()->getFilename(),
            public_path() . '/uploads/avatar/' . 'thumb_' . $file->getFileInfo()->getFilename());
    }
}
