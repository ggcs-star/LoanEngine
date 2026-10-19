<?php

namespace LoanEngine\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) config('loanengine-auth.registration_enabled');
    }

    public function rules(): array
    {
        $model = config('auth.providers.users.model');
        $table = (new $model())->getTable();
        $min = (int) config('loanengine-auth.password_min_length', 10);
        $nameMax = (int) config('loanengine-auth.name_max_length', 120);

        return [
            'name' => ['required', 'string', 'min:2', "max:{$nameMax}"],
            'email' => ['required', 'string', 'email:rfc', 'max:254', Rule::unique($table, 'email')],
            'password' => ['required', 'string', 'confirmed', Password::min($min)->letters()->mixedCase()->numbers()],
            'terms' => ['accepted'],
        ];
    }
}
