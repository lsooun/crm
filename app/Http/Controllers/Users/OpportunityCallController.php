<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Http\Requests\CallRequest;
use App\Models\Call;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\Salesteam;
use App\Models\User;
use App\Repositories\CompanyRepository;
use App\Repositories\UserRepository;
use Sentinel;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class OpportunityCallController extends UserController
{
    /**
     * @var CompanyRepository
     */
    private $companyRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(CompanyRepository $companyRepository,
                                UserRepository $userRepository)
    {
        parent::__construct();

        $this->companyRepository = $companyRepository;
        $this->userRepository = $userRepository;

        view()->share('type', 'opportunitycall');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Opportunity $opportunity
     * @return \Illuminate\Http\Response
     */
    public function index(Opportunity $opportunity)
    {
        $title = trans('call.opportunity_calls');
        return view('user.opportunitycall.index', compact('title', 'opportunity'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Opportunity $opportunity)
    {
        $title = trans('call.opportunity_new');

        $this->generateParams();
        return view('user.opportunitycall.create', compact('title', 'opportunity'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Opportunity $opportunity
     * @param CallRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Opportunity $opportunity, CallRequest $request)
    {
        $request->merge(['company_id'=>$opportunity->company_name]);
        $opportunity->calls()->create($request->all(), ['user_id' => $this->user->id]);

        return redirect("opportunitycall/" . $opportunity->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Opportunity $opportunity , Call $call
     * @return \Illuminate\Http\Response
     */
    public function edit(Opportunity $opportunity, Call $call)
    {
        $title = trans('call.opportunity_edit');

        $this->generateParams();

        return view('user.opportunitycall.create', compact('title', 'call', 'opportunity'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param CallRequest|Request $request
     * @param Opportunity $opportunity
     * @param  Call $call
     * @return \Illuminate\Http\Response
     */
    public function update(CallRequest $request, Opportunity $opportunity, Call $call)
    {
        $request->merge(['company_id'=>$opportunity->company_name]);
        $call->update($request->all());

        return redirect("opportunitycall/" . $opportunity->id);
    }


    public function delete(Opportunity $opportunity, Call $call)
    {
        $title = trans('call.opportunity_delete');
        $this->generateParams();
        return view('user.opportunitycall.delete', compact('title', 'call', 'opportunity'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Opportunity $opportunity , Call $call
     * @return \Illuminate\Http\Response
     */
    public function destroy(Opportunity $opportunity, Call $call)
    {
        $call->delete();
        return redirect('opportunitycall/' . $opportunity->id);
    }

    public function data(Opportunity $opportunity,Datatables $datatables)
    {
        $calls = $opportunity->calls()
            ->with('responsible', 'company')
            ->get()
            ->map(function ($call) use ($opportunity) {
                $companies = $this->companyRepository->getAll()->orderBy("name", "asc")
                    ->pluck('name', 'id')
                    ->prepend(trans('company.company_name'), '');
                return [
                    'id' => $call->id,
                    'date' => $call->date,
                    'call_summary' => $call->call_summary,
                    'company' => $companies[$opportunity->company_name] ,
                    'call_type_id' => $opportunity->id,
                    'responsible' => isset($call->responsible->full_name) ?$call->responsible->full_name : '',
                ];
            });
        return $datatables->collection($calls)
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'opportunities.write\']) || Sentinel::inRole(\'admin\'))
<a href="{{ url(\'opportunitycall/\' . $call_type_id . \'/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning"></i>  </a>
                                            @endif
                                            @if(Sentinel::getUser()->hasAccess([\'opportunities.delete\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'opportunitycall/\' . $call_type_id . \'/\' . $id . \'/delete\' ) }}"  title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                            @endif')
            ->removeColumn('id')
            ->removeColumn('call_type_id')
            ->escapeColumns( [ 'actions' ] )->make();
    }

    private function generateParams()
    {
        $companies = $this->companyRepository->getAll()->orderBy("name", "asc")
	            ->pluck('name', 'id')
	            ->prepend(trans('dashboard.select_company'), '');

        $staffs = $this->userRepository->getParentStaff()
	            ->pluck('full_name', 'id')
	            ->prepend(trans('dashboard.select_staff'), '');

        view()->share('staffs', $staffs);
        view()->share('companies', $companies);
    }

}
