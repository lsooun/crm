<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Models\Quotation;
use App\Models\Saleorder;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class QuotationConvertedListController extends UserController
{
    private $quotationRepository;

    public function __construct()
    {
        parent::__construct();


        view()->share('type', 'quotation_converted_list');
    }

    public function index()
    {
        $title = trans('quotation.converted_list');
        return view('user.quotation.converted_list',compact('title'));
    }


    public function data(Datatables $datatables)
    {
        $convertedList = Quotation::onlyConvertedLists()->get()
            ->map(function ($convertedList) {
                return [
                    'id' => $convertedList->id,
                    'quotations_number' => $convertedList->quotations_number,
                    'customer' => isset($convertedList->customer) ? $convertedList->customer->full_name : '',
                    'sales_team_id' => $convertedList->salesTeam->salesteam,
                    'sales_person' => isset($convertedList->salesPerson) ? $convertedList->salesPerson->full_name : '',
                    'final_price' => $convertedList->final_price,
                    'payment_term' => $convertedList->payment_term,
                    'status' => $convertedList->status
                ];
            });

        return $datatables->collection($convertedList)
            ->addColumn('actions', '
                                    <a href="{{ url(\'quotation_converted_list/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>')
            ->removeColumn('id')
            ->escapeColumns( [ 'actions' ] )->make();
    }
    public function salesOrderList($id)
    {
        $salesorder_id = Saleorder::where('quotation_id', $id)->get()->first();
        if(isset($salesorder_id)){
            return redirect('sales_order/' . $salesorder_id->id . '/show');
        }else{
            return redirect('quotation_converted_list')->withErrors(trans('quotation.sales_order_converted'));
        }
    }
}
