<?php

namespace App\Repositories;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ProductRepository
{
    public function getAll();

    public function create(array $data);

    public function uploadProductImage(UploadedFile $file);
}