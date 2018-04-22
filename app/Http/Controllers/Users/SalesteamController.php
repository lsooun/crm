<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Http\Requests\SalesteamRequest;
use App\Models\Salesteam;
use App\Models\User;
use App\Repositories\ExcelRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\UserRepository;
use Sentinel;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;
use App\Helpers\ExcelfileValidator;

class SalesteamController extends UserController {
  /**
   * @var SalesTeamRepository
   */
  private $salesTeamRepository;
  /**
   * @var UserRepository
   */
  private $userRepository;
  /**
   * @var ExcelRepository
   */
  private $excelRepository;

  /**
   * @param SalesTeamRepository $salesTeamRepository
   * @param UserRepository $userRepository
   * @param ExcelRepository $excelRepository
   */
  public function __construct(SalesTeamRepository $salesTeamRepository,
                              UserRepository $userRepository,
                              ExcelRepository $excelRepository) {
    $this->middleware('authorized:sales_team.read', ['only' => ['index', 'data']]);
    $this->middleware('authorized:sales_team.write', ['only' => ['create', 'store', 'update', 'edit']]);
    $this->middleware('authorized:sales_team.delete', ['only' => ['delete']]);

    parent::__construct();

    $this->salesTeamRepository = $salesTeamRepository;
    $this->userRepository = $userRepository;
    $this->excelRepository = $excelRepository;


    view()->share('type', 'salesteam');
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index() {
    $title = trans('salesteam.salesteams');
    return view('user.salesteam.index', compact('title'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create(salesteam $salesteam) {
    $title = trans('salesteam.new');

    $this->generateParams();

    return view('user.salesteam.create', compact('title', 'newSales'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(SalesteamRequest $request) {
    $newsales = $this->salesTeamRepository->create($request->all());

    return redirect("salesteam");
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int $id
   * @return \Illuminate\Http\Response
   */
  public function edit(Salesteam $salesteam) {
    $title = trans('salesteam.edit');

    $this->generateParams();
    $salesteam_stafs = User::whereIn('id', $salesteam->team_members)->pluck('id', 'id')->all();
    return view('user.salesteam.edit', compact('title', 'salesteam', 'salesteam_stafs'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request $request
   * @param  int $id
   * @return \Illuminate\Http\Response
   */
  public function update(SalesteamRequest $request, Salesteam $salesteam) {
    $salesteam->quotations = ($request->quotations) ? $request->quotations : 0;
    $salesteam->leads = ($request->leads) ? $request->leads : 0;
    $salesteam->opportunities = ($request->opportunities) ? $request->opportunities : 0;
    $salesteam->update($request->all());
    return redirect("salesteam");
  }

  public function show(Salesteam $salesteam) {
    $title = trans('salesteam.show');
    $action = "show";
    $user = User::all();
    return view('user.salesteam.show', compact('title', 'salesteam', 'action', 'user'));
  }

  public function delete(Salesteam $salesteam) {
    $title = trans('salesteam.delete');
    $user = User::all();
    return view('user.salesteam.delete', compact('title', 'salesteam', 'user'));
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int $id
   * @return \Illuminate\Http\Response
   */
  public function destroy(Salesteam $salesteam) {
    $salesteam->delete();
    return redirect('salesteam');
  }

  public function data(Datatables $datatables) {

    $salesteam = $this->salesTeamRepository->getAll()
      ->with('actualInvoice')
      ->get()
      ->map(function ($salesteam) {
        return [
          'id' => $salesteam->id,
          'salesteam' => $salesteam->salesteam,
          'target' => $salesteam->invoice_target,
//                'invoice_forecast' => $salesteam->invoice_forecast,
          'actual_invoice' => $salesteam->actualInvoice->sum('grand_total'),
          'count_uses' => $salesteam->agentSalesteam->count() +
            $salesteam->opportunitySalesteam->count() +
            $salesteam->quotationSalesteam->count() +
            $salesteam->salesorderSalesteam->count() +
            $salesteam->actualInvoice->count()

        ];
      });

    return $datatables->collection($salesteam)
      ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'sales_team.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'salesteam/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning"></i>  </a>
                                     @endif
                                     @if(Sentinel::getUser()->hasAccess([\'sales_team.read\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'salesteam/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                    @endif
                                     @if(Sentinel::getUser()->hasAccess([\'sales_team.delete\']) && $count_uses==0 || Sentinel::inRole(\'admin\') && $count_uses==0)
                                        <a href="{{ url(\'salesteam/\' . $id . \'/delete\' ) }}"  title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                     @endif')
      ->removeColumn('id')
      ->removeColumn('count_uses')
      ->escapeColumns(['actions'])->make();
  }

  private function generateParams() {
    $staffs = $this->userRepository->getParentStaff()->pluck('full_name', 'id')->prepend(trans('salesteam.team_leader'), '');

    view()->share('staffs', $staffs);
  }

  public function downloadExcelTemplate() {
    return response()->download(base_path('resources/excel-templates/sales-teams.xlsx'));
  }

  public function getImport() {
    $title = trans('salesteam.salesteams');
    return view('user.salesteam.import', compact('title'));
  }

  public function postImport(Request $request) {
    if (!ExcelfileValidator::validate($request)) {
      return response('invalid File or File format', 500);
    }

    $reader = $this->excelRepository->load($request->file('file'));

    $data = [
      'salesteams' => $reader->all(),
      'staff' => $this->userRepository->getParentStaff()->map(function ($user) {
        return [
          'text' => $user->full_name,
          'id' => $user->id
        ];
      })->values(),
    ];

    return response()->json(compact('data'), 200);
  }

  public function postAjaxStore(SalesteamRequest $request) {
    $this->salesTeamRepository->create($request->except('created', 'errors', 'selected'));
    return response()->json([], 200);
  }
}
