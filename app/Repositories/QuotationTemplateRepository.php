<?php namespace App\Repositories;

interface QuotationTemplateRepository
{
    public function getAll();

    public function create(array $data);
}