<?php

namespace App\Http\Controllers\Users;

use App\Events\Call\CallCreated;
use App\Http\Controllers\UserController;
use App\Http\Requests\CallRequest;
use App\Models\Call;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Repositories\CallRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\UserRepository;
use Sentinel;
use Yajra\Datatables\Datatables;

class CallController extends UserController {
  /**
   * @var UserRepository
   */
  private $userRepository;
  /**
   * @var CallRepository
   */
  private $callRepository;
  /**
   * @var CompanyRepository
   */
  private $companyRepository;

  public function __construct(UserRepository $userRepository,
                              CallRepository $callRepository,
                              CompanyRepository $companyRepository) {
    parent::__construct();

    $this->middleware('authorized:logged_calls.read', ['only' => ['index', 'data']]);
    $this->middleware('authorized:logged_calls.write', ['only' => ['create', 'store', 'update', 'edit']]);
    $this->middleware('authorized:logged_calls.delete', ['only' => ['delete']]);

    $this->userRepository = $userRepository;
    $this->callRepository = $callRepository;
    $this->companyRepository = $companyRepository;

    view()->share('type', 'call');
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index() {
    $title = trans('call.calls');
    return view('user.call.index', compact('title'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create() {
    $title = trans('call.new');

    $this->generateParams();

    return view('user.call.create', compact('title'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(CallRequest $request) {
    $call = $this->callRepository->create($request->all());

    event(new CallCreated($call));

    return redirect("call");
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  Call $call
   * @return \Illuminate\Http\Response
   */
  public function edit(Opportunity $opportunity, Call $call) {
    $title = trans('call.edit');

    $this->generateParams();

    return view('user.call.create', compact('title', 'call'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request $request
   * @param  Call $call
   * @return \Illuminate\Http\Response
   */
  public function update(CallRequest $request, Call $call) {
    $call->update($request->all());

    return redirect("call");
  }


  public function show(Call $call) {
    $title = trans('call.show');
    $this->generateParams();
    $action = "show";
    return view('user.call.show', compact('title', 'call', 'action'));
  }

  public function delete(Call $call) {
    $title = trans('call.delete');
    $this->generateParams();
    return view('user.call.delete', compact('title', 'call'));
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  Call $call
   * @return \Illuminate\Http\Response
   */
  public function destroy(Call $call) {
    $call->delete();
    return redirect('call');
  }

  public function data(Datatables $datatables) {
    $lead = Lead::all();
    $calls = $this->callRepository->getAll()
      ->with('user', 'company')
      ->get()
      ->map(function ($call) use ($lead) {
        $companies = $this->companyRepository->getAll()->orderBy("name", "asc")
          ->pluck('name', 'id')->prepend(trans('dashboard.select_company'), '');
        if (is_int($call->company_id) && $call->company_id > 0) {
          $company_name = $companies[$call->company_id];
        } else {
          $company_name = $call->company_name;
        }
        return [
          'id' => $call->id,
          'date' => $call->date,
          'call_summary' => $call->call_summary,
          'duration' => $call->duration,
          'company' => $company_name,
          'user' => isset($call->resp_staff) ? $call->resp_staff->full_name : '',
        ];
      });
    return $datatables->collection($calls)
      ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'logged_calls.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'call/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning "></i> </a>
                                     @endif
                                     <a href="{{ url(\'call/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.show\') }}">
                                            <i class="fa fa-fw fa-eye text-primary "></i> </a>
                                     @if(Sentinel::getUser()->hasAccess([\'logged_calls.delete\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'call/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                     @endif')
      ->removeColumn('id')
      ->escapeColumns(['actions'])->make();
  }

  private function generateParams() {
    $companies = $this->companyRepository->getAll()->orderBy("name", "asc")
      ->pluck('name', 'id')->prepend(trans('dashboard.select_company'), '');

    $staffs = $this->userRepository->getStaff()
      ->pluck('full_name', 'id')->prepend(trans('dashboard.select_staff'), '');

    view()->share('staffs', $staffs);
    view()->share('companies', $companies);
  }

}
