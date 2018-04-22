<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class QuotationDeleteListController extends UserController
{
    public function __construct()
    {
        parent::__construct();


        view()->share('type', 'quotation_delete_list');
    }
    public function index()
    {
        $title = trans('quotation.delete_list');
        return view('user.quotation_delete_list.index',compact('title'));
    }

    public function show($quotation)
    {
        $quotation = Quotation::onlyDeleteLists()->where('id',$quotation)->get()->first();
        $title = trans('quotation.show_delete_list');
        $action = 'show';
        return view('user.quotation_delete_list.show', compact('title', 'quotation','action'));
    }

    public function delete($quotation){
        $quotation = Quotation::onlyDeleteLists()->where('id',$quotation)->get()->first();
        $title = trans('quotation.restore_delete_list');
        $action = 'delete';
        return view('user.quotation_delete_list.restore', compact('title', 'quotation','action'));
    }

    public function restoreQuotation($quotation)
    {
        $quotation = Quotation::onlyDeleteLists()->where('id',$quotation)->get()->first();
        $quotation->update(['is_delete_list'=>0]);
        return redirect('quotation');
    }

    public function data(Datatables $datatables)
    {
        $quotationDeleteList = Quotation::onlyDeleteLists()->get()
            ->map(function ($quotationDeleteList) {
                return [
                    'id' => $quotationDeleteList->id,
                    'quotations_number' => $quotationDeleteList->quotations_number,
                    'customer' => isset($quotationDeleteList->customer) ? $quotationDeleteList->customer->full_name : '',
                    'sales_team_id' => $quotationDeleteList->salesTeam->salesteam,
                    'sales_person' => isset($quotationDeleteList->salesPerson) ? $quotationDeleteList->salesPerson->full_name : '',
                    'final_price' => $quotationDeleteList->final_price,
                    'payment_term' => $quotationDeleteList->payment_term,
                    'status' => $quotationDeleteList->status
                ];
            });

        return $datatables->collection($quotationDeleteList)

            ->addColumn('actions', '
                                    <a href="{{ url(\'quotation_delete_list/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                    @if(Sentinel::getUser()->hasAccess([\'quotations.write\']) || Sentinel::inRole(\'admin\'))
                                    <a href="{{ url(\'quotation_delete_list/\' . $id . \'/restore\' ) }}"  title="{{ trans(\'table.restore\') }}">
                                            <i class="fa fa-fw fa-undo text-success"></i> </a>
                                       @endif')
            ->removeColumn('id')
            ->escapeColumns( [ 'actions' ] )->make();
    }
}
