<?php

namespace Devrahul\Signinupapi\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

class Media extends FormRequest
{

    private $image_ext = ['jpg', 'jpeg', 'png', 'gif'];
    //private $audio_ext = ['mp3', 'ogg', 'mpga'];
    //private $video_ext = ['mp4', 'mpeg'];
    //private $document_ext = ['pdf'];

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
     * @return array
     */
    public function rules()
    {
        $max_size = (int)ini_get('upload_max_filesize') * 1000;
        $all_ext = implode(',', $this->allExtensions());

        return [
            'file' => 'required|file|mimes:' . $all_ext . '|max:' . $max_size
        ];
    }

    /**
     * Overwrite Validation error response
     * 
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $response = new JsonResponse([ 
            'status' => 0, 
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors()
        ], 422);

        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }

    
    /**
     * Get all extensions
     * @return array Extensions of all file types
     */
    private function allExtensions()
    {
        //return array_merge($this->image_ext, $this->audio_ext, $this->video_ext, $this->document_ext);
        return $this->image_ext;
    }
}
