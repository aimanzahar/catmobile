<?php

namespace App\Http\Requests\Pet;

use App\Support\NativeUploadedFileResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StorePetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'breed' => ['nullable', 'string', 'max:255'],
            'age' => ['nullable', 'integer', 'min:0', 'max:99'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'special_notes' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'image_native_path' => ['nullable', 'string', 'max:1024'],
        ];
    }

    protected function passedValidation(): void
    {
        $native = NativeUploadedFileResolver::resolveFromRequest($this, 'image_native_path');
        if ($native !== null) {
            Log::info('[StorePetRequest] adopting native picker file', [
                'name' => $native->getClientOriginalName(),
                'size' => $native->getSize(),
            ]);
            $this->files->set('image', $native);
            $this->merge(['image' => $native]);
        }
    }
}
