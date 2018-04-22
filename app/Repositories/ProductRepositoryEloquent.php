<?php namespace App\Repositories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;
use Sentinel;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductRepositoryEloquent implements ProductRepository
{
    /**
     * @var Product
     */
    private $model;
    /**
     * ProductRepositoryEloquent constructor.
     * @param Product $model
     */
    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
//	    $user = User::find(Sentinel::getUser()->id);
        $models = $this->model;
//        ->whereHas('user', function ($q) use ($user) {
//            $q->where(function ($query) use ($user) {
//                $query
//                    ->orWhere('id', $user->parent->id)
//                    ->orWhere('users.user_id', $user->parent->id);
//            });
//        });
        return $models;
    }

    public function create(array $data)
    {
	    $user = User::find(Sentinel::getUser()->id);
        $model = $user->products()->create($data);
        return $model;
    }

    public function uploadProductImage(UploadedFile $file)
    {
        $destinationPath = public_path() . '/uploads/products/';
        $extension = $file->getClientOriginalExtension();
        $filename = $file->getClientOriginalName();
        $picture = Str::slug(substr($filename, 0, strrpos($filename, "."))) . '_' . time() . '.' . $extension;
        return $file->move($destinationPath, $picture);
    }
}