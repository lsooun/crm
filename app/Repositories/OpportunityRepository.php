<?php namespace App\Repositories;


interface OpportunityRepository
{
    public function getAll(array $with = []);

    public function create(array $data);

    public function getAllForCustomer($company_id);

    public function getAllForUser($user_id);
}