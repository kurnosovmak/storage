<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Folder\StoreFolderRequest;
use App\Http\Requests\Customer\Folder\UpdateFolderRequest;
use App\Http\Resources\Customer\FolderResource;
use App\Http\Resources\Customer\FullFolderResource;
use App\Http\Resources\Message\ErrorMessageResource;
use App\Models\Folder;
use App\Services\StorageService\StorageService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function __construct(protected StorageService $service)
    {
    }

    public function store(StoreFolderRequest $request): FolderResource|ErrorMessageResource
    {
        try {
            $customerId = auth()->user()->customer->id;
            $rootFolder = $this->service->findOrCreateRootFolder($customerId);
            $folder = $this->service->createFolder($request->name, $rootFolder->id);
        } catch (Exception $exception) {
            return ErrorMessageResource::make($exception->getMessage());
        }
        return FolderResource::make($folder);
    }
    public function showRoot()
    {
        $customerId = auth()->user()->customer->id;
        $folder = $this->service->findOrCreateRootFolder($customerId);
        $folder->size = $this->service->getSizeFolder($folder);
        return FullFolderResource::make($folder);
    }

    public function show(Folder $folder)
    {
        $folder->size = $this->service->getSizeFolder($folder);
        return FullFolderResource::make($folder);
    }

    public function update(UpdateFolderRequest $request, Folder $folder): FolderResource|ErrorMessageResource
    {
        try {
            $f = $this->service->renameFolder($request->name, $folder->id);
        } catch (Exception $exception) {
            return ErrorMessageResource::make($exception->getMessage());
        }
        return FolderResource::make($f);
    }

    public function delete(Folder $folder): JsonResponse|ErrorMessageResource
    {
        try {
            $this->service->deleteFolder($folder->id);
        } catch (Exception $exception) {
            return ErrorMessageResource::make($exception->getMessage());
        }
        return response()->json(null, 204);
    }
}
