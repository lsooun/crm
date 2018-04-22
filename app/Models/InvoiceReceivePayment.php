<?php

namespace App\Models;

use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceReceivePayment extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'invoice_receive_payments';

    public function date_format()
    {
        return Settings::get('date_format');
    }

    public function invoice()
    {
        return $this->belongsTo(InvoicePayment::class, 'invoice_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function setPaymentDateAttribute($payment_date)
    {
        if ($payment_date) {
            $this->attributes['payment_date'] = date('Y-m-d H:i',strtotime($payment_date));
        } else {
            $this->attributes['payment_date'] = date('Y-m-d H:i');
        }
    }

    public function getPaymentDateAttribute($payment_date)
    {
        if ($payment_date == "0000-00-00 00:00" || $payment_date == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($payment_date));
        }
    }

    public function customer()
    {
        return $this->belongsTo(User::class);
    }
}
