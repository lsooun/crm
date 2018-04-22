<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\UserController;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\Saleorder;
use App\Repositories\QuotationRepository;
use App\Repositories\SalesOrderRepository;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Sentinel;
use Yajra\Datatables\Datatables;

class SalesorderController extends UserController
{
    /**
     * @var QuotationRepository
     */
    private $salesOrderRepository;

    public function __construct(SalesOrderRepository $salesOrderRepository)
    {
        parent::__construct();
        $this->salesOrderRepository = $salesOrderRepository;

        view()->share('type', 'customers/sales_order');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('sales_order.sales_orders');
        return view('customers.sales_order.index', compact('title'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Saleorder $saleorder
     * @return \Illuminate\Http\Response
     */
    public function show(Saleorder $saleorder)
    {
        $title = trans('quotation.show');
        return view('customers.sales_order.show', compact('title', 'saleorder'));
    }
    public function data(Datatables $datatables)
    {
        $sales_orders = $this->salesOrderRepository->getAllForCustomer(Sentinel::getUser()->id)
            ->where('status',trans('sales_order.send_salesorder'))
            ->with('user', 'customer')
            ->get()
            ->map(function ($saleOrder) {
                return [
                    'id' => $saleOrder->id,
                    'sale_number' => $saleOrder->sale_number,
                    'customer' => isset($saleOrder->customer) ?$saleOrder->customer->full_name : '',
                    'final_price' => $saleOrder->final_price,
                    'date' => $saleOrder->date,
                    'exp_date' => $saleOrder->exp_date,
                    'payment_term' => $saleOrder->payment_term,
                    'status' => $saleOrder->status
                ];
            });
        return Datatables::of($sales_orders)
            ->addColumn(
                'expired',
                '@if(strtotime(date("m/d/Y"))>strtotime("+".$payment_term." ",strtotime($exp_date)))
                                        <i class="fa fa-bell-slash text-danger" title="{{trans(\'sales_order.salesorder_expired\')}}"></i> 
                                     @else
                                      <i class="fa fa-bell text-warning" title="{{trans(\'sales_order.salesorder_will_expire\')}}"></i> 
                                     @endif'
            )
            ->addColumn('actions', '<a href="{{ url(\'customers/sales_order/\' . $id . \'/show\' ) }}"  title="{{ trans(\'table.details\') }}">
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>')
            ->removeColumn('id')
            ->escapeColumns( [ 'actions' ] )->make();
    }
    public function printQuot(Saleorder $saleorder)
    {
        $filename = 'SaleOrder-' . $saleorder->sale_number;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4','landscape');
        $pdf->loadView('saleorder_template.'.Settings::get('saleorder_template'), compact('saleorder'));
        return $pdf->download($filename . '.pdf');
    }

    public function ajaxCreatePdf(Saleorder $saleorder)
    {
        $filename = 'SaleOrder-' . $saleorder->sale_number;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4','landscape');
        $pdf->loadView('saleorder_template.'.Settings::get('saleorder_template'), compact('saleorder'));
        $pdf->save('./pdf/' . $filename . '.pdf');
        $pdf->stream();
        echo url("pdf/" . $filename . ".pdf");

    }

    public function makeInvoice(Saleorder $saleorder,$id)
    {
        $saleorder = Saleorder::where('id',$id)->get()->first();
        $invoices = DB::table('invoices')->get()->count();
        if($invoices == 0){
            $total_fields = 0;
        }else{
            $total_fields = DB::table('invoices')->orderBy('id','desc')->first()->id;
        }
        $invoice_number = Settings::get('invoice_prefix') . (Settings::get('invoice_start_number') + (isset($total_fields) ? $total_fields : 0) + 1);
        $saleorder->update(['is_invoice_list' => 1]);

        $invoice_details = array(
            'order_id' => $saleorder->id,
            'customer_id' => $saleorder->customer_id,
            'sales_person_id' => $saleorder->sales_person_id,
            'sales_team_id' => $saleorder->sales_team_id,
            'invoice_number' => $invoice_number,
            'invoice_date' =>date(Settings::get('date_format')),
            'due_date' => $saleorder->exp_date,
            'payment_term' => isset($saleorder->payment_term)?$saleorder->payment_term:0,
            'status' => 'Open Invoice',
            'total' => $saleorder->total,
            'tax_amount' => $saleorder->tax_amount,
            'grand_total' => $saleorder->grand_total,
            'unpaid_amount' => $saleorder->final_price,
            'discount' => $saleorder->discount,
            'final_price' => $saleorder->final_price,
            'user_id' => Sentinel::getUser()->id,
        );
        $invoice = Invoice::create($invoice_details);

        $products = $saleorder->products;
        if ($products->count()>0) {
            foreach ($products as $item) {
                $product_add = array(
                    'invoice_id' => $invoice->id,
                    'product_id' => $item->id,
                    'product_name' => $item->product_name,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'sub_total' => $item->sub_total
                );
                InvoiceProduct::create($product_add);
            }
        }

        return redirect('customers/invoice');
    }
}
