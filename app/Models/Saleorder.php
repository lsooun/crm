<?php

namespace App\Models;

use App\Scopes\SalesOrderScope;
use App\Scopes\SalesOrderTrait;
use Carbon\Carbon;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;

class Saleorder extends Model
{
    use SoftDeletes,RevisionableTrait, SalesOrderTrait;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'sales_orders';

    public function date_format()
    {
        return Settings::get('date_format');
    }

    public function products()
    {
        return $this->hasMany(SaleorderProduct::class, 'order_id');
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

    public function salesPerson()
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function salesTeam()
    {
        return $this->belongsTo(Salesteam::class, 'sales_team_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new SalesOrderScope);
    }
}
