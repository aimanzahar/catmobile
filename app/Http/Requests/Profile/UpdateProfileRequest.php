<?php

namespace App\Http\Requests\Profile;

use App\Support\NativeUploadedFileResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'avatar_native_path' => ['nullable', 'string', 'max:1024'],
        ];
    }

    protected function passedValidation(): void
    {
        $native = NativeUploadedFileResolver::resolveFromRequest($this, 'avatar_native_path');
        if ($native !== null) {
            Log::info('[UpdateProfileRequest] adopting native picker file', [
                'name' => $native->getClientOriginalName(),
                'size' => $native->getSize(),
            ]);
            $this->files->set('avatar', $native);
            $this->merge(['avatar' => $native]);
        }
    }
}
