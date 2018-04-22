<?php

namespace App\Http\Controllers\Users;

use App\Helpers\ExcelfileValidator  ;
use App\Helpers\Thumbnail;
use App\Http\Controllers\UserController;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Option;
use App\Models\Product;
use App\Repositories\CategoryRepository;
use App\Repositories\ExcelRepository;
use App\Repositories\OptionRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\Str;
use Sentinel;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use App\Http\Requests;
use Yajra\Datatables\Datatables;

class ProductController extends UserController
{

    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;
    /**
     * @var ExcelRepository
     */
    private $excelRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     * @param ExcelRepository $excelRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(ProductRepository $productRepository,
                                CategoryRepository $categoryRepository,
                                ExcelRepository $excelRepository,
                                OptionRepository $optionRepository)
    {

        $this->middleware('authorized:products.read', ['only' => ['index', 'data']]);
        $this->middleware('authorized:products.write', ['only' => ['create', 'store', 'update', 'edit']]);
        $this->middleware('authorized:products.delete', ['only' => ['delete']]);

        parent::__construct();

        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->excelRepository = $excelRepository;
        $this->optionRepository = $optionRepository;

        view()->share('type', 'product');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('product.products');
        return view('user.product.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('product.new');

        $this->generateParams();

        return view('user.product.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProductRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        if ($request->hasFile('product_image_file')) {
            $file = $request->file('product_image_file');
            $file = $this->productRepository->uploadProductImage($file);

            $request->merge([
                'product_image' => $file->getFileInfo()->getFilename(),
            ]);

            $this->generateProductThumbnail($file);
        }

        $product = $this->productRepository->create($request->except('attribute_name', 'product_attribute_value', 'product_image_file'));

        if (!empty($request->attribute_name)) {
            foreach ($request->attribute_name as $key => $item) {
                $productVariant = new ProductVariant();
                $productVariant->attribute_name = $item;
                $productVariant->product_attribute_value = $request->product_attribute_value[$key];
                $product->productVariants()->save($productVariant);
            }
        }

        return redirect("product");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Product $product
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function edit(Product $product)
    {
        $title = trans('product.edit');

        $this->generateParams();

        return view('user.product.edit', compact('title', 'product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ProductRequest|Request $request
     * @param Product $product
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function update(ProductRequest $request, Product $product)
    {
        if ($request->hasFile('product_image_file')) {
            $file = $request->file('product_image_file');
            $file = $this->productRepository->uploadProductImage($file);

            $request->merge([
                'product_image' => $file->getFileInfo()->getFilename(),
            ]);

            $this->generateProductThumbnail($file);
        }

        $product->update($request->except('attribute_name', 'product_attribute_value', 'product_image_file'));

        ProductVariant::where('product_id', $product->id)->delete();

        if (!empty($request->attribute_name)) {
            foreach ($request->attribute_name as $key => $item) {
                $productVariant = new ProductVariant();
                $productVariant->attribute_name = $item;
                $productVariant->product_attribute_value = $request->product_attribute_value[$key];
                $product->productVariants()->save($productVariant);
            }
        }

        return redirect("product");
    }


    public function show(Product $product)
    {
        $action = "show";
        $title = trans('product.view');
        return view('user.product.show', compact('title', 'product', 'action'));
    }

    public function delete(Product $product)
    {
        $title = trans('product.delete');
        return view('user.product.delete', compact('title', 'product'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Product $product
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect("product");
    }

    public function data(Datatables $datatables)
    {
        $products = $this->productRepository->getAll()
            ->with('category','invoiceProduct','quotationProduct','qtemplateProduct','salesOrderProduct')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'product_name' => $p->product_name,
                    'category' => is_null($p->category) ? '': $p->category->name,
                    'product_type' => $p->product_type,
                    'status' => $p->status,
                    'quantity_on_hand' => $p->quantity_on_hand,
                    'quantity_available' => $p->quantity_available,
                    'count_uses' => $p->invoiceProduct->count() +
                                    $p->quotationProduct->count() +
                                    $p->qtemplateProduct->count() +
                                    $p->salesOrderProduct->count()
                ];
            });
        return $datatables->collection($products)
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'products.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'product/\' . $id . \'/edit\' ) }}"  title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning "></i> </a>
                                     @endif
                                     <a href="{{ url(\'product/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}">
                                            <i class="fa fa-fw fa-eye text-primary"></i></a>
                                     @if((Sentinel::getUser()->hasAccess([\'products.delete\']) || Sentinel::inRole(\'admin\')) && $count_uses==0)
                                        <a href="{{ url(\'product/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                     @endif')
            ->removeColumn('id')
            ->removeColumn('count_uses')
            ->escapeColumns( [ 'actions' ] )->make();
    }

    /**
     * @param $file
     */
    private function generateProductThumbnail($file)
    {
        $sourcePath = $file->getPath() . '/' . $file->getFilename();
        $thumbPath = $file->getPath() . '/thumb_' . $file->getFilename();
        Thumbnail::generate_image_thumbnail($sourcePath, $thumbPath);
    }

    private function generateParams()
    {
        $statuses = $this->optionRepository->getAll()
            ->where('category', 'product_status')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title','value')->prepend(trans('Select Status'), '');
        $product_types = $this->optionRepository->getAll()
            ->where('category', 'product_type')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title','value')->prepend(trans('product.product_type'), '');

        $categories = $this->categoryRepository->getAll()
            ->orderBy("id", "desc")
            ->get()
	        ->map(function ($category) {
		        return [
			        'title' => $category->name,
			        'value'   => $category->id,
		        ];
	        })->pluck('title','value')->prepend(trans('product.category_id'), '');

        view()->share('statuses', $statuses);
        view()->share('product_types', $product_types);
        view()->share('categories', $categories);
    }

    public function getImport()
    {
		//return 'jimmy';
        $title = trans('product.import');
        return view('user.product.import', compact('title'));
    }

    public function postImport(Request $request)
    {

        if(! ExcelfileValidator::validate( $request ))
        {
            return response('invalid File or File format', 500);
        }

        $reader = $this->excelRepository->load($request->file('file'));

         $data = $reader->all()->map(function ($product) {
                return [
                    'product_name' => $product->product_name,
                    'product_type' => $product->product_type,
                    'status' => $product->status,
                    'quantity_on_hand' => $product->quantity_on_hand,
                    'quantity_available' => $product->quantity_available,
                    'sale_price' => $product->sale_price,
                    'description' => $product->description,
                    'description_for_quotations' => $product->description_for_quotations,
                    'variants' => $this->getProductVariants($product->variants),
                ];
            }) ;

        $categories = $this->categoryRepository->getAll()
            ->orderBy("id", "desc")
            ->get()
            ->map(function ($category) {
                return [
                    'title' => $category->name,
                    'id' => $category->id,
                ];
            });
        $productTypes = $this->optionRepository->getAll()
            ->where('category', 'product_type')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title','value');

        $statuses = $this->optionRepository->getAll()
            ->where('category', 'product_status')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title','value');

        return response()->json(compact('data', 'categories','productTypes','statuses'), 200);
    }

    public function postAjaxStore(ProductRequest $request)
    {
       $product =  $this->productRepository->create($request->except('created', 'errors', 'selected' , 'variants'));

         if (!empty($request->variants)) {

            foreach ($request->variants as $key => $item) {
                $productVariant = new ProductVariant();
                $productVariant->attribute_name = $item[0];
                $productVariant->product_attribute_value = $item[1] ;
                $product->productVariants()->save($productVariant);
            }
        }
        return response()->json([], 200);
    }

    public function downloadExcelTemplate()
    {
        $path = base_path('resources/excel-templates/products.xlsx');

        if (file_exists($path)) {
            return response()->download($path);
        }

        return 'File not found!';
    }

    private function getProductVariants($variants = [])
    {
        if (isset($variants)) {
            $variants = array_map(
                function ($v) {
                    return explode(':', $v);
                },
                explode(',', $variants)
            );
        }

        return $variants;
    }

}
