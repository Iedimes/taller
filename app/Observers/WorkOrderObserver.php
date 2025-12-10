<?php

namespace App\Observers;

use App\Models\WorkOrder;
use Illuminate\Support\Facades\Log;

class WorkOrderObserver
{
    /**
     * Handle the WorkOrder "creating" event.
     *
     * @param  \App\Models\WorkOrder  $workOrder
     * @return void
     */
    public function creating(WorkOrder $workOrder)
    {
        // Generar número de orden si no existe
        if (empty($workOrder->order_number)) {
            $workOrder->order_number = WorkOrder::generateOrderNumber();
        }

        // Establecer valores por defecto
        if (is_null($workOrder->labor_cost)) {
            $workOrder->labor_cost = 0;
        }

        if (is_null($workOrder->status)) {
            $workOrder->status = 'pending';
        }

        Log::info('Nueva orden de trabajo creada', [
            'order_number' => $workOrder->order_number,
            'vehicle_id' => $workOrder->vehicle_id,
        ]);
    }

    /**
     * Handle the WorkOrder "created" event.
     *
     * @param  \App\Models\WorkOrder  $workOrder
     * @return void
     */
    public function created(WorkOrder $workOrder)
    {
        Log::info('Orden de trabajo guardada en BD', [
            'id' => $workOrder->id,
            'order_number' => $workOrder->order_number,
        ]);
    }

    /**
     * Handle the WorkOrder "updated" event.
     *
     * @param  \App\Models\WorkOrder  $workOrder
     * @return void
     */
    public function updated(WorkOrder $workOrder)
    {
        // Log de cambios de estado
        if ($workOrder->isDirty('status')) {
            Log::info('Cambio de estado en orden de trabajo', [
                'order_number' => $workOrder->order_number,
                'old_status' => $workOrder->getOriginal('status'),
                'new_status' => $workOrder->status,
            ]);
        }

        // Si se marca como entregada, registrar fecha actual si no existe
        if ($workOrder->status === 'delivered' && is_null($workOrder->actual_delivery_date)) {
            $workOrder->actual_delivery_date = now();
            $workOrder->saveQuietly(); // Evitar loop infinito
        }
    }

    /**
     * Handle the WorkOrder "deleted" event.
     *
     * @param  \App\Models\WorkOrder  $workOrder
     * @return void
     */
    public function deleted(WorkOrder $workOrder)
    {
        Log::warning('Orden de trabajo eliminada', [
            'id' => $workOrder->id,
            'order_number' => $workOrder->order_number,
        ]);
    }

    /**
     * Handle the WorkOrder "restored" event.
     *
     * @param  \App\Models\WorkOrder  $workOrder
     * @return void
     */
    public function restored(WorkOrder $workOrder)
    {
        Log::info('Orden de trabajo restaurada', [
            'id' => $workOrder->id,
            'order_number' => $workOrder->order_number,
        ]);
    }

    /**
     * Handle the WorkOrder "force deleted" event.
     *
     * @param  \App\Models\WorkOrder  $workOrder
     * @return void
     */
    public function forceDeleted(WorkOrder $workOrder)
    {
        Log::warning('Orden de trabajo eliminada permanentemente', [
            'id' => $workOrder->id,
            'order_number' => $workOrder->order_number,
        ]);
    }
}
