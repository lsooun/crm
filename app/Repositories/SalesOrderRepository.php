<?php namespace App\Repositories;

interface SalesOrderRepository
{
    public function getAll();
    public function getAllToday();
    public function getAllYesterday();
    public function getAllWeek();
    public function getAllMonth();
    public function getAllForCustomer($customer_id);
}