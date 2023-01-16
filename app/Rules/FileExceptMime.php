<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

class FileExceptMime implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(protected array $extensions = ['php'])
    {

    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (in_array($value->getClientOriginalExtension(), $this->extensions)) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.except_mimetypes') . ' ' . $this->getStringExtensions();
    }

    protected function getStringExtensions(): string
    {
        $res = '';
        foreach ($this->extensions as $extension){
            $res .= $extension . ' ';
        }
        return  $res;
    }
}
