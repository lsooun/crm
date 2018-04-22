<?php

namespace App\Models;

use App\Scopes\InvoiceScope;
use App\Scopes\InvoiceTrait;
use Carbon\Carbon;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;

class Invoice extends Model
{
	use SoftDeletes, RevisionableTrait, InvoiceTrait;

	protected $dates = ['deleted_at'];
	protected $guarded = array('id');
	protected $table = 'invoices';

	public function date_format()
	{
		return Settings::get('date_format');
	}

	public function products()
	{
		return $this->hasMany(InvoiceProduct::class, 'invoice_id');
	}

	public function customer()
	{
		return $this->belongsTo(User::class, 'customer_id');
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

	public function setDueDateAttribute($due_date)
	{
		$this->attributes['due_date'] = Carbon::createFromFormat($this->date_format(),$due_date)->format('Y-m-d');
	}

	public function getDueDateAttribute($due_date)
	{
		if ($due_date == "0000-00-00" || $due_date == "") {
			return "";
		} else {
			return date($this->date_format(), strtotime($due_date));
		}
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

	public function receivePayment()
	{
		return $this->hasMany(InvoiceReceivePayment::class, 'invoice_id');
	}

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new InvoiceScope);
    }
}
