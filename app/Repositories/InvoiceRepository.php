<?php

namespace App\Repositories;

interface InvoiceRepository
{
    public function getAll();

    public function create(array $data);

    public function getAllOpen();

    public function getAllOverdue();

    public function getAllPaid();

    public function getAllForCustomer($customer_id);

    public function getAllOpenForCustomer($customer_id);

    public function getAllOverdueForCustomer($customer_id);

    public function getAllPaidForCustomer($customer_id);

    public function getAllOpenMonth();

    public function getAllOverdueMonth();

    public function getAllPaidMonth();


}