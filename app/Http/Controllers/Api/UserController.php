<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Mail\StaffInvite;
use App\Models\Call;
use App\Models\Category;
use App\Models\City;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Email;
use App\Models\EmailTemplate;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\InvoiceReceivePayment;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\Opportunity;
use App\Models\Option;
use App\Models\Product;
use App\Models\Qtemplate;
use App\Models\QtemplateProduct;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\Saleorder;
use App\Models\SaleorderProduct;
use App\Models\Salesteam;
use App\Models\State;
use App\Models\Task;
use App\Models\User;
use App\Repositories\CompanyRepository;
use App\Repositories\EmailTemplateRepository;
use App\Repositories\InviteUserRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\LeadRepository;
use App\Repositories\OpportunityRepository;
use App\Repositories\OptionRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ContractRepository;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use Efriandika\LaravelSettings\Facades\Settings;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Validator;
use JWTAuth;
use DB;
use Jenssegers\Date\Date;

use App\Repositories\UserRepository;

/**
 * User and staff endpoints, can be accessed only with role "user" or "staff"
 *
 * @Resource("User", uri="/user")
 */
class UserController extends Controller {
  use Helpers;

  private $user;

  /**
   * @var UserRepository
   */
  private $userRepository;
  private $invoiceRepository;
  /**
   * @var CompanyRepository
   */
  private $companyRepository;
  /**
   * @var ContractRepository
   */
  private $contractRepository;
  /**
   * @var OpportunityRepository
   */
  private $opportunityRepository;
  /**
   * @var ProductRepository
   */
  private $productRepository;
  /**
   * @var LeadRepository
   */
  private $leadRepository;
  /**
   * @var OptionRepository
   */
  private $optionRepository;
  /**
   * @var EmailTemplateRepository
   */
  private $emailTemplateRepository;
  /**
   * @var InviteUserRepository
   */
  private $inviteUserRepository;

  /**
   * UserController constructor.
   *
   * @param UserRepository $userRepository
   * @param InvoiceRepository $invoiceRepository
   * @param CompanyRepository $companyRepository
   * @param ContractRepository $contractRepository
   * @param OpportunityRepository $opportunityRepository
   * @param ProductRepository $productRepository
   * @param LeadRepository $leadRepository
   * @param OptionRepository $optionRepository
   * @param EmailTemplateRepository $emailTemplateRepository
   * @param InviteUserRepository $inviteUserRepository
   */
  public function __construct(UserRepository $userRepository, InvoiceRepository $invoiceRepository,
                              CompanyRepository $companyRepository, ContractRepository $contractRepository,
                              OpportunityRepository $opportunityRepository, ProductRepository $productRepository,
                              LeadRepository $leadRepository, OptionRepository $optionRepository,
                              EmailTemplateRepository $emailTemplateRepository, InviteUserRepository $inviteUserRepository) {
    $this->userRepository = $userRepository;
    $this->invoiceRepository = $invoiceRepository;
    $this->companyRepository = $companyRepository;
    $this->contractRepository = $contractRepository;
    $this->opportunityRepository = $opportunityRepository;
    $this->productRepository = $productRepository;
    $this->leadRepository = $leadRepository;
    $this->optionRepository = $optionRepository;
    $this->emailTemplateRepository = $emailTemplateRepository;
    $this->inviteUserRepository = $inviteUserRepository;
  }

  private $events = [];

  /**
   * Get all calendar items
   *
   * @Get("/calendar")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "salesteam": {
  {
  "id": 1,
  "title": "Name of team",
  "start_date": "2016-12-12",
  "end_date": "2016-12-12",
  "all_day": true,
  "type": "quotation",
  }
  }
  }),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function calendar() {
    $date = strtotime(date(Settings::get('date_format')));
    $this->user = JWTAuth::parseToken()->authenticate();

    $quotations = Quotation::whereHas('user', function ($q) {
      $q->where(function ($query) {
        $query
          ->orWhere('id', $this->user->parent->id)
          ->orWhere('users.user_id', $this->user->parent->id);
      });
    })->where('exp_date', '>', $date)
      ->with('user', 'customer')
      ->get()
      ->map(function ($quotation) {
        return [
          'id' => $quotation->id,
          'title' => $quotation->quotations_number,
          'start_date' => $quotation->exp_date,
          'end_date' => $quotation->exp_date,
          'type' => 'quotation'
        ];
      });
    $this->add_events_to_list($quotations);

    $meetings = Meeting::whereHas('user', function ($q) {
      $q->where(function ($query) {
        $query
          ->orWhere('id', $this->user->parent->id)
          ->orWhere('users.user_id', $this->user->parent->id);
      });
    })->where('starting_date', '>', $date)
      ->with('responsible')
      ->latest()->get()->map(function ($meeting) {
        return [
          'id' => $meeting->id,
          'title' => $meeting->meeting_subject,
          'start_date' => $meeting->starting_date,
          'end_date' => $meeting->ending_date,
          'type' => 'meeting'
        ];
      });
    $this->add_events_to_list($meetings);

    $invoices = Invoice::whereHas('user', function ($q) {
      $q->where(function ($query) {
        $query
          ->orWhere('id', $this->user->parent->id)
          ->orWhere('users.user_id', $this->user->parent->id);
      });
    })->where('due_date', '>', $date)
      ->get()
      ->map(function ($invoice) {
        return [
          'id' => $invoice->id,
          'title' => $invoice->invoice_number,
          'start_date' => $invoice->invoice_date,
          'end_date' => $invoice->invoice_date,
          'type' => 'invoice'
        ];
      });
    $this->add_events_to_list($invoices);

    $contracts = Contract::whereHas('user', function ($q) {
      $q->where(function ($query) {
        $query
          ->orWhere('id', $this->user->parent->id)
          ->orWhere('users.user_id', $this->user->parent->id);
      });
    })->where('end_date', '>', $date)
      ->with('company', 'user')
      ->get()
      ->map(function ($contract) {
        return [
          'id' => $contract->id,
          'title' => $contract->description,
          'start_date' => $contract->start_date,
          'end_date' => $contract->end_date,
          'type' => 'contract'
        ];
      });
    $this->add_events_to_list($contracts);

    $opportunities = Opportunity::whereHas('user', function ($q) {
      $q->where(function ($query) {
        $query
          ->orWhere('id', $this->user->parent->id)
          ->orWhere('users.user_id', $this->user->parent->id);
      });
    })->where('next_action', '>', $date)
      ->with('salesteam', 'calls', 'meetings')
      ->get()
      ->map(function ($opportunity) {
        return [
          'id' => $opportunity->id,
          'title' => $opportunity->opportunity,
          'start_date' => $opportunity->next_action,
          'end_date' => $opportunity->expected_closing,
          'type' => 'opportunity'
        ];
      });
    $this->add_events_to_list($opportunities);

    return response()->json(['events' => $this->events], 200);
  }

  // Get all countries
  public function countries() {
    $countries = Country::orderBy("name", "asc")->pluck('name', 'id');

    return response()->json(['countries' => $countries], 200);
  }

  // Get particular states of given country
  public function states(Request $request) {
    $states = State::where('country_id', $request->id)->orderBy("name", "asc")->pluck('name', 'id');

    return response()->json(['states' => $states], 200);
  }

  // Get particular cities of given state
  public function cities(Request $request) {
    $cities = City::where('state_id', $request->id)->orderBy("name", "asc")->pluck('name', 'id');

    return response()->json(['cities' => $cities], 200);

  }

  /**
   * Get settings
   *
   * @Get("/settings")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "settings": {
  "site_name": "zxiaoke.cn",
  "site_logo": "591d9416b9e91.jpg",
  "site_email": "admin@mw.life",
  "allowed_extensions": "gif,jpg,jpeg,png,pdf,txt",
  "backup_type": "local",
  "email_driver": "mail",
  "minimum_characters": "",
  "date_format": "m/d/Y",
  "time_format": "g:i A",
  "currency": "USD",
  "email_host": "",
  "email_port": "",
  "email_username": "",
  "email_password": "",
  "address1": "",
  "address2": "",
  "phone": "",
  "fax": "",
  "currency_position": "right",
  "max_upload_file_size": "1000",
  "sales_tax": "0",
  "payment_term1": "1",
  "payment_term2": "2",
  "payment_term3": "3",
  "opportunities_reminder_days": "10",
  "contract_renewal_days": "10",
  "invoice_reminder_days": "123",
  "quotation_prefix": "q_",
  "quotation_start_number": "1",
  "quotation_template": "quotation_red_green",
  "sales_prefix": "s_",
  "sales_start_number": "2",
  "saleorder_template": "saleorder_red_green",
  "invoice_prefix": "i",
  "invoice_start_number": "3",
  "invoice_template": "invoice_red_green",
  "invoice_payment_prefix": "",
  "invoice_payment_start_number": "",
  "pusher_app_id": "",
  "pusher_key": "",
  "pusher_secret": "",
  "paypal_username": "",
  "paypal_password": "",
  "paypal_signature": "",
  "stripe_secret": "",
  "stripe_publishable": "",
  "pdf_logo": "logo_1492175789.png",
  "jquery_date": "MM/DD/GGGG",
  "jquery_date_time": "MM/DD/GGGG h:mm A",
  },
  "logo" : "http://zxiaoke.cn/image.jpg",
  "pdf_logo" : "http://zxiaoke.cn/image.jpg",
  "max_upload_file_size": {
  "1000": "1MB",
  "2000": "2MB",
  "3000": "3MB",
  "4000": "4MB",
  "5000": "5MB",
  "6000": "6MB",
  "7000": "7MB",
  "8000": "8MB",
  "9000": "9MB",
  "10000": "10MB"
  },
  "currency": {
  "USD": "USD",
  "EUR": "EUR"
  },
  "backup_type": {
  {
  "text": "Local",
  "id": "local"
  },
  {
  "text": "Dropbox",
  "id": "dropbox"
  },
  {
  "text": "Amazon S3",
  "id": "s3"
  }
  }
  }),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function settings() {
    $max_upload_file_size = array(
      '1000' => '1MB',
      '2000' => '2MB',
      '3000' => '3MB',
      '4000' => '4MB',
      '5000' => '5MB',
      '6000' => '6MB',
      '7000' => '7MB',
      '8000' => '8MB',
      '9000' => '9MB',
      '10000' => '10MB',
    );

    $currency = Option::where('category', 'currency')
      ->get()
      ->map(
        function ($title) {
          return [
            'text' => $title->title,
            'id' => $title->value,
          ];
        }
      )->pluck('text', 'id');

    $backup_type = Option::where('category', 'backup_type')
      ->get()
      ->map(
        function ($title) {
          return [
            'text' => $title->value,
            'id' => $title->title,
          ];
        }
      );
    $settings = Settings::getAll();
    unset($settings['logo']);
    unset($settings['token']);
    return response()->json(['settings' => $settings,
      'max_upload_file_size' => $max_upload_file_size,
      'currency' => $currency,
      'logo' => asset('uploads/site/' . Settings::get('site_logo')),
      'pdf_logo' => asset('uploads/site/' . Settings::get('pdf_logo')),
      'backup_type' => $backup_type], 200);
  }

  /**
   * Post call
   *
   * @Post("/update_settings")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "logo":"base64:image", "pdf_logo":"base64:image","date_format_custom": "d-m-Y", "time_format_custom":"H:m","site_name":"CRM","address1":"Address1","address2":"Address2","site_email":"email@email.com", "currency":"USD"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function updateSettings(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    if (!is_null($request->logo)) {
      $output_file = str_random(10) . ".jpg";
      $ifp = fopen(public_path() . '/uploads/site/' . $output_file, "wb");
      fwrite($ifp, base64_decode($request->logo));
      fclose($ifp);
      $request->merge(['site_logo' => $output_file]);
    }
    if (!is_null($request->pdf_logo)) {
      $output_file = str_random(10) . ".jpg";
      $ifp = fopen(public_path() . '/uploads/site/' . $output_file, "wb");
      fwrite($ifp, base64_decode($request->pdf_logo));
      fclose($ifp);
      $request->merge(['pdf_logo' => $output_file]);
    }

    Settings::set('modules', []);
    $request->date_format = $request->date_format_custom;
    $request->time_format = $request->time_format_custom;
    if ($request->date_format == "") {
      $request->date_format = 'd.m.Y';
    }
    if ($request->time_format == "") {
      $request->time_format = 'H:i';
    }
    $request->merge([
      'jquery_date' => $this->dateformat_PHP_to_jQueryUI($request->date_format),
      'jquery_date_time' => $this->dateformat_PHP_to_jQueryUI($request->date_format . ' ' . $request->time_format),
    ]);

    foreach ($request->except('_token', 'site_logo_file', 'pdf_logo_file', 'date_format_custom', 'time_format_custom', 'pages') as $key => $value) {
      Settings::set($key, $value);
    }
    return response()->json(['success' => 'success'], 200);
  }

  function dateformat_PHP_to_jQueryUI($php_format) {
    $SYMBOLS_MATCHING = array(
      // Day
      'd' => 'DD',
      'D' => 'ddd',
      'j' => 'D',
      'l' => 'dddd',
      'N' => 'do',
      'S' => 'do',
      'w' => 'd',
      'z' => 'DDD',
      // Week
      'W' => 'w',
      // Month
      'F' => 'MMMM',
      'm' => 'MM',
      'M' => 'MMM',
      'n' => 'M',
      't' => '',
      // Year
      'L' => '',
      'o' => '',
      'Y' => 'GGGG',
      'y' => 'GG',
      // Time
      'a' => 'a',
      'A' => 'A',
      'B' => '',
      'g' => 'h',
      'G' => 'H',
      'h' => 'hh',
      'H' => 'HH',
      'i' => 'mm',
      's' => 'ss',
      'u' => ''
    );


    $jqueryui_format = "";
    $escaping = false;
    for ($i = 0; $i < strlen($php_format); $i++) {
      $char = $php_format[$i];
      if ($char === '\\') // PHP date format escaping character
      {
        $i++;
        if ($escaping) $jqueryui_format .= $php_format[$i];
        else $jqueryui_format .= '\'' . $php_format[$i];
        $escaping = true;
      } else {
        if ($escaping) {
          $jqueryui_format .= "'";
          $escaping = false;
        }
        if (isset($SYMBOLS_MATCHING[$char]))
          $jqueryui_format .= $SYMBOLS_MATCHING[$char];
        else
          $jqueryui_format .= $char;
      }
    }
    return $jqueryui_format;
  }

  /**
   * @param $events_data
   */
  private function add_events_to_list($events_data) {
    foreach ($events_data as $d) {
      $event = [];
      $start_date = date(Settings::get('date_format'), (is_numeric($d['start_date']) ? $d['start_date'] : strtotime($d['start_date'])));
      $end_date = date(Settings::get('date_format'), (is_numeric($d['end_date']) ? $d['end_date'] : strtotime($d['end_date'])));
      $event['title'] = $d['title'];
      $event['id'] = $d['id'];
      $event['start'] = $start_date;
      $event['end'] = $end_date;
      $event['allDay'] = true;
      $event['type'] = $d['type'];
      array_push($this->events, $event);
    }
  }


  /**
   * Get all calls
   *
   * @Get("/calls")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "calls": {
  {
  "id": 1,
  "date": "2015-10-15",
  "call_summary": "Call summary",
  "company": "Company",
  "user": "User",
  }
  }
  }),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function calls() {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('logged_calls.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $calls = Call::whereHas('user', function ($q) {
      $q->where(function ($query) {
        $query
          ->orWhere('id', $this->user->parent->id)
          ->orWhere('users.user_id', $this->user->parent->id);
      });
    })
      ->with('user', 'company')
      ->get()
      ->map(function ($call) {
        return [
          'id' => $call->id,
          'date' => $call->date,
          'call_summary' => $call->call_summary,
          'company' => $call->company->name,
          'user' => $call->user->full_name,
        ];
      });

    return response()->json(['calls' => $calls], 200);
  }

  /**
   * Get call item
   *
   * @Get("/call")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "call_id":"1"}),
   *       @Response(200, body={"call": {
  "id" : 1,
  "date": "2015-10-15",
  "call_summary": "Call summary",
  "duration" :"30",
  "company": "Company",
  "resp_staff": "User",
  "user_id" : 1,
  "created_at" : "2015-12-22 20:17:20",
  "updated_at" : "2015-12-22 20:19:11",
  "deleted_at" : null
  }}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function call(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('logged_calls.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'call_id' => $request->input('call_id'),
    );
    $rules = array(
      'call_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $call = Call::find($request->call_id)->with('company', 'responsible')
        ->get()
        ->map(function ($call) {
          return [
            'id' => $call->id,
            'date' => $call->date,
            'call_summary' => $call->call_summary,
            'duration' => $call->duration,
            'company' => isset($call->company) ? $call->company->name : "",
            'resp_staff' => $call->responsible->full_name,
          ];
        });
      return response()->json(['call' => $call], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Post call
   *
   * @Post("/post_call")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "date":"2015-10-11", "call_summary":"call summary","duration": "30", "company_id":"1","resp_staff_id":"12"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function postCall(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('logged_calls.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'date' => $request->input('date'),
      'call_summary' => $request->input('call_summary'),
      'duration' => $request->input('duration'),
      'company_id' => $request->input('company_id'),
      'resp_staff_id' => $request->input('resp_staff_id'),
    );
    $rules = array(
      'date' => 'required|date_format:"' . Settings::get('date_format') . '"',
      'call_summary' => 'required',
      'duration' => 'required',
      'company_id' => 'required',
      'resp_staff_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $this->user->calls()->create($request->except('token'));

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit call
   *
   * @Post("/edit_call")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "call_id":"1","date":"2015-10-11", "call_summary":"call summary","company_id":"1","resp_staff_id":"12"}),
   *       @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function editCall(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('logged_calls.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'call_id' => $request->input('call_id'),
      'date' => $request->input('date'),
      'call_summary' => $request->input('call_summary'),
      'company_id' => $request->input('company_id'),
      'resp_staff_id' => $request->input('resp_staff_id'),
    );
    $rules = array(
      'call_id' => 'required',
      'date' => 'required|date_format:"' . Settings::get('date_format') . '"',
      'call_summary' => 'required',
      'company_id' => 'required',
      'resp_staff_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $call = Call::find($request->call_id);
      $call->update($request->except('token', 'call_id'));

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete call
   *
   * @Post("/delete_call")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "call_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteCall(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('logged_calls.delete')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'call_id' => $request->input('call_id'),
    );
    $rules = array(
      'call_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $call = Call::find($request->call_id);
      $call->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }


  /**
   * Get all categories
   *
   * @Get("/categories")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "category": {
  {
  "id": 1,
  "name": "Category name",
  }
  }
  }),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function categories() {
    $this->user = JWTAuth::parseToken()->authenticate();
    $categories = Category::whereHas('user', function ($q) {
      $q->where(function ($query) {
        $query
          ->orWhere('id', $this->user->parent->id)
          ->orWhere('users.user_id', $this->user->parent->id);
      });
    })
      ->get()
      ->map(function ($category) {
        return [
          'id' => $category->id,
          'name' => $category->name,
        ];
      });

    return response()->json(['categories' => $categories], 200);
  }

  /**
   * Get category item
   *
   * @Get("/category")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "category_id":"1"}),
   *       @Response(200, body={"category": {
  "id" : 1,
  "name" : "Category",
  "user_id" : 1,
  "created_at" : "2015-12-23 16:58:25",
  "updated_at" : "2015-12-23 16:58:25",
  "deleted_at" : null
  }}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function category(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'category_id' => $request->input('category_id'),
    );
    $rules = array(
      'category_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $category = Category::find($request->category_id);
      return response()->json(['category' => $category->toArray()], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Post category
   *
   * @Post("/post_category")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "name":"category name"}),
   *      @Response(200, body={"success":"success"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function postCategory(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'name' => $request->input('name'),
    );
    $rules = array(
      'name' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $this->user->categories()->create($request->except('token'));

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit category
   *
   * @Post("/edit_category")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "category_id":"1","name":"category name"}),
   *       @Response(200, body={"success":"success"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function editCategory(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'id' => $request->input('category_id'),
      'name' => $request->input('name'),
    );
    $rules = array(
      'id' => 'required',
      'name' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $category = Category::find($request->category_id);
      $category->update($request->except('token', 'category_id'));

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete category
   *
   * @Post("/delete_category")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "call_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteCategory(Request $request) {
    $data = array(
      'id' => $request->input('category_id'),
    );
    $rules = array(
      'id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $category = Category::find($request->category_id);
      $category->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }


  /**
   * Get all companies
   *
   * @Get("/companies")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "companies": {
  {
  "id": 1,
  "name": "Name",
  "customer": "customer name",
  "phone": "634654165456",
  }
  }
  }),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function companies() {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('contacts.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $companies = Company::whereHas('user', function ($q) {
      $q->where(function ($query) {
        $query
          ->orWhere('id', $this->user->parent->id)
          ->orWhere('users.user_id', $this->user->parent->id);
      });
    })->latest()->get()->map(function ($comp) {
      return [
        'id' => $comp->id,
        'name' => $comp->name,
        'customer' => isset($comp->user) ? $comp->user->full_name : "",
        'phone' => $comp->phone,
      ];
    });

    return response()->json(['companies' => $companies], 200);
  }

  /**
   * Get company item
   *
   * @Post("/company")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "company_id":"1"}),
   *       @Response(200, body={"company": {
  "id" : 2,
  "name" : "dg dfg",
  "email" : "user@mw.life",
  "password" : "",
  "lostpw" : "",
  "address" : "fdgdfg",
  "website" : "gfdgfdg",
  "phone" : "45454",
  "mobile" : "45",
  "fax" : "4545",
  "title" : "",
  "company_avatar" : "",
  "company_attachment" : "",
  "main_contact_person" : 3,
  "sales_team_id" : 1,
  "country_id" : 1,
  "state_id" : 43,
  "city_id" : 5914,
  "longitude" : "63.30929400000002",
  "latitude" : "35.6403478",
  "user_id" : 1,
  "created_at" : "2015-12-26 07:10:25",
  "updated_at" : "2015-12-26 07:10:25",
  "deleted_at" : null
  }}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function company(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('contacts.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'company_id' => $request->input('company_id'),
    );
    $rules = array(
      'company_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $company = Company::find($request->company_id)
        ->with('contactPerson', 'salesTeam')
        ->get()
        ->map(function ($company) {
          return [
            'id' => $company->id,
            'name' => $company->name,
            'email' => $company->email,
            'address' => $company->address,
            'website' => $company->website,
            'phone' => $company->phone,
            'mobile' => $company->mobile,
            'fax' => $company->fax,
            'title' => $company->title,
            'company_avatar' => $company->company_avatar,
            'main_contact_person' => isset($company->contactPerson) ? $company->contactPerson->full_name : "",
            'sales_team' => isset($company->salesTeam) ? $company->salesTeam->salesteam : "",
            'country_id' => $company->country_id,
            'state_id' => $company->state_id,
            'city_id' => $company->city_id,
            'avatar' => $company->avatar,
          ];
        });
      return response()->json(['company' => $company], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Post company
   *
   * @Post("/post_company")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "name":"Company name","email":"email@email.com","address":"first street,NY",
   *     "sales_team_id":"1","main_contact_person":"1","phone":"123132214","avatar":"base64_encoded_image" }),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function postCompany(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('contacts.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'name' => $request->input('name'),
      'email' => $request->input('email'),
      'address' => $request->input('address'),
      'sales_team_id' => $request->input('sales_team_id'),
      'main_contact_person' => $request->input('main_contact_person'),
      'phone' => $request->input('phone'),
    );
    $rules = array(
      'name' => 'required|min:1|max:50',
      'email' => 'required|email',
      'address' => 'required',
      'sales_team_id' => 'required',
      'main_contact_person' => 'required',
      'phone' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $company = $this->user->companies()->create($request->except('token', 'avatar'));
      if (!is_null($request->avatar)) {
        $output_file = str_random(10) . ".jpg";
        $ifp = fopen(public_path() . '/uploads/company/' . $output_file, "wb");
        fwrite($ifp, base64_decode($request->avatar));
        fclose($ifp);
        $company->company_avatar = $output_file;
        $company->save();
      }
      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit company
   *
   * @Post("/edit_company")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "company_id":"1","name":"Company name","email":"email@email.com", "avatar":"base64_encoded_image"}),
   *       @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function editCompany(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('contacts.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'company_id' => $request->input('company_id'),
      'name' => $request->input('name'),
      'email' => $request->input('email'),
      'address' => $request->input('address'),
      'sales_team_id' => $request->input('sales_team_id'),
      'main_contact_person' => $request->input('main_contact_person'),
      'phone' => $request->input('phone'),
    );
    $rules = array(
      'company_id' => 'required',
      'name' => 'required|min:1|max:50',
      'email' => 'required|email',
      'address' => 'required',
      'sales_team_id' => 'required',
      'main_contact_person' => 'required',
      'phone' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $company = Company::find($request->company_id);
      if (!is_null($request->avatar)) {
        $output_file = str_random(10) . ".jpg";
        $ifp = fopen(public_path() . '/uploads/company/' . $output_file, "wb");
        fwrite($ifp, base64_decode($request->avatar));
        fclose($ifp);
        $company->company_avatar = $output_file;
      }
      $company->update($request->except('token', 'company_id', 'avatar'));

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete company
   *
   * @Post("/delete_company")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "company_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteCompany(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('contacts.delete')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'company_id' => $request->input('company_id'),
    );
    $rules = array(
      'company_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $company = Company::find($request->company_id);
      $company->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }


  /**
   * Get all contracts
   *
   * @Get("/contracts")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "company": {
  {
  "id": 1,
  "start_date": "2015-11-12",
  "description": "Description",
  "name": "Company name",
  "user": "User name",
  }
  }
  }),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function contracts() {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('contracts.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $contracts = Contract::whereHas('user', function ($q) {
      $q->where(function ($query) {
        $query
          ->orWhere('id', $this->user->parent->id)
          ->orWhere('users.user_id', $this->user->parent->id);
      });
    })
      ->with('company', 'user')
      ->get()
      ->map(function ($contract) {
        return [
          'id' => $contract->id,
          'start_date' => $contract->start_date,
          'description' => $contract->description,
          'name' => isset($contract->company) ? $contract->company->name : "",
          'user' => isset($contract->user) ? $contract->user->full_name : ""
        ];
      });

    return response()->json(['contracts' => $contracts], 200);
  }

  /**
   * Get contract item
   *
   * @Get("/contract")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "contract_id":"1"}),
   *       @Response(200, body={"contract": {
  "id" : 1,
  "start_date" : "21.12.2015.",
  "end_date" : "23.12.2015.",
  "description" : "ffdgfdg",
  "company_id" : 1,
  "resp_staff_id" : 2,
  "real_signed_contract" : "",
  "user_id" : 1,
  "created_at" : "2015-12-22 20:27:37",
  "updated_at" : "2015-12-22 20:27:37",
  "deleted_at" : null
  }}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function contract(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('contracts.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'contract_id' => $request->input('contract_id'),
    );
    $rules = array(
      'contract_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $contract = Contract::find($request->contract_id)
        ->with('company', 'responsible')
        ->get()
        ->map(function ($contract) {
          return [
            'id' => $contract->id,
            'start_date' => $contract->start_date,
            'end_date' => $contract->end_date,
            'company' => isset($contract->company) ? $contract->company->name : "",
            'responsible' => isset($contract->responsible) ? $contract->responsible->full_name : "",
            'description' => $contract->description
          ];
        });

      return response()->json(['contract' => $contract], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Post contract
   *
   * @Post("/post_contract")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "start_date":"2015-11-11","end_date":"2015-11-11","description": "Description",
   *     "company_id":"1","resp_staff_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function postContract(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('contracts.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'start_date' => $request->input('start_date'),
      'end_date' => $request->input('end_date'),
      'description' => $request->input('description'),
      'company_id' => $request->input('company_id'),
      'resp_staff_id' => $request->input('resp_staff_id'),
    );
    $rules = array(
      'start_date' => 'required|date_format:"' . Settings::get('date_format') . '"',
      'end_date' => 'required|date_format:"' . Settings::get('date_format') . '"',
      'description' => 'required',
      'company_id' => 'required',
      'resp_staff_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $this->user->contracts()->create($request->except('token'));

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit contract
   *
   * @Post("/edit_contract")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "company_id":"1","name":"Company name","email":"email@email.com"}),
   *       @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function editContract(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('contracts.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'contract_id' => $request->input('contract_id'),
      'start_date' => $request->input('start_date'),
      'end_date' => $request->input('end_date'),
      'description' => $request->input('description'),
    );
    $rules = array(
      'contract_id' => 'required',
      'start_date' => 'required|date_format:"' . Settings::get('date_format') . '"',
      'end_date' => 'required|date_format:"' . Settings::get('date_format') . '"',
      'description' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $contract = Contract::find($request->contract_id);
      $contract->update($request->except('token', 'contract_id'));

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete contract
   *
   * @Post("/delete_contract")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "company_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteContract(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('contracts.delete')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'contract_id' => $request->input('contract_id'),
    );
    $rules = array(
      'contract_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $contract = Contract::find($request->contract_id);
      $contract->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }


  /**
   * Get all customers
   *
   * @Get("/customers")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "customers": {
  {
  "user_id": 1,
  "customer_id": 2,
  "full_name": "full name",
  "email": "email@email.com",
  "created_at": "2015--11-11",
  "avatar": "http://avatar.com/avatar.jpg"
  }
  }
  }),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function customers() {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('contacts.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $staffs = collect();
    $this->user->users()
      ->with('users.users')
      ->get()
      ->each(function ($user) use ($staffs) {
        foreach ($user->users as $u) {
          $staffs->push($u);
        }
      });

    $staffs = $staffs->filter(function ($user) {
      return $user->inRole('customer');
    });
    $customers = $staffs->map(function ($user) {
      return [
        'user_id' => $user->id,
        'customer_id' => $user->customer->id,
        'full_name' => $user->full_name,
        'email' => $user->email,
        'avatar' => $user->avatar,
        'created_at' => $user->created_at->format('Y-d-m')
      ];
    });
    return response()->json(['customers' => $customers], 200);
  }

  /**
   * Get customer item
   *
   * @Get("/customer")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "customer_id":"1"}),
   *       @Response(200, body={"contract": {
  "id" : 1,
  "user_id" : 3,
  "belong_user_id" : 2,
  "address" : "",
  "website" : "",
  "job_position" : "",
  "mobile" : "5456",
  "fax" : "",
  "title" : "",
  "company_id" : 0,
  "sales_team_id" : 0,
  "created_at" : "2015-12-22 19:26:19",
  "avatar": "http://avatar.com/avatar.jpg"
  }}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function customer(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('contacts.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'customer_id' => $request->input('customer_id'),
    );
    $rules = array(
      'customer_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $customer = Customer::find($request->customer_id)
        ->with('salesTeam', 'company')
        ->get()
        ->map(function ($customer) {
          return [
            'id' => $customer->id,
            'belong_user_id' => $customer->belong_user_id,
            'user_id' => $customer->user_id,
            'full_name' => $customer->user->full_name,
            'email' => $customer->user->email,
            'website' => $customer->website,
            'mobile' => $customer->mobile,
            'company' => isset($customer->company) ? $customer->company->name : "",
            'salesteam' => isset($customer->salesTeam) ? $customer->salesTeam->salesteam : "",
            'address' => $customer->address,
            'job_position' => $customer->job_position,
            'title' => $customer->title,
            'avatar' => $customer->avatar,
            'created_at' => $customer->created_at->format('Y-d-m H:i:s')
          ];
        });
      return response()->json(['customer' => $customer], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Post customer
   *
   * @Post("/post_customer")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "first_name":"first name", "last_name":"last name","email":"email@email.com","password":"password","password_confirmation":"password","phone_number":"+54212425", "sales_team_id":1, "company_id":1,"address": "address", "job_position":"developer", "mobile" :" +545231","fax" : "+2314521", "title" : "mr"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function postCustomer(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('contacts.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'first_name' => $request->input('first_name'),
      'last_name' => $request->input('last_name'),
      'email' => $request->input('email'),
      'password' => $request->input('password'),
      'password_confirmation' => $request->input('password_confirmation'),
      'phone_number' => $request->input('phone_number'),
      'sales_team_id' => $request->input('sales_team_id'),
      'company_id' => $request->input('company_id'),
      'address' => $request->input('address'),
      'job_position' => $request->input('job_position'),
      'mobile' => $request->input('mobile'),
      'fax' => $request->input('fax'),
      'title' => $request->input('title'),
    );
    $rules = array(
      'first_name' => 'required|min:1|max:50',
      'last_name' => 'min:1|max:50',
      'email' => 'required|email|unique:users,email',
      'password' => 'required|min:6|confirmed',
      'password_confirmation' => 'required|min:6',
      'phone_number' => 'required',
      'sales_team_id' => 'required',
      'company_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $user = Sentinel::registerAndActivate($request->only('first_name', 'last_name', 'phone_number', 'email', 'password'));

      $role = Sentinel::findRoleBySlug('customer');
      $role->users()->attach($user);

      $user = User::find($user->id);
      $user->phone_number = $request->phone_number;
      if (!is_null($request->avatar)) {
        $output_file = str_random(10) . ".jpg";
        $ifp = fopen(public_path() . '/uploads/avatar/' . $output_file, "wb");
        fwrite($ifp, base64_decode($request->avatar));
        fclose($ifp);
        $user->user_avatar = $output_file;
      }
      $user->save();

      $customer = new Customer($request->except('first_name', 'avatar', 'last_name', 'phone_number', 'email', 'password', 'user_avatar', 'password_confirmation'));
      $customer->user_id = $user->id;
      $customer->belong_user_id = $this->user->id;
      $customer->save();

      $currentUser = $this->user;
      $currentUser->users()->save($user);

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit customer
   *
   * @Post("/edit_customer")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "customer_id":"1","first_name":"first name", "last_name":"last name","email":"email@email.com","sales_team_id":1, "company_id":1,"address":"address", "phone_number":"+54212425", "address", "job_position":"developer", "mobile" :" +545231","fax" : "+2314521", "title" : "mr"}),
   *       @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function editCustomer(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('contacts.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'customer_id' => $request->input('customer_id'),
      'sales_team_id' => $request->input('sales_team_id'),
      'company_id' => $request->input('company_id'),
      'first_name' => $request->input('first_name'),
      'last_name' => $request->input('last_name'),
      'email' => $request->input('email'),
      'address' => $request->input('address'),
      'job_position' => $request->input('job_position'),
      'mobile' => $request->input('mobile'),
      'fax' => $request->input('fax'),
      'title' => $request->input('title'),
    );
    $rules = array(
      'customer_id' => 'required',
      'sales_team_id' => 'required',
      'company_id' => 'required',
      'first_name' => 'required|min:1|max:50',
      'last_name' => 'min:1|max:50',
      'email' => 'required|email|unique:users,email',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $customer = Customer::find($request->customer_id);
      $customer->update($request->except('first_name', 'last_name', 'avatar', 'phone_number', 'email', 'password', 'user_avatar', 'password_confirmation'));

      $user = User::find($customer->user->id);
      if (!is_null($request->avatar)) {
        $output_file = str_random(10) . ".jpg";
        $ifp = fopen(public_path() . '/uploads/avatar/' . $output_file, "wb");
        fwrite($ifp, base64_decode($request->avatar));
        fclose($ifp);
        $user->user_avatar = $output_file;
      }
      $user->update($request->only('first_name', 'last_name', 'phone_number', 'email', 'password'));
      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete customer
   *
   * @Post("/delete_customer")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "customer_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteCustomer(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('contacts.delete')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'customer_id' => $request->input('customer_id'),
    );
    $rules = array(
      'customer_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $customer = Customer::find($request->customer_id);
      $user = User::find($customer->user->id);
      $user->delete();
      $customer->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }


  /**
   * Get all invoices
   *
   * @Get("/invoices")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "invoices": {
  {
  "id": 1,
  "invoice_number": "1465456",
  "invoice_date": "2015-11-11",
  "customer": "Customer Name",
  "unpaid_amount": "15.2",
  "status": "Status",
  "due_date": "2015-11-11",
  }
  },
  "month_overdue": 1,
  "month_paid": 5,
  "month_open": 3
  }),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function invoices() {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('invoices.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $invoices = Invoice::with('customer')->whereHas('user', function ($q) {
      $q->where(function ($query) {
        $query
          ->orWhere('id', $this->user->parent->id)
          ->orWhere('users.user_id', $this->user->parent->id);
      });
    })->get()
      ->map(function ($invoice) {
        return [
          'id' => $invoice->id,
          'invoice_number' => $invoice->invoice_number,
          'invoice_date' => $invoice->invoice_date,
          'customer' => isset($invoice->customer) ? $invoice->customer->full_name : "",
          'unpaid_amount' => $invoice->unpaid_amount,
          'status' => $invoice->status,
          'due_date' => $invoice->due_date,
        ];
      });
    $month_overdue = round($this->invoiceRepository->getAllOverdueMonth()->sum('unpaid_amount'), 3);
    $month_paid = round($this->invoiceRepository->getAllPaidMonth()->sum('final_price'), 3);
    $month_open = round($this->invoiceRepository->getAllOpenMonth()->sum('final_price'), 3);

    return response()->json(['invoices' => $invoices,
      'month_overdue' => $month_overdue,
      'month_paid' => $month_paid,
      'month_open' => $month_open], 200);
  }

  /**
   * Get invoice item
   *
   * @Get("/invoice")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "invoice_id":"1"}),
   *       @Response(200, body={"invoice": {
  "id" : 1,
  "order_id" : 0,
  "customer_id" : 3,
  "sales_person_id" : "2",
  "sales_team_id" : 1,
  "invoice_number" : "I0001",
  "invoice_date" : "08.12.2015. 00:00",
  "due_date" : "24.12.2015. 00:00",
  "payment_term" : "10",
  "status" : "Open Invoice",
  "total" : 1221.0,
  "tax_amount" : 195.36,
  "grand_total" : 1416.36,
  "discount" : 10,
  "final_price" : 1216.36,
  "unpaid_amount" : 1173.06,
  "user_id" : 1,
  "created_at" : "2015-12-23 18:05:35",
  "updated_at" : "2015-12-28 19:21:48",
  "deleted_at" : null,
  },"products": {
  "product" : "product",
  "description" : "description",
  "quantity" : 3,
  "unit_price" : 1.95,
  "taxes" : 1.55,
  "subtotal" : 195.36,
  }}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function invoice(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('invoices.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'invoice_id' => $request->input('invoice_id'),
    );
    $rules = array(
      'invoice_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $invoice = Invoice::find($request->get('invoice_id'))
        ->with('salesPerson', 'customer', 'salesTeam')
        ->get()
        ->map(function ($invoice) {
          return [
            'id' => $invoice->id,
            'order_id' => $invoice->order_id,
            'customer' => isset($invoice->customer) ? $invoice->customer->name : "",
            'sales_person' => isset($invoice->salesPerson) ? $invoice->salesPerson->full_name : "",
            'salesteam' => isset($invoice->salesTeam) ? $invoice->salesTeam->salesteam : "",
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->invoice_date,
            'due_date' => $invoice->due_date,
            'payment_term' => $invoice->payment_term,
            'status' => $invoice->status,
            'total' => $invoice->total,
            'tax_amount' => $invoice->tax_amount,
            'grand_total' => $invoice->grand_total,
            'discount' => $invoice->discount,
            'final_price' => $invoice->final_price,
            'unpaid_amount' => $invoice->unpaid_amount
          ];
        });
      $products = array();
      $invoiceNew = Invoice::find($request->get('invoice_id'));
      if ($invoiceNew->products->count() > 0) {
        foreach ($invoiceNew->products as $index => $variants) {
          $products[] = ['product' => $variants->product_name,
            'description' => $variants->description,
            'quantity' => $variants->quantity,
            'unit_price' => $variants->price,
            'taxes' => number_format($variants->quantity * $variants->price * floatval(Settings::get('sales_tax')) / 100, 2,
              '.', ''),
            'subtotal' => $variants->sub_total];
        }
      }
      return response()->json(['invoice' => $invoice, 'products' => $products], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Post invoice
   *
   * @Post("/post_invoice")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "customer_id":"5", "invoice_date":"2015-11-11","sales_person_id":"2","status":"status","total":"10.00","tax_amount":"01.10","grand_total":"11.10","discount":1.2,"final_price":9.85,"invoice_prefix":"I00","invoice_start_number":"0"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function postInvoice(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('invoices.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'customer_id' => $request->input('customer_id'),
      'invoice_date' => $request->input('invoice_date'),
      'due_date' => $request->input('due_date'),
      'sales_team_id' => $request->input('sales_team_id'),
      'sales_person_id' => $request->input('sales_person_id'),
      'status' => $request->input('status'),
      'grand_total' => $request->input('grand_total'),
      'tax_amount' => $request->input('tax_amount'),
      'discount' => $request->input('discount'),
      'final_price' => $request->input('final_price'),
      'total' => $request->input('total'),
      'payment_term' => $request->input('payment_term'),
      'invoice_prefix' => $request->input('invoice_prefix'),
      'invoice_start_number' => $request->input('invoice_start_number'),
    );
    $rules = array(
      'customer_id' => 'required',
      'invoice_date' => 'required',
      'due_date' => 'required',
      'sales_person_id' => 'required',
      'sales_team_id' => 'required',
      'status' => 'required',
      'grand_total' => 'required',
      'tax_amount' => 'required',
      'discount' => 'required',
      'final_price' => 'required',
      'total' => 'required',
      'payment_term' => "required",
      'invoice_start_number' => "required",
      'invoice_prefix' => "required"
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $total_fields = Invoice::whereNull('deleted_at')->orWhereNotNull('deleted_at')->orderBy('id', 'desc')->first();
      $invoice_no = $request->input('invoice_prefix') . ($request->input('invoice_start_number') + (isset($total_fields) ? $total_fields->id : 0) + 1);
      $exp_date = date(Settings::get('date_format'), strtotime(' + ' .
        isset($request->payment_term) ? $request->payment_term : Settings::get('invoice_reminder_days') . ' days'
        )
      );

      $invoice = new Invoice($request->only('customer_id', 'invoice_date', 'payment_term',
        'sales_person_id', 'sales_team_id', 'status', 'total',
        'tax_amount', 'grand_total', 'final_price', 'discount'));
      $invoice->invoice_number = $invoice_no;
      $invoice->unpaid_amount = $request->get('grand_total');
      $invoice->due_date = isset($request->due_date) ? $request->get('due_date') : strtotime($exp_date);
      $invoice->user_id = Sentinel::getUser()->id;
      $invoice->save();

      InvoiceProduct::where('invoice_id', $invoice->id)->delete();

      if (!empty($request->get('product_id'))) {
        foreach ($request->get('product_id') as $key => $item) {
          if ($item != "" && $request->get('product_name')[$key] != "" && $request->get('description')[$key] != "" &&
            $request->get('quantity')[$key] != "" && $request->get('price')[$key] != "" && $request->get('sub_total')[$key] != ""
          ) {
            $invoiceProduct = new InvoiceProduct();
            $invoiceProduct->invoice_id = $invoice->id;
            $invoiceProduct->product_id = $item;
            $invoiceProduct->product_name = $request->get('product_name')[$key];
            $invoiceProduct->description = $request->get('description')[$key];
            $invoiceProduct->quantity = $request->get('quantity')[$key];
            $invoiceProduct->price = $request->get('price')[$key];
            $invoiceProduct->sub_total = $request->get('sub_total')[$key];
            $invoiceProduct->save();
          }
        }
      }

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit invoice
   *
   * @Post("/edit_invoice")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "invoice_id":"1","customer_id":"5", "invoice_date":"2015-11-11","sales_person_id":"2","status":"status","total":"10.00","tax_total":"01.10","grand_total":"11.10","discount":"0.10","final_price":"9.10"}),
   *       @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function editInvoice(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('invoices.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'invoice_id' => $request->input('invoice_id'),
      'customer_id' => $request->input('customer_id'),
      'invoice_date' => $request->input('invoice_date'),
      'due_date' => $request->input('due_date'),
      'sales_person_id' => $request->input('sales_person_id'),
      'status' => $request->input('status'),
      'grand_total' => $request->input('grand_total'),
      'total' => $request->input('total'),
      'discount' => $request->input('discount'),
      'final_price' => $request->input('final_price'),
      'payment_term' => $request->input('payment_term'),
    );
    $rules = array(
      'invoice_id' => 'required',
      'customer_id' => 'required',
      'invoice_date' => 'required',
      'due_date' => 'required',
      'sales_person_id' => 'required',
      'status' => 'required',
      'grand_total' => 'required',
      'total' => 'required',
      'discount' => 'required',
      'final_price' => 'required',
      'payment_term' => "required",
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $invoice = Invoice::find($request->get('invoice_id'));

      $exp_date = date(Settings::get('date_format'), strtotime(' + ' . isset($request->payment_term) ? $request->payment_term : 0 . ' days'));

      $payments = InvoiceReceivePayment::where('invoice_id', $invoice->id);

      $invoice->unpaid_amount = $request->get('grand_total') - (($payments->count() > 0) ? $payments->sum('payment_received') : 0);
      $invoice->due_date = isset($request->due_date) ? $request->get('due_date') : strtotime($exp_date);
      $invoice->update($request->only('customer_id', 'invoice_date', 'payment_term',
        'sales_person_id', 'sales_team_id', 'status', 'total', 'final_price', 'discount',
        'tax_amount', 'grand_total'));
      InvoiceProduct::where('invoice_id', $invoice->id)->delete();

      if (!empty($request->get('product_id'))) {
        foreach ($request->get('product_id') as $key => $item) {
          if ($item != "" && $request->get('product_name')[$key] != "" && $request->get('description')[$key] != "" &&
            $request->get('quantity')[$key] != "" && $request->get('price')[$key] != "" && $request->get('sub_total')[$key] != ""
          ) {
            $invoiceProduct = new InvoiceProduct();
            $invoiceProduct->invoice_id = $invoice->id;
            $invoiceProduct->product_id = $item;
            $invoiceProduct->product_name = $request->get('product_name')[$key];
            $invoiceProduct->description = $request->get('description')[$key];
            $invoiceProduct->quantity = $request->get('quantity')[$key];
            $invoiceProduct->price = $request->get('price')[$key];
            $invoiceProduct->sub_total = $request->get('sub_total')[$key];
            $invoiceProduct->save();
          }
        }
      }
      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete invoice
   *
   * @Post("/delete_invoice")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "invoice_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteInvoice(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('invoices.delete')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'invoice_id' => $request->input('invoice_id'),
    );
    $rules = array(
      'invoice_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $invoice = Invoice::find($request->get('invoice_id'));
      $invoice->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }


  /**
   * Get all invoice_payments
   *
   * @Get("/invoice_payments")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "invoice_payments": {
  {
  "id": 1,
  "payment_number": "P002",
  "payment_received": "1525.26",
  "payment_method": "Paypal",
  "payment_date": "2015-11-11",
  "customer": "Customer Name",
  "person": "Person Name"
  }
  }
  }),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function invoicePayments() {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('invoices.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $invoice_payments = InvoiceReceivePayment::whereHas('user', function ($q) {
      $q->where(function ($query) {
        $query
          ->orWhere('id', $this->user->parent->id)
          ->orWhere('users.user_id', $this->user->parent->id);
      });
    })->with('invoice.customer', 'invoice.salesPerson')
      ->get()->map(function ($ip) {
        return [
          'id' => $ip->id,
          'payment_number' => $ip->payment_number,
          'payment_received' => $ip->payment_received,
          'invoice_number' => isset($ip->invoice) ? $ip->invoice->invoice_number : "-",
          'payment_method' => $ip->payment_method,
          'payment_date' => $ip->payment_date,
          'customer' => isset($ip->invoice->customer) ? $ip->invoice->customer->full_name : null,
          'salesperson' => isset($ip->invoice->salesPerson) ? $ip->invoice->salesPerson->full_name : null,
        ];
      });

    return response()->json(['invoice_payments' => $invoice_payments], 200);
  }

  /**
   * Get invoice_payment item
   *
   * @Get("/invoice_payment")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "invoice_payment": {
  {
  "id": 1,
  "payment_number": "P002",
  "payment_received": "1525.26",
  "payment_method": "Paypal",
  "payment_date": "2015-11-11",
  "customer": "Customer Name",
  "person": "Person Name"
  }
  }
  }),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */

  public function invoicePayment(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('invoices.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $invoice_payment = InvoiceReceivePayment::find($request->invoice_payment_id)
      ->with('invoice.customer', 'invoice.salesPerson')
      ->get()
      ->map(function ($invoice_payment) {
        return [
          'id' => $invoice_payment->id,
          'payment_number' => $invoice_payment->payment_number,
          'payment_received' => $invoice_payment->payment_received,
          'invoice_number' => isset($ip->invoice) ? $ip->invoice->invoice_number : "-",
          'payment_method' => $invoice_payment->payment_method,
          'payment_date' => $invoice_payment->payment_date,
          'customer' => isset($invoice_payment->invoice->customer) ? $invoice_payment->invoice->customer->full_name : null,
          'salesperson' => isset($invoice_payment->invoice->salesPerson) ? $invoice_payment->invoice->salesPerson->full_name : null,
        ];
      });

    return response()->json(['invoice_payment' => $invoice_payment], 200);
  }

  /**
   * Post invoice_payment
   *
   * @Post("/post_invoice_payment")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "invoice_id":"5", "payment_date":"2015-11-11","payment_method":"2","payment_received":"555"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */

  public function postInvoicePayment(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('invoices.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'invoice_id' => $request->input('invoice_id'),
      'payment_date' => $request->input('payment_date'),
      'payment_method' => $request->input('payment_method'),
      'payment_received' => $request->input('payment_received'),
    );
    $rules = array(
      'invoice_id' => 'required',
      'payment_date' => 'required',
      'payment_method' => 'required',
      'payment_received' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $invoice = Invoice::find($request->get('invoice_id'));

      $total_fields = InvoiceReceivePayment::orderBy('id', 'desc')->first();

      $quotation_no = Settings::get('invoice_payment_prefix') . (Settings::get('invoice_payment_start_number') + (isset($total_fields) ? $total_fields->id : 0) + 1);

      $payment_date = date(Settings::get('date_format'), strtotime(' + ' . $request->payment_date));

      $invoiceRepository = $this->invoiceRepository->create($request->except('token', 'invoice_id'));
      $invoiceRepository->invoice()->associate($invoice);
      $invoiceRepository->payment_number = $quotation_no;
      $invoiceRepository->payment_date = isset($request->payment_date) ? $request->payment_date : strtotime($payment_date);
      $invoiceRepository->save();

      $unpaid_amount_new = bcsub($invoice->unpaid_amount, $request->payment_received, 2);

      if ($unpaid_amount_new <= '0') {
        $invoice_data = array(
          'unpaid_amount' => $unpaid_amount_new,
          'status' => 'Paid Invoice',
        );
      } else {
        $invoice_data = array(
          'unpaid_amount' => $unpaid_amount_new,
        );
      }

      $invoice->update($invoice_data);

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }


  /**
   * Get all leads
   *
   * @Get("/leads")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "leads": {
  {
  "id": 1,
  "register_time": "2015-12-22",
  "opportunity": "1.2",
  "contact_name": "Contact name",
  "email": "dsad@asd.com",
  "phone": "456469465",
  "salesteam": "Test team",
  }
  }
  }),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function leads() {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('leads.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $leads = Lead::whereHas('user', function ($q) {
      $q->where(function ($query) {
        $query
          ->orWhere('id', $this->user->parent->id)
          ->orWhere('users.user_id', $this->user->parent->id);
      });
    })
      ->with('country', 'salesteam')
      ->get()
      ->map(function ($lead) {
        return [
          'id' => $lead->id,
          'register_time' => $lead->register_time,
          'opportunity' => $lead->opportunity,
          'contact_name' => $lead->contact_name,
          'country' => isset($lead->country->name) ? $lead->country->name : "",
          'email' => $lead->email,
          'phone' => $lead->phone,
          'salesteam' => isset($lead->salesTeam->salesteam) ? $lead->salesTeam->salesteam : ""
        ];
      });

    return response()->json(['leads' => $leads], 200);
  }

  /**
   * Get lead item
   *
   * @Get("/lead")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "lead_id":"1"}),
   *       @Response(200, body={"invoice": {
  "id" : 1,
  "opportunity" : "Lead",
  "company_name" : "sdfsdf sdf",
  "customer_id" : 1,
  "address" : "sd fsdfd",
  "country_id" : 1,
  "state_id" : 43,
  "city_id" : 5914,
  "sales_person_id" : 1,
  "sales_team_id" : 1,
  "contact_name" : "sdfsdf sdf sdf ",
  "title" : "Doctor",
  "email" : "user@mw.life",
  "function" : "asdasd sad asd ",
  "phone" : "1545",
  "mobile" : "545",
  "fax" : "1545",
  "tags" : "2,4",
  "priority" : "Low",
  "internal_notes" : "asd asd asd ",
  "assigned_partner_id" : 0,
  "user_id" : 1,
  "created_at" : "2015-12-22 19:56:54",
  "updated_at" : "2015-12-22 19:56:54",
  "deleted_at" : null
  }}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function lead(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('leads.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'lead_id' => $request->input('lead_id'),
    );
    $rules = array(
      'lead_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      //$lead = Lead::find($request->lead_id);
      $lead = Lead::find($request->lead_id)
        ->get()
        ->map(function ($lead) {
          return [
            'id' => $lead->id,
            'opportunity' => $lead->opportunity,
            'customer' => $lead->customerCompany ? $lead->customerCompany->name : null,
            'address' => $lead->address,
            'country' => $lead->country ? $lead->country->name : null,
            'state_id' => $lead->state ? $lead->state->name : null,
            'city_id' => $lead->city ? $lead->city->name : null,
            'salesteam' => $lead->salesteam,
            'sales_person' => $lead->salesPerson ? $lead->salesPerson->full_name : null,
            'contact_name' => $lead->contact_name,
            'title' => $lead->title,
            'email' => $lead->email,
            'function' => $lead->function,
            'phone' => $lead->phone,
            'mobile' => $lead->mobile,
            'fax' => $lead->fax,
            'tags' => $lead->tags,
            'priority' => $lead->priority,
            'internal_notes' => $lead->internal_notes];
        });

      return response()->json(['lead' => $lead], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Post lead
   *
   * @Post("/post_lead")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "opportunity":"125.5", "email":"test@test.com","customer_id":"12","sales_team_id":"1","tags":"Softwae","country_id":"15","sales_person_id":"12"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function postLead(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('leads.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'opportunity' => $request->input('opportunity'),
      'email' => $request->input('email'),
      'country_id' => $request->input('country_id'),
      'customer_id' => $request->input('customer_id'),
      'sales_team_id' => $request->input('sales_team_id'),
      'sales_person_id' => $request->input('sales_person_id'),
    );
    $rules = array(
      'opportunity' => 'required',
      'email' => 'required|email',
      'country_id' => 'required',
      'customer_id' => 'required',
      'sales_team_id' => 'required',
      'sales_person_id' => "required"
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $this->user->leads()->save(new Lead($request->except('token')));

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit lead
   *
   * @Post("/edit_lead")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo","lead_id":1, "opportunity":"125.5", "email":"test@test.com","customer_id":"12","sales_team_id":"1","tags":"Softwae","country_id":"15"}),
   *       @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function editLead(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('leads.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'lead_id' => $request->input('lead_id'),
      'opportunity' => $request->input('opportunity'),
      'email' => $request->input('email'),
      'country_id' => $request->input('country_id'),
      'customer_id' => $request->input('customer_id'),
      'sales_team_id' => $request->input('sales_team_id'),
      'sales_person_id' => $request->input('sales_person_id'),
    );
    $rules = array(
      'lead_id' => 'required',
      'opportunity' => 'required',
      'email' => 'required|email',
      'country_id' => 'required',
      'customer_id' => 'required',
      'sales_team_id' => 'required',
      'sales_person_id' => "required"
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $lead = Lead::find($request->lead_id);
      $lead->tags = implode(',', $request->get('tags', []));
      $lead->update($request->except('token', 'tags', 'lead_id'));

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete lead
   *
   * @Post("/delete_lead")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "lead_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteLead(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('leads.delete')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'lead_id' => $request->input('lead_id'),
    );
    $rules = array(
      'lead_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $lead = Lead::find($request->lead_id);
      $lead->calls()->delete();
      $lead->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Get all lead calls
   *
   * @Get("/lead_calls")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "lead_id":"1"}),
   *      @Response(200, body={
  "calls": {
  {
  "id": 1,
  "date": "2015-10-15",
  "call_summary": "Call summary",
  "company": "Company",
  "responsible": "User",
  }
  }
  }),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function leadCalls(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('leads.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'lead_id' => $request->input('lead_id'),
    );
    $rules = array(
      'lead_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $lead = Lead::find($request->lead_id);
      $calls = $lead->calls()->with('responsible', 'company')
        ->get()
        ->map(function ($call) {
          return [
            'id' => $call->id,
            'date' => $call->date,
            'call_summary' => $call->call_summary,
            'company' => isset($call->company) ? $call->company->name : "",
            'responsible' => isset($call->responsible) ? $call->responsible->full_name : ""
          ];
        });

      return response()->json(['calls' => $calls], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }


  /**
   * Get all lead call
   *
   * @Get("/lead_call")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "lead_id":"1"}),
   *      @Response(200, body={
  "calls": {
  {
  "id": 1,
  "date": "2015-10-15",
  "call_summary": "Call summary",
  "company": "Company",
  "responsible": "User",
  }
  }
  }),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function leadCall(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('leads.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $lead = Lead::find($request->lead_id);
    $calls = $lead->calls()
      ->with('responsible', 'company')
      ->get()
      ->map(function ($call) {
        return [
          'id' => $call->id,
          'date' => $call->date,
          'call_summary' => $call->call_summary,
          'company' => isset($call->company) ? $call->company->name : "",
          'responsible' => isset($call->responsible) ? $call->responsible->full_name : ""
        ];
      });

    return response()->json(['calls' => $calls], 200);
  }

  /**
   * Post lead call
   *
   * @Post("/post_lead_call")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "lead_id":"1","date":"2015-10-11", "call_summary":"call summary","company_id":"1","resp_staff_id":"12"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function postLeadCall(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('leads.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'lead_id' => $request->input('lead_id'),
      'date' => $request->input('date'),
      'call_summary' => $request->input('call_summary'),
      'company_id' => $request->input('company_id'),
      'resp_staff_id' => $request->input('resp_staff_id'),
    );
    $rules = array(
      'date' => 'required|date_format:"' . Settings::get('date_format') . '"',
      'lead_id' => "required",
      'call_summary' => 'required',
      'company_id' => 'required',
      'resp_staff_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $lead = Lead::find($request->lead_id);
      $call = $lead->calls()->create($request->except('lead_id', 'token'), ['user_id' => $this->user->id]);
      $call->user_id = $this->user->id;
      $call->save();

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit lead call
   *
   * @Post("/edit_lead_call")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "call_id":"1","lead_id":"1","date":"2015-10-11", "call_summary":"call summary","company_id":"1","resp_staff_id":"12"}),
   *       @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function editLeadCall(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('leads.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'call_id' => $request->input('call_id'),
      'lead_id' => $request->input('lead_id'),
      'date' => $request->input('date'),
      'call_summary' => $request->input('call_summary'),
      'company_id' => $request->input('company_id'),
      'resp_staff_id' => $request->input('resp_staff_id'),
    );
    $rules = array(
      'call_id' => 'required',
      'lead_id' => "required",
      'date' => 'required|date_format:"' . Settings::get('date_format') . '"',
      'call_summary' => 'required',
      'company_id' => 'required',
      'resp_staff_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $call = Call::find($request->call_id);
      $call->update($request->except('token', 'lead_id', 'call_id'));

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete lead call
   *
   * @Post("/delete_lead_call")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "call_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteLeadCall(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('leads.delete')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'call_id' => $request->input('call_id'),
    );
    $rules = array(
      'call_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $call = Call::find($request->call_id);
      $call->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Get all meetings
   *
   * @Get("/meetings")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "meetings": {
  {
  "id": 1,
  "meeting_subject": "meeting subject",
  "starting_date": "2015-12-22",
  "ending_date": "2015-12-22",
  "responsible": "User name"
  }
  }
  }),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function meetings() {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('meetings.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $meetings = Meeting::whereHas('user', function ($q) {
      $q->where(function ($query) {
        $query
          ->orWhere('id', $this->user->parent->id)
          ->orWhere('users.user_id', $this->user->parent->id);
      });
    })
      ->with('responsible')
      ->latest()->get()->map(function ($meeting) {
        return [
          'id' => $meeting->id,
          'meeting_subject' => $meeting->meeting_subject,
          'starting_date' => $meeting->starting_date,
          'ending_date' => $meeting->ending_date,
          'responsible' => isset($meeting->responsible) ? $meeting->responsible->full_name : ""
        ];
      });

    return response()->json(['meetings' => $meetings], 200);
  }

  /**
   * Get meeting item
   *
   * @Get("/meeting")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "meeting_id":"1"}),
   *       @Response(200, body={"meeting": {
  "id" : 2,
  "meeting_subject" : "Meeting",
  "attendees" : "1",
  "responsible_id" : 2,
  "starting_date" : "29.12.2015. 00:00",
  "ending_date" : "08.01.2016. 00:00",
  "all_day" : 0,
  "location" : "sdfsdf",
  "meeting_description" : "ftyf hgfhgfh",
  "privacy" : "Everyone",
  "show_time_as" : "Free",
  "duration" : "",
  "user_id" : 0,
  "created_at" : "2015-12-22 20:19:42",
  "updated_at" : "2015-12-26 15:03:37",
  "deleted_at" : null
  }}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function meeting(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('meetings.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'meeting_id' => $request->input('meeting_id'),
    );
    $rules = array(
      'meeting_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $meeting = Meeting::find($request->meeting_id)
        ->with('responsible')
        ->get()
        ->map(function ($meeting) {
          return [
            'id' => $meeting->id,
            'meeting_subject' => $meeting->meeting_subject,
//                        'attendees' =>$meeting->attendees,
            'responsible' => isset($meeting->responsible) ? $meeting->responsible->full_name : "",
            'starting_date' => $meeting->starting_date,
            'ending_date' => $meeting->ending_date,
            'all_day' => $meeting->all_day,
            'location' => $meeting->location,
            'meeting_description' => $meeting->meeting_description,
            'privacy' => $meeting->privacy,
            'show_time_as' => $meeting->show_time_as,
            'duration' => $meeting->duration
          ];
        });
      $attendees = array();
      $meetingNew = Meeting::find($request->meeting_id);
      $attendeesArray = array();
      $attendeesArray[] = explode(",", $meetingNew->attendees);
      foreach ($attendeesArray as $key => $attendeeId) {
        do {
          $attendees[] = Company::find($attendeeId)->pluck('name');
        } while ($key == sizeof($attendeeId));
      }
      return response()->json(['meeting' => $meeting, 'attendees' => $attendees], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Post meeting
   *
   * @Post("/post_meeting")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "meeting_subject":"Subject", "starting_date":"2015-11-11","ending_date":"2015-11-11"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function postMeeting(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('meetings.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'meeting_subject' => $request->input('meeting_subject'),
      'starting_date' => $request->input('starting_date'),
      'ending_date' => $request->input('ending_date'),
      'responsible_id' => $request->input('responsible_id'),
    );
    $rules = array(
      'meeting_subject' => 'required',
      'responsible_id' => 'required',
      'starting_date' => 'required|date_format:"' . Settings::get('date_format') . ' ' . Settings::get('time_format') . '"',
      'ending_date' => 'required|date_format:"' . Settings::get('date_format') . ' ' . Settings::get('time_format') . '"',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $request->merge([
        'attendees' => implode(',', $request->get('attendees', []))
      ]);

      $user = Sentinel::getUser();
      $user->meetings()->create($request->except('token'));

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit meeting
   *
   * @Post("/edit_meeting")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo","meeting_id":1, "meeting_subject":"Subject", "starting_date":"2015-11-11","ending_date":"2015-11-11"}),
   *       @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function editMeeting(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('meetings.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }

    $data = array(
      'meeting_id' => $request->input('meeting_id'),
      'meeting_subject' => $request->input('meeting_subject'),
      'responsible_id' => $request->input('responsible_id'),
      'starting_date' => $request->input('starting_date'),
      'ending_date' => $request->input('ending_date'),
    );
    $rules = array(
      'meeting_id' => 'required',
      'meeting_subject' => 'required',
      'responsible_id' => 'required',
      'starting_date' => 'required|date_format:"' . Settings::get('date_format') . ' ' . Settings::get('time_format') . '"',
      'ending_date' => 'required|date_format:"' . Settings::get('date_format') . ' ' . Settings::get('time_format') . '"',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $meeting = Meeting::find($request->meeting_id);
      $meeting->attendees = implode(',', $request->get('attendees', []));
      $meeting->update($request->except('token', 'attendees', 'meeting_id'));

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete meeting
   *
   * @Post("/delete_meeting")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "meeting_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteMeeting(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('meetings.delete')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }

    $data = array(
      'meeting_id' => $request->input('meeting_id'),
    );
    $rules = array(
      'meeting_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $meeting = Meeting::find($request->meeting_id);
      $meeting->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Get all opportunity calls
   *
   * @Get("/opportunity_calls")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "opportunity_id":"1"}),
   *      @Response(200, body={
  "calls": {
  {
  "id": 1,
  "date": "2015-10-15",
  "call_summary": "Call summary",
  "company": "Company",
  "responsible": "User",
  }
  }
  }),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function opportunityCalls(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('opportunities.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $opportunity = Opportunity::find($request->opportunity_id);
    $calls = $opportunity->calls()
      ->with('responsible', 'company')
      ->get()
      ->map(function ($call) {
        return [
          'id' => $call->id,
          'date' => $call->date,
          'call_summary' => $call->call_summary,
          'company' => isset($call->company) ? $call->company->name : "",
          'responsible' => isset($call->responsible) ? $call->responsible->full_name : ""
        ];
      });

    return response()->json(['calls' => $calls], 200);
  }

  /**
   * Get opportunity call
   *
   * @Get("/opportunity_call")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "opportunity_id":"1","call_id":"1"}),
   *       @Response(200, body={"calls": {
  "id": 1,
  "date": "2015-10-15",
  "call_summary": "Call summary",
  "company": "Company",
  "responsible": "User",
  }}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */

  public function opportunityCall(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('opportunities.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $opportunity = Opportunity::find($request->opportunity_id);
    $calls = $opportunity->calls()->where('call_id', $request->call_id)
      ->with('responsible', 'company')
      ->get()
      ->map(function ($call) {
        return [
          'id' => $call->id,
          'date' => $call->date,
          'call_summary' => $call->call_summary,
          'company' => isset($call->company) ? $call->company->name : "",
          'responsible' => isset($call->responsible) ? $call->responsible->full_name : ""
        ];
      });

    return response()->json(['calls' => $calls], 200);
  }

  /**
   * Post opportunity call
   *
   * @Post("/post_opportunity_call")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "opportunity_id":"1","date":"2015-10-11", "call_summary":"call summary","company_id":"1","resp_staff_id":"12"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function postOpportunityCall(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('opportunities.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'opportunity_id' => $request->input('opportunity_id'),
      'date' => $request->input('date'),
      'call_summary' => $request->input('call_summary'),
      'company_id' => $request->input('company_id'),
      'resp_staff_id' => $request->input('resp_staff_id'),
    );
    $rules = array(
      'date' => 'required|date_format:"' . Settings::get('date_format') . '"',
      'opportunity_id' => "required",
      'call_summary' => 'required',
      'company_id' => 'required',
      'resp_staff_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $opportunity = Opportunity::find($request->opportunity_id);
      $call = $opportunity->calls()->create($request->except('opportunity_id', 'token'), ['user_id' => $this->user->id]);
      $call->user_id = $this->user->id;
      $call->save();

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit opportunity call
   *
   * @Post("/edit_opportunity_call")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "opportunity_id":"1","lead_id":"1","date":"2015-10-11", "call_summary":"call summary","company_id":"1","resp_staff_id":"12"}),
   *       @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function editOpportunityCall(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('opportunities.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'call_id' => $request->input('call_id'),
      'opportunity_id' => $request->input('opportunity_id'),
      'date' => $request->input('date'),
      'call_summary' => $request->input('call_summary'),
      'company_id' => $request->input('company_id'),
      'resp_staff_id' => $request->input('resp_staff_id'),
    );
    $rules = array(
      'call_id' => 'required',
      'opportunity_id' => "required",
      'date' => 'required|date_format:"' . Settings::get('date_format') . '"',
      'call_summary' => 'required',
      'company_id' => 'required',
      'resp_staff_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $call = Call::find($request->call_id);
      $call->update($request->except('token', 'call_id', 'opportunity_id'));

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete opportunity call
   *
   * @Post("/delete_opportunity_call")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "call_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteOpportunityCall(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('opportunities.delete')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'call_id' => $request->input('call_id'),
    );
    $rules = array(
      'call_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $call = Call::find($request->call_id);
      $call->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }


  /**
   * Get all opportunities
   *
   * @Get("/opportunities")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "opportunities": {
  {
  "id": 1,
  "opportunity": "Opportunity",
  "company": "Company",
  "next_action": "2015-12-22",
  "stages": "Stages",
  "expected_revenue": "Expected revenue",
  "probability": "probability",
  "salesteam": "salesteam",
  "calls": "5",
  "meetings": "5"
  }
  }
  }),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function opportunities() {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('opportunities.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $opportunities = Opportunity::whereHas('user', function ($q) {
      $q->where(function ($query) {
        $query
          ->orWhere('id', $this->user->parent->id)
          ->orWhere('users.user_id', $this->user->parent->id);
      });
    })
      ->with('salesteam', 'customer', 'calls', 'meetings')
      ->get()
      ->map(function ($opportunity) {
        return [
          'id' => $opportunity->id,
          'opportunity' => $opportunity->opportunity,
          'company' => isset($opportunity->customer) ? $opportunity->customer->full_name : '',
          'next_action' => $opportunity->next_action,
          'stages' => $opportunity->stages,
          'expected_revenue' => $opportunity->expected_revenue,
          'probability' => $opportunity->probability,
          'salesteam' => isset($opportunity->salesteam) ? $opportunity->salesTeam : '',
          'calls' => $opportunity->calls->count(),
          'meetings' => $opportunity->meetings->count()
        ];
      });

    return response()->json(['opportunities' => $opportunities], 200);
  }

  /**
   * Get opportunity item
   *
   * @Get("/opportunity")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "opportunity_id":"1"}),
   *       @Response(200, body={"opportunity": {
  "id" : 1,
  "opportunity" : "r dfgfdg dfg",
  "stages" : "New",
  "customer_id" : 1,
  "expected_revenue" : "sad asd ",
  "probability" : "0",
  "email" : "admin@gmail.com",
  "phone" : 787889,
  "sales_person_id" : 2,
  "sales_team_id" : 1,
  "next_action" : "21.12.2015.",
  "next_action_title" : "454545",
  "expected_closing" : "29.12.2015.",
  "priority" : "Low",
  "tags" : "1,3",
  "lost_reason" : "Too expensive",
  "internal_notes" : "ghkhjkhjk",
  "assigned_partner_id" : 1,
  "user_id" : 1,
  "created_at" : "2015-12-22 20:17:20",
  "updated_at" : "2015-12-22 20:19:11",
  "deleted_at" : null
  }}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function opportunity(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('opportunities.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'opportunity_id' => $request->input('opportunity_id'),
    );
    $rules = array(
      'opportunity_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $opportunity = Opportunity::find($request->opportunity_id)
        ->get()
        ->map(function ($opportunity) {
          return [
            'id' => $opportunity->id,
            'opportunity' => $opportunity->opportunity,
            'company' => isset(User::find($opportunity->customer_id)->full_name) ? User::find($opportunity->customer_id)->full_name : "",
            'next_action' => $opportunity->next_action,
            'next_action_title' => $opportunity->next_action_title,
            'email' => $opportunity->email,
            'phone' => $opportunity->phone,
            'priority' => $opportunity->priority,
            'stages' => $opportunity->stages,
            'expected_revenue' => $opportunity->expected_revenue,
            'probability' => $opportunity->probability,
            'salesteam' => isset(Salesteam::find($opportunity->sales_team_id)->salesteam) ? Salesteam::find($opportunity->sales_team_id)->salesteam : "",
            'calls' => $opportunity->calls->count(),
            'meetings' => $opportunity->meetings->count(),
            'sales_person' => isset(User::find($opportunity->sales_person_id)->full_name) ? User::find($opportunity->sales_person_id)->full_name : ""
          ];
        });

      // $opportunity = Opportunity::find($request->opportunity_id);
      return ['opportunity' => $opportunity];
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Post opportunity
   *
   * @Post("/post_opportunity")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "opportunity":"Opportunity", "stages" : "New", "email":"email@email.com","customer":"1","sales_team_id":"1","next_action":"2015-11-11","expected_closing":"2015-11-11"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function postOpportunity(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('opportunities.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'opportunity' => $request->input('opportunity'),
      'email' => $request->input('email'),
      'stages' => $request->input('stages'),
      'customer_id' => $request->input('customer_id'),
      'sales_team_id' => $request->input('sales_team_id'),
      'next_action' => $request->input('next_action'),
      'expected_closing' => $request->input('expected_closing'),
    );
    $rules = array(
      'opportunity' => 'required',
      'email' => 'required|email',
      'customer_id' => 'required',
      'stages' => 'required',
      'sales_team_id' => 'required',
      'next_action' => 'required',
      'expected_closing' => 'required'
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $opportunity = new Opportunity($request->except('token'));
      if (isset($request->tags)) {
        $opportunity->tags = implode(',', $request->tags);
      }
      // $opportunity->register_time = strtotime(date('d F Y g:i a'));
      // $opportunity->ip_address = $request->ip();

      $this->user->opportunities()->save($opportunity);

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit opportunity
   *
   * @Post("/edit_opportunity")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo","opportunity_id":1,"stages" : "New", "opportunity":"Opportunity", "email":"email@email.com","customer":"1","sales_team_id":"1","next_action":"2015-11-11","expected_closing":"2015-11-11"}),
   *       @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function editOpportunity(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('opportunities.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'opportunity_id' => $request->input('opportunity_id'),
      'opportunity' => $request->input('opportunity'),
      'stages' => $request->input('stages'),
      'email' => $request->input('email'),
      'customer_id' => $request->input('customer_id'),
      'sales_team_id' => $request->input('sales_team_id'),
      'next_action' => $request->input('next_action'),
      'expected_closing' => $request->input('expected_closing'),
    );
    $rules = array(
      'opportunity_id' => 'required',
      'opportunity' => 'required',
      'email' => 'required|email',
      'stages' => 'required',
      'customer_id' => 'required',
      'sales_team_id' => 'required',
      'next_action' => 'required',
      'expected_closing' => 'required'
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $opportunity = Opportunity::find($request->opportunity_id);
      if (isset($request->tags)) {
        $opportunity->tags = implode(',', $request->tags);
      }
      $opportunity->update($request->except('token', 'tags', 'opportunity_id'));

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete opportunity
   *
   * @Post("/delete_opportunity")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "opportunity_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteOpportunity(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('opportunities.delete')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'opportunity_id' => $request->input('opportunity_id'),
    );
    $rules = array(
      'opportunity_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $opportunity = Opportunity::find($request->opportunity_id);
      $opportunity->calls()->delete();
      $opportunity->meetings()->delete();
      $opportunity->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }


  /**
   * Get all opportunity meetings
   *
   * @Get("/opportunity_meetings")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo","opportunity_id":"1"}),
   *      @Response(200, body={
  "salesteam": {
  {
  "id": 1,
  "meeting_subject": "meeting subject",
  "starting_date": "2015-12-22",
  "ending_date": "2015-12-22",
  "responsible": "User name"
  },
  {
  "id": 1,
  "meeting_subject": "meeting subject",
  "starting_date": "2015-12-22",
  "ending_date": "2015-12-22",
  "responsible": "User name"
  }
  }
  }),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function opportunityMeetings(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('meetings.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $opportunity = Opportunity::find($request->opportunity_id);
    $meetings = $opportunity->meetings()
      ->with('responsible')
      ->get()
      ->map(function ($meeting) {
        return [
          'id' => $meeting->id,
          'meeting_subject' => $meeting->meeting_subject,
          'starting_date' => $meeting->starting_date,
          'ending_date' => $meeting->ending_date,
          'responsible' => isset($meeting->responsible) ? $meeting->responsible->full_name : 'N/A'
        ];
      });

    return response()->json(['meetings' => $meetings], 200);
  }

  /**
   * Get opportunity meeting
   *
   * @Get("/opportunity_meeting")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "opportunity_id":"1","meeting_id":"1"}),
   *       @Response(200, body={"meetings": {
  "id": 1,
  "meeting_subject": "meeting subject",
  "starting_date": "2015-12-22",
  "ending_date": "2015-12-22",
  "responsible": "User name"
  }}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */

  public function opportunityMeeting(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('meetings.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $opportunity = Opportunity::find($request->opportunity_id);
    $meetings = $opportunity->meetings()->where('meeting_id', $request->meeting_id)
      ->with('responsible')
      ->get()
      ->map(function ($meeting) {
        return [
          'id' => $meeting->id,
          'meeting_subject' => $meeting->meeting_subject,
          'starting_date' => $meeting->starting_date,
          'ending_date' => $meeting->ending_date,
          'responsible' => isset($meeting->responsible) ? $meeting->responsible->full_name : 'N/A'
        ];
      });

    return response()->json(['meetings' => $meetings], 200);
  }

  /**
   * Post opportunity meeting
   *
   * @Post("/post_opportunity_meeting")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "opportunity_id":1, "meeting_subject":"Subject", "starting_date":"2015-11-11 10:15AM","ending_date":"2015-11-11 10:30AM","responsible_id":1}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function postOpportunityMeeting(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('meetings.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'opportunity_id' => $request->input('opportunity_id'),
      'meeting_subject' => $request->input('meeting_subject'),
      'starting_date' => $request->input('starting_date'),
      'ending_date' => $request->input('ending_date'),
      'responsible_id' => $request->input('responsible_id'),
    );
    $rules = array(
      'opportunity_id' => 'required',
      'meeting_subject' => 'required',
      'responsible_id' => 'required',
      'starting_date' => 'required|date_format:"' . Settings::get('date_format') . ' ' . Settings::get('time_format') . '"',
      'ending_date' => 'required|date_format:"' . Settings::get('date_format') . ' ' . Settings::get('time_format') . '"',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $opportunity = Opportunity::find($request->opportunity_id);
      $request->merge([
        'attendees' => implode(',', $request->get('attendees', []))
      ]);
      $opportunity->meetings()->create($request->except('opportunity_id', 'token'), ['user_id' => $this->user->id]);
      $opportunity->user_id = $this->user->id;
      $opportunity->save();

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit opportunity meeting
   *
   * @Post("/edit_opportunity_meeting")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo","meeting_id":1, "opportunity_id":1, "meeting_subject":"Subject", "starting_date":"2015-11-11 10:15AM","ending_date":"2015-11-11 10:30AM","responsible_id":1}),
   *       @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function editOpportunityMeeting(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('meetings.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'meeting_id' => $request->input('meeting_id'),
      'opportunity_id' => $request->input('opportunity_id'),
      'meeting_subject' => $request->input('meeting_subject'),
      'starting_date' => $request->input('starting_date'),
      'ending_date' => $request->input('ending_date'),
      'responsible_id' => $request->input('responsible_id'),
    );
    $rules = array(
      'meeting_id' => 'required',
      'opportunity_id' => 'required',
      'meeting_subject' => 'required',
      'starting_date' => 'required|date_format:"' . Settings::get('date_format') . ' ' . Settings::get('time_format') . '"',
      'ending_date' => 'required|date_format:"' . Settings::get('date_format') . ' ' . Settings::get('time_format') . '"',
      'responsible_id' => 'required'
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $meeting = Meeting::find($request->meeting_id);
      $meeting->attendees = implode(',', $request->get('attendees', []));
      $meeting->update($request->except('opportunity_id', 'attendees', 'meeting_id', 'token'));

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete opportunity meeting
   *
   * @Post("/delete_opportunity_meeting")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "meeting_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteOpportunityMeeting(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('meetings.delete')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'meeting_id' => $request->input('meeting_id'),
    );
    $rules = array(
      'meeting_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $meeting = Meeting::find($request->meeting_id);
      $meeting->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }


  /**
   * Get all products
   *
   * @Get("/products")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "products": {
  {
  "id": 1,
  "product_name": "product name",
  "name": "category",
  "product_type": "Type",
  "status": "1",
  "quantity_on_hand": "12",
  "quantity_available": "52"
  }
  }
  }),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function products() {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('products.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $products = Product::whereHas('user', function ($q) {
      $q->where(function ($query) {
        $query
          ->orWhere('id', $this->user->parent->id)
          ->orWhere('users.user_id', $this->user->parent->id);
      });
    })
      ->with('category')
      ->get()
      ->map(function ($p) {
        return [
          'id' => $p->id,
          'product_name' => $p->product_name,
          'name' => $p->category->name,
          'product_type' => $p->product_type,
          'status' => $p->status,
          'quantity_on_hand' => $p->quantity_on_hand,
          'quantity_available' => $p->quantity_available,
        ];
      });

    return response()->json(['products' => $products], 200);
  }

  /**
   * Get product item
   *
   * @Get("/product")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "product_id":"1"}),
   *       @Response(200, body={"product": {
  "id" : 1,
  "product_name" : "product",
  "product_image" : "",
  "category_id" : 1,
  "product_type" : "Consumable",
  "status" : "In Development",
  "quantity_on_hand" : 12,
  "quantity_available" : 22,
  "sale_price" : 1.0,
  "description" : "sdfdsfsdf",
  "description_for_quotations" : "sdfsdfsdfsdf",
  "user_id" : 1,
  "created_at" : "2015-12-23 16:58:51",
  "updated_at" : "2015-12-26 07:24:51",
  "deleted_at" : null
  }}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function product(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('products.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'product_id' => $request->input('product_id'),
    );
    $rules = array(
      'product_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $product = Product::find($request->get('product_id'))
        ->get()
        ->map(function ($product) {
          return [
            'id' => $product->id,
            'product_name' => $product->product_name,
            'product_image' => $product->product_image,
            'category_id' => Category::find($product->category_id)->name,
            'product_type' => $product->product_type,
            'status' => $product->status,
            'quantity_on_hand' => $product->quantity_on_hand,
            'quantity_available' => $product->quantity_available,
            'sale_price' => $product->sale_price,
            'description' => $product->description,

          ];
        });
      return response()->json(['product' => $product], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Post product
   *
   * @Post("/post_product")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo","product_name":"product name", "sale_price":"15.2","description":"sadsadsd","quantity_on_hand":"12","quantity_available":"11"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function postProduct(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('products.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'product_name' => $request->input('product_name'),
      'sale_price' => $request->input('sale_price'),
      'description' => $request->input('description'),
      'quantity_on_hand' => $request->input('quantity_on_hand'),
      'quantity_available' => $request->input('quantity_available'),
      'product_type' => $request->input('product_type'),
      'status' => $request->input('status'),
      'category_id' => $request->input('category_id'),
    );
    $rules = array(
      'product_name' => "required",
      'sale_price' => "required",
      'description' => "required",
      'quantity_on_hand' => "required",
      'quantity_available' => "required",
      'product_type' => "required",
      'status' => "required",
      'category_id' => "required",
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $product = new Product($request->except('token'));
      $this->user->products()->save($product);

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit product
   *
   * @Post("/edit_product")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "product_id":"1","product_name":"product name", "sale_price":"15.2","description":"sadsadsd","quantity_on_hand":"12","quantity_available":"11"}),
   *       @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function editProduct(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('products.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'product_id' => $request->input('product_id'),
      'product_name' => $request->input('product_name'),
      'sale_price' => $request->input('sale_price'),
      'description' => $request->input('description'),
      'quantity_on_hand' => $request->input('quantity_on_hand'),
      'quantity_available' => $request->input('quantity_available'),
      'product_type' => $request->input('product_type'),
      'status' => $request->input('status'),
      'category_id' => $request->input('category_id'),
    );
    $rules = array(
      'product_id' => 'required',
      'product_name' => "required",
      'sale_price' => "required",
      'description' => "required",
      'quantity_on_hand' => "required",
      'quantity_available' => "required",
      'product_type' => "required",
      'status' => "required",
      "category_id" => "required"
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $product = Product::find($request->get('product_id'));
      $product->update($request->except('token', 'product_id'));

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete product
   *
   * @Post("/delete_product")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "product_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteProduct(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('products.delete')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'product_id' => $request->input('product_id'),
    );
    $rules = array(
      'product_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $product = Product::find($request->get('product_id'));
      $product->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }


  /**
   * Get all qtemplates
   *
   * @Get("/qtemplates")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "qtemplates": {
  {
  "id": 1,
  "quotation_template": "product name",
  "quotation_duration": "10",
  }
  }
  }),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function qtemplates() {
    $this->user = JWTAuth::parseToken()->authenticate();
    $qtemplates = Qtemplate::whereHas('user', function ($q) {
      $q->where(function ($query) {
        $query
          ->orWhere('id', $this->user->parent->id)
          ->orWhere('users.user_id', $this->user->parent->id);
      });
    })->select('id', 'quotation_template', 'quotation_duration')->get();

    return response()->json(['qtemplates' => $qtemplates], 200);
  }

  /**
   * Get qtemplate item
   *
   * @Get("/qtemplate")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "qtemplate_id":"1"}),
   *       @Response(200, body={"qtemplate": {
  "id" : 1,
  "quotation_template" : "testaa",
  "quotation_duration" : 19,
  "immediate_payment" : 0,
  "terms_and_conditions" : "sd f sdf 22",
  "total" : 2553.0,
  "tax_amount" : 408.48,
  "grand_total" : 2961.48,
  "user_id" : 1,
  "created_at" : "2015-12-23 18:45:58",
  "updated_at" : "2015-12-23 18:46:21",
  "deleted_at" : null
  },"products": {
  "product" : "product",
  "description" : "description",
  "quantity" : 3,
  "unit_price" : 1.95,
  "taxes" : 1.55,
  "subtotal" : 195.36
  }}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function qtemplate(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'qtemplate_id' => $request->input('qtemplate_id'),
    );
    $rules = array(
      'qtemplate_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $qtemplate = Qtemplate::find($request->qtemplate_id);
      $products = array();
      if ($qtemplate->products->count() > 0) {
        foreach ($qtemplate->products as $index => $variants) {
          $products[] = ['product' => $variants->product_name,
            'description' => $variants->description,
            'quantity' => $variants->quantity,
            'unit_price' => $variants->price,
            'taxes' => number_format($variants->quantity * $variants->price * floatval(Settings::get('sales_tax')) / 100, 2,
              '.', ''),
            'subtotal' => $variants->sub_total];
        }
      }
      return response()->json(['qtemplate' => $qtemplate->toArray(), 'products' => $products], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Post qtemplate
   *
   * @Post("/post_qtemplate")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo","product_name":"product name", "sale_price":"15.2","description":"sadsadsd","quantity_on_hand":"12","quantity_available":"11","total":"10.00","tax_amount":"1.11","grand_total":"11.11"}),
   *      @Response(200, body={"success":"success"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function postQtemplate(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'quotation_template' => $request->input('quotation_template'),
      'quotation_duration' => $request->input('quotation_duration'),
      'total' => $request->input('total'),
      'tax_amount' => $request->input('tax_amount'),
      'grand_total' => $request->input('grand_total'),
    );
    $rules = array(
      'quotation_template' => 'required',
      'quotation_duration' => "required",
      'total' => "required",
      'tax_amount' => "required",
      'grand_total' => "required",
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $qtemplate = new Qtemplate($request->only('quotation_template', 'quotation_duration', 'total',
        'tax_amount', 'grand_total'));

      $this->user->qtemplates()->save($qtemplate);

      if (!empty($request->get('product_id'))) {
        foreach ($request->get('product_id') as $key => $item) {
          if ($item != "" && $request->get('product_name')[$key] != "" && $request->get('description')[$key] != "" &&
            $request->get('quantity')[$key] != "" && $request->get('price')[$key] != "" && $request->get('sub_total')[$key] != ""
          ) {
            $qtemplateProduct = new QtemplateProduct();
            $qtemplateProduct->qtemplate_id = $qtemplate->id;
            $qtemplateProduct->product_id = $item;
            $qtemplateProduct->product_name = $request->get('product_name')[$key];
            $qtemplateProduct->description = $request->get('description')[$key];
            $qtemplateProduct->quantity = $request->get('quantity')[$key];
            $qtemplateProduct->price = $request->get('price')[$key];
            $qtemplateProduct->sub_total = $request->get('sub_total')[$key];
            $qtemplateProduct->save();
          }
        }
      }

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit qtemplate
   *
   * @Post("/edit_qtemplate")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "qtemplate_id":"1","product_name":"product name", "sale_price":"15.2","description":"sadsadsd","quantity_on_hand":"12","quantity_available":"11","total":"10.00","tax_amount":"1.11","grand_total":"11.11"}),
   *       @Response(200, body={"success":"success"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function editQtemplate(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'qtemplate_id' => $request->input('qtemplate_id'),
      'quotation_template' => $request->input('quotation_template'),
      'quotation_duration' => $request->input('quotation_duration'),
      'total' => $request->input('total'),
      'tax_amount' => $request->input('tax_amount'),
      'grand_total' => $request->input('grand_total'),
    );
    $rules = array(
      'qtemplate_id' => 'required',
      'quotation_template' => 'required',
      'quotation_duration' => "required",
      'total' => "required",
      'tax_amount' => "required",
      'grand_total' => "required",
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $qtemplate = Qtemplate::find($request->qtemplate_id);
      $qtemplate->update($request->only('quotation_template', 'quotation_duration', 'total',
        'tax_amount', 'grand_total'));

      QtemplateProduct::where('qtemplate_id', $qtemplate->id)->delete();

      if (!empty($request->get('product_id'))) {
        foreach ($request->get('product_id') as $key => $item) {
          if ($item != "" && $request->get('product_name')[$key] != "" && $request->get('description')[$key] != "" &&
            $request->get('quantity')[$key] != "" && $request->get('price')[$key] != "" && $request->get('sub_total')[$key] != ""
          ) {
            $qtemplateProduct = new QtemplateProduct();
            $qtemplateProduct->qtemplate_id = $qtemplate->id;
            $qtemplateProduct->product_id = $item;
            $qtemplateProduct->product_name = $request->get('product_name')[$key];
            $qtemplateProduct->description = $request->get('description')[$key];
            $qtemplateProduct->quantity = $request->get('quantity')[$key];
            $qtemplateProduct->price = $request->get('price')[$key];
            $qtemplateProduct->sub_total = $request->get('sub_total')[$key];
            $qtemplateProduct->save();
          }
        }
      }

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete qtemplate
   *
   * @Post("/delete_qtemplate")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "qtemplate_id":"1"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteQtemplate(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'qtemplate_id' => $request->input('qtemplate_id'),
    );
    $rules = array(
      'qtemplate_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $qtemplate = Qtemplate::find($request->qtemplate_id);
      $qtemplate->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }


  /**
   * Get all quotations
   *
   * @Get("/quotations")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "quotations": {
  {
  "id": 1,
  "quotations_number": "4545",
  "date": "2015-11-11",
  "customer": "customer name",
  "person": "person name",
  "final_price": "12",
  "status": "1",
  }
  }
  }),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function quotations() {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('quotations.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $quotations = Quotation::whereHas('user', function ($q) {
      $q->where(function ($query) {
        $query
          ->orWhere('id', $this->user->parent->id)
          ->orWhere('users.user_id', $this->user->parent->id);
      });
    })
      ->with('user', 'customer')
      ->get()
      ->map(function ($quotation) {
        return [
          'id' => $quotation->id,
          'quotations_number' => $quotation->quotations_number,
          'date' => $quotation->date,
          'customer' => isset($quotation->customer) ? $quotation->customer->full_name : "",
          'person' => isset($quotation->user) ? $quotation->user->full_name : "",
          'final_price' => $quotation->final_price,
          'status' => $quotation->status
        ];
      });

    return response()->json(['quotations' => $quotations], 200);
  }

  /**
   * Get quotation item
   *
   * @Get("/quotation")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "quotation_id":"1"}),
   *       @Response(200, body={"quotation": {
  "id" : 1,
  "quotations_number" : "Q0001",
  "customer_id" : 3,
  "qtemplate_id" : 0,
  "date" : "08.12.2015. 00:00",
  "exp_date" : "30.12.2015.",
  "payment_term" : "10",
  "sales_person_id" : 2,
  "sales_team_id" : 1,
  "terms_and_conditions" : "dff dfg dfg",
  "status" : "Draft Quotation",
  "total" : 333.0,
  "tax_amount" : 53.28,
  "grand_total" : 386.28,
  "discount" : 11.28,
  "final_price" : 289.28,
  "user_id" : 1,
  "created_at" : "2015-12-23 18:39:12",
  "updated_at" : "2015-12-23 18:39:12",
  "deleted_at" : null
  },"products": {
  "product" : "product",
  "description" : "description",
  "quantity" : 3,
  "unit_price" : 1.95,
  "taxes" : 1.55,
  "subtotal" : 195.36
  }}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function quotation(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('quotations.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'quotation_id' => $request->input('quotation_id'),
    );
    $rules = array(
      'quotation_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $quotation = Quotation::find($request->quotation_id)
        ->with('customer', 'salesPerson', 'salesTeam')
        ->get()
        ->map(function ($quotation) {
          return [
            'id' => $quotation->id,
            'quotations_number' => $quotation->quotations_number,
            'company' => isset($quotation->customer) ? $quotation->customer->full_name : "",
            'qtemplate' => isset($quotation->qtemplate_id) ? Qtemplate::find($quotation->qtemplate_id)->quotation_template : "",
            'date' => $quotation->date,
            'exp_date' => $quotation->exp_date,
            'payment_term' => $quotation->payment_term,
            'sales_person' => isset($quotation->salesPerson) ? $quotation->salesPerson->full_name : "",
            'salesteam' => isset($quotation->salesTeam) ? $quotation->salesTeam->salesteam : "",
            'terms_and_conditions' => $quotation->terms_and_conditions,
            'status' => $quotation->status,
            'total' => $quotation->total,
            'tax_amount' => $quotation->tax_amount,
            'grand_total' => $quotation->grand_total,
            'discount' => $quotation->discount,
            'final_price' => $quotation->final_price
          ];
        });
      $products = array();
      $quotationNew = Quotation::find($request->quotation_id);
      if ($quotationNew->products->count() > 0) {
        foreach ($quotationNew->products as $index => $variants) {
          $products[] = ['product' => $variants->product_name,
            'description' => $variants->description,
            'quantity' => $variants->quantity,
            'unit_price' => $variants->price,
            'taxes' => number_format($variants->quantity * $variants->price * floatval(Settings::get('sales_tax')) / 100, 2,
              '.', ''),
            'subtotal' => $variants->sub_total];
        }
      }
      return response()->json(['quotation' => $quotation, 'products' => $products], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Post quotation
   *
   * @Post("/post_quotation")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo","customer_id":"1", "date":"2015-11-11","qtemplate_id":"1","payment_term":"term","sales_person_id":"1","sales_team_id":"1","grand_total":"12.5","discount":"10.2","final_price":"10.25","quotation_prefix":"Q00","quotation_start_number":"0"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function postQuotation(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('quotations.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'customer_id' => $request->input('customer_id'),
      'date' => $request->input('date'),
      'exp_date' => $request->input('exp_date'),
      'qtemplate_id' => $request->input('qtemplate_id'),
      'payment_term' => $request->input('payment_term'),
      'sales_person_id' => $request->input('sales_person_id'),
      'sales_team_id' => $request->input('sales_team_id'),
      'status' => $request->input('status'),
      'grand_total' => $request->input('grand_total'),
      'discount' => $request->input('discount'),
      'final_price' => $request->input('final_price'),
      'total' => $request->input('total'),
      'tax_amount' => $request->input('tax_amount'),
      'quotation_prefix' => $request->input('quotation_prefix'),
      'quotation_start_number' => $request->input('quotation_start_number'),
    );
    $rules = array(
      'customer_id' => 'required',
      'exp_date' => 'date_format:"' . Settings::get('date_format') . '"',
      'date' => 'required|date_format:"' . Settings::get('date_format') . '"',
      'qtemplate_id' => 'required',
      'payment_term' => 'required',
      'sales_person_id' => 'required',
      'sales_team_id' => 'required',
      'status' => 'required',
      'grand_total' => 'required',
      'tax_amount' => 'required',
      'discount' => 'required',
      'final_price' => 'required',
      'total' => 'required',
      'quotation_prefix' => 'required',
      'quotation_start_number' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $total_fields = Quotation::whereNull('deleted_at')->orWhereNotNull('deleted_at')->orderBy('id', 'desc')->first();
      $quotation_no = $request->quotation_prefix . ($request->quotation_start_number + (isset($total_fields) ? $total_fields->id : 0) + 1);
      $exp_date = date(Settings::get('date_format'), strtotime(' + ' . isset($request->payment_term) ? $request->payment_term : 0 . ' days'));


      $quotation = new Quotation($request->only('customer_id', 'qtemplate_id', 'date',
        'exp_date', 'payment_term', 'sales_person_id', 'sales_team_id', 'terms_and_conditions', 'status', 'total',
        'tax_amount', 'grand_total', 'discount', 'final_price'));
      $quotation->quotations_number = $quotation_no;
      $quotation->exp_date = isset($request->exp_date) ? $request->exp_date : strtotime($exp_date);
      $quotation->user_id = $this->user->id;
      $quotation->save();

      QuotationProduct::where('quotation_id', $quotation->id)->delete();
      if (!empty($request->get('product_id'))) {
        foreach ($request->get('product_id') as $key => $item) {
          if ($item != "" && $request->get('product_name')[$key] != "" && $request->get('description')[$key] != "" &&
            $request->get('quantity')[$key] != "" && $request->get('price')[$key] != "" && $request->get('sub_total')[$key] != ""
          ) {
            $quotationProduct = new QuotationProduct();
            $quotationProduct->quotation_id = $quotation->id;
            $quotationProduct->product_id = $item;
            $quotationProduct->product_name = $request->get('product_name')[$key];
            $quotationProduct->description = $request->get('description')[$key];
            $quotationProduct->quantity = $request->get('quantity')[$key];
            $quotationProduct->price = $request->get('price')[$key];
            $quotationProduct->sub_total = $request->get('sub_total')[$key];
            $quotationProduct->save();
          }
        }
      }
      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit quotation
   *
   * @Post("/edit_quotation")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "quotation_id":"1","customer_id":"1", "date":"2015-11-11","qtemplate_id":"1","payment_term":"term","sales_person":"1","sales_team_id":"1","grand_total":"12.5","discount":"10.2","final_price":"10.25"}),
   *       @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function editQuotation(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('quotations.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'quotation_id' => $request->input('quotation_id'),
      'customer_id' => $request->input('customer_id'),
      'date' => $request->input('date'),
      'exp_date' => $request->input('exp_date'),
      'qtemplate_id' => $request->input('qtemplate_id'),
      'payment_term' => $request->input('payment_term'),
      'sales_person_id' => $request->input('sales_person_id'),
      'sales_team_id' => $request->input('sales_team_id'),
      'grand_total' => $request->input('grand_total'),
      'discount' => $request->input('discount'),
      'final_price' => $request->input('final_price'),
    );
    $rules = array(
      'quotation_id' => 'required',
      'customer_id' => 'required',
      'exp_date' => 'date_format:"' . Settings::get('date_format') . '"',
      'date' => 'required|date_format:"' . Settings::get('date_format') . '"',
      'qtemplate_id' => 'required',
      'payment_term' => 'required',
      'sales_person_id' => 'required',
      'sales_team_id' => 'required',
      'grand_total' => 'required',
      'discount' => 'required',
      'final_price' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $quotation = Quotation::find($request->quotation_id);

      $quotation->update($request->only('customer_id', 'qtemplate_id', 'date',
        'exp_date', 'payment_term', 'sales_person_id', 'sales_team_id', 'terms_and_conditions', 'status', 'total',
        'tax_amount', 'grand_total', 'discount', 'final_price'));

      QuotationProduct::where('quotation_id', $quotation->id)->delete();
      if (!empty($request->get('product_id'))) {
        foreach ($request->get('product_id') as $key => $item) {
          if ($item != "" && $request->get('product_name')[$key] != "" && $request->get('description')[$key] != "" &&
            $request->get('quantity')[$key] != "" && $request->get('price')[$key] != "" && $request->get('sub_total')[$key] != ""
          ) {
            $quotationProduct = new QuotationProduct();
            $quotationProduct->quotation_id = $quotation->id;
            $quotationProduct->product_id = $item;
            $quotationProduct->product_name = $request->get('product_name')[$key];
            $quotationProduct->description = $request->get('description')[$key];
            $quotationProduct->quantity = $request->get('quantity')[$key];
            $quotationProduct->price = $request->get('price')[$key];
            $quotationProduct->sub_total = $request->get('sub_total')[$key];
            $quotationProduct->save();
          }
        }
      }
      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete quotation
   *
   * @Post("/delete_quotation")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "quotation_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteQuotation(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('quotations.delete')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'quotation_id' => $request->input('quotation_id'),
    );
    $rules = array(
      'quotation_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $quotation = Quotation::find($request->quotation_id);
      $quotation->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }


  /**
   * Get all sales orders
   *
   * @Get("/sales_orders")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "salesorders": {
  {
  "id": 1,
  "quotations_number": "product name",
  "date": "2015-11-11",
  "customer": "customer name",
  "person": "sales person name",
  "final_price": "12.53",
  "status": "1",
  }
  }
  }),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function salesOrders() {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('sales_orders.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $salesorder = Saleorder::whereHas('user', function ($q) {
      $q->where(function ($query) {
        $query
          ->orWhere('id', $this->user->parent->id)
          ->orWhere('users.user_id', $this->user->parent->id);
      });
    })
      ->with('user', 'customer')
      ->get()
      ->map(function ($quotation) {
        return [
          'id' => $quotation->id,
          'quotations_number' => $quotation->quotations_number,
          'date' => $quotation->date,
          'customer' => is_null($quotation->customer) ? "" : $quotation->customer->full_name,
          'person' => is_null($quotation->user) ? "" : $quotation->user->full_name,
          'final_price' => $quotation->final_price,
          'status' => $quotation->status
        ];
      });

    return response()->json(['salesorders' => $salesorder], 200);
  }

  /**
   * Get salesorder item
   *
   * @Get("/salesorder")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "salesorder_id":"1"}),
   *       @Response(200, body={"salesorder": {
  "id" : 1,
  "sale_number" : "S0001",
  "customer_id" : 3,
  "qtemplate_id" : 0,
  "date" : "15.12.2015.",
  "exp_date" : "15.12.2015.",
  "payment_term" : "15",
  "sales_person_id" : 2,
  "sales_team_id" : 1,
  "terms_and_conditions" : "drtret",
  "status" : "Draft sales order",
  "total" : 1221.0,
  "tax_amount" : 195.36,
  "grand_total" : 1416.36,
  "discount" : 11.28,
  "final_price" : 289.28,
  "user_id" : 1,
  "created_at" : "2015-12-23 17:12:39",
  "updated_at" : "2015-12-23 17:12:39",
  "deleted_at" : null
  },"products": {
  "product" : "product",
  "description" : "description",
  "quantity" : 3,
  "unit_price" : 1.95,
  "taxes" : 1.55,
  "subtotal" : 195.36
  }}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function salesorder(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('sales_orders.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'salesorder_id' => $request->input('salesorder_id'),
    );
    $rules = array(
      'salesorder_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $salesorder = Saleorder::find($request->salesorder_id)
        ->with('customer', 'salesTeam', 'salesPerson')
        ->get()
        ->map(function ($salesorder) {
          return [
            'id' => $salesorder->id,
            'sale_number' => $salesorder->sale_number,
            'customer' => isset($salesorder->customer) ? $salesorder->customer->full_name : "",
            'date' => $salesorder->date,
            'exp_date' => $salesorder->exp_date,
            'payment_term' => $salesorder->payment_term,
            'sales_person' => isset($salesorder->salesPerson) ? $salesorder->salesPerson->full_name : "",
            'salesteam' => isset($salesorder->salesTeam) ? $salesorder->salesTeam->salesteam : "",
            'terms_and_conditions' => $salesorder->terms_and_conditions,
            'status' => $salesorder->status,
            'total' => $salesorder->total,
            'tax_amount' => $salesorder->tax_amount,
            'grand_total' => $salesorder->grand_total,
            'discount' => $salesorder->discount,
            'final_price' => $salesorder->final_price,
          ];
        });

      $products = array();
      $salesorderNew = Saleorder::find($request->salesorder_id);
      if ($salesorderNew->products->count() > 0) {
        foreach ($salesorderNew->products as $index => $variants) {
          $products[] = ['product' => $variants->product_name,
            'description' => $variants->description,
            'quantity' => $variants->quantity,
            'unit_price' => $variants->price,
            'taxes' => number_format($variants->quantity * $variants->price * floatval(Settings::get('sales_tax')) / 100, 2,
              '.', ''),
            'subtotal' => $variants->sub_total];
        }
      }
      return response()->json(['salesorder' => $salesorder, 'products' => $products], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Post Sales Order
   *
   * @Post("/post_sales_order")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo","customer_id":"1", "date":"2015-11-11","qtemplate_id":"1","payment_term":"term","sales_person_id":"1","sales_team_id":"1","grand_total":"12.5","discount":"10.2","final_price":"10.25","sales_prefix":"S00","sales_start_number":"0","status" : "Draft sales order"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function postSalesOrder(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('sales_orders.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'customer_id' => $request->input('customer_id'),
      'date' => $request->input('date'),
      'qtemplate_id' => $request->input('qtemplate_id'),
      'payment_term' => $request->input('payment_term'),
      'sales_person_id' => $request->input('sales_person_id'),
      'sales_team_id' => $request->input('sales_team_id'),
      'status' => $request->input('status'),
      'grand_total' => $request->input('grand_total'),
      'discount' => $request->input('discount'),
      'tax_amount' => $request->input('tax_amount'),
      'final_price' => $request->input('final_price'),
      'total' => $request->input('total'),
      "sales_prefix" => $request->input('sales_prefix'),
      "sales_start_number" => $request->input('sales_start_number'),
    );
    $rules = array(
      'customer_id' => 'required',
      'date' => 'required|date_format:"' . Settings::get('date_format') . '"',
      'qtemplate_id' => 'required',
      'payment_term' => 'required',
      'sales_person_id' => 'required',
      'sales_team_id' => 'required',
      'status' => 'required',
      'grand_total' => 'required',
      'discount' => 'required',
      'tax_amount' => 'required',
      'final_price' => 'required',
      'total' => 'required',
      'sales_prefix' => 'required',
      'sales_start_number' => 'required',
      'exp_date' => 'date',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $total_fields = Saleorder::whereNull('deleted_at')->orWhereNotNull('deleted_at')->orderBy('id', 'desc')->first();
      $sale_no = $request->input('sales_prefix') . ($request->input('sales_start_number') + (isset($total_fields) ? $total_fields->id : 0) + 1);
      $exp_date = date(Settings::get('date_format'), strtotime(' + ' . isset($request->payment_term) ? $request->payment_term : 0 . ' days'));

      $saleorder = new Saleorder($request->only('customer_id', 'qtemplate_id', 'date',
        'exp_date', 'payment_term', 'sales_person_id', 'sales_team_id', 'terms_and_conditions', 'status', 'total',
        'tax_amount', 'grand_total', 'discount', 'final_price'));
      $saleorder->sale_number = $sale_no;
      $saleorder->exp_date = isset($request->exp_date) ? $request->exp_date : strtotime($exp_date);
      $saleorder->user_id = $this->user->id;
      $saleorder->save();

      SaleorderProduct::where('order_id', $saleorder->id)->delete();
      if (!empty($request->get('product_id'))) {
        foreach ($request->get('product_id') as $key => $item) {
          if ($item != "" && $request->get('product_name')[$key] != "" && $request->get('description')[$key] != "" &&
            $request->get('quantity')[$key] != "" && $request->get('price')[$key] != "" && $request->get('sub_total')[$key] != ""
          ) {
            $saleorderProduct = new SaleorderProduct();
            $saleorderProduct->order_id = $saleorder->id;
            $saleorderProduct->product_id = $item;
            $saleorderProduct->product_name = $request->get('product_name')[$key];
            $saleorderProduct->description = $request->get('description')[$key];
            $saleorderProduct->quantity = $request->get('quantity')[$key];
            $saleorderProduct->price = $request->get('price')[$key];
            $saleorderProduct->sub_total = $request->get('sub_total')[$key];
            $saleorderProduct->save();
          }
        }
      }

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit quotation
   *
   * @Post("/edit_sales_order")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "sales_order_id":"1","customer_id":"1", "date":"2015-11-11","qtemplate_id":"1","payment_term":"term","sales_person_id":"1","sales_team_id":"1","grand_total":"12.5","discount":"10.2","final_price":"10.25","status" : "Draft sales order"}),
   *       @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function editSalesOrder(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('sales_orders.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'sales_order_id' => $request->input('sales_order_id'),
      'customer_id' => $request->input('customer_id'),
      'date' => $request->input('date'),
      'exp_date' => $request->input('exp_date'),
      'qtemplate_id' => $request->input('qtemplate_id'),
      'payment_term' => $request->input('payment_term'),
      'sales_person_id' => $request->input('sales_person_id'),
      'sales_team_id' => $request->input('sales_team_id'),
      'grand_total' => $request->input('grand_total'),
      'discount' => $request->input('discount'),
      'final_price' => $request->input('final_price'),
    );
    $rules = array(
      'sales_order_id' => 'required',
      'customer_id' => 'required',
      'date' => 'required|date_format:"' . Settings::get('date_format') . '"',
      'exp_date' => 'date_format:"' . Settings::get('date_format') . '"',
      'qtemplate_id' => 'required',
      'payment_term' => 'required',
      'sales_person_id' => 'required',
      'sales_team_id' => 'required',
      'grand_total' => 'required',
      'discount' => 'required',
      'final_price' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $sales_order = Saleorder::find($request->sales_order_id);
      $sales_order->update($request->only('customer_id', 'qtemplate_id', 'date',
        'exp_date', 'payment_term', 'sales_person_id', 'sales_team_id', 'terms_and_conditions', 'status', 'total',
        'tax_amount', 'grand_total', 'discount', 'final_price'));

      SaleorderProduct::where('order_id', $sales_order->id)->delete();
      if (!empty($request->get('product_id'))) {
        foreach ($request->get('product_id') as $key => $item) {
          if ($item != "" && $request->get('product_name')[$key] != "" && $request->get('description')[$key] != "" &&
            $request->get('quantity')[$key] != "" && $request->get('price')[$key] != "" && $request->get('sub_total')[$key] != ""
          ) {
            $saleorderProduct = new SaleorderProduct();
            $saleorderProduct->order_id = $sales_order->id;
            $saleorderProduct->product_id = $item;
            $saleorderProduct->product_name = $request->get('product_name')[$key];
            $saleorderProduct->description = $request->get('description')[$key];
            $saleorderProduct->quantity = $request->get('quantity')[$key];
            $saleorderProduct->price = $request->get('price')[$key];
            $saleorderProduct->sub_total = $request->get('sub_total')[$key];
            $saleorderProduct->save();
          }
        }
      }
      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete quotation
   *
   * @Post("/delete_sales_order")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "sales_order_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteSalesOrder(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('sales_orders.delete')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'sales_order_id' => $request->input('sales_order_id'),
    );
    $rules = array(
      'sales_order_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $sales_order = Saleorder::find($request->sales_order_id);
      $sales_order->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }


  /**
   * Get all salesteams
   *
   * @Get("/salesteams")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "salesteam": {
  {
  "id": 1,
  "salesteam": "Name of team",
  "target": "111",
  "invoice_forecast": "1125",
  "actual_invoice": "205",
  }
  }
  }),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function salesTeams() {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('sales_teams.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $salesteam = Salesteam::whereHas('user', function ($q) {
      $q->where(function ($query) {
        $query
          ->orWhere('id', $this->user->parent->id)
          ->orWhere('users.user_id', $this->user->parent->id);
      });
    })->latest()->get()->map(function ($user) {
      return [
        'id' => $user->id,
        'salesteam' => $user->salesteam,
        'target' => $user->invoice_target,
        'invoice_forecast' => $user->invoice_forecast,
        'actual_invoice' => $user->actual_invoice,
      ];
    });

    return response()->json(['salesteams' => $salesteam], 200);
  }

  /**
   * Get salesteam item
   *
   * @Get("/salesteam")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "salesteam_id":"1"}),
   *       @Response(200, body={"salesteam": {
  "id" : 1,
  "salesteam" : "testera tim 1",
  "team_leader" : 2,
  "quotations" : false,
  "leads" : false,
  "opportunities" : false,
  "invoice_target" : 15,
  "invoice_forecast" : 22,
  "actual_invoice" : 0,
  "notes" : "dfg fdg dfg",
  "user_id" : 1,
  "created_at" : "2015-12-22 19:47:18",
  "updated_at" : "2015-12-22 19:47:29",
  "deleted_at" : null
  }}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function salesteam(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('sales_teams.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'salesteam_id' => $request->input('salesteam_id'),
    );
    $rules = array(
      'salesteam_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      //$salesteam = Salesteam::find($request->salesteam_id);
      $salesteam = Salesteam::find($request->salesteam_id)
        ->with('teamLeader')
        ->get()
        ->map(function ($salesteam) {
          return [
            'id' => $salesteam->id,
            'salesteam' => $salesteam->salesteam,
            'team_leader' => isset($salesteam->teamLeader) ? $salesteam->teamLeader->full_name : "",
            'quotations' => $salesteam->quotations,
            'leads' => $salesteam->leads,
            'opportunities' => $salesteam->opportunities,
            'invoice_target' => $salesteam->invoice_target,
            'invoice_forecast' => $salesteam->invoice_forecast,
            'actual_invoice' => $salesteam->actual_invoice,
            'notes' => $salesteam->notes
          ];
        });
      return response()->json(['salesteam' => $salesteam], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Post salesteam
   *
   * @Post("/post_salesteam")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo","salesteam":"Title", "invoice_target":"8","invoice_forecast":"1","team_leader":"12","team_members":"1,2,5"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function postSalesTeam(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('sales_teams.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'salesteam' => $request->input('salesteam'),
      'invoice_target' => $request->input('invoice_target'),
      'invoice_forecast' => $request->input('invoice_forecast'),
      'team_leader' => $request->input('team_leader'),
      'team_members' => $request->input('team_members'),
    );
    $rules = array(
      'salesteam' => 'required',
      'invoice_target' => 'required',
      'invoice_forecast' => 'required',
      'team_leader' => 'required',
      'team_members' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $salesteam = new Salesteam($request->except('token'));
      $salesteam->team_members = implode(',', array($request->get('team_members', [])));
      //$salesteam->register_time = strtotime(date('d F Y g:i a'));
      //$salesteam->ip_address = $request->server('REMOTE_ADDR');
      $salesteam->quotations = ($request->quotations) ? $request->quotations : 0;
      $salesteam->leads = ($request->leads) ? $request->leads : 0;
      $salesteam->opportunities = ($request->opportunities) ? $request->opportunities : 0;
      //  $salesteam->status = ($request->status) ? $request->status : 0;
      $this->user->salesTeams()->save($salesteam);

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit salesteam
   *
   * @Post("/edit_salesteam")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "salesteam_id":"1","salesteam":"Title", "invoice_target":"8","invoice_forecast":"1","team_leader":"12","team_members":"1,2,5"}),
   *       @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function editSalesTeam(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('sales_teams.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'salesteam_id' => $request->input('salesteam_id'),
      'salesteam' => $request->input('salesteam'),
      'invoice_target' => $request->input('invoice_target'),
      'invoice_forecast' => $request->input('invoice_forecast'),
      'team_leader' => $request->input('team_leader'),
      'team_members' => $request->input('team_members'),
    );
    $rules = array(
      'salesteam_id' => 'required',
      'salesteam' => 'required',
      'invoice_target' => 'required',
      'invoice_forecast' => 'required',
      'team_leader' => 'required',
      'team_members' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $salesteam = Salesteam::find($request->salesteam_id);
      $salesteam->team_members = implode(',', array($request->get('team_members', [])));
      $salesteam->quotations = ($request->quotations) ? $request->quotations : 0;
      $salesteam->leads = ($request->leads) ? $request->leads : 0;
      $salesteam->opportunities = ($request->opportunities) ? $request->opportunities : 0;
//            $salesteam->status = ($request->status) ? $request->status : 0;
      $salesteam->update($request->except('token', 'team_members', 'quotations', 'leads', 'opportunities', 'status', 'salesteam_id'));

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete salesteam
   *
   * @Post("/delete_salesteam")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "salesteam_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteSalesTeam(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('sales_teams.delete')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'salesteam_id' => $request->input('salesteam_id'),
    );
    $rules = array(
      'salesteam_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $salesteam = Salesteam::find($request->salesteam_id);
      $salesteam->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }


  /**
   * Get all staff
   *
   * @Get("/staffs")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "staffs": {
  {
  "id": 1,
  "full_name": "product name",
  "email": "email@email.com",
  "created_at": "2015-11-11"
  }
  }
  }),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function staffs() {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('staffs.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $staffs = $this->userRepository->getAll()
      ->get()
      ->filter(function ($user) {
        return ($user->inRole('staff') && $user->id != $this->user->id);
      })->map(function ($user) {
        return [
          'id' => $user->id,
          'full_name' => $user->full_name,
          'email' => $user->email,
          'created_at' => $user->created_at->format(Settings::get('date_format')),
        ];
      });

    return response()->json(['staffs' => $staffs], 200);
  }

  /**
   * Get single staff
   *
   * @Get("/staff")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo","staff_id":"33"}),
   *      @Response(200, body={
  "staff": {
  {
  "id": 1,
  "full_name": "full name",
  "first_name": "name",
  "last_name": "last name",
  "phone_number": "+564514368765",
  "email": "email@email.com",
  "created_at": "2015-11-11",
  "permissions": {
  "sales_team.read": true,
  "sales_team.write": true,
  "leads.read": true,
  "leads.write": true,
  "opportunities.read": true,
  "opportunities.write": true,
  "logged_calls.read": true,
  "logged_calls.write": true
  },
  }
  }
  }),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function staff(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('staffs.read')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $staff_roles = User::with('roles')->find($request->staff_id);
    $roles = $staff_roles->roles;
    foreach ($roles as $role) {
      if ($role->slug == 'staff') {
        $staff = User::select('id', 'first_name', 'last_name', 'email', 'phone_number', 'user_id', 'user_avatar', 'permissions')->find($request->staff_id);
        return response()->json(['staff' => $staff], 200);
      } else {
        return response()->json(['error' => 'not_valid_data'], 500);
      }
    }
  }

  /**
   * Post staff
   *
   * @Post("/post_staff")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo","first_name":"first name", "last_name":"last name","email":"email@email.com","password":"1password","permissions":{"sales_team.read": true,"sales_team.write": true,"avatar":"base64_encoded_image" }}),
   *      @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function postStaff(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('staffs.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'first_name' => $request->input('first_name'),
      'last_name' => $request->input('last_name'),
      'email' => $request->input('email'),
      'password' => $request->input('password'),
    );
    $rules = array(
      'first_name' => 'required|min:1|max:50',
      'last_name' => 'min:1|max:50',
      'email' => 'required|email|unique:users,email',
      'password' => 'required|min:6',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {

      $user = Sentinel::registerAndActivate($request->only('first_name', 'last_name', 'email', 'password'));

      $role = Sentinel::findRoleBySlug('staff');
      $role->users()->attach($user);
      $user = User::find($user->id);

      if (!is_null($request->avatar)) {
        $output_file = str_random(10) . ".jpg";
        $ifp = fopen(public_path() . '/uploads/avatar/' . $output_file, "wb");
        fwrite($ifp, base64_decode($request->avatar));
        fclose($ifp);
        $user->user_avatar = $output_file;
      }
      if (!is_null($request->get('permissions'))) {
        $permissions = explode(",", $request->get('permissions'));
        foreach ($permissions as $permission) {
          $user->addPermission($permission);
        }
      }

      $user->user_id = Sentinel::getUser()->user_id;
      $user->save();

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit staff
   *
   * @Post("/edit_staff")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "staff_id":"1","first_name":"first name", "last_name":"last name","password":"1password","permissions":{"sales_team.read": true,"sales_team.write": true,"avatar":"base64_encoded_image"}}),
   *       @Response(200, body={"success":"success"}),
   *       @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */
  public function editStaff(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('staffs.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'staff_id' => $request->input('staff_id'),
      'first_name' => $request->input('first_name'),
      'last_name' => $request->input('last_name'),
      'password' => $request->input('password'),
    );
    $rules = array(
      'staff_id' => 'required',
      'first_name' => 'required|min:1|max:50',
      'last_name' => 'min:1|max:50',
      'password' => 'required|min:6',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $staff = User::find($request->staff_id);
      foreach ($staff->getPermissions() as $key => $item) {
        $staff->removePermission($key);
      }
      if (!is_null($request->avatar)) {
        $output_file = str_random(10) . ".jpg";
        $ifp = fopen(public_path() . '/uploads/avatar/' . $output_file, "wb");
        fwrite($ifp, base64_decode($request->avatar));
        fclose($ifp);
        $staff->user_avatar = $output_file;
      }

      if (!is_null($request->get('permissions'))) {
        $permissions = explode(",", $request->get('permissions'));
        foreach ($permissions as $permission) {
          $staff->addPermission($permission);
        }
      }

      $staff->update($request->except('staff_id', 'email'));

      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete staff
   *
   * @Post("/delete_staff")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "staff_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteStaff(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('staffs.delete')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'staff_id' => $request->input('staff_id'),
    );
    $rules = array(
      'staff_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $staff = User::find($request->staff_id);
      $staff->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Get all tasks
   *
   * @Get("/tasks")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "tasks": {
  {
  "id": 1,
  "task_from": "full_name",
  "finished": "0",
  "task_deadline": "2015-11-11",
  "task_description": "asasd"
  }
  }
  }),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function tasks(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $tasks = Task::where('user_id', $this->user->id)
      ->with('task_from_users')
      ->orderBy("finished", "ASC")
      ->orderBy("task_deadline", "DESC")
      ->get()
      ->map(function ($task) {
        return [
          'id' => $task->id,
          'task_from' => isset($task->task_from_users) ? $task->task_from_users->full_name : "",
          'finished' => $task->finished,
          'task_deadline' => $task->task_deadline,
          "task_description" => $task->task_description,
        ];
      });
    return response()->json(['tasks' => $tasks], 200);
  }

  /**
   * Get single task
   *
   * @Get("/task")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo","task_id":"1"}),
   *      @Response(200, body={
  "task": {
  "id": 1,
  "task_from": "full_name",
  "finished": "0",
  "task_deadline": "2017-11-11",
  "task_description": "Lorem ipsum"
  }
  }),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function task(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'task_id' => $request->input('task_id')
    );
    $rules = array(
      'task_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes() && $this->user) {
      $task = Task::where('user_id', $this->user->id)
        ->with('task_from_users')
        ->find($request->task_id)
        ->get()
        ->map(function ($task) {
          return [
            'id' => $task->id,
            'task_from' => isset($task->task_from_users) ? $task->task_from_users->full_name : "",
            'finished' => $task->finished,
            'task_deadline' => $task->task_deadline,
            "task_description" => $task->task_description,
          ];
        });
      return response()->json(['task' => $task], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Post task
   *
   * @Post("/post_task")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo","user_id":"1", "task_description":"asasas","task_deadline":"2016-10-10"}),
   *       @Response(200, body={"success":"success"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   **/

  public function postTask(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'user_id' => $request->input('user_id'),
      'task_description' => $request->input('task_description'),
      'task_deadline' => $request->input('task_deadline')
    );
    $rules = array(
      'user_id' => 'required|integer',
      'task_description' => 'required',
      'task_deadline' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $request->merge(['task_from_user' => $this->user->id]);
      $task = new Task($request->except('token', 'full_name'));
      $task->save();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit task
   *
   * @Post("/edit_task")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "task_id":"1","user_id":"1", "task_description":"asasas","task_deadline":"2016-10-10"}),
   *       @Response(200, body={"success":"success"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */

  public function editTask(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'task_id' => $request->input('task_id'),
      'user_id' => $request->input('user_id'),
      'task_description' => $request->input('task_description'),
      'task_deadline' => $request->input('task_deadline')
    );
    $rules = array(
      'task_id' => 'required',
      'user_id' => 'required|integer',
      'task_description' => 'required',
      'task_deadline' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $request->merge(['task_from_user' => $this->user->id]);
      $task = Task::find($request->task_id);
      $task->update($request->except('token', 'task_id'));
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete task
   *
   * @Post("/delete_task")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "task_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteTask(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'task_id' => $request->input('task_id'),
    );
    $rules = array(
      'task_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $task = Task::find($request->task_id);
      $task->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Get dashboard data
   *
   * @Get("/dashboard")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "customers": 20,
  "contracts": 12,
  "opportunities": 17,
  "products": 8,
  "opportunity_leads": {
  {
  "month": "Feb",
  "year": "2016",
  "opportunity": 0,
  "leads": 0
  },
  {
  "month": "Mar",
  "year": "2016",
  "opportunity": 0,
  "leads": 0
  },
  {
  "month": "Apr",
  "year": "2016",
  "opportunity": 0,
  "leads": 0
  },
  {
  "month": "May",
  "year": "2016",
  "opportunity": 0,
  "leads": 0
  },
  {
  "month": "Jun",
  "year": "2016",
  "opportunity": 0,
  "leads": 0
  },
  {
  "month": "Jul",
  "year": "2016",
  "opportunity": 0,
  "leads": 0
  },
  {
  "month": "Aug",
  "year": "2016",
  "opportunity": 0,
  "leads": 0
  },
  {
  "month": "Sep",
  "year": "2016",
  "opportunity": 1,
  "leads": 0
  },
  {
  "month": "Oct",
  "year": "2016",
  "opportunity": 0,
  "leads": 3
  },
  {
  "month": "Nov",
  "year": "2016",
  "opportunity": 0,
  "leads": 0
  },
  {
  "month": "Dec",
  "year": "2016",
  "opportunity": 13,
  "leads": 3
  },
  {
  "month": "Jan",
  "year": "2017",
  "opportunity": 3,
  "leads": 1
  }
  },
  "stages_chart": {
  {
  "title": "New",
  "value": "New",
  "color": "#4fc1e9",
  "opprotunities": 0
  },
  {
  "title": "Qualification",
  "value": "Qualification",
  "color": "#a0d468",
  "opprotunities": 0
  }
  }
  }),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function dashboard(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();

    if ($this->user->id) {
      $customers = $this->companyRepository->getAll()->count();
      $contracts = $this->contractRepository->getAll()->count();
      $opportunities = $this->opportunityRepository->getAll()->count();
      $products = $this->productRepository->getAll()->count();

      $opportunity_leads = array();
      for ($i = 11; $i >= 0; $i--) {
        $opportunity_leads[] =
          array(
            'month' => Date::now()->subMonth($i)->format('M'),
            'year' => Carbon::now()->subMonth($i)->format('Y'),
            'opportunity' => $this->opportunityRepository->getAll()->where('created_at', 'LIKE',
              Carbon::now()->subMonth($i)->format('Y-m') . '%')->count(),
            'leads' => $this->leadRepository->getAll()->where('created_at', 'LIKE',
              Carbon::now()->subMonth($i)->format('Y-m') . '%')->count()
          );
      }
      $stages = $this->optionRepository->getAll()
        ->where('category', 'stages')
        ->get()
        ->map(function ($title) {
          return [
            'title' => $title->title,
            'value' => $title->value,
          ];
        })->toArray();
      $colors = array('#4fc1e9', '#a0d468', '#37bc9b', '#ffcc66', '#fd9883', '#c2185b', '#00796b', '#7b1fa2', '#3f51b5', '#00796b', '#607d8b', '#00b0ff');
      foreach ($stages as $key => $item) {
        $stages[$key]['color'] = isset($colors[$key]) ? $colors[$key] : "";
        $stages[$key]['opprotunities'] = $this->opportunityRepository->getAllForUser($this->user->id)->where('stages', $item['title'])->count();
      }

      return response()->json(['customers' => $customers, 'contracts' => $contracts, 'opportunities' => $opportunities,
        'products' => $products, 'opportunity_leads' => $opportunity_leads, 'stages_chart' => $stages], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }

  }

  /**
   * Get all permissions
   *
   * @Get("/permissions")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "permissions": {
  "id": 1,
  "task_from": "full_name",
  "finished": "0",
  "task_deadline": "2017-11-11",
  "task_description": "Lorem ipsum"
  }
  }),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function permissions(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user) {
      $permissions = ['sales_team.read' => 'sales_team.read',
        'sales_team.delete' => 'sales_team.delete',
        'sales_team.write' => 'sales_team.write',
        'leads.read' => 'leads.read',
        'leads.delete' => 'leads.delete',
        'leads.write' => 'leads.write',
        'opportunities.read' => 'opportunities.read',
        'opportunities.delete' => 'opportunities.delete',
        'opportunities.write' => 'opportunities.write',
        'logged_calls.read' => 'logged_calls.read',
        'logged_calls.delete' => 'logged_calls.delete',
        'logged_calls.write' => 'lgged_calls.write',
        'meetings.read' => 'meetings.read',
        'meetings.delete' => 'meetings.delete',
        'meetings.write' => 'meetings.write',
        'products.read' => 'products.read',
        'products.delete' => 'products.delete',
        'products.write' => 'products.write',
        'quotations.read' => 'quotations.read',
        'quotations.delete' => 'quotations.delete',
        'quotations.write' => 'quotations.write',
        'sales_orders.read' => 'sales_orders.read',
        'sales_orders.delete' => 'sales_orders.delete',
        'sales_orders.write' => 'sales_orders.write',
        'invoices.read' => 'invoices.read',
        'invoices.delete' => 'invoices.delete',
        'invoices.write' => 'invoices.write',
        'contracts.read' => 'contracts.read',
        'contracts.delete' => 'contracts.delete',
        'contracts.write' => 'contracts.write',
        'staff.read' => 'staff.read',
        'staff.delete' => 'staff.delete',
        'staff.write' => 'staff.write',
        'contacts.read' => 'contacts.read',
        'contacts.delete' => 'contacts.delete',
        'contacts.write' => 'contacts.write',
      ];
      return response()->json(['permissions' => $permissions], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Get all email Templates
   *
   * @Get("/email_templates")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "email_templates": {
  {
  "id": 1,
  "title": "Title",
  "text": "Email text"
  }
  }
  }),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function emailTemplates(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $email_templates = $this->emailTemplateRepository->getAll()
      ->get()
      ->map(function ($email_template) {
        return [
          'id' => $email_template->id,
          'title' => $email_template->title,
          'text' => $email_template->text,
        ];
      })->values();
    return response()->json(['email_templates' => $email_templates], 200);
  }

  /**
   * Get single email template
   *
   * @Get("/email_template")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo","email_template_id":"1"}),
   *      @Response(200, body={
  "email_template": {
  "id": 1,
  "title": "Title",
  "text": "Email text",
  "user_id": 1,
  "created_at": "2017-06-20 08:57:01",
  "updated_at": "2017-06-20 08:57:01",
  "deleted_at": null
  }
  }),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function emailTemplate(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'email_template_id' => $request->input('email_template_id')
    );
    $rules = array(
      'email_template_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes() && $this->user) {
      $email_template = EmailTemplate::find($request->input('email_template_id'));
      return response()->json(['email_template' => $email_template], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Post email template
   *
   * @Post("/post_email_template")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo","title":"Title","text":"Email text"}),
   *       @Response(200, body={"success":"success"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   **/

  public function postEmailTemplate(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'title' => $request->input('title'),
      'text' => $request->input('text')
    );
    $rules = array(
      'title' => 'required',
      'text' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $request->merge(['user_id' => $this->user->id]);
      $task = new EmailTemplate($request->only('user_id', 'title', 'text'));
      $task->save();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Edit email template
   *
   * @Post("/edit_email_template")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "email_template_id":"1","title":"Title","text":"Email text"}),
   *       @Response(200, body={"success":"success"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */

  public function editEmailTemplate(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'email_template_id' => $request->input('email_template_id'),
      'title' => $request->input('title'),
      'text' => $request->input('text')
    );
    $rules = array(
      'email_template_id' => 'required|integer',
      'title' => 'required',
      'text' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $request->merge(['user_id' => $this->user->id]);
      $emailTemplate = EmailTemplate::find($request->email_template_id);
      $emailTemplate->update($request->only('user_id', 'title', 'text'));
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete email template
   *
   * @Post("/delete_email_template")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "email_template_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteEmailTemplate(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'email_template_id' => $request->input('email_template_id'),
    );
    $rules = array(
      'email_template_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $emailTemplate = EmailTemplate::find($request->email_template_id);
      $emailTemplate->delete();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Invite staff
   *
   * @Post("/invite_staff")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "emails":"email@mail.com,email2@mail.com"}),
   *      @Response(200, body={"success":"success"}),
   *      @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function inviteStaff(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('staffs.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'emails' => $request->input('emails')
    );
    $rules = array(
      'emails' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $emails = explode(";", $request->emails);
      foreach ($emails as $email) {
        $validator = \Validator::make(
          array('individualEmail' => $email),
          array('individualEmail' => 'email')
        );

        if ($validator->passes()) {
          $invite = $this->inviteUserRepository->create(['email' => trim($email)]);
          Mail::to($email)
            ->send(new StaffInvite($invite));
        }
      }
      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Convert opportunity to quotation
   *
   * @Post("/convert_opportunity_to_quotation")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "opportunity_id":1}),
   *      @Response(200, body={"success":"success"}),
   *      @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function convertOpportunityToQuotation(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('staffs.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'opportunity_id' => $request->input('opportunity_id')
    );
    $rules = array(
      'opportunity_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $opportunity = Opportunity::find($request->input('opportunity_id'));
      $total_fields = Quotation::whereNull('deleted_at')->orWhereNotNull('deleted_at')->orderBy('id', 'desc')->first();
      $quotation_no = Settings::get('quotation_prefix') . (Settings::get('quotation_start_number') + (isset($total_fields) ? $total_fields->id : 0) + 1);

      Quotation::create([
        'quotations_number' => $quotation_no,
        'customer_id' => $opportunity->customer_id,
        'date' => date(Settings::get('date_format')),
        'exp_date' => $opportunity->expected_closing,
        'payment_term' => Settings::get('payment_term1'),
        'sales_person_id' => $opportunity->sales_person_id,
        'sales_team_id' => $opportunity->sales_team_id,
        'status' => 'Draft Quotation',
        'user_id' => Sentinel::getUser()->id
      ]);
      $opportunity->update(array('stages' => 'Won'));

      $opportunity->delete();
      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Convert quotation to sale order
   *
   * @Post("/convert_quotation_to_sale_order")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "quotation_id":1}),
   *      @Response(200, body={"success":"success"}),
   *      @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function convertQuotationToSaleOrder(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('staffs.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'quotation_id' => $request->input('quotation_id')
    );
    $rules = array(
      'quotation_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $quotation = Quotation::find($request->input('quotation_id'));
      $total_fields = Saleorder::whereNull('deleted_at')->orWhereNotNull('deleted_at')->orderBy('id', 'desc')->first();
      $sale_no = Settings::get('sales_prefix') . (Settings::get('sales_start_number') + (isset($total_fields) ? $total_fields->id : 0) + 1);

      $saleorder = Saleorder::create([
        'sale_number' => $sale_no,
        'customer_id' => $quotation->customer_id,
        'date' => date(Settings::get('date_format')),
        'exp_date' => $quotation->exp_date,
        'qtemplate_id' => $quotation->qtemplate_id,
        'payment_term' => isset($quotation->payment_term) ? $quotation->payment_term : 0,
        "sales_person_id" => $quotation->sales_person_id,
        "terms_and_conditions" => $quotation->terms_and_conditions,
        "total" => $quotation->total,
        "tax_amount" => $quotation->tax_amount,
        "grand_total" => $quotation->grand_total,
        "discount" => is_null($quotation->discount) ? 0 : $quotation->discount,
        "final_price" => $quotation->final_price,
        'status' => 'Draft sales order',
        'user_id' => Sentinel::getUser()->id
      ]);

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
      $quotation->delete();
      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Convert quotation to invoice
   *
   * @Post("/convert_quotation_to_invoice")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "quotation_id":1}),
   *      @Response(200, body={"success":"success"}),
   *      @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function convertQuotationToInvoice(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('staffs.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'quotation_id' => $request->input('quotation_id')
    );
    $rules = array(
      'quotation_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $quotation = Quotation::find($request->input('quotation_id'));
      $total_fields = Invoice::whereNull('deleted_at')->orWhereNotNull('deleted_at')->orderBy('id', 'desc')->first();
      $invoice_number = Settings::get('invoice_prefix') . (Settings::get('invoice_start_number') + (isset($total_fields) ? $total_fields->id : 0) + 1);

      $invoice_details = array(
        'order_id' => $quotation->id,
        'customer_id' => $quotation->customer_id,
        'sales_person_id' => $quotation->sales_person_id,
        'sales_team_id' => $quotation->sales_team_id,
        'invoice_number' => $invoice_number,
        'invoice_date' => date(Settings::get('date_format')),
        'due_date' => $quotation->exp_date,
        'payment_term' => isset($quotation->payment_term) ? $quotation->payment_term : 0,
        'status' => 'Open Invoice',
        'total' => $quotation->total,
        'tax_amount' => $quotation->tax_amount,
        'grand_total' => $quotation->grand_total,
        'unpaid_amount' => $quotation->final_price,
        'discount' => $quotation->discount,
        'final_price' => $quotation->final_price,
        'user_id' => Sentinel::getUser()->id,
      );
      $invoice = Invoice::create($invoice_details);

      $products = $quotation->products;
      if (!empty($products)) {
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
      $quotation->delete();
      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Convert sale order to invoice
   *
   * @Post("/convert_sale_order_to_invoice")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "sale_order_id":1}),
   *      @Response(200, body={"success":"success"}),
   *      @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function convertSaleOrderToInvoice(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('staffs.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'sale_order_id' => $request->input('sale_order_id')
    );
    $rules = array(
      'sale_order_id' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $saleorder = Saleorder::find($request->input('sale_order_id'));
      $total_fields = Invoice::whereNull('deleted_at')->orWhereNotNull('deleted_at')->orderBy('id', 'desc')->first();
      $invoice_number = Settings::get('invoice_prefix') . (Settings::get('invoice_start_number') + (isset($total_fields) ? $total_fields->id : 0) + 1);

      $invoice = Invoice::create([
        'order_id' => $saleorder->id,
        'customer_id' => $saleorder->customer_id,
        'sales_person_id' => $saleorder->sales_person_id,
        'sales_team_id' => $saleorder->sales_team_id,
        'invoice_number' => $invoice_number,
        'invoice_date' => date(Settings::get('date_format')),
        'due_date' => $saleorder->exp_date,
        'payment_term' => isset($saleorder->payment_term) ? $saleorder->payment_term : 0,
        'status' => 'Open Invoice',
        'total' => $saleorder->total,
        'tax_amount' => $saleorder->tax_amount,
        'grand_total' => $saleorder->grand_total,
        'unpaid_amount' => $saleorder->final_price,
        'discount' => $saleorder->discount,
        'final_price' => $saleorder->final_price,
        'user_id' => Sentinel::getUser()->id,
      ]);

      $products = $saleorder->products;
      if ($products->count() > 0) {
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
      $saleorder->delete();
      return response()->json(['success' => 'success'], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  private function ajaxCreateQuotationPdf(Quotation $quotation) {
    $filename = 'Quotation-' . $quotation->quotations_number;
    $pdf = App::make('dompdf.wrapper');
    $pdf->setPaper('a4', 'landscape');
    $pdf->loadView('quotation_template.' . Settings::get('quotation_template'), compact('quotation'));
    $pdf->save('./pdf/' . $filename . '.pdf');
    $pdf->stream();
    return $filename . ".pdf";
  }

  /**
   * Send quotation to email
   *
   * @Post("/send_quotation")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "quotation_id":1,"subject":"subject","recipients":{1,2,3}, "body":"body"}),
   *      @Response(200, body={"success":"success"}),
   *      @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function sendQuotation(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('quotations.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'quotation_id' => $request->input('quotation_id'),
      'subject' => $request->input('subject'),
      'recipients' => $request->input('recipients'),
      'body' => $request->input('body')
    );
    $rules = array(
      'quotation_id' => 'required',
      'subject' => 'required',
      'recipients' => 'required',
      'body' => 'required'
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $quotation = Quotation::find($request->quotation_id)->first();
      $email_subject = $request->subject;
      $to_customers = Customer::whereIn('user_id', $request->recipients)->get();
      $email_body = $request->body;
      $message_body = Common::parse_template($email_body);
      $quotation_pdf = $this->ajaxCreateQuotationPdf($quotation);

      if (!empty($to_customers) && !filter_var(Settings::get('site_email'), FILTER_VALIDATE_EMAIL) === false) {
        foreach ($to_customers as $item) {
          if (!filter_var($item->email, FILTER_VALIDATE_EMAIL) === false) {
            Mail::send('emails.quotation', array('message_body' => $message_body), function ($message)
            use ($item, $email_subject, $quotation_pdf) {
              $message->from(Settings::get('site_email'), Settings::get('site_name'));
              $message->to($item->email)->subject($email_subject);
              $message->attach(url('/pdf/' . $quotation_pdf));
            });
          }
          $email = new Email();
          $email->assign_customer_id = $item->id;
          $email->from = Sentinel::getUser()->id;
          $email->subject = $email_subject;
          $email->message = $message_body;
          $email->save();
        }
        return response()->json(['success' => 'success'], 200);
      }
      return response()->json(['error' => 'not_valid_data'], 500);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  private function ajaxCreateSaleOrderPdf(Saleorder $saleorder) {
    $filename = 'SalesOrder-' . $saleorder->sale_number;
    $pdf = App::make('dompdf.wrapper');
    $pdf->setPaper('a4', 'landscape');
    $pdf->loadView('saleorder_template.' . Settings::get('saleorder_template'), compact('saleorder'));
    $pdf->save('./pdf/' . $filename . '.pdf');
    $pdf->stream();
    return $filename . ".pdf";
  }

  /**
   * Send sale order to email
   *
   * @Post("/send_sale_order")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "sale_order_id":1,"subject":"subject","recipients":{1,2,3}, "body":"body"}),
   *      @Response(200, body={"success":"success"}),
   *      @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function sendSaleOrder(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('sales_orders.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'sale_order_id' => $request->input('sale_order_id'),
      'subject' => $request->input('subject'),
      'recipients' => $request->input('recipients'),
      'body' => $request->input('body')
    );
    $rules = array(
      'sale_order_id' => 'required',
      'subject' => 'required',
      'recipients' => 'required',
      'body' => 'required'
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $saleorder = Saleorder::find($request->sale_order_id)->first();
      $email_subject = $request->subject;
      $to_customers = Customer::whereIn('user_id', $request->recipients)->get();
      $email_body = $request->body;
      $message_body = Common::parse_template($email_body);
      $saleorder_pdf = $this->ajaxCreateSaleOrderPdf($saleorder);

      if (!empty($to_customers) && !filter_var(Settings::get('site_email'), FILTER_VALIDATE_EMAIL) === false) {
        foreach ($to_customers as $item) {
          if (!filter_var($item->email, FILTER_VALIDATE_EMAIL) === false) {
            Mail::send('emails.quotation', array('message_body' => $message_body), function ($message)
            use ($item, $email_subject, $saleorder_pdf) {
              $message->from(Settings::get('site_email'), Settings::get('site_name'));
              $message->to($item->email)->subject($email_subject);
              $message->attach(url('/pdf/' . $saleorder_pdf));
            });
          }
          $email = new Email();
          $email->assign_customer_id = $item->id;
          $email->from = Sentinel::getUser()->id;
          $email->subject = $email_subject;
          $email->message = $message_body;
          $email->save();
        }
        return response()->json(['success' => 'success'], 200);
      }
      return response()->json(['error' => 'not_valid_data'], 500);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  private function ajaxCreateInvoicePdf(Invoice $invoice) {
    $filename = 'Invoice-' . $invoice->invoice_number;
    $pdf = App::make('dompdf.wrapper');
    $pdf->setPaper('a4', 'landscape');
    $print_type = trans('invoice.invoice_no');
    $pdf->loadView('invoice_template.' . Settings::get('invoice_template'), compact('invoice', 'print_type'));
    $pdf->save('./pdf/' . $filename . '.pdf');
    $pdf->stream();
    return $filename . ".pdf";
  }

  /**
   * Send invoice to email
   *
   * @Post("/send_invoice")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "invoice_id":1,"subject":"subject","recipients":{1,2,3}, "body":"body"}),
   *      @Response(200, body={"success":"success"}),
   *      @Response(403, body={"error":"no_permissions"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function sendInvoice(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    if ($this->user->inRole('staff') && !$this->user->authorized('invoices.write')) {
      return response()->json(['error' => 'no_permissions'], 403);
    }
    $data = array(
      'invoice_id' => $request->input('invoice_id'),
      'subject' => $request->input('subject'),
      'recipients' => $request->input('recipients'),
      'body' => $request->input('body')
    );
    $rules = array(
      'invoice_id' => 'required',
      'subject' => 'required',
      'recipients' => 'required',
      'body' => 'required'
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $invoice = Invoice::find($request->invoice_id)->first();
      $email_subject = $request->subject;
      $to_customers = Customer::whereIn('user_id', $request->recipients)->get();
      $email_body = $request->body;
      $message_body = Common::parse_template($email_body);
      $invoice_pdf = $this->ajaxCreateInvoicePdf($invoice);

      if (!empty($to_customers) && !filter_var(Settings::get('site_email'), FILTER_VALIDATE_EMAIL) === false) {
        foreach ($to_customers as $item) {
          if (!filter_var($item->email, FILTER_VALIDATE_EMAIL) === false) {
            Mail::send('emails.quotation', array('message_body' => $message_body), function ($message)
            use ($item, $email_subject, $invoice_pdf) {
              $message->from(Settings::get('site_email'), Settings::get('site_name'));
              $message->to($item->email)->subject($email_subject);
              $message->attach(url('/pdf/' . $invoice_pdf));
            });
          }
          $email = new Email();
          $email->assign_customer_id = $item->id;
          $email->from = Sentinel::getUser()->id;
          $email->subject = $email_subject;
          $email->message = $message_body;
          $email->save();
        }
        return response()->json(['success' => 'success'], 200);
      }
      return response()->json(['error' => 'not_valid_data'], 500);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }
}
