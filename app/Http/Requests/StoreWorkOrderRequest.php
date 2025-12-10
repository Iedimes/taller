<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkOrderRequest extends FormRequest
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
            'vehicle_id' => 'required|exists:vehicles,id',
            'entry_date' => 'required|date',
            'estimated_delivery_date' => 'nullable|date|after_or_equal:entry_date',
            'description' => 'required|string|min:10|max:1000',
            'labor_cost' => 'nullable|numeric|min:0|max:999999.99',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'vehicle_id.required' => 'El vehículo es obligatorio',
            'vehicle_id.exists' => 'El vehículo seleccionado no existe',
            'entry_date.required' => 'La fecha de ingreso es obligatoria',
            'entry_date.date' => 'La fecha de ingreso debe ser una fecha válida',
            'estimated_delivery_date.date' => 'La fecha estimada debe ser una fecha válida',
            'estimated_delivery_date.after_or_equal' => 'La fecha estimada debe ser igual o posterior a la fecha de ingreso',
            'description.required' => 'La descripción es obligatoria',
            'description.min' => 'La descripción debe tener al menos 10 caracteres',
            'description.max' => 'La descripción no puede exceder 1000 caracteres',
            'labor_cost.numeric' => 'El costo de mano de obra debe ser un número',
            'labor_cost.min' => 'El costo de mano de obra no puede ser negativo',
            'labor_cost.max' => 'El costo de mano de obra es demasiado alto',
            'notes.max' => 'Las notas no pueden exceder 2000 caracteres',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'vehicle_id' => 'vehículo',
            'entry_date' => 'fecha de ingreso',
            'estimated_delivery_date' => 'fecha estimada de entrega',
            'description' => 'descripción',
            'labor_cost' => 'costo de mano de obra',
            'notes' => 'notas',
        ];
    }
}
