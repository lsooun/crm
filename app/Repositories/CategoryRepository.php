<?php namespace App\Repositories;

interface CategoryRepository
{
    public function getAll();

    public function create(array $data);
}