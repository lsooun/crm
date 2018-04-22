<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Models\Company;
use App\Models\Opportunity;
use App\Models\User;
use Yajra\Datatables\Datatables;

class OpportunityDeleteListController extends UserController
{
    public function __construct()
    {
        parent::__construct();


        view()->share('type', 'opportunity_delete_list');
    }
    public function index()
    {
        $title = trans('opportunity.delete_list');
        return view('user.opportunity_delete_list.index',compact('title'));
    }

    public function show($opportunity)
    {
        $opportunity = Opportunity::onlyDeleteLists()->where('id',$opportunity)->get()->first();
        $title = 'Show Delete List';
        $action = 'show';
        $this->generateParams();
        return view('user.opportunity_delete_list.show', compact('title', 'opportunity','action'));
    }

    public function delete($opportunity){
        $opportunity = Opportunity::onlyDeleteLists()->where('id',$opportunity)->get()->first();
        $title = 'Restore Delete List';
        $action = 'delete';
        $this->generateParams();
        return view('user.opportunity_delete_list.restore', compact('title', 'opportunity','action'));
    }

    public function restoreOpportunity($opportunity)
    {
        $opportunity = Opportunity::onlyDeleteLists()->where('id',$opportunity)->get()->first();
        $opportunity->update(['is_delete_list'=>0]);
        return redirect('opportunity');
    }

    public function data(Datatables $datatables)
    {
        $opportunityDeleteList = Opportunity::onlyDeleteLists()->get()
            ->map(function ($opportunityDeleteList) {
                $company_name = Company::get()->pluck('name','id');
                return [
                    'id' => $opportunityDeleteList->id,
                    'opportunity' => $opportunityDeleteList->opportunity,
                    'company' => $company_name[$opportunityDeleteList->company_name],
                    'next_action' => $opportunityDeleteList->next_action,
                    'stages' => $opportunityDeleteList->stages,
                    'expected_revenue' => $opportunityDeleteList->expected_revenue,
                    'probability' => $opportunityDeleteList->probability,
                    'sales_team_id' => $opportunityDeleteList->salesTeam->salesteam,
                    'salesteam' => User::where('id',$opportunityDeleteList->salesteam)->get()->first()->full_name,
                ];
            });

        return $datatables->collection($opportunityDeleteList)

            ->addColumn('actions', '
                                    <a href="{{ url(\'opportunity_delete_list/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                    @if(Sentinel::getUser()->hasAccess([\'opportunities.write\']) || Sentinel::inRole(\'admin\'))
                                    <a href="{{ url(\'opportunity_delete_list/\' . $id . \'/restore\' ) }}"  title="{{ trans(\'table.restore\') }}">
                                            <i class="fa fa-fw fa-undo text-success"></i> </a>
                                       @endif')
            ->removeColumn('id')
            ->escapeColumns( [ 'actions' ] )->make();
    }
    private function generateParams(){
        $user = User::all();
        $company_name = Company::get()->pluck('name','id');
        view()->share('user', $user);
        view()->share('company_name',$company_name);
    }
}
