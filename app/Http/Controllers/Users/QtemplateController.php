<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Http\Requests\QtemplateRequest;
use App\Models\Product;
use App\Models\Qtemplate;
use App\Models\QtemplateProduct;
use App\Models\Setting;
use App\Repositories\ProductRepository;
use App\Repositories\QuotationTemplateRepository;
use Illuminate\Http\Request;
use App\Http\Requests;
use Sentinel;
use Yajra\Datatables\Datatables;

class QtemplateController extends UserController
{
    /**
     * @var QuotationTemplateRepository
     */
    private $qtemplateRepository;
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * QtemplateController constructor.
     * @param QuotationTemplateRepository $qtemplateRepository
     * @param ProductRepository $productRepository
     */
    public function __construct(QuotationTemplateRepository $qtemplateRepository,
                                ProductRepository $productRepository)
    {
        parent::__construct();
        $this->qtemplateRepository = $qtemplateRepository;
        $this->productRepository = $productRepository;

        view()->share('type', 'qtemplate');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('qtemplate.qtemplates');
        return view('user.qtemplate.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('qtemplate.new');

        $this->generateParams();

        return view('user.qtemplate.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param QtemplateRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(QtemplateRequest $request)
    {
        $qtemplate = $this->qtemplateRepository->create($request->only('quotation_template', 'quotation_duration', 'total', 'tax_amount', 'grand_total', 'terms_and_conditions', 'immediate_payment'));

        if (!empty($request->product_id)) {
            foreach ($request->product_id as $key => $item) {
                if ($item != "" && $request->product_name[$key] != "" &&
                    $request->quantity[$key] != "" && $request->price[$key] != "" && $request->sub_total[$key] != ""
                ) {
                    $qtemplateProject = new QtemplateProduct();
                    $qtemplateProject->qtemplate_id = $qtemplate->id;
                    $qtemplateProject->product_id = $item;
                    $qtemplateProject->product_name = $request->product_name[$key];
                    $qtemplateProject->description = $request->description[$key];
                    $qtemplateProject->quantity = $request->quantity[$key];
                    $qtemplateProject->price = $request->price[$key];
                    $qtemplateProject->sub_total = $request->sub_total[$key];
                    $qtemplateProject->save();
                }
            }
        }
        return redirect("qtemplate");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Qtemplate $qtemplate
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function edit(Qtemplate $qtemplate)
    {
        $title = trans('qtemplate.edit');

        $this->generateParams();

        return view('user.qtemplate.create', compact('title', 'qtemplate'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(QtemplateRequest $request, Qtemplate $qtemplate)
    {
        $qtemplate->update($request->only('quotation_template', 'quotation_duration', 'total', 'tax_amount', 'grand_total', 'terms_and_conditions', 'immediate_payment'));
        QtemplateProduct::where('qtemplate_id', $qtemplate->id)->delete();

        if (!empty($request->product_id)) {
            foreach ($request->product_id as $key => $item) {
                if ($item != "" && $request->product_name[$key] != "" &&
                    $request->quantity[$key] != "" && $request->price[$key] != "" && $request->sub_total[$key] != ""
                ) {
                    $qtemplateProject = new QtemplateProduct();
                    $qtemplateProject->qtemplate_id = $qtemplate->id;
                    $qtemplateProject->product_id = $item;
                    $qtemplateProject->product_name = $request->product_name[$key];
                    $qtemplateProject->description = $request->description[$key];
                    $qtemplateProject->quantity = $request->quantity[$key];
                    $qtemplateProject->price = $request->price[$key];
                    $qtemplateProject->sub_total = $request->sub_total[$key];
                    $qtemplateProject->save();
                }
            }
        }
        return redirect("qtemplate");
    }


    public function delete(Qtemplate $qtemplate)
    {
        $title = trans('qtemplate.delete');
        return view('user.qtemplate.delete', compact('title', 'qtemplate'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Qtemplate $qtemplate)
    {
        $qtemplate->products()->delete();
        $qtemplate->delete();
        return redirect('qtemplate');
    }

    /**
     * @return mixed
     */
    public function data(Datatables $datatables)
    {
        $qtemplates = $this->qtemplateRepository->getAll()->select('id', 'quotation_template', 'quotation_duration')->get();

        return $datatables->collection($qtemplates)
            ->addColumn('actions', '<a href="{{ url(\'qtemplate/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning "></i>  </a>
                                     <a href="{{ url(\'qtemplate/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i></a>')
            ->removeColumn('id')
            ->escapeColumns( [ 'actions' ] )->make();
    }

    private function generateParams()
    {
        $products = $this->productRepository->getAll()->orderBy("id", "desc")->get();

        view()->share('products', $products);
    }
}
