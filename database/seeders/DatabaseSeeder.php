<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Part;
use App\Models\PartPurchase;
use App\Models\WorkOrder;
use App\Models\WorkOrderPart;
use App\Models\Service;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Crear usuarios primero
        $this->call(UserSeeder::class);

        // Crear clientes
        $customer1 = Customer::create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'phone' => '123456789',
            'document' => '12345678',
            'address' => 'Av. Principal 123, Lima',
        ]);

        $customer2 = Customer::create([
            'name' => 'María García',
            'email' => 'maria@example.com',
            'phone' => '987654321',
            'document' => '87654321',
            'address' => 'Jr. Los Olivos 456, Lima',
        ]);

        $customer3 = Customer::create([
            'name' => 'Carlos Rodríguez',
            'email' => 'carlos@example.com',
            'phone' => '555123456',
            'document' => '11223344',
            'address' => 'Calle Las Flores 789, Lima',
        ]);

        // Crear vehículos
        $vehicle1 = Vehicle::create([
            'customer_id' => $customer1->id,
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => '2020',
            'plate' => 'ABC-123',
            'vin' => '1HGBH41JXMN109186',
            'color' => 'Rojo',
            'mileage' => 50000,
        ]);

        $vehicle2 = Vehicle::create([
            'customer_id' => $customer2->id,
            'brand' => 'Honda',
            'model' => 'Civic',
            'year' => '2019',
            'plate' => 'DEF-456',
            'vin' => '2HGFC2F59JH123456',
            'color' => 'Azul',
            'mileage' => 35000,
        ]);

        $vehicle3 = Vehicle::create([
            'customer_id' => $customer3->id,
            'brand' => 'Nissan',
            'model' => 'Sentra',
            'year' => '2021',
            'plate' => 'GHI-789',
            'vin' => '3N1AB7AP5LY123456',
            'color' => 'Blanco',
            'mileage' => 20000,
        ]);

        // Crear repuestos
        $part1 = Part::create([
            'code' => 'FLT-001',
            'name' => 'Filtro de Aceite',
            'description' => 'Filtro de aceite para motor',
            'purchase_price' => 15.50,
            'sale_price' => 25.00,
            'stock' => 50,
            'min_stock' => 10,
        ]);

        $part2 = Part::create([
            'code' => 'FLT-002',
            'name' => 'Filtro de Aire',
            'description' => 'Filtro de aire para motor',
            'purchase_price' => 12.00,
            'sale_price' => 20.00,
            'stock' => 30,
            'min_stock' => 8,
        ]);

        $part3 = Part::create([
            'code' => 'ACE-001',
            'name' => 'Aceite 5W-30',
            'description' => 'Aceite sintético 5W-30 (4 litros)',
            'purchase_price' => 35.00,
            'sale_price' => 55.00,
            'stock' => 25,
            'min_stock' => 5,
        ]);

        $part4 = Part::create([
            'code' => 'PAS-001',
            'name' => 'Pastillas de Freno',
            'description' => 'Pastillas de freno delanteras',
            'purchase_price' => 45.00,
            'sale_price' => 75.00,
            'stock' => 15,
            'min_stock' => 4,
        ]);

        $part5 = Part::create([
            'code' => 'BAT-001',
            'name' => 'Batería 12V',
            'description' => 'Batería 12V 60Ah',
            'purchase_price' => 120.00,
            'sale_price' => 180.00,
            'stock' => 8,
            'min_stock' => 2,
        ]);

        // Crear compras de repuestos
        PartPurchase::create([
            'part_id' => $part1->id,
            'supplier' => 'Distribuidora AutoParts',
            'quantity' => 50,
            'unit_price' => 15.50,
            'total' => 775.00,
            'purchase_date' => '2025-12-01',
            'notes' => 'Compra inicial de stock',
        ]);

        PartPurchase::create([
            'part_id' => $part3->id,
            'supplier' => 'Lubricantes del Perú',
            'quantity' => 30,
            'unit_price' => 35.00,
            'total' => 1050.00,
            'purchase_date' => '2025-12-05',
            'notes' => 'Reposición de aceite',
        ]);

        // Crear órdenes de trabajo
        $workOrder1 = WorkOrder::create([
            'vehicle_id' => $vehicle1->id,
            'entry_date' => '2025-12-08',
            'estimated_delivery_date' => '2025-12-10',
            'status' => 'completed',
            'description' => 'Cambio de aceite y filtros',
            'labor_cost' => 50.00,
            'notes' => 'Cliente solicita aceite sintético',
        ]);

        $workOrder2 = WorkOrder::create([
            'vehicle_id' => $vehicle2->id,
            'entry_date' => '2025-12-09',
            'estimated_delivery_date' => '2025-12-12',
            'status' => 'in_progress',
            'description' => 'Cambio de pastillas de freno',
            'labor_cost' => 80.00,
            'notes' => 'Revisar discos de freno',
        ]);

        $workOrder3 = WorkOrder::create([
            'vehicle_id' => $vehicle3->id,
            'entry_date' => '2025-12-10',
            'estimated_delivery_date' => '2025-12-11',
            'status' => 'pending',
            'description' => 'Mantenimiento preventivo',
            'labor_cost' => 60.00,
        ]);

        // Agregar repuestos a la orden 1
        WorkOrderPart::create([
            'work_order_id' => $workOrder1->id,
            'part_id' => $part1->id,
            'quantity' => 1,
            'unit_cost' => 15.50,
            'unit_price' => 25.00,
        ]);

        WorkOrderPart::create([
            'work_order_id' => $workOrder1->id,
            'part_id' => $part2->id,
            'quantity' => 1,
            'unit_cost' => 12.00,
            'unit_price' => 20.00,
        ]);

        WorkOrderPart::create([
            'work_order_id' => $workOrder1->id,
            'part_id' => $part3->id,
            'quantity' => 1,
            'unit_cost' => 35.00,
            'unit_price' => 55.00,
        ]);

        // Agregar repuestos a la orden 2
        WorkOrderPart::create([
            'work_order_id' => $workOrder2->id,
            'part_id' => $part4->id,
            'quantity' => 1,
            'unit_cost' => 45.00,
            'unit_price' => 75.00,
        ]);

        // Agregar servicios
        Service::create([
            'work_order_id' => $workOrder1->id,
            'name' => 'Lavado de motor',
            'description' => 'Lavado completo del motor',
            'cost' => 10.00,
            'price' => 20.00,
        ]);

        Service::create([
            'work_order_id' => $workOrder2->id,
            'name' => 'Alineación y balanceo',
            'description' => 'Alineación y balanceo de 4 ruedas',
            'cost' => 25.00,
            'price' => 50.00,
        ]);

        Service::create([
            'work_order_id' => $workOrder3->id,
            'name' => 'Revisión general',
            'description' => 'Revisión de 21 puntos',
            'cost' => 15.00,
            'price' => 30.00,
        ]);

        echo "✅ Base de datos poblada exitosamente!\n";
        echo "📊 Resumen:\n";
        echo "   - 3 Clientes\n";
        echo "   - 3 Vehículos\n";
        echo "   - 5 Repuestos\n";
        echo "   - 2 Compras de repuestos\n";
        echo "   - 3 Órdenes de trabajo\n";
        echo "   - 4 Repuestos en órdenes\n";
        echo "   - 3 Servicios\n";
    }
}
