<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExplorerResource;
use App\Models\Folder;
use App\Services\StorageService\StorageService;
use Illuminate\Http\Request;

class ExplorerController extends Controller
{
    public function __construct(protected StorageService $service)
    {
    }

    public function index(): ExplorerResource
    {
        $customer = auth()->user()->customer;
        $rootFolder = $this->service->findOrCreateRootFolder($customer->id);
        return ExplorerResource::make($rootFolder);
    }

    public function show(Folder $folder): ExplorerResource
    {
        return ExplorerResource::make($folder);
    }
}
