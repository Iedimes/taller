# Sistema de Taller Mecánico - Laravel

Sistema completo de gestión para taller mecánico desarrollado en Laravel con PostgreSQL.

## 📋 Características

- **Gestión de Clientes**: CRUD completo de clientes con información de contacto
- **Gestión de Vehículos**: Registro de vehículos asociados a clientes
- **Inventario de Repuestos**: Control de stock, precios de compra y venta
- **Compras de Repuestos**: Registro de compras con actualización automática de stock
- **Órdenes de Trabajo**: Gestión completa de servicios realizados
- **Cálculo Automático de Utilidades**: Sistema automático de cálculo de costos, precios y ganancias
- **Reportes de Utilidad**: Filtros por fecha y estado

## 🗄️ Estructura de Base de Datos

### Tablas Principales

1. **customers** - Clientes del taller
2. **vehicles** - Vehículos de los clientes
3. **parts** - Catálogo de repuestos
4. **part_purchases** - Compras de repuestos
5. **work_orders** - Órdenes de trabajo
6. **work_order_parts** - Repuestos usados en órdenes
7. **services** - Servicios realizados en órdenes

## 🚀 Instalación

### Requisitos Previos
- PHP >= 8.1
- Composer
- PostgreSQL
- Extensión pgsql de PHP

### Pasos de Instalación

1. **Clonar o ubicar el proyecto**
```bash
cd C:\Users\osemidei\.gemini\antigravity\scratch\taller-mecanico
```

2. **Instalar dependencias**
```bash
composer install
```

3. **Configurar base de datos**
El archivo `.env` ya está configurado con:
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=Taller
DB_USERNAME=postgres
DB_PASSWORD=123
```

4. **Ejecutar migraciones** (Ya ejecutadas)
```bash
php artisan migrate
```

5. **Iniciar servidor**
```bash
php artisan serve
```

## 📡 API Endpoints

### Clientes (Customers)
- `GET /api/customers` - Listar todos los clientes
- `POST /api/customers` - Crear nuevo cliente
- `GET /api/customers/{id}` - Ver detalle de cliente
- `PUT /api/customers/{id}` - Actualizar cliente
- `DELETE /api/customers/{id}` - Eliminar cliente

**Ejemplo de creación:**
```json
{
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "phone": "123456789",
    "document": "12345678",
    "address": "Calle Principal 123"
}
```

### Vehículos (Vehicles)
- `GET /api/vehicles` - Listar todos los vehículos
- `POST /api/vehicles` - Crear nuevo vehículo
- `GET /api/vehicles/{id}` - Ver detalle de vehículo
- `PUT /api/vehicles/{id}` - Actualizar vehículo
- `DELETE /api/vehicles/{id}` - Eliminar vehículo

**Ejemplo de creación:**
```json
{
    "customer_id": 1,
    "brand": "Toyota",
    "model": "Corolla",
    "year": "2020",
    "plate": "ABC-123",
    "vin": "1HGBH41JXMN109186",
    "color": "Rojo",
    "mileage": 50000
}
```

### Repuestos (Parts)
- `GET /api/parts` - Listar todos los repuestos
- `GET /api/parts-low-stock` - Listar repuestos con stock bajo
- `POST /api/parts` - Crear nuevo repuesto
- `GET /api/parts/{id}` - Ver detalle de repuesto
- `PUT /api/parts/{id}` - Actualizar repuesto
- `DELETE /api/parts/{id}` - Eliminar repuesto

**Ejemplo de creación:**
```json
{
    "code": "REP-001",
    "name": "Filtro de Aceite",
    "description": "Filtro de aceite para motor",
    "purchase_price": 15.50,
    "sale_price": 25.00,
    "stock": 10,
    "min_stock": 5
}
```

### Compras de Repuestos (Part Purchases)
- `GET /api/part-purchases` - Listar todas las compras
- `POST /api/part-purchases` - Registrar nueva compra
- `GET /api/part-purchases/{id}` - Ver detalle de compra
- `PUT /api/part-purchases/{id}` - Actualizar compra
- `DELETE /api/part-purchases/{id}` - Eliminar compra

**Ejemplo de creación:**
```json
{
    "part_id": 1,
    "supplier": "Distribuidora XYZ",
    "quantity": 20,
    "unit_price": 15.00,
    "purchase_date": "2025-12-10",
    "notes": "Compra de emergencia"
}
```

### Órdenes de Trabajo (Work Orders)
- `GET /api/work-orders` - Listar todas las órdenes
- `GET /api/work-orders-status/{status}` - Filtrar por estado
- `GET /api/work-orders-profit-report` - Reporte de utilidades
- `POST /api/work-orders` - Crear nueva orden
- `GET /api/work-orders/{id}` - Ver detalle de orden
- `PUT /api/work-orders/{id}` - Actualizar orden
- `DELETE /api/work-orders/{id}` - Eliminar orden

**Estados disponibles:** `pending`, `in_progress`, `completed`, `delivered`, `cancelled`

**Ejemplo de creación:**
```json
{
    "vehicle_id": 1,
    "entry_date": "2025-12-10",
    "estimated_delivery_date": "2025-12-15",
    "description": "Cambio de aceite y filtros",
    "labor_cost": 50.00,
    "notes": "Cliente solicita llamar antes de entregar"
}
```

### Agregar Repuestos a Orden
- `POST /api/work-orders/{id}/parts` - Agregar repuesto
- `DELETE /api/work-orders/{id}/parts/{partId}` - Quitar repuesto

**Ejemplo:**
```json
{
    "part_id": 1,
    "quantity": 2,
    "unit_cost": 15.50,
    "unit_price": 25.00
}
```

### Agregar Servicios a Orden
- `POST /api/work-orders/{id}/services` - Agregar servicio
- `DELETE /api/work-orders/{id}/services/{serviceId}` - Quitar servicio

**Ejemplo:**
```json
{
    "name": "Alineación y Balanceo",
    "description": "Servicio completo de alineación",
    "cost": 20.00,
    "price": 40.00
}
```

### Reporte de Utilidades
```
GET /api/work-orders-profit-report?start_date=2025-12-01&end_date=2025-12-31
```

**Respuesta:**
```json
{
    "work_orders": [...],
    "summary": {
        "total_profit": 1500.00,
        "total_cost": 3000.00,
        "total_price": 4500.00,
        "count": 15
    }
}
```

## 🔄 Funcionalidades Automáticas

### Actualización de Stock
- **Al registrar compra**: El stock del repuesto se incrementa automáticamente
- **Al agregar repuesto a orden**: El stock se reduce automáticamente
- **Al eliminar repuesto de orden**: El stock se restaura automáticamente

### Cálculo de Utilidades
Cuando se agregan repuestos o servicios a una orden, el sistema calcula automáticamente:
- **parts_cost**: Suma de costos de todos los repuestos
- **total_cost**: parts_cost + labor_cost + costos de servicios
- **total_price**: Suma de precios de repuestos + precios de servicios
- **profit**: total_price - total_cost

### Generación de Números de Orden
Las órdenes de trabajo generan automáticamente números secuenciales:
- Formato: `OT-000001`, `OT-000002`, etc.

## 📊 Modelos y Relaciones

### Customer (Cliente)
- `hasMany(Vehicle)` - Un cliente tiene muchos vehículos

### Vehicle (Vehículo)
- `belongsTo(Customer)` - Un vehículo pertenece a un cliente
- `hasMany(WorkOrder)` - Un vehículo tiene muchas órdenes de trabajo

### Part (Repuesto)
- `hasMany(PartPurchase)` - Un repuesto tiene muchas compras
- `belongsToMany(WorkOrder)` - Un repuesto puede estar en muchas órdenes

### WorkOrder (Orden de Trabajo)
- `belongsTo(Vehicle)` - Una orden pertenece a un vehículo
- `hasMany(Service)` - Una orden tiene muchos servicios
- `belongsToMany(Part)` - Una orden puede tener muchos repuestos

## 🛠️ Métodos Útiles en Modelos

### Part
```php
$part->isLowStock() // Verifica si el stock está bajo el mínimo
```

### WorkOrder
```php
$workOrder->updateTotals() // Recalcula todos los totales
$workOrder->calculatePartsCost() // Calcula costo de repuestos
$workOrder->calculateServicesCost() // Calcula costo de servicios
WorkOrder::generateOrderNumber() // Genera nuevo número de orden
```

## 📝 Notas Importantes

1. **Soft Deletes**: Todos los modelos principales usan soft deletes, por lo que los registros no se eliminan físicamente
2. **Validaciones**: Todos los controladores incluyen validaciones completas
3. **Transacciones**: Las operaciones críticas deberían usar transacciones de base de datos
4. **Stock Negativo**: El sistema permite stock negativo, considera agregar validación si lo necesitas

## 🔐 Seguridad

Para producción, considera:
- Implementar autenticación (Laravel Sanctum ya está incluido)
- Agregar middleware de autorización
- Validar permisos por rol
- Implementar rate limiting

## 📈 Próximas Mejoras Sugeridas

- [ ] Dashboard con estadísticas
- [ ] Generación de PDF para órdenes de trabajo
- [ ] Sistema de notificaciones
- [ ] Historial de servicios por vehículo
- [ ] Alertas de stock bajo
- [ ] Reportes avanzados con gráficos
- [ ] Sistema de citas/agenda

## 🧪 Testing

Para ejecutar tests (cuando los crees):
```bash
php artisan test
```

## 📞 Soporte

Para cualquier duda o problema, revisa la documentación de Laravel en https://laravel.com/docs

---

**Desarrollado con Laravel 10.x y PostgreSQL**
