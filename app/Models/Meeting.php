<?php

namespace App\Models;

use Carbon\Carbon;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;

class Meeting extends Model
{
    use SoftDeletes,RevisionableTrait;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'meetings';

    public function date_time_format()
    {
        return Settings::get('date_format').' '.Settings::get('time_format');
    }

    public function setStartingDateAttribute($starting_date)
    {
        if ($starting_date) {
            $this->attributes['starting_date'] =  Carbon::createFromFormat($this->date_time_format(),$starting_date)->format('Y-m-d H:i');
        } else {
            $this->attributes['starting_date'] = '';
        }
    }

    public function getStartingDateAttribute($starting_date)
    {
        if ($starting_date == "0000-00-00 00:00" || $starting_date == "") {
            return "";
        } else {
            return date($this->date_time_format(), strtotime($starting_date));
        }
    }

    public function setEndingDateAttribute($ending_date)
    {
        if ($ending_date) {
            $this->attributes['ending_date'] = Carbon::createFromFormat($this->date_time_format(),$ending_date)->format('Y-m-d H:i');
        } else {
            $this->attributes['ending_date'] = '';
        }
    }

    public function getEndingDateAttribute($ending_date)
    {
        if ($ending_date == "0000-00-00 00:00" || $ending_date == "") {
            return "";
        } else {
            return date($this->date_time_format(), strtotime($ending_date));
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }
}
