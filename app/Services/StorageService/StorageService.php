<?php

namespace App\Services\StorageService;

use App\Models\Customer;
use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StorageService
{
    public const DEFAULT_FOLDER = '/data/';
    public const MAX_SIZE_ROOT = 1000 * 1000 * 100; // 100mb

    public function __construct(protected Customer $customer, protected Folder $folder, protected File $file)
    {

    }

    /**
     * @param int $customerId
     * @return Folder
     * @throws Exception
     */
    public function findOrCreateRootFolder(int $customerId): Folder
    {
        DB::beginTransaction();
        try {
            /** @var Folder $folder */
            $folder = $this->folder->query()->firstOrCreate([
                'name' => $customerId,
                'customer_id' => $customerId,
                'folder_id' => null,
            ]);

            $path = $this->getPath($customerId);
            if (!Storage::exists($path)) {
                Storage::makeDirectory($path);
            }
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
        return $folder;
    }

    /**
     * @param string $name
     * @param int $parentFolderId
     * @return Folder
     * @throws Exception
     */
    public function createFolder(string $name, int $parentFolderId): Folder
    {
        if ($this->folder->query()->where('folder_id', $parentFolderId)->where('name', $name)->first()) {
            throw new Exception(__('storage.folder.exists'));
        }
        $parentFolder = $this->folder->query()->findOrFail($parentFolderId);
        DB::beginTransaction();
        try {
            $folder = $this->folder->create([
                'name' => $name,
                'customer_id' => $parentFolder->customer_id,
                'folder_id' => $parentFolder->id,
            ]);

            $path = $this->getPath($folder->path);
            if (!Storage::exists($path)) {
                Storage::makeDirectory($path);
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }


        return $folder;
    }

    /**
     * @param string $name
     * @param int $idFolder
     * @return Folder
     * @throws Exception
     */
    public function renameFolder(string $name, int $idFolder): Folder
    {
        /** @var Customer $customer */
        $customer = auth()->user()->customer;
        // Find folder
        /** @var Folder $folder */
        if (!$folder = $this->folder->query()->where('customer_id', $customer->id)->find($idFolder)) {
            throw new Exception(__('storage.folder.notfund'));
        }
        if ($folder->isRoot()) {
            throw new Exception(__('storage.folder.rootfolder'));
        }
        // Check folder with new name is exists
        if ($this->folder->query()->where('folder_id', $folder->folder_id)->where('name', $name)->first()) {
            throw new Exception(__('storage.folder.exists'));
        }
        DB::beginTransaction();
        try {
            $path = $this->getPath($folder->path);
            $folder->name = $name;
            $folder->save();
            $newPath = $this->getPath($folder->path);
            if (!Storage::exists($newPath)) {
                Storage::move($path, $newPath);
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
        return $folder;
    }

    /**
     * @param int $idFolder
     * @throws Exception
     */
    public function deleteFolder(int $idFolder): void
    {
        /** @var Customer $customer */
        $customer = auth()->user()->customer;
        // Find folder
        if (!$folder = $this->folder->query()->where('customer_id', $customer->id)->find($idFolder)) {
            throw new Exception(__('storage.folder.notfund'));
        }

        if ($folder->isRoot()) {
            throw new Exception(__('storage.folder.rootfolder'));
        }

        DB::beginTransaction();
        try {
            $path = $folder->path;
            $this->deleteFolderInDb($folder);

            Storage::delete($path);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param Folder $folder
     */
    private function deleteFolderInDb(Folder $folder)
    {
        if (count($folder->childrenFolders) > 0) {
            foreach ($folder->childrenFolders as $folder) {
                $this->deleteFolderInDb($folder);
            }
        }
        $folder->files()->delete();
        $folder->delete();
    }

    /**
     * @param string $filePath
     * @return string
     */
    public function getPath(string $filePath): string
    {
        return self::DEFAULT_FOLDER . $filePath;
    }

    /**
     * @param $files
     * @param int $folderId
     * @return Collection
     * @throws Exception
     */
    public function createFiles($files, int $folderId): Collection
    {
        $folder = $this->folder->query()->findOrFail($folderId);
        $rootFolder = $this->findOrCreateRootFolder($folder->customer_id);

        $newSize = $this->getSizeFolder($rootFolder);
        foreach ($files as $file) {
            $newSize += $file->getSize();
        }

        if ($newSize > self::MAX_SIZE_ROOT) {
            throw new Exception(__('storage.file.size'));
        }

        $rez = collect();
        foreach ($files as $file) {
            $rez->push($this->createFile($file, $folderId));
        }
        return $rez;
    }

    /**
     * @param int $fileId
     * @param bool $ignoreCustomer
     * @return string
     * @throws Exception
     */
    public function downloadFile(int $fileId, bool $ignoreCustomer = false): string
    {
        $file = $this->file->query()->findOrFail($fileId);

        if (!$ignoreCustomer && $file->folder->customer_id !== auth()->user()->customer->id) {
            throw new Exception(__('storage.file.guard'));
        }
        return Storage::path($this->getPath($file->path));
    }

    /**
     * @param $file
     * @param int $folderId
     * @return File
     * @throws Exception
     */
    public function createFile($file, int $folderId): File
    {
        $folder = $this->folder->query()->findOrFail($folderId);
        $rootFolder = $this->findOrCreateRootFolder($folder->customer_id);

        $newSize = $this->getSizeFolder($rootFolder) + $file->getSize();

        if ($newSize > self::MAX_SIZE_ROOT) {
            throw new Exception(__('storage.file.size'));
        }

        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $newName = $name . '.' . $extension;
        $i = 1;
        while ($this->file->query()->where('name', $newName)->where('folder_id', $folderId)->first()) {
            $newName = $name . ' (' . $i . ').' . $extension;
            $i++;
        }
        DB::beginTransaction();
        try {
            /** @var File $f */
            $f = $this->file->query()->create([
                'name' => $newName,
                'size' => $file->getSize(),
                'folder_id' => $folderId,
            ]);
            Storage::put($this->getPath($f->path), file_get_contents($file));

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
        return $f;
    }

    /**
     * @param string $name
     * @param int $fileId
     * @return File
     * @throws Exception
     */
    public function renameFile(string $name, int $fileId): File
    {
        $file = $this->file->query()->findOrFail($fileId);
        if ($this->file->where('name', $name)->where('folder_id', $file->folder_id)->first()) {
            throw new Exception(__('storage.exists'));
        }
        DB::beginTransaction();
        try {
            $path = $this->getPath($file->path);
            $file->name = $name;
            $file->save();
            Log::info($path);
            Log::info($file->path);
            Storage::move($path, $this->getPath($file->path));

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
        return $file;
    }

    /**
     * @param int $fileId
     * @throws Exception
     */
    public function deleteFile(int $fileId): void
    {
        DB::beginTransaction();
        try {
            $file = $this->file->query()->findOrFail($fileId);
            $path = $file->path;
            $file->delete();

            Storage::delete($this->getPath($path));

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param Folder $folder
     * @return int
     */
    public function getSizeFolder(Folder $folder): int
    {
        $size = 0;
        foreach ($folder->childrenFolders as $f) {

            $size += $this->getSizeFolder($f);
        }

        $size += $folder->files()->sum('size');
        return $size;
    }

}
