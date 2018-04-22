<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Http\Requests;
use App\Http\Requests\OpportunityLostReason;
use App\Http\Requests\OpportunityRequest;
use App\Models\Call;
use App\Models\Company;
use App\Models\Opportunity;
use App\Models\Option;
use App\Models\Quotation;
use App\Models\Tag;
use App\Models\User;
use App\Repositories\CompanyRepository;
use App\Repositories\OpportunityRepository;
use App\Repositories\OptionRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\UserRepository;
use Efriandika\LaravelSettings\Facades\Settings;
//use Illuminate\Contracts\Logging\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Sentinel;
use Yajra\Datatables\Datatables;
use App\Models\Customer;
use App\Models\Salesteam;
use Illuminate\Support\Facades\Log;


class OpportunityController extends UserController {

  public $companyRepository;
  public $userRepository;
  /**
   * @var OpportunityRepository
   */
  private $opportunityRepository;
  /**
   * @var SalesTeamRepository
   */
  private $salesTeamRepository;
  /**
   * @var OptionRepository
   */
  private $optionRepository;

  /**
   * OpportunityController constructor.
   * @param CompanyRepository $companyRepository
   * @param UserRepository $userRepository
   * @param OpportunityRepository $opportunityRepository
   * @param SalesTeamRepository $salesTeamRepository
   * @param OptionRepository $optionRepository
   */
  public function __construct(CompanyRepository $companyRepository,
                              UserRepository $userRepository,
                              OpportunityRepository $opportunityRepository,
                              SalesTeamRepository $salesTeamRepository,
                              OptionRepository $optionRepository) {
    $this->middleware('authorized:opportunities.read', ['only' => ['index', 'data']]);
    $this->middleware('authorized:opportunities.write', ['only' => ['create', 'store', 'update', 'edit']]);
    $this->middleware('authorized:opportunities.delete', ['only' => ['delete']]);

    parent::__construct();

    $this->opportunityRepository = $opportunityRepository;
    $this->companyRepository = $companyRepository;
    $this->userRepository = $userRepository;
    $this->salesTeamRepository = $salesTeamRepository;
    $this->optionRepository = $optionRepository;

    view()->share('type', 'opportunity');
  }


  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Opportunity $opportunity) {
    $title = trans('opportunity.opportunities');
    return view('user.opportunity.index', compact('title'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create() {
    $title = trans('opportunity.new');
    $calls = 0;
    $meetings = 0;
    $this->generateParams();
    return view('user.opportunity.create', compact('title', 'meetings', 'calls', 'user', 'salesteam'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(OpportunityRequest $request) {
    $request->merge(['customer_id' => $request->customer_id]);
    $this->opportunityRepository->create($request->all());
    return redirect("opportunity");
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int $id
   * @return \Illuminate\Http\Response
   */
  public function edit(Opportunity $opportunity) {
    $customer_company = Customer::where('company_id', $opportunity->company_name)->pluck('user_id', 'id');
    $agent_name = User::whereIn('id', $customer_company)->get()->pluck('full_name', 'id')->all();

    $sales_team = Salesteam::where('id', $opportunity->sales_team_id)->pluck('team_leader', 'id');
    $sales_team_members = Salesteam::where('id', $opportunity->sales_team_id)->pluck('team_members', 'id');
    $sales_team_members = $sales_team_members[$opportunity->sales_team_id];
    $sales_team_members = $sales_team->merge($sales_team_members);
    $main_staff = User::whereIn('id', $sales_team_members)->get()->pluck('full_name', 'id');

    $calls = $opportunity->calls()->count();
    $meetings = $opportunity->meetings()->count();

    $title = trans('opportunity.edit');

    $this->generateParams();

    return view('user.opportunity.edit', compact('title', 'calls', 'meetings', 'opportunity', 'agent_name', 'main_staff'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request $request
   * @param  int $id
   * @return \Illuminate\Http\Response
   */
  public function update(OpportunityRequest $request, Opportunity $opportunity) {
    $companies = $this->companyRepository->getAll()->orderBy("name", "asc")
      ->pluck('name', 'id');
    $opportunity->update($request->all());
    $calls = $opportunity->calls()->count();
    if (!$calls == 0) {
      $calls_id = DB::table('callables')->get()->where('callable_id', $opportunity->id)->pluck('call_id', 'callable_id');
      $calls_id = $calls_id[$opportunity->id];
      $calls = Call::where('id', $calls_id)->first();
      $calls->company_name = $companies[$opportunity->company_name];
      $calls->save();
    }

    return redirect("opportunity");
  }

  public function show(Opportunity $opportunity) {
    $title = trans('opportunity.show');
    $action = 'show';
    $this->generateParams();
    return view('user.opportunity.show', compact('title', 'opportunity', 'action'));
  }

  public function won(Opportunity $opportunity) {
    $title = trans('opportunity.won');
    $this->generateParams();
    $action = 'won';
    return view('user.opportunity.lost_won', compact('title', 'opportunity', 'action'));
  }

  public function lost(Opportunity $opportunity) {
    $title = trans('opportunity.lost');
    $this->generateParams();
    $action = 'lost';
    return view('user.opportunity.lost_won', compact('title', 'opportunity', 'action'));
  }

  public function updateLost(Request $request, Opportunity $opportunity) {
    $request->merge([
      'stages' => 'Lost',
    ]);
    $opportunity->update($request->all());

    return redirect("opportunity");
  }

  public function delete(Opportunity $opportunity) {
    $title = trans('opportunity.delete');
    $this->generateParams();
    return view('user.opportunity.delete', compact('title', 'opportunity'));
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int $id
   * @return \Illuminate\Http\Response
   */
  public function destroy(Opportunity $opportunity) {
    $opportunity->calls()->delete();
    $opportunity->meetings()->delete();
    return redirect('opportunity');
  }

  public function data(Datatables $datatables) {
    $opportunities = $this->opportunityRepository->getAll()
      ->with('salesTeam', 'customer', 'calls', 'meetings', 'user')
      ->get()
      ->map(function ($opportunity) {
        return [
          'id' => $opportunity->id,
          'opportunity' => $opportunity->opportunity,
          'company' => isset($opportunity->companies->name) ? $opportunity->companies->name : null,
          'next_action' => $opportunity->next_action,
          'stages' => $opportunity->stages,
          'expected_revenue' => $opportunity->expected_revenue,
          'probability' => $opportunity->probability,
          'sales_team_id' => isset($opportunity->salesTeam) ? $opportunity->salesTeam->salesteam : '',
          'salesteam' => isset($opportunity->staffs->full_name) ? $opportunity->staffs->full_name : null,
          'calls' => $opportunity->calls->count(),
          'meetings' => $opportunity->meetings->count(),
        ];
      });
    return $datatables->collection($opportunities)
      ->addColumn('actions', ' 
 @if(Sentinel::getUser()->hasAccess([\'opportunities.write\']) || Sentinel::inRole(\'admin\'))
                                         <a href="{{ url(\'opportunity/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                                <i class="fa fa-fw fa-pencil text-warning "></i></a>                                     
                                       
                                      @endif
                                      <a href="{{ url(\'opportunitycall/\' . $id .\'/\' ) }}" title="{{ trans(\'table.calls\') }}">
                                                <i class="fa fa-phone text-primary"></i><sup>{{ $calls }}</sup> </a>
                                         <a href="{{ url(\'opportunitymeeting/\' . $id .\'/calendar\' ) }}" title="{{ trans(\'table.meeting\') }}">
                                                <i class="fa fa-fw fa-users text-primary"></i> <sup>{{ $meetings }}</sup></a>
                                      @if(Sentinel::getUser()->hasAccess([\'opportunities.read\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'opportunity/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                    @endif                                   
                                      @if(Sentinel::getUser()->hasAccess([\'opportunities.delete\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'opportunity/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i></a>
                                      @endif')
      ->addColumn('options', ' @if(Sentinel::getUser()->hasAccess([\'opportunities.write\']) || Sentinel::inRole(\'admin\'))
                                    
                                         <a href="{{ url(\'opportunity/\' . $id .\'/lost\' ) }}" class="btn btn-danger" title="{{ trans(\'opportunity.lost\') }}">
                                                Lost</a>
                                      @endif
                                       @if(Sentinel::getUser()->hasAccess([\'quotations.write\']) && Sentinel::getUser()->hasAccess([\'opportunities.write\']) || Sentinel::getUser()->inRole(\'admin\'))
                                       <a href="{{ url(\'opportunity/\' . $id .\'/won\' ) }}" class="btn btn-success m-t-10" title="{{ trans(\'opportunity.won\') }}">
                                                Won</a>
                                       @endif
                                    ')
      ->removeColumn('id')
      ->removeColumn('calls')
      ->removeColumn('meetings')
      ->escapeColumns(['actions'])->make();
  }


  private function generateParams() {
    $tags = Tag::pluck('title', 'id');

    $stages = $this->optionRepository->getAll()
      ->where('category', 'stages')
      ->get()
      ->map(function ($title) {
        return [
          'title' => $title->title,
          'value' => $title->value,
        ];
      })->pluck('title', 'value')
      ->prepend(trans('dashboard.select_stage'), '');

    $priority = $this->optionRepository->getAll()
      ->where('category', 'priority')
      ->get()
      ->map(function ($title) {
        return [
          'title' => $title->title,
          'value' => $title->value,
        ];
      })->pluck('title', 'value')
      ->prepend(trans('dashboard.select_priority'), '');
    $lost_reason = $this->optionRepository->getAll()->where('category', 'lost_reason')->pluck('title', 'value')
      ->prepend(trans('opportunity.lost_reason'), '');

    $agents = $this->userRepository->getCustomers()
      ->pluck('full_name', 'id')
      ->prepend(trans('dashboard.select_customer'), '');

    $staffs = $this->userRepository->getStaff()
      ->pluck('full_name', 'id')->prepend(trans('dashboard.select_staff'), '');

    $salesteams = $this->salesTeamRepository->getAll()
      ->orderBy("id", "asc")
      ->pluck('salesteam', 'id')
      ->prepend(trans('dashboard.select_sales_team'), '');
    $companies = $this->companyRepository->getAll()->orderBy("name", "asc")
      ->pluck('name', 'id')
      ->prepend(trans('company.company_name'), '');
    $user = User::all();

    view()->share('salesteams', $salesteams);
    view()->share('tags', $tags);
    view()->share('stages', $stages);
    view()->share('priority', $priority);
    view()->share('lost_reason', $lost_reason);
    view()->share('staffs', $staffs);
    view()->share('agents', $agents);
    view()->share('companies', $companies);
    view()->share('user', $user);
  }

  /**
   * @param Opportunity $opportunity
   * @return \Illuminate\Http\RedirectResponse
   */
  public function convertToQuotation(Opportunity $opportunity) {
    $quotation = DB::table('quotations')->get()->count();
    if ($quotation == 0) {
      $total_fields = 0;
    } else {
      $total_fields = DB::table('quotations')->orderBy('id', 'desc')->first()->id;
    }
    $start_number = Settings::get('quotation_start_number');
    $quotation_no = Settings::get('quotation_prefix') . (is_int($start_number) ? $start_number : 0 + (isset($total_fields) ? $total_fields : 0) + 1);
    //$exp_date = date(Settings::get('date_format'), strtotime(' + ' . Settings::get('payment_term1') . ' days'));

    Quotation::create([
      'quotations_number' => $quotation_no,
      'customer_id' => $opportunity->customer_id,
      'date' => date(Settings::get('date_format')),
      'exp_date' => $opportunity->expected_closing,
      'payment_term' => Settings::get('payment_term1') . " Days",
      'sales_person_id' => $opportunity->salesteam,
      'sales_team_id' => $opportunity->sales_team_id,
      'status' => 'Draft Quotation',
      'user_id' => Sentinel::getUser()->id,
      'discount' => 0,
      'opportunity_id' => $opportunity->id
    ]);
    $opportunity->update(['stages' => 'Won', 'is_converted_list' => 1]);
    return redirect('quotation/draft_quotations');
  }

//    convert to archive
  public function convertToArchive(Opportunity $opportunity, OpportunityLostReason $request) {
    $opportunity->update(['stages' => 'Loss', 'is_archived' => 1, 'lost_reason' => $request->lost_reason]);
    return redirect('opportunity_archive');
  }

  //    convert to delete list
  public function convertToDeleteList(Opportunity $opportunity) {
    $this->generateParams();
    $opportunity->update(['is_delete_list' => 1]);
    return redirect('opportunity_delete_list');
  }

  public function ajaxAgentList(Request $request) {
    $customer_company = Customer::where('company_id', $request->id)->pluck('user_id', 'id');
    $agent_name = User::whereIn('id', $customer_company)->get()->pluck('full_name', 'id')->all();
    return $agent_name;
  }

  public function ajaxMainStaffList(Request $request) {
    $sales_team = Salesteam::where('id', $request->id)->pluck('team_leader', 'id');
    $team_leader = User::where('id', $sales_team)->get()->first()->id;
    $sales_team_members = Salesteam::where('id', $request->id)->pluck('team_members', 'id');
    $sales_team_members = $sales_team_members[$request->id];
    $main_staff = User::whereIn('id', $sales_team->merge($sales_team_members))->get()->pluck('full_name', 'id');
    return ['main_staff' => $main_staff, 'team_leader' => $team_leader];
  }
}
