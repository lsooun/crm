<?php

namespace App\Models;

use Carbon\Carbon;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class InvoicePayment extends Model
{
    use SoftDeletes, RevisionableTrait;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'invoices';

    public function date_format()
    {
        return Settings::get('date_format');
    }
    public function setInvoiceDateAttribute($invoice_date)
    {
        $this->attributes['invoice_date'] = Carbon::createFromFormat($this->date_format(),$invoice_date)->format('Y-m-d');
    }

    public function getInvoiceDateAttribute($invoice_date)
    {
        if ($invoice_date == "0000-00-00" || $invoice_date == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($invoice_date));
        }
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }


    public function salesPerson()
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function salesTeam()
    {
        return $this->belongsTo(Salesteam::class, 'sales_team_id');
    }
}
