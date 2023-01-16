<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\File\StoreFileRequest;
use App\Http\Requests\Customer\File\UpdateFileRequest;
use App\Http\Resources\Customer\FileResource;
use App\Http\Resources\Customer\LinkFileResource;
use App\Http\Resources\Message\ErrorMessageResource;
use App\Models\File;
use App\Models\Folder;
use App\Models\Link;
use App\Services\StorageService\StorageService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileController extends Controller
{
    public function __construct(protected StorageService $service)
    {
    }

    public function store(StoreFileRequest $request): AnonymousResourceCollection|ErrorMessageResource
    {
        try {
            $customerId = auth()->user()->customer->id;
            $rootFolder = $this->service->findOrCreateRootFolder($customerId);
            $f = $this->service->createFiles($request->file('files'), $request->folder_id ?? $rootFolder->id);
        } catch (Exception $exception) {
            return ErrorMessageResource::make($exception->getMessage());
        }
        return FileResource::collection($f);
    }

    public function publicDownload(string $token): BinaryFileResponse|ErrorMessageResource
    {
        $link = Link::query()->where('token', $token)->first();
        if (!$link) {
            return ErrorMessageResource::make(__('storage.folder.notfund'));
        }
        try {
            $file = $link->file;
            $fullPathToFile = $this->service->downloadFile($file->id, true);
        } catch (Exception $exception) {
            return ErrorMessageResource::make($exception->getMessage());
        }
        return response()->download($fullPathToFile, $file->name);

    }

    public function public(File $file): LinkFileResource
    {
        $link = Link::query()->firstOrCreate([
            'file_id' => $file->id,
        ], ['token' => Str::random(16)]);
        return LinkFileResource::make($link);
    }

    public function private(File $file): JsonResponse
    {
        Link::query()->where('file_id', $file->id)->delete();
        return response()->json(null, 204);
    }

    public function download(File $file): BinaryFileResponse|ErrorMessageResource
    {
        try {
            $fullPathToFile = $this->service->downloadFile($file->id);
        } catch (Exception $exception) {
            return ErrorMessageResource::make($exception->getMessage());
        }
        return response()->download($fullPathToFile, $file->name);
    }

    public function update(UpdateFileRequest $request, File $file): FileResource|ErrorMessageResource
    {
        try {
            $f = $this->service->renameFile($request->name, $file->id);
        } catch (Exception $exception) {
            return ErrorMessageResource::make($exception->getMessage());
        }
        return FileResource::make($f);
    }

    public function delete(File $file): JsonResponse|ErrorMessageResource
    {
        try {
            $this->service->deleteFile($file->id);
        } catch (Exception $exception) {
            return ErrorMessageResource::make($exception->getMessage());
        }
        return response()->json(null, 204);
    }
}
