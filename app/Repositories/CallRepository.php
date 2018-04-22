<?php namespace App\Repositories;


interface CallRepository {
  public function getAll();

  public function getAllLeads();

  public function getAllOppotunity();

  public function create(array $data);
}