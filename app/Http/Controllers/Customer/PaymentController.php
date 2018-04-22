<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\UserController;
use App\Http\Requests;
use App\Http\Requests\PayRequest;
use App\Models\Invoice;
use App\Models\InvoiceReceivePayment;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Omnipay\Omnipay;
use Sentinel;
use Datatables;
use Session;
use DB;

class PaymentController extends UserController
{
    public $invoice;
    public function __construct()
    {
        parent::__construct();

        $payment_method = array("paypal" => "Paypal", "stripe" => "Stripe");
        view()->share('payment_method', $payment_method);

        view()->share('type', 'payment');
    }

    public function pay(Invoice $invoice)
    {
        $title = trans('payment.pay_invoice');
        return view('customers/payment.pay', compact('title','invoice'));
    }

    public function paypal(PayRequest $request,Invoice $invoice)
    {
        $params = array(
            'cancelUrl' => url('customers/payment/'.$invoice->id.'/paypal_cancel'),
            'returnUrl' => url('customers/payment/'.$invoice->id.'/paypal_success'),
            'name' => $invoice->invoice_number,
            'description' => $invoice->invoice_number .' - '.
            (Settings::get('currency_position')=='left')?
                Settings::get('currency').$invoice->unpaid_amount:
                $invoice->unpaid_amount.' '.Settings::get('currency'),
            'amount' => $invoice->unpaid_amount,
            'currency' => Settings::get('currency')
        );
        Session::put('params', $params);
        Session::save();

        $gateway = Omnipay::create('PayPal_Express');
        $gateway->setUsername(Settings::get('paypal_username'));
        $gateway->setPassword(Settings::get('paypal_password'));
        $gateway->setSignature(Settings::get('paypal_signature'));
        $gateway->setTestMode(Settings::get('paypal_testmode'));

        $response = $gateway->purchase($params)->send();

        if ($response->isSuccessful()) {
            // payment was successful: update database
        } elseif ($response->isRedirect()) {
            // redirect to offsite payment gateway
            $response->redirect();
        } else {
            // payment failed: display message to customer
            echo $response->getMessage();
        }
    }

    public function paypalSuccess(Invoice $invoice)
    {
        $gateway = Omnipay::create('PayPal_Express');
        $gateway->setUsername(Settings::get('paypal_username'));
        $gateway->setPassword(Settings::get('paypal_password'));
        $gateway->setSignature(Settings::get('paypal_signature'));
        $gateway->setTestMode(Settings::get('paypal_testmode'));

        $params = Session::get('params');

        $response = $gateway->completePurchase($params)->send();
        $paypalResponse = $response->getData();

        if (isset($paypalResponse['PAYMENTINFO_0_ACK']) && $paypalResponse['PAYMENTINFO_0_ACK'] === 'Success') {

            $total_fields = InvoiceReceivePayment::orderBy('id', 'desc')->first();
            $start_number = Settings::get('invoice_payment_start_number');
            $quotation_no = Settings::get('invoice_payment_prefix') . (is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields->id : 0) + 1);

            $invoiceRepository = new InvoiceReceivePayment();
            $invoiceRepository->invoice_id = $invoice->id;
            $invoiceRepository->payment_date = $paypalResponse['TIMESTAMP'];
            $invoiceRepository->payment_method = "Pay pal";
            $invoiceRepository->payment_received = $invoice->unpaid_amount;
            $invoiceRepository->payment_number = $quotation_no;
            $invoiceRepository->paykey = $paypalResponse['TOKEN'];
            $invoiceRepository->user_id = $this->user->id;
            $invoiceRepository->save();

            $unpaid_amount_new = bcsub($invoice->unpaid_amount, $invoiceRepository->payment_received, 2);

            if ($unpaid_amount_new <= '0') {
                $invoice_data = array(
                    'unpaid_amount' => $unpaid_amount_new,
                    'status' => 'Paid Invoice',
                );
            } else {                $invoice_data = array(
                    'unpaid_amount' => $unpaid_amount_new,
                );
            }

            $invoice->update($invoice_data);

            return redirect('customers/payment/success');

        } else {
            $title = trans('payment.error');
        }
        return view('customers.payment.result', compact('paypalResponse', 'title'));
    }

    public function stripe(Request $request,Invoice $invoice)
    {
        $total_fields = InvoiceReceivePayment::orderBy('id', 'desc')->first();
        $start_number =Settings::get('invoice_payment_start_number');
        $quotation_no = Settings::get('invoice_payment_prefix') . (is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields->id : 0) + 1);
        $invoiceRepository = new InvoiceReceivePayment();
        $invoiceRepository->invoice_id = $invoice->id;
        $invoiceRepository->payment_date = date(Settings::get('date_format'));
        $invoiceRepository->payment_method = "Stripe";
        $invoiceRepository->payment_received = $invoice->unpaid_amount;
        $invoiceRepository->payment_number = $quotation_no;
        $invoiceRepository->paykey = $request->stripeToken;
        $invoiceRepository->user_id = $this->user->id;
        $invoiceRepository->customer_id = $this->user->id;
        $invoiceRepository->save();
        $unpaid_amount_new = $invoice->unpaid_amount - $invoiceRepository->payment_received;
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
        $stripe_secret_key = Settings::get('stripe_secret');
        \Stripe\Stripe::setApiKey($stripe_secret_key);

        $token = $request->stripeToken;
        $charge = \Stripe\Charge::create(array(
            "amount" => $invoice->unpaid_amount*100,
            "currency" => "usd",
            "description" => "Example charge",
            "source" => $token,
        ));

        $invoice->update($invoice_data);

        return redirect('customers/payment/success');
    }

    public function success()
    {
        $title = trans('payment.payment_finish');
        return view('customers.payment.success', compact('title'));
    }

    public function cancel()
    {
        return redirect('customers');
    }


}
