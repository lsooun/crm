<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\UserController;
use App\Models\Quotation;
use App\Models\Saleorder;
use App\Models\SaleorderProduct;
use App\Repositories\QuotationRepository;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Sentinel;
use Yajra\Datatables\Datatables;

class QuotationController extends UserController
{

    /**
     * @var QuotationRepository
     */
    private $quotationRepository;

    public function __construct(QuotationRepository $quotationRepository)
    {
        parent::__construct();

        $this->quotationRepository = $quotationRepository;

        view()->share('type', 'customers/quotation');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('quotation.quotations');
        return view('customers.quotation.index', compact('title'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Quotation $quotation
     * @return \Illuminate\Http\Response
     */
    public function show(Quotation $quotation)
    {
        $title = trans('quotation.show');
        $action = "show";
        return view('customers.quotation.show', compact('title', 'quotation','action'));
    }
    /**
     * @return mixed
     */
    public function data(Datatables $datatables)
    {
        $quotations = $this->quotationRepository->getAllForCustomer(Sentinel::getUser()->id)
              ->where([
                  ['status','!=',trans('quotation.draft_quotation')]
            ])
            ->with('user', 'customer')
            ->get()
            ->map(function ($quotation) {
                return [
                    'id' => $quotation->id,
                    'quotations_number' => $quotation->quotations_number,
                    'customer' => isset($quotation->customer) ?$quotation->customer->full_name : '',
                    'final_price' => $quotation->final_price,
                    'date' => $quotation->date,
                    'exp_date' => $quotation->exp_date,
                    'payment_term' => $quotation->payment_term,
                    'status' => $quotation->status
                ];
            });
        return Datatables::of($quotations)
            ->addColumn(
                'expired',
                '@if(strtotime(date("m/d/Y")) > strtotime("+".$payment_term." ",strtotime($exp_date)))
                                        <i class="fa fa-bell-slash text-danger" title="{{trans(\'quotation.quotation_expired\')}}"></i> 
                                     @else
                                      <i class="fa fa-bell text-warning" title="{{trans(\'quotation.quotation_will_expire\')}}"></i> 
                                     @endif'
            )
            ->addColumn('actions', '<a href="{{ url(\'customers/quotation/\' . $id . \'/show\' ) }}"  title="{{ trans(\'table.details\') }}">
                                            <i class="fa fa-fw fa-eye text-primary"></i>  </a>
                                             @if(Sentinel::getUser()->hasAccess([\'sales_orders.write\']) || Sentinel::inRole(\'customer\') && $status == \'Send Quotation\' 
                                             && strtotime(date("m/d/Y"))<= strtotime("+".$payment_term." ",strtotime($exp_date)) )
                                            <a href="{{ url(\'customers/quotation/\' . $id . \'/accept_quotation\' ) }}" title="{{ trans(\'quotation.accept_quotation\') }}">
                                            <i class="fa fa-fw fa-check text-primary"></i> </a>
                                            <a href="{{ url(\'customers/quotation/\' . $id . \'/reject_quotation\' ) }}" title="{{ trans(\'quotation.reject_quotation\') }}">
                                            <i class="fa fa-fw fa-close text-danger"></i> </a>
                                    @endif
                                     <a href="{{ url(\'customers/quotation/\' . $id . \'/print_quot\' ) }}"  title="{{ trans(\'table.print\') }}">
                                            <i class="fa fa-fw fa-print text-warning"></i>  </a>')
            ->removeColumn('id')
            ->escapeColumns( [ 'actions' ] )->make();
    }

    function confirmSalesOrder(Quotation $quotation)
    {
        $quotation->update(['is_converted_list' => 1]);
        $sales_orders = DB::table('sales_orders')->get()->count();
        if($sales_orders == 0){
            $total_fields = 0;
        }else{
            $total_fields = DB::table('sales_orders')->orderBy('id','desc')->first()->id;
        }
        $start_number = Settings::get('sales_start_number');
        $sale_no = Settings::get('sales_prefix') . (is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields : 0) + 1);

        $saleorder = Saleorder::create(array(
            'sale_number' => $sale_no,
            'customer_id' => $quotation->customer_id,
            'date' => date(Settings::get('date_format')),
            'exp_date' => $quotation->exp_date,
            'qtemplate_id' => $quotation->qtemplate_id,
            'payment_term' => isset($quotation->payment_term)?$quotation->payment_term:0,
            "sales_person_id" => $quotation->sales_person_id,
            "sales_team_id" => $quotation->sales_team_id,
            "terms_and_conditions" => $quotation->terms_and_conditions,
            "total" => $quotation->total,
            "tax_amount" => $quotation->tax_amount,
            "grand_total" => $quotation->grand_total,
            "discount" => is_null($quotation->discount)?0:$quotation->discount,
            "final_price" => $quotation->final_price,
            'status' => 'Draft sales order',
            'user_id' => Sentinel::getUser()->id,
            'quotation_id' => $quotation->id
        ));

        if (!empty($quotation->products->count() > 0)) {
            foreach ($quotation->products as $item) {
                $saleorderProduct = new SaleorderProduct();
                $saleorderProduct->order_id = $saleorder->id;
                $saleorderProduct->product_id = $item->product_id;
                $saleorderProduct->product_name = $item->product_name;
                $saleorderProduct->description = $item->description;
                $saleorderProduct->quantity = $item->quantity;
                $saleorderProduct->price = $item->price;
                $saleorderProduct->sub_total = $item->sub_total;
                $saleorderProduct->save();
            }
        }


        return redirect('customers/sales_order');
    }


    public function printQuot(Quotation $quotation)
    {
        $filename = 'Quotation-' . $quotation->quotations_number;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4','landscape');
        $pdf->loadView('quotation_template.'.Settings::get('quotation_template'), compact('quotation'));
        return $pdf->download($filename . '.pdf');
    }

    public function ajaxCreatePdf(Quotation $quotation)
    {
        $filename = 'Quotation-' . $quotation->quotations_number;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4','landscape');
        $pdf->loadView('quotation_template.'.Settings::get('quotation_template'), compact('quotation'));
        $pdf->save('./pdf/' . $filename . '.pdf');
        $pdf->stream();
        echo url("pdf/" . $filename . ".pdf");
    }

    function acceptQuotation(Quotation $quotation){
        $quotation->update(['status' => 'Quotation Accepted']);
        return redirect('customers/quotation')->with('success_message',trans('quotation.quotation_accepted'));
    }
    function rejectQuotation(Quotation $quotation){
        $quotation->update(['status' => 'Quotation Rejected']);
        return redirect('customers/quotation')->with('quotation_rejected',trans('quotation.quotation_rejected'));
    }
}
