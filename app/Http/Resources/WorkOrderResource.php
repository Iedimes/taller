<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'vehicle' => [
                'id' => $this->vehicle->id,
                'plate' => $this->vehicle->plate,
                'brand' => $this->vehicle->brand,
                'model' => $this->vehicle->model,
                'year' => $this->vehicle->year,
                'mileage' => $this->vehicle->mileage, // Agregado
                'customer' => [
                    'id' => $this->vehicle->customer->id,
                    'name' => $this->vehicle->customer->name,
                    'document' => $this->vehicle->customer->document, // RUC
                    'address' => $this->vehicle->customer->address,
                    'phone' => $this->vehicle->customer->phone,
                    'email' => $this->vehicle->customer->email,
                ],
            ],
            'dates' => [
                'entry' => $this->entry_date->format('Y-m-d'),
                'estimated_delivery' => $this->estimated_delivery_date?->format('Y-m-d'),
                'actual_delivery' => $this->actual_delivery_date?->format('Y-m-d'),
            ],
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'description' => $this->description,
            'costs' => [
                'labor' => (float) $this->labor_cost,
                'parts' => (float) $this->parts_cost,
                'total' => (float) $this->total_cost,
            ],
            'prices' => [
                'total' => (float) $this->total_price,
            ],
            'profit' => [
                'amount' => (float) $this->profit,
                'percentage' => $this->total_cost > 0
                    ? round(($this->profit / $this->total_cost) * 100, 2)
                    : 0,
            ],
            'parts' => $this->whenLoaded('workOrderParts', function () {
                return $this->workOrderParts->map(function ($wop) {
                    return [
                        'id' => $wop->id,
                        'part' => [
                            'id' => $wop->part->id,
                            'code' => $wop->part->code,
                            'name' => $wop->part->name,
                        ],
                        'quantity' => $wop->quantity,
                        'unit_cost' => (float) $wop->unit_cost,
                        'unit_price' => (float) $wop->unit_price,
                        'subtotal_cost' => (float) $wop->subtotal_cost,
                        'subtotal_price' => (float) $wop->subtotal_price,
                    ];
                });
            }),
            'services' => $this->whenLoaded('services', function () {
                return $this->services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'description' => $service->description,
                        'cost' => (float) $service->cost,
                        'price' => (float) $service->price,
                    ];
                });
            }),
            'notes' => $this->notes,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get status label in Spanish
     */
    private function getStatusLabel()
    {
        $labels = [
            'pending' => 'Pendiente',
            'in_progress' => 'En Progreso',
            'completed' => 'Completado',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
        ];

        return $labels[$this->status] ?? $this->status;
    }
}
