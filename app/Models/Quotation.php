<?php

namespace App\Models;

use App\Scopes\QuotationScope;
use App\Scopes\QuotationTrait;
use Carbon\Carbon;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;

class Quotation extends Model
{
    use SoftDeletes,RevisionableTrait,QuotationTrait;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'quotations';

    public function date_format()
    {
        return Settings::get('date_format');
    }

    public function products()
    {
        return $this->hasMany(QuotationProduct::class, 'quotation_id');
    }

    public function setDateAttribute($date)
    {
        $this->attributes['date'] = Carbon::createFromFormat($this->date_format(),$date)->format('Y-m-d');
    }

    public function getDateAttribute($date)
    {
        if ($date == "0000-00-00" || $date == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($date));
        }
    }
    public function setExpDateAttribute($exp_date)
    {
        $this->attributes['exp_date'] = Carbon::createFromFormat($this->date_format(),$exp_date)->format('Y-m-d');
    }

    public function getExpDateAttribute($exp_date)
    {
        if ($exp_date == "0000-00-00" || $exp_date == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($exp_date));
        }
    }


    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function salesPerson()
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function salesTeam()
    {
        return $this->belongsTo(Salesteam::class, 'sales_team_id');
    }

    public function quotationCustomer(){
        return $this->belongsTo(Customer::class,'customer_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new QuotationScope);
    }
}
