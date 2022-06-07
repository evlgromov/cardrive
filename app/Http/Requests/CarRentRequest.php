<?php

namespace App\Http\Requests;

use App\Rules\CarUnique;
use App\Rules\UserUnique;
use Illuminate\Foundation\Http\FormRequest;

class CarRentRequest extends FormRequest
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
     * Здесь задаём человеческие имена для наших полей для сообщений валидации
     * @return string[]
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'Пользователь',
            'car_id' => 'Автомобиль',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => ['bail', 'required', 'numeric', new UserUnique()],
            'car_id' => ['bail', 'required', 'numeric', new CarUnique()],
        ];
    }

    public function messages()
    {
        return [
            '*.numeric' => 'Поле :attribute должно быть числом',
            '*.required' => 'Поле :attribute обязательное для заполнения',
        ];
    }
}
