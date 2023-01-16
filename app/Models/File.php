<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'size',
        'folder_id'
    ];

    public function folder(): HasOne
    {
        return $this->hasOne(Folder::class, 'id', 'folder_id');
    }

    public function getPathAttribute(): string
    {
        return $this->folder->path . $this->name;
    }
}
