<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Models\Company;
use App\Models\Opportunity;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Repositories\QuotationRepository;

class OpportunityConvertedListController extends UserController
{
    private $quotationRepository;

    public function __construct()
    {
        parent::__construct();


        view()->share('type', 'opportunity_converted_list');
    }

    public function index()
    {

        $title = trans('opportunity.converted_list');
        return view('user.opportunity.converted_list',compact('title'));
    }


    public function data(Datatables $datatables)
    {
        $convertedList = Opportunity::onlyConvertedLists()->get()
            ->map(function ($convertedList) {
                $company_name = Company::get()->pluck('name','id');

                return [
                    'id' => $convertedList->id,
                    'opportunity' => $convertedList->opportunity,
                    'company' => $company_name[$convertedList->company_name],
                    'next_action' => $convertedList->next_action,
                    'stages' => $convertedList->stages,
                    'expected_revenue' => $convertedList->expected_revenue,
                    'probability' => $convertedList->probability,
                    'sales_team_id' => $convertedList->salesTeam->salesteam,
                    'salesteam' => User::where('id',$convertedList->salesteam)->get()->first()->full_name,
                ];
            });

        return $datatables->collection($convertedList)
            ->addColumn('actions', '
                                    <a href="{{ url(\'convertedlist_view/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>')
            ->removeColumn('id')
            ->escapeColumns( [ 'actions' ] )->make();
    }
    public function quatationList($id)
    {
        $quotation_id = Quotation::where('opportunity_id', $id)->get()->first();
        if(isset($quotation_id)){
            return redirect('quotation/' . $quotation_id->id . '/show');
        }else{
            return redirect('opportunity_converted_list')->withErrors(trans('opportunity.converted_salesorder'));
        }
    }

}
