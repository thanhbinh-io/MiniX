<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTweetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tweet' => ['required_without:file', 'nullable', 'string', 'max:280'],
            'file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,mp4', 'max:51200'],
            'parent_id' => ['nullable', 'exists:tweets,id'],
            'quote_of_id' => ['nullable', 'exists:tweets,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'tweet.required_without' => 'Please provide some text or a media file for your tweet.',
            'tweet.max' => 'Your tweet cannot exceed 280 characters.',
            'file.mimes' => 'The file must be an image (jpg, jpeg, png) or video (mp4).',
            'file.max' => 'The uploaded file must not be larger than 50MB.',
        ];
    }
}
