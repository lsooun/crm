<?php

namespace App\Repositories;


interface InviteUserRepository
{
    public function getAll();

    public function create(array $data);
}