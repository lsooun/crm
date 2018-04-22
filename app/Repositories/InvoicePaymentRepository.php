<?php namespace App\Repositories;


interface InvoicePaymentRepository
{
    public function getAll();
    public function getAllForCustomer($customer_id);
}