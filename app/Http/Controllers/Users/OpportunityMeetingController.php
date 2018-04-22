<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Models\Customer;
use App\Http\Requests\MeetingRequest;
use App\Models\Meeting;
use App\Models\Company;
use App\Models\Opportunity;
use App\Models\Option;
use App\Models\Salesteam;
use App\Models\User;
use App\Repositories\CompanyRepository;
use App\Repositories\MeetingRepository;
use App\Repositories\OptionRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Support\Facades\URL;
use Sentinel;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;

class OpportunityMeetingController extends UserController
{
    /**
     * @var MeetingRepository
     */
    private $meetingRepository;
    /**
     * @var CompanyRepository
     */
    private $companyRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;

	/**
     * OpportunityMeetingController constructor.
     * @param MeetingRepository $meetingRepository
     * @param CompanyRepository $companyRepository
     * @param UserRepository $userRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(MeetingRepository $meetingRepository,
                                CompanyRepository $companyRepository,
                                UserRepository $userRepository,
                                OptionRepository $optionRepository)
    {
        parent::__construct();

        $this->meetingRepository = $meetingRepository;
        $this->companyRepository = $companyRepository;
        $this->userRepository = $userRepository;
        $this->optionRepository = $optionRepository;

        view()->share('type', 'opportunitymeeting');

    }

    /**
     * Display a listing of the resource.
     *
     * @param Opportunity $opportunity
     * @return \Illuminate\Http\Response
     */
    public function index(Opportunity $opportunity)
    {
        $title = trans('meeting.opportunity_meetings');
        return view('user.opportunitymeeting.index', compact('title', 'opportunity'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Opportunity $opportunity)
    {
        $title = trans('meeting.opportunity_new');

        $this->generateParams();
        $this->companyAttendees($opportunity);
        return view('user.opportunitymeeting.create', compact('title', 'opportunity'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Opportunity $opportunity
     * @param MeetingRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Opportunity $opportunity, MeetingRequest $request)
    {
        $company_attendees = implode(',',$request->company_attendees);
        if(isset($request->staff_attendees)){
            $staff_attendees = implode(',',$request->staff_attendees);
            $request->merge(['staff_attendees'=>$staff_attendees]);
        }
        $request->merge(['company_attendees'=>$company_attendees,'company_name'=>$opportunity->company_name]);
        $opportunity->meetings()->create($request->all(), ['user_id' => $this->user->id]);

        return redirect("opportunitymeeting/" . $opportunity->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Opportunity $opportunity , Meeting $meeting
     * @return \Illuminate\Http\Response
     */
    public function edit(Opportunity $opportunity, Meeting $meeting)
    {
        $title = trans('meeting.opportunity_edit');
        $this->generateParams();
        $this->companyAttendees($opportunity);
        $company_attendees = User::whereIn('id',explode(',',$meeting->company_attendees))->pluck('id','id')->all();
        $staff_attendees = explode(',', $meeting->staff_attendees);
        $staff_attendees = User::whereIn('id', $staff_attendees)->pluck('id', 'id')->all();
        return view('user.opportunitymeeting.create', compact('title', 'meeting', 'opportunity','staff_attendees','company_attendees'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param MeetingRequest|Request $request
     * @param Opportunity $opportunity
     * @param  Meeting $meeting
     * @return \Illuminate\Http\Response
     */
    public function update(MeetingRequest $request, Opportunity $opportunity, Meeting $meeting)
    {
        $company_attendees = implode(',',$request->company_attendees);
        if(isset($request->staff_attendees)){
            $staff_attendees = implode(',',$request->staff_attendees);
            $request->merge(['staff_attendees'=>$staff_attendees]);
        }
        $request->merge(['company_attendees'=>$company_attendees,'staff_attendees'=>$request->staff_attendees,'company_name'=>$opportunity->company_name]);
        $meeting->all_day = ($request->all_day) ? $request->all_day : 0;
        $meeting->update($request->all());

        return redirect("opportunitymeeting/" . $opportunity->id);
    }


    public function delete(Opportunity $opportunity, Meeting $meeting)
    {
        $title = trans('meeting.opportunity_delete');
        $this->generateParams();
        $this->companyAttendees($opportunity);
        return view('user.opportunitymeeting.delete', compact('title', 'meeting', 'opportunity'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Opportunity $opportunity , Meeting $meeting
     * @return \Illuminate\Http\Response
     */
    public function destroy(Opportunity $opportunity, Meeting $meeting)
    {
        $meeting->delete();
        return redirect('opportunitymeeting/' . $opportunity->id);
    }

    public function data(Opportunity $opportunity,Datatables $datatables)
    {
        $meetings = $opportunity->meetings()
            ->with('responsible')
            ->get()
            ->filter(function ($meeting) {
                return ($meeting->privacy=='Everyone' ||
                    ($meeting->privacy=='Main Staff' && $meeting->responsible_id==$this->user->id)
                    || $meeting->user_id == Sentinel::getUser()->id);
            })
            ->map(function ($meeting) use ($opportunity) {
                $companies = $this->companyRepository->getAll()->orderBy("name", "asc")
                    ->pluck('name', 'id')
                    ->prepend(trans('company.company_name'), '');
                return [
                    'id' => $meeting->id,
                    'meeting_subject' => $meeting->meeting_subject,
                    'company_name' => $companies[$opportunity->company_name],
                    'starting_date' => $meeting->starting_date,
                    'ending_date' => $meeting->ending_date,
                    'meeting_type_id' => $opportunity->id,
                    'responsible' => isset($meeting->responsible) ? $meeting->responsible->full_name : 'N/A'
                ];
            });

        return $datatables->collection($meetings)
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'opportunities.write\']) || Sentinel::inRole(\'admin\'))
<a href="{{ url(\'opportunitymeeting/\' . $meeting_type_id . \'/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning "></i> </a>
                                            @endif
                                            @if(Sentinel::getUser()->hasAccess([\'opportunities.delete\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'opportunitymeeting/\' . $meeting_type_id . \'/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                            @endif')
            ->removeColumn('id')
            ->removeColumn('meeting_type_id')
            ->escapeColumns( [ 'actions' ] )->make();
    }

    private function generateParams()
    {
        $companies = $this->companyRepository->getAll()->orderBy("name", "asc")->pluck('name', 'id');

        $staffs = $this->userRepository->getParentStaff()
	            ->pluck('full_name', 'id');

        $privacy = $this->optionRepository->getAll()
            ->where('category', 'privacy')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title', 'value');

        $show_times = $this->optionRepository->getAll()
            ->where('category', 'show_times')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title', 'value');

        view()->share('show_times', $show_times);
        view()->share('privacy', $privacy);
        view()->share('companies', $companies);
        view()->share('staffs', $staffs);
    }
    public function companyAttendees($opportunity)
    {
        $customers = Customer::where('company_id',$opportunity->company_name)->pluck('user_id','id');
        $company_customer = [];
        foreach ($customers as $customer){
            $company_customer[]=$customer;
        }
        $company_customer = User::whereIn('id',$company_customer)->get()->pluck('full_name','id');

        $sales_team = Salesteam::where('id',$opportunity->sales_team_id)->pluck('team_leader','id');
        $sales_team_members=Salesteam::where('id',$opportunity->sales_team_id)->pluck('team_members','id');
        $sales_team_members = $sales_team_members[$opportunity->sales_team_id];
        $sales_team_members = $sales_team->merge($sales_team_members);
        $main_staff = User::whereIn('id',$sales_team_members)->get()->pluck('full_name','id')->prepend(trans('salesteam.main_staff'),'');

        view()->share('company_customer',$company_customer);
        view()->share('sales_team',$sales_team);
        view()->share('main_staff',$main_staff);
    }

    public function calendar(Opportunity $opportunity)
    {
        $title = trans('meeting.opportunity_meetings');
        return view('user.opportunitymeeting.calendar', compact('title', 'opportunity'));
    }

    public function calendar_data(Opportunity $opportunity)
    {
        $events = array();
        $meetings = $opportunity->meetings()
            ->with('responsible')
            ->get()
            ->filter(function ($meeting) {
                return ($meeting->privacy=='Everyone' ||
                    ($meeting->privacy=='Main Staff' && $meeting->responsible_id==$this->user->id)
                    || $meeting->user_id == Sentinel::getUser()->id);
            })
            ->map(function ($meeting) use ($opportunity) {
                return [
                    'id' => $meeting->id,
                    'title' => $meeting->meeting_subject,
                    'start_date' => $meeting->starting_date,
                    'end_date' => $meeting->ending_date,
                    'meeting_type_id' => $opportunity->id,
                    'type' => 'meeting'
                ];
            });

        foreach ($meetings as $d) {
            $event = [];
            $dateFormat = Settings::get('date_format');
            $timeFormat = Settings::get('time_format');
            $start_date = Carbon::createFromFormat($dateFormat.' '.$timeFormat,$d['start_date'])->format('M d Y');
            $end_date = Carbon::createFromFormat($dateFormat.' '.$timeFormat,$d['end_date'])->addDay()->format('M d Y');
            $event['title'] = $d['title'];
            $event['id'] = $d['id'];
            $event['start'] = $start_date;
            $event['end'] = $end_date;
            $event['allDay'] = true;
            $event['description'] = $d['title'] . '&nbsp;<a href="' . url($d['type'] . '/' . $d['id'] . '/edit') . '" class="btn btn-sm btn-success"><i class="fa fa-pencil-square-o">&nbsp;</i></a>';
            array_push($events, $event);
        }
        return json_encode($events);
    }

}
