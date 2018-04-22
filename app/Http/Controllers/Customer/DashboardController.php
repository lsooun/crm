<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\UserController;
use App\Models\Company;
use App\Models\Customer;
use App\Models\InvoicePayment;
use App\Repositories\ContractRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\LeadRepository;
use App\Repositories\OpportunityRepository;
use App\Repositories\OptionRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\SalesOrderRepository;
use Carbon\Carbon;
use App\Http\Requests;
use Sentinel;

class DashboardController extends UserController
{
    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;
    /**
     * @var QuotationRepository
     */
    private $quotationRepository;
    /**
     * @var SalesOrderRepository
     */
    private $salesOrderRepository;
    /**
     * @var ContractRepository
     */
    private $contractRepository;
    /**
     * @var OpportunityRepository
     */
    private $opportunityRepository;
    /**
     * @var LeadRepository
     */
    private $leadRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * DashboardController constructor.
     * @param InvoiceRepository $invoiceRepository
     * @param QuotationRepository $quotationRepository
     * @param SalesOrderRepository $salesOrderRepository
     * @param ContractRepository $contractRepository
     * @param OpportunityRepository $opportunityRepository
     * @param LeadRepository $leadRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(InvoiceRepository $invoiceRepository, QuotationRepository $quotationRepository,
                                SalesOrderRepository $salesOrderRepository,
                                ContractRepository $contractRepository, OpportunityRepository $opportunityRepository,
                                LeadRepository $leadRepository, OptionRepository $optionRepository)
    {
        parent::__construct();

        $this->invoiceRepository = $invoiceRepository;
        $this->quotationRepository = $quotationRepository;
        $this->salesOrderRepository = $salesOrderRepository;
        $this->contractRepository = $contractRepository;
        $this->opportunityRepository = $opportunityRepository;
        $this->leadRepository = $leadRepository;
        $this->optionRepository = $optionRepository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = Company::where('main_contact_person', $this->user->id)->get();
		$customer = Customer::where('user_id', $this->user->id)->first();
        $data = array();
        for($i=11;$i>=0;$i--)
        {
            $data[] =
                [
                    'month' =>Carbon::now()->subMonth($i)->format('M'),
                    'year' =>Carbon::now()->subMonth($i)->format('Y'),
                    'invoices_unpaid'=>$this->invoiceRepository->getAllForCustomer($this->user->id)->where('status','Overdue Invoice')->where('created_at','LIKE',
                        Carbon::now()->subMonth($i)->format('Y-m').'%')->sum('unpaid_amount'),
                    'contracts'=>$this->contractRepository->getAllForCustomer($companies)->where('created_at','LIKE',
                        Carbon::now()->subMonth($i)->format('Y-m').'%')->count(),
                    'opportunity'=>$this->opportunityRepository->getAllForCustomer($this->user->id)->where('created_at','LIKE',
                        Carbon::now()->subMonth($i)->format('Y-m').'%')->count(),
                    'leads'=>$this->leadRepository->getAllForCustomer($this->user->id)->where('created_at','LIKE',
                        Carbon::now()->subMonth($i)->format('Y-m').'%')->count()
                ];
        }

        $idx = 0;
        $stages = $this->optionRepository->getAll()
            ->where('category', 'stages')
            ->get()
            ->map(function ($title, $idx= 0) {
                $colors = array('#4fc1e9','#a0d468','#37bc9b','#ffcc66','#fd9883','#c2185b','#00796b','#7b1fa2','#3f51b5','#00796b','#607d8b','#00b0ff');
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                    'color' => isset($colors[$idx])?$colors[$idx]:"",
                    'opportunities' => $this->opportunityRepository->getAllForCustomer($this->user->id)->where('stages',$title->title)->count(),
                ];
                $idx++;
            });
        return view('customers.index', compact('data','stages'));

    }
}
