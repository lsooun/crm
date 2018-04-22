<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Models\Saleorder;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class SalesorderDeleteListController extends UserController
{
    public function __construct()
    {
        parent::__construct();


        view()->share('type', 'salesorder_delete_list');
    }
    public function index()
    {
        $title = trans('sales_order.delete_list');
        return view('user.salesorder_delete_list.index',compact('title'));
    }

    public function show($saleorder)
    {
        $saleorder = Saleorder::onlyDeleteLists()->where('id',$saleorder)->get()->first();
        $title = trans('sales_order.show_delete_list');
        $action = 'show';
        return view('user.salesorder_delete_list.show', compact('title', 'saleorder','action'));
    }

    public function delete($saleorder){
        $saleorder = Saleorder::onlyDeleteLists()->where('id',$saleorder)->get()->first();
        $title = trans('sales_order.restore_delete_list');
        $action = 'delete';
        return view('user.salesorder_delete_list.restore', compact('title', 'saleorder','action'));
    }

    public function restoreSalesorder($saleorder)
    {
        $saleorder = Saleorder::onlyDeleteLists()->where('id',$saleorder)->get()->first();
        $saleorder->update(['is_delete_list'=>0]);
        return redirect('sales_order');
    }

    public function data(Datatables $datatables)
    {
        $salesOrderDeleteList = Saleorder::onlyDeleteLists()->get()
            ->map(function ($salesOrderDeleteList) {
                return [
                    'id' => $salesOrderDeleteList->id,
                    'sale_number' => $salesOrderDeleteList->sale_number,
                    'date' => $salesOrderDeleteList->date,
                    'customer' => isset($salesOrderDeleteList->customer) ?$salesOrderDeleteList->customer->full_name : '',
                    'person' => isset($salesOrderDeleteList->user) ?$salesOrderDeleteList->user->full_name : '',
                    'final_price' => $salesOrderDeleteList->final_price,
                    'status' => $salesOrderDeleteList->status
                ];
            });

        return $datatables->collection($salesOrderDeleteList)

            ->addColumn('actions', '
                                    <a href="{{ url(\'salesorder_delete_list/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                    @if(Sentinel::getUser()->hasAccess([\'sales_orders.write\']) || Sentinel::inRole(\'admin\'))
                                    <a href="{{ url(\'salesorder_delete_list/\' . $id . \'/restore\' ) }}"  title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-undo text-success"></i> </a>
                                       @endif')
            ->removeColumn('id')
            ->escapeColumns( [ 'actions' ] )->make();
    }
}
