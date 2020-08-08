<?php

namespace App\Http\Requests;

use App\Support\Deployer;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class DeployRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'environment' => [
                'required',
                $this->validateEnvironment(),
            ],
        ];
    }

    private function validateEnvironment(): Closure
    {
        return function ($attribute, $value, $fail) {
            if ($value == '' || $value == 'null') {
                return $fail($attribute.' is invalid.');
            }
            $sources = Deployer::getSources();
            $scripts = Deployer::getScripts();
            if (! $sources->has($value)) {
                return $fail($attribute.' is invalid.');
            }
            if (! $scripts->has($value)) {
                return $fail($attribute.' has no valid script.');
            }

            return true;
        };
    }
}
