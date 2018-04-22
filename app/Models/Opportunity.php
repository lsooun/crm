<?php

namespace App\Models;

use App\Scopes\OpportunityArchiveScope;
use Carbon\Carbon;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;
use App\Scopes\OpportunityArchiveTrait;

class Opportunity extends Model
{
    use SoftDeletes, CallableTrait, MeetableTrait, RevisionableTrait, OpportunityArchiveTrait;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'opportunities';

    public function date_format()
    {
        return Settings::get('date_format');
    }

    public function setNextActionAttribute($next_action)
    {
        $this->attributes['next_action'] = Carbon::createFromFormat($this->date_format(),$next_action)->format('Y-m-d');
    }

    public function getNextActionAttribute($next_action)
    {
        if ($next_action == "0000-00-00" || $next_action == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($next_action));
        }
    }

    public function setExpectedClosingAttribute($expected_closing)
    {
        $this->attributes['expected_closing'] = Carbon::createFromFormat($this->date_format(),$expected_closing)->format('Y-m-d');
    }

    public function getExpectedClosingAttribute($expected_closing)
    {
        if ($expected_closing == "0000-00-00" || $expected_closing == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($expected_closing));
        }
    }
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
    public function salesTeam()
    {
        return $this->belongsTo(Salesteam::class, 'sales_team_id');
    }

    public function salesPerson()
    {
        return $this->belongsTo(User::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function companies(){
        return $this->belongsTo(Company::class,'company_name');
    }

    public function staffs(){
        return $this->belongsTo(User::class,'salesteam');
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new OpportunityArchiveScope);
    }
}
