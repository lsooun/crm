<?php

namespace App\Http\Controllers\Users;

use App\Helpers\ExcelfileValidator;
use App\Http\Controllers\UserController;
use App\Http\Requests\LeadImportRequest;
use App\Http\Requests\LeadRequest;
use App\Models\City;
use App\Models\Country;
use App\Models\Lead;
use App\Models\State;
use App\Models\Tag;
use App\Repositories\CompanyRepository;
use App\Repositories\LeadRepository;
use App\Repositories\OptionRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\UserRepository;
use App\Repositories\ExcelRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Sentinel;
use Yajra\Datatables\Datatables;


class LeadController extends UserController {
	/**
	 * @var CompanyRepository
	 */
	private $companyRepository;
	/**
	 * @var UserRepository
	 */
	private $userRepository;
	/**
	 * @var LeadRepository
	 */
	private $leadRepository;
	/**
	 * @var SalesTeamRepository
	 */
	private $salesTeamRepository;
	/**
	 * @var OptionRepository
	 */
	private $optionRepository;

	/**
	 * @var ExcelRepository
	 */
	private $excelRepository;

	/**
	 * SalesTeamController constructor.
	 *
	 * @param CompanyRepository $companyRepository
	 * @param UserRepository $userRepository
	 * @param LeadRepository $leadRepository
	 * @param SalesTeamRepository $salesTeamRepository
	 * @param OptionRepository $optionRepository
	 */
	public function __construct(
		CompanyRepository $companyRepository,
		UserRepository $userRepository,
		LeadRepository $leadRepository,
		SalesTeamRepository $salesTeamRepository,
		OptionRepository $optionRepository,
		ExcelRepository $excelRepository
	) {
		$this->middleware( 'authorized:leads.read', [ 'only' => [ 'index', 'data' ] ] );
		$this->middleware( 'authorized:leads.write', [ 'only' => [ 'create', 'store', 'update', 'edit' ] ] );
		$this->middleware( 'authorized:leads.delete', [ 'only' => [ 'delete' ] ] );

		parent::__construct();

		$this->companyRepository   = $companyRepository;
		$this->userRepository      = $userRepository;
		$this->companyRepository   = $companyRepository;
		$this->leadRepository      = $leadRepository;
		$this->salesTeamRepository = $salesTeamRepository;
		$this->optionRepository    = $optionRepository;
		$this->excelRepository     = $excelRepository;

		view()->share( 'type', 'lead' );
	}

	public function index() {
		$title = trans( 'lead.leads' );

		return view( 'user.lead.index', compact( 'title' ) );
	}

	public function create() {
		$title = trans( 'lead.new' );
		$calls = 0;

		$this->generateParams();

		return view( 'user.lead.create', compact( 'title', 'calls' ) );
	}

	public function store( LeadRequest $request ) {

		$this->leadRepository->store( $request->all() );

		return redirect( "lead" );
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function edit( Lead $lead ) {
	    $title = trans( 'lead.edit' );
        $this->generateParams();
		$calls  = $lead->calls()->count();
		$states = State::where( 'country_id', $lead->country_id )->orderBy( "name", "asc" )->pluck( 'name', 'id' );
		$cities = City::where( 'state_id', $lead->state_id )->orderBy( "name", "asc" )->pluck( 'name', 'id' );
        $lead->load('country');
		$lead->load('state');
        $lead->load('city');

		return view( 'user.lead.edit', compact( 'lead', 'title', 'calls', 'states', 'cities' ) );
	}

	public function update( Lead $lead, LeadRequest $request ) {
		$lead->update( $request->all() );
		return redirect( "lead" );
	}

	public function show( Lead $lead ) {
		$title  = trans( 'lead.show' );
		$action = "show";
		$this->generateParams();

		return view( 'user.lead.show', compact( 'title', 'lead', 'action' ) );
	}

	public function delete( Lead $lead ) {

		$title = trans( 'lead.delete' );
		$this->generateParams();
        $action = "delete";
		return view( 'user.lead.delete', compact( 'title', 'lead','action' ) );
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy( Lead $lead ) {
		$lead->calls()->delete();
		$lead->delete();

		return redirect( 'lead' );
	}

	public function data( Datatables $datatables ) {
		$leads = $this->leadRepository->getAll()
		                              ->with( 'country', 'salesTeam' )
		                              ->get()
		                              ->map( function ( $lead ) {
			                              return [
				                              'id'           => $lead->id,
				                              'created_at'   => $lead->created_at,
				                              'company_name' => $lead->company_name,
                                               'client_name' => $lead->client_name,
				                              'product_name' => $lead->product_name,
				                              'email'        => $lead->email,
				                              'phone'        => $lead->phone,
				                              'calls'        => $lead->calls->count(),
				                              'priority'     => $lead->priority,
			                              ];
		                              } );

		return $datatables->collection( $leads )
		                  ->edit_column( 'created_at', '{{ $created_at->format(Settings::get(\'date_format\'))}}' )
		                  ->addColumn( 'actions', '@if(Sentinel::getUser()->hasAccess([\'leads.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'lead/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning"></i> </a>
                                        <a href="{{ url(\'leadcall/\'. $id .\'/\' ) }}" title="{{ trans(\'table.calls\') }}">
                                            <i class="fa fa-fw fa-phone text-primary"></i> <sup>{{ $calls }}</sup></a>
                                    @endif
                                     @if(Sentinel::getUser()->hasAccess([\'leads.read\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'lead/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                    @endif
                                    @if(Sentinel::getUser()->hasAccess([\'leads.delete\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'lead/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                    @endif' )
		                  ->removeColumn( 'id' )
		                  ->removeColumn( 'calls' )
		                  ->escapeColumns( [ 'actions' ] )->make();
	}

	public function ajaxStateList( Request $request ) {
		return State::where( 'country_id', $request->id )->orderBy( "name", "asc" )
		            ->pluck( 'name', 'id' )
		            ->prepend( trans( 'lead.select_state' ),'' );
	}

	public function ajaxCityList( Request $request ) {
		return City::where( 'state_id', $request->id )->orderBy( "name", "asc" )
		           ->pluck( 'name', 'id' )
		           ->prepend( trans( 'lead.select_city' ), '' );
	}

	private function generateParams() {
		$tags = Tag::pluck( 'title', 'id' );

		$priority = $this->optionRepository->getAll()->where( 'category', 'priority' )->get()
		                                   ->map( function ( $title ) {
			                                   return [
				                                   'title' => $title->title,
				                                   'value' => $title->value,
			                                   ];
		                                   } )->pluck( 'title', 'value' );

		$titles = $this->optionRepository->getAll()->where( 'category', 'titles' )->get()
		                                 ->map( function ( $title ) {
			                                 return [
				                                 'title' => $title->title,
				                                 'value' => $title->value,
			                                 ];
		                                 } )->pluck( 'title', 'value' )
                                            ->prepend(trans('lead.select_title'), '');

		$companies = $this->companyRepository->getAll()->orderBy( "name", "asc" )->pluck( 'name', 'id' )
		                                     ->prepend( trans( 'dashboard.select_company' ), '' );

		$countries = Country::orderBy( "name", "asc" )->pluck( 'name', 'id' )
									->prepend( trans( 'lead.select_country' ), '' );

		$staffs = $this->userRepository->getStaff()->pluck( 'full_name', 'id' )
											->prepend( trans( 'dashboard.select_staff' ), '' );

		$salesteams = $this->salesTeamRepository->getAll()->orderBy( "id", "asc" )
		                                        ->pluck( 'salesteam', 'id' )
												->prepend( trans( 'dashboard.select_sales_team' ), '');

		$states = State::orderBy( "name", "asc" )->pluck( 'name', 'id' );
		$cities = City::orderBy( "name", "asc" )->pluck( 'name', 'id' );

        $functions = $this->optionRepository->getAll()->where( 'category', 'function_type' )->get()
            ->map( function ( $title ) {
                return [
                    'title' => $title->title,
                    'value' => $title->value,
                ];
            } )->pluck( 'title', 'value' )
            ->prepend(trans('lead.select_function'), '');

		view()->share( 'tags', $tags );
		view()->share( 'priority', $priority );
		view()->share( 'titles', $titles );
		view()->share( 'companies', $companies );
		view()->share( 'countries', $countries );
		view()->share( 'staffs', $staffs );
		view()->share( 'salesteams', $salesteams );
		view()->share( 'states', $states );
		view()->share( 'cities', $cities );
        view()->share( 'functions', $functions );
	}

	public function downloadExcelTemplate() {
		return response()->download( base_path( 'resources/excel-templates/leads.xlsx' ) );
	}

	public function getImport() {
		$title = trans( 'lead.newupload' );

		//  return 'jimmy';
		return view( 'user.lead.import', compact( 'title' ) );
	}

	public function postImport( Request $request ) {

		if(! ExcelfileValidator::validate( $request ))
		{
			return response('invalid File or File format', 500);
		}

		$reader = $this->excelRepository->load( $request->file( 'file' ) );

		$salesteam = $this->salesTeamRepository->getAll()->orderBy( "id", "asc" )->get();
		$customers = $reader->all()->map( function ( $row ) use ( $salesteam ) {
			return [
				'company_name'   => $row->company,
                'company_site'   => $row->company_site,
 				'address'        => $row->address,
                'product_name'   => $row->product_name,
				'contact_name'   => $row->names,
				'email'          => $row->email,
				'function'       => $row->function,
				'phone'          => $row->phone,
				'mobile'         => $row->mobile,
                'client_name'    => $row->client_name,
				'country_id'     => 101,
                'priority'       => $row->priority,
			];
		} );

		$companies = $this->companyRepository->getAll()->get()->map( function ( $company ) {
			return [
				'text' => $company->name,
				'id'   => $company->id,
			];
		} );

		$countries = Country::orderBy( "name", "asc" )
		                    ->select( 'id', DB::raw( 'name as text' ) )
							->get()->map( function ( $country ) {
								return [
									'text' => $country->text,
									'id'   => $country->id,
								];
							} );

		$salesteams = $this->salesTeamRepository->getAllLeads()->orderBy( "id", "asc" )
		                                        ->select( 'id', DB::raw( 'salesteam as text' ) )
												->get()->map( function ( $salesteam ) {
													return [
														'text' => $salesteam->text,
														'id'   => $salesteam->id,
													];
												} );
        $functions = $this->optionRepository->getAll()->where( 'category', 'function_type' )->get()
            ->map( function ( $title ) {
                return [
                    'title' => $title->title,
                    'value' => $title->value,
                ];
            } )->pluck( 'title', 'value' );
        $priorities = $this->optionRepository->getAll()->where( 'category', 'priority' )->get()
            ->map( function ( $title ) {
                return [
                    'title' => $title->title,
                    'value' => $title->value,
                ];
            } )->pluck( 'title', 'value' );

		return response()->json( compact( 'customers', 'companies', 'countries', 'salesteams','functions','priorities' ), 200 );
	}

	public function postAjaxStore( LeadImportRequest $request ) {
		$this->leadRepository->store( $request->except( 'created', 'errors', 'selected' ) );

		return response()->json( [], 200 );
	}

	public function importExcelData( Request $request ) {
		$this->validate( $request, [
			'file' => 'required|mimes:xlsx,xls,csv|max:5000',
		] );

		$reader = $this->excelRepository->load( $request->file( 'file' ) );
	}


}
