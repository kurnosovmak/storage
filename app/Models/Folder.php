<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'customer_id',
        'folder_id',
    ];

    public function isRoot(): bool
    {
        return $this->folder_id === null;
    }

    public function parentFolder(): HasOne
    {
        return $this->hasOne(Folder::class, 'id', 'folder_id');
    }

    public function childrenFolders(): HasMany
    {
        return $this->hasMany(Folder::class, 'folder_id', 'id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'folder_id', 'id');
    }

    public function getPathAttribute(): string
    {
        $folder = $this;
        $path = '';
        while ($folder && !$folder->isRoot()) {
            $path = $folder->name . '/' . $path;
            $folder = $folder->parentFolder;
        }
        // Add name root folder
        $path = $folder->name . '/' . $path;
        return $path;
    }
}
