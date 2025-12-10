# 🚀 Guía Rápida de Uso - Sistema Taller Mecánico

## ✅ Estado del Sistema

El sistema está completamente configurado y listo para usar:

- ✅ Base de datos PostgreSQL conectada (Base: Taller)
- ✅ Migraciones ejecutadas (7 tablas creadas)
- ✅ Modelos con relaciones y lógica de negocio
- ✅ Controladores CRUD completos
- ✅ Rutas API configuradas
- ✅ Datos de ejemplo cargados
- ✅ Servidor corriendo en http://localhost:8000

## 📊 Datos de Ejemplo Cargados

- 3 Clientes
- 3 Vehículos
- 5 Repuestos
- 2 Compras de repuestos
- 3 Órdenes de trabajo
- 4 Repuestos en órdenes
- 3 Servicios

## 🔥 Pruebas Rápidas con cURL

### 1. Listar todos los clientes
```bash
curl http://localhost:8000/api/customers
```

### 2. Ver un cliente específico con sus vehículos
```bash
curl http://localhost:8000/api/customers/1
```

### 3. Crear un nuevo cliente
```bash
curl -X POST http://localhost:8000/api/customers \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"Pedro Sánchez\",\"phone\":\"999888777\",\"email\":\"pedro@example.com\"}"
```

### 4. Listar todos los vehículos
```bash
curl http://localhost:8000/api/vehicles
```

### 5. Listar todos los repuestos
```bash
curl http://localhost:8000/api/parts
```

### 6. Ver repuestos con stock bajo
```bash
curl http://localhost:8000/api/parts-low-stock
```

### 7. Listar órdenes de trabajo
```bash
curl http://localhost:8000/api/work-orders
```

### 8. Ver una orden específica con todos sus detalles
```bash
curl http://localhost:8000/api/work-orders/1
```

### 9. Filtrar órdenes por estado
```bash
curl http://localhost:8000/api/work-orders-status/pending
curl http://localhost:8000/api/work-orders-status/in_progress
curl http://localhost:8000/api/work-orders-status/completed
```

### 10. Reporte de utilidades
```bash
curl "http://localhost:8000/api/work-orders-profit-report?start_date=2025-12-01&end_date=2025-12-31"
```

### 11. Crear una nueva orden de trabajo
```bash
curl -X POST http://localhost:8000/api/work-orders \
  -H "Content-Type: application/json" \
  -d "{\"vehicle_id\":1,\"entry_date\":\"2025-12-10\",\"description\":\"Cambio de aceite\",\"labor_cost\":50}"
```

### 12. Agregar un repuesto a una orden
```bash
curl -X POST http://localhost:8000/api/work-orders/1/parts \
  -H "Content-Type: application/json" \
  -d "{\"part_id\":1,\"quantity\":2,\"unit_cost\":15.50,\"unit_price\":25.00}"
```

### 13. Agregar un servicio a una orden
```bash
curl -X POST http://localhost:8000/api/work-orders/1/services \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"Lavado\",\"description\":\"Lavado completo\",\"cost\":10,\"price\":20}"
```

### 14. Actualizar estado de una orden
```bash
curl -X PUT http://localhost:8000/api/work-orders/1 \
  -H "Content-Type: application/json" \
  -d "{\"status\":\"completed\"}"
```

### 15. Registrar una compra de repuesto
```bash
curl -X POST http://localhost:8000/api/part-purchases \
  -H "Content-Type: application/json" \
  -d "{\"part_id\":1,\"supplier\":\"Proveedor ABC\",\"quantity\":10,\"unit_price\":15.00,\"purchase_date\":\"2025-12-10\"}"
```

## 🌐 Usando Postman

1. Importa el archivo `postman_collection.json` en Postman
2. Todas las rutas están pre-configuradas con ejemplos
3. Modifica los valores según necesites

## 🗄️ Acceso Directo a PostgreSQL

```bash
psql -U postgres -d Taller
```

Contraseña: `123`

### Consultas útiles en PostgreSQL:

```sql
-- Ver todas las órdenes con utilidad
SELECT order_number, total_cost, total_price, profit FROM work_orders;

-- Ver stock de repuestos
SELECT code, name, stock, min_stock FROM parts;

-- Ver clientes con sus vehículos
SELECT c.name, v.plate, v.brand, v.model 
FROM customers c 
JOIN vehicles v ON c.id = v.customer_id;
```

## 📁 Estructura de Archivos Importantes

```
taller-mecanico/
├── app/
│   ├── Models/              # Modelos con lógica de negocio
│   │   ├── Customer.php
│   │   ├── Vehicle.php
│   │   ├── Part.php
│   │   ├── PartPurchase.php
│   │   ├── WorkOrder.php
│   │   ├── WorkOrderPart.php
│   │   └── Service.php
│   └── Http/Controllers/    # Controladores CRUD
│       ├── CustomerController.php
│       ├── VehicleController.php
│       ├── PartController.php
│       ├── PartPurchaseController.php
│       └── WorkOrderController.php
├── database/
│   ├── migrations/          # Estructura de base de datos
│   └── seeders/             # Datos de ejemplo
├── routes/
│   └── api.php             # Rutas de la API
├── README.md               # Documentación completa
├── postman_collection.json # Colección de Postman
└── consultas_utiles.sql    # Consultas SQL útiles
```

## 🔧 Comandos Útiles

### Reiniciar base de datos (CUIDADO: Borra todo)
```bash
php artisan migrate:fresh --seed
```

### Ver rutas disponibles
```bash
php artisan route:list
```

### Limpiar caché
```bash
php artisan cache:clear
php artisan config:clear
```

### Detener el servidor
Presiona `Ctrl+C` en la terminal donde corre el servidor

### Iniciar el servidor nuevamente
```bash
php artisan serve
```

## 💡 Flujo de Trabajo Típico

### Escenario: Cliente trae su auto para mantenimiento

1. **Verificar si el cliente existe**
   ```bash
   curl http://localhost:8000/api/customers
   ```

2. **Si no existe, crear el cliente**
   ```bash
   curl -X POST http://localhost:8000/api/customers \
     -H "Content-Type: application/json" \
     -d "{\"name\":\"Nuevo Cliente\",\"phone\":\"123456789\"}"
   ```

3. **Registrar el vehículo**
   ```bash
   curl -X POST http://localhost:8000/api/vehicles \
     -H "Content-Type: application/json" \
     -d "{\"customer_id\":1,\"brand\":\"Toyota\",\"model\":\"Corolla\",\"year\":\"2020\",\"plate\":\"XYZ-789\"}"
   ```

4. **Crear orden de trabajo**
   ```bash
   curl -X POST http://localhost:8000/api/work-orders \
     -H "Content-Type: application/json" \
     -d "{\"vehicle_id\":1,\"entry_date\":\"2025-12-10\",\"description\":\"Mantenimiento\",\"labor_cost\":80}"
   ```

5. **Agregar repuestos usados**
   ```bash
   curl -X POST http://localhost:8000/api/work-orders/1/parts \
     -H "Content-Type: application/json" \
     -d "{\"part_id\":1,\"quantity\":1,\"unit_cost\":15.50,\"unit_price\":25.00}"
   ```

6. **Agregar servicios realizados**
   ```bash
   curl -X POST http://localhost:8000/api/work-orders/1/services \
     -H "Content-Type: application/json" \
     -d "{\"name\":\"Alineación\",\"cost\":20,\"price\":40}"
   ```

7. **Ver la orden completa con utilidad calculada**
   ```bash
   curl http://localhost:8000/api/work-orders/1
   ```

8. **Actualizar estado cuando se complete**
   ```bash
   curl -X PUT http://localhost:8000/api/work-orders/1 \
     -H "Content-Type: application/json" \
     -d "{\"status\":\"completed\",\"actual_delivery_date\":\"2025-12-10\"}"
   ```

## 📈 Cálculos Automáticos

El sistema calcula automáticamente:

- ✅ **Stock**: Se actualiza al comprar o usar repuestos
- ✅ **Subtotales**: Se calculan al agregar repuestos/servicios
- ✅ **Costo Total**: Suma de repuestos + mano de obra + servicios
- ✅ **Precio Total**: Suma de precios de repuestos + servicios
- ✅ **Utilidad**: Precio Total - Costo Total
- ✅ **Número de Orden**: Se genera automáticamente (OT-000001, etc.)

## 🎯 Próximos Pasos Sugeridos

1. Crear un frontend (React, Vue, o Blade templates)
2. Agregar autenticación de usuarios
3. Implementar roles y permisos
4. Crear reportes en PDF
5. Agregar sistema de notificaciones
6. Implementar dashboard con gráficos

## 📞 Ayuda

- Documentación completa: Ver `README.md`
- Consultas SQL útiles: Ver `consultas_utiles.sql`
- Colección Postman: Importar `postman_collection.json`

---

**¡El sistema está listo para usar! 🎉**
