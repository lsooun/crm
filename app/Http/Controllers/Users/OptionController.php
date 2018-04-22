<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Http\Requests\OptionRequest;
use App\Models\Option;
use App\Repositories\OptionRepository;
use Sentinel;
use Yajra\Datatables\Facades\Datatables;

class OptionController extends UserController {
  private $categories;
  /**
   * @var OptionRepository
   */
  private $optionRepository;

  /**
   * OptionController constructor.
   * @param OptionRepository $optionRepository
   */
  public function __construct(OptionRepository $optionRepository) {
    parent::__construct();

    $this->categories = [
      'priority' => trans('option.priority'),
      'titles' => trans('option.titles'),
      'payment_methods' => trans('option.payment_methods'),
      'invoice_status' => trans('option.invoice_status'),
      'privacy' => trans('option.privacy'),
      'show_times' => trans('option.show_times'),
      'stages' => trans('option.stages'),
      'lost_reason' => trans('option.lost_reason'),
      'interval' => trans('option.interval'),
      'currency' => trans('option.currency'),
      'product_type' => trans('option.product_type'),
//            'quotation_status' => 'Quotation status',
      'product_status' => trans('option.product_status'),
//            'sales_order_status' => 'Sales order status'
    ];


    view()->share('type', 'option');
    $this->optionRepository = $optionRepository;
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index() {
    $title = trans('option.options');

    $this->generateParams();

    return View('user.option.index', compact('title'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create() {
    $title = trans('option.create');

    $this->generateParams();

    return view('user.option.create', compact('title'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(OptionRequest $request) {
    Option::create($request->all());
    return redirect("option");
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param Option $option
   * @return \Illuminate\Http\Response
   * @internal param int $id
   */
  public function edit(Option $option) {
    $title = trans('option.edit');

    $this->generateParams();

    return view('user.option.edit', compact('title', 'option'));
  }

  /**
   * Update the specified resource in storage.
   *
   */
  public function update(OptionRequest $request, Option $option) {
    $option->update($request->all());

    return redirect("option");
  }

  public function show(Option $option) {
    $action = "show";
    $title = trans('option.show');
    return view('user.option.show', compact('title', 'option', 'action'));
  }

  public function delete(Option $option) {
    $title = trans('option.delete');
    return view('user.option.delete', compact('title', 'option'));
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  Option $option
   * @return \Illuminate\Http\Response
   */
  public function destroy(Option $option) {
    $option->delete();
    return redirect('option');
  }

  /**
   * Get ajax datatables data
   *
   */
  public function data($category = '__', Datatables $datatables) {
    $options = $this->optionRepository->getAll();

    if ($category != "__") {
      $options = $options->where('category', $category);
    }
    $options = $options->get()
      ->map(function ($option) {
        return [
          "id" => $option->id,
          "category" => trans('option.' . $option->category),
          "title" => $option->title,
          "value" => $option->value,
        ];
      });

    return Datatables::of($options)
      ->addColumn('actions', '<a href="{{ url(\'option/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning"></i>  </a>
                                     <a href="{{ url(\'option/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}">
                                            <i class="fa fa-fw fa-eye text-primary"></i></a>
                                     <a href="{{ url(\'option/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>')
      ->removeColumn('id')
      ->escapeColumns(['actions'])->make();
  }


  private function generateParams() {
    view()->share('categories', $this->categories);
  }
}
