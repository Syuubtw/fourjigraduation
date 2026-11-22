<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGalleryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_name'      => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255', 'unique:galleries,slug', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'pin'              => ['nullable', 'string', 'max:10'],
            'gdrive_folder_id' => ['required', 'string', 'max:255'],
            'max_select'       => ['required', 'integer', 'min:1', 'max:500'],
            'status'           => ['required', 'in:draft,open,submitted,completed'],
            'auto_sync'        => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'client_name.required'      => 'Nama klien / wisudawan wajib diisi.',
            'slug.unique'               => 'Slug URL ini sudah digunakan untuk sesi galeri lain.',
            'slug.regex'                => 'Format slug hanya boleh berupa huruf kecil, angka, dan tanda hubung (-).',
            'gdrive_folder_id.required' => 'Google Drive Folder ID wajib diisi.',
            'max_select.required'       => 'Batas kuota foto wajib ditentukan.',
            'max_select.min'            => 'Batas kuota foto minimal 1.',
        ];
    }
}
