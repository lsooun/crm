<?php namespace App\Repositories;


interface SalesTeamRepository
{
    public function getAll();

    public function teamLeader();

    public function create(array $data);

    public function getAllQuotations();

    public function getAllLeads();

    public function getAllOpportunities();
}