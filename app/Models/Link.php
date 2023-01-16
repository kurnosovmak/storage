<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Link extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'token',
        'file_id',
    ];

    public function file(): HasOne
    {
        return $this->hasOne(File::class, 'id', 'file_id');
    }
}
