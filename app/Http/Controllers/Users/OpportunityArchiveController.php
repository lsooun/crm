<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Models\Company;
use App\Models\Opportunity;
use App\Models\User;
use Yajra\Datatables\Datatables;
class OpportunityArchiveController extends UserController
{
    public function __construct()
    {
        parent::__construct();


        view()->share('type', 'opportunity_archive');
    }
    public function index()
    {
        $title = trans('opportunity.archive');
        return view('user.opportunity_archive.index',compact('title'));
    }

    public function show($opportunity)
    {
        $opportunity = Opportunity::onlyArchived()->where('id',$opportunity)->get()->first();
        $title = 'Show Archive';
        $action = 'show';
        $this->generateParams();
        $company_name = Company::get()->pluck('name','id');
        return view('user.opportunity_archive.show', compact('title', 'opportunity','action','company_name'));
    }
    public function data(Datatables $datatables)
    {
        $opportunityArchive = Opportunity::onlyArchived()->get()
            ->map(function ($opportunityArchive) {
                $company_name = Company::get()->pluck('name','id');
                return [
                    'id' => $opportunityArchive->id,
                    'opportunity' => $opportunityArchive->opportunity,
                    'company' => $company_name[$opportunityArchive->company_name],
                    'next_action' => $opportunityArchive->next_action,
                    'stages' => $opportunityArchive->stages,
                    'expected_revenue' => $opportunityArchive->expected_revenue,
                    'probability' => $opportunityArchive->probability,
                    'sales_team_id' => $opportunityArchive->salesTeam->salesteam,
                    'salesteam' => User::where('id',$opportunityArchive->salesteam)->get()->first()->full_name,
                    'lost_reason' => $opportunityArchive->lost_reason,
                ];
            });

        return $datatables->collection($opportunityArchive)

            ->addColumn('actions', '
                                    <a href="{{ url(\'opportunity_archive/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                    ')
            ->removeColumn('id')
            ->escapeColumns( [ 'actions' ] )->make();
    }
    private function generateParams(){
        $user = User::all();
        view()->share('user', $user);
    }
}
