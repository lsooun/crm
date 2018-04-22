<?php namespace App\Repositories;


interface MeetingRepository
{
    public function getAll();

    public function create(array $data);
}