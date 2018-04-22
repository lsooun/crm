<?php

namespace App\Http\Controllers\Users;

use App\Events\Meeting\MeetingCreated;
use App\Http\Controllers\UserController;
use App\Http\Requests\MeetingRequest;
use App\Models\Customer;
use App\Models\Meeting;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Sentinel;
use Illuminate\Http\Request;
use App\Http\Requests;
use Yajra\Datatables\Datatables;

class MeetingController extends UserController
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

    public function __construct(MeetingRepository $meetingRepository,
                                CompanyRepository $companyRepository,
                                UserRepository $userRepository,
                                OptionRepository $optionRepository)
    {
        $this->middleware('authorized:meetings.read', ['only' => ['index', 'data']]);
        $this->middleware('authorized:meetings.write', ['only' => ['create', 'store', 'update', 'edit']]);
        $this->middleware('authorized:meetings.delete', ['only' => ['delete']]);

        parent::__construct();

        $this->meetingRepository = $meetingRepository;
        $this->companyRepository = $companyRepository;
        $this->userRepository = $userRepository;
        $this->optionRepository = $optionRepository;

        view()->share('type', 'meeting');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('meeting.meetings');

        return view('user.meeting.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('meeting.new');

        $this->generateParams();

        return view('user.meeting.create', compact('title','company_attendees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param MeetingRequest $request
     * @return \Illuminate\Http\Response
     * @internal param $
     */
    public function store(MeetingRequest $request)
    {
        $company_attendees = implode(',',$request->company_attendees);
        if(isset($request->staff_attendees)){
            $staff_attendees = implode(',',$request->staff_attendees);
            $request->merge(['staff_attendees'=>$staff_attendees]);
        }
        $request->merge(['company_attendees'=>$company_attendees]);
        $this->meetingRepository->create($request->all(), ['user_id' => $this->user->id]);

        return redirect("meeting");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Meeting $meeting
     * @return \Illuminate\Http\Response
     */
    public function edit(Meeting $meeting)
    {
        $meetables = DB::table('meetables')->where('meeting_id',$meeting->id)->get();
        $meetables = $meetables->first();
        if (isset($meetables)){
            $sales_team_id = Opportunity::where('id',$meetables->meetable_id)->get();
            $sales_team_id = $sales_team_id->first()->sales_team_id;
            $sales_team = Salesteam::where('id',$sales_team_id)->pluck('team_leader','id');
            $sales_team_members=Salesteam::where('id',$sales_team_id)->pluck('team_members','id');
            $sales_team_members = $sales_team_members[$sales_team_id];
            $sales_team_members = $sales_team->merge($sales_team_members);
            $mainStaff = User::whereIn('id',$sales_team_members)->get()->pluck('full_name','id')->prepend(trans('salesteam.main_staff'),'');
        }else{
            $mainStaff = $this->userRepository->getStaff()
                ->pluck('full_name', 'id')->prepend(trans('salesteam.main_staff'),'');
        }
        $title = trans('meeting.edit');
        $this->generateParams();
        $customers=explode(',',$meeting->company_attendees);
        $company_attendee = User::whereIn('id',$customers)->pluck('id','id')->all();
        $staff_attendees = explode(',', $meeting->staff_attendees);
        $staff_attendees = User::whereIn('id', $staff_attendees)->pluck('id', 'id')->all();
        return view('user.meeting.create', compact('title', 'meeting', 'opportunity','staff_attendees','company_attendee','mainStaff'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param MeetingRequest $request
     * @param  Meeting $meeting
     * @return \Illuminate\Http\Response
     * @internal param $
     */
    public function update(MeetingRequest $request, Meeting $meeting)
    {
        $company_attendees = implode(',',$request->company_attendees);
        if(isset($request->staff_attendees)){
            $staff_attendees = implode(',',$request->staff_attendees);
            $request->merge(['staff_attendees'=>$staff_attendees]);
        }
        $request->merge(['company_attendees'=>$company_attendees,'staff_attendees'=>$request->staff_attendees]);
        $meeting->all_day = ($request->all_day) ? $request->all_day : 0;
        $meeting->update($request->all());
        return redirect("meeting");
    }


    public function delete(Meeting $meeting)
    {
        $title = trans('meeting.delete');
        $user = User::all();
        return view('user.meeting.delete', compact('title', 'meeting','user'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Meeting $meeting
     * @return \Illuminate\Http\Response
     */
    public function destroy(Meeting $meeting)
    {
        $meeting->delete();
        return redirect('meeting');
    }

    public function data(Datatables $datatables)
    {
        $meetings = $this->meetingRepository->getAll()
            ->with('responsible')
            ->get()
            ->filter(function ($meeting) {
                return ($meeting->privacy=='Everyone' ||
                        ($meeting->privacy=='Only me' && $meeting->responsible_id==$this->user->id)
                        || $meeting->user_id == Sentinel::getUser()->id);
            })
            ->map(function ($meeting) {
                return [
                    'id' => $meeting->id,
                    'meeting_subject' => $meeting->meeting_subject,
                    'starting_date' => $meeting->starting_date,
                    'ending_date' => $meeting->ending_date,
                    'responsible' => isset($meeting->responsible) ? $meeting->responsible->full_name : '',
                ];
            });
        return $datatables->collection($meetings)
            ->addColumn('actions', ' @if(Sentinel::getUser()->hasAccess([\'meetings.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'meeting/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}" >
                                            <i class="fa fa-fw fa-pencil text-warning"></i>  </a>
                                     @endif
                                     @if(Sentinel::getUser()->hasAccess([\'meetings.delete\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'meeting/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                     @endif')
            ->removeColumn('id')
            ->escapeColumns( [ 'actions' ] )->make();
    }

    private function generateParams()
    {
        $companies = $this->companyRepository->getAll()->orderBy("name", "asc")->pluck('name', 'id');

        $staffs = $this->userRepository->getStaff()
	            ->pluck('full_name', 'id')->prepend(trans('salesteam.main_staff'),'');

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

        $customers = Customer::all()->pluck('user_id','id');
        $company_customer = [];
        foreach ($customers as $customer){
            $company_customer[]=$customer;
        }
        $company_attendees = User::whereIn('id',$company_customer)->get()->pluck('full_name','id');

        view()->share('privacy', $privacy);
        view()->share('show_times', $show_times);
        view()->share('staffs', $staffs);
        view()->share('companies', $companies);
        view()->share('company_attendees',$company_attendees);
    }

    public function calendar()
    {
        $title = trans('meeting.meetings');
        return view('user.meeting.calendar', compact('title', 'opportunity'));
    }

    public function calendar_data()
    {
        $events = array();
        $meetings = $this->meetingRepository->getAll()
            ->with('responsible')
            ->latest()->get()
            ->filter(function ($meeting) {
                return ($meeting->privacy=='Everyone' ||
                        ($meeting->privacy=='Main Staff' && $meeting->responsible_id==$this->user->id)
                        || $meeting->user_id == Sentinel::getUser()->id);
            })
            ->map(function ($meeting) {
                return [
                    'id' => $meeting->id,
                    'title' => $meeting->meeting_subject,
                    'start_date' => $meeting->starting_date,
                    'end_date' => $meeting->ending_date,
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
