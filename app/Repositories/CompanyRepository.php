<?php namespace App\Repositories;


use Symfony\Component\HttpFoundation\File\UploadedFile;

interface CompanyRepository
{
    public function getAll();

    public function create(array $data);

    public function uploadAvatar(UploadedFile $file);
}