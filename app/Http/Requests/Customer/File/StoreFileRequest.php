<?php

namespace App\Http\Requests\Customer\File;

use App\Rules\FileExceptMime;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'files.*'=> ['required', 'file', 'max:20480', new FileExceptMime(['php'])],
            'folder_id' => 'exists:folders,id'
        ];
    }
}
