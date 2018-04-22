<?php

namespace App\Repositories;

interface EmailTemplateRepository
{
    public function getAll();

    public function create(array $data);
}