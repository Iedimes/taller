# 📊 DOCUMENTACIÓN DE REPORTES Y ANÁLISIS

## Nuevos Endpoints Agregados

El sistema ahora incluye **7 endpoints de reportes avanzados** para análisis de negocio.

---

## 📍 Endpoints de Reportes

Todos los endpoints de reportes están bajo el prefijo `/api/reports/`

### 1. **Dashboard General**
```
GET /api/reports/dashboard
```

**Descripción:** Obtiene un resumen general del sistema con estadísticas clave.

**Respuesta:**
```json
{
  "summary": {
    "total_customers": 3,
    "total_vehicles": 3,
    "total_parts": 5,
    "low_stock_parts": 2
  },
  "work_orders": {
    "total": 3,
    "pending": 1,
    "in_progress": 1,
    "completed_this_month": 1
  },
  "financial": {
    "total_profit_all_time": 150.50,
    "total_profit_this_month": 50.00,
    "average_profit_per_order": 50.17
  },
  "inventory_value": 2500.00
}
```

**Ejemplo cURL:**
```bash
curl http://localhost:8000/api/reports/dashboard
```

---

### 2. **Reporte de Ventas por Período**
```
GET /api/reports/sales?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD
```

**Parámetros requeridos:**
- `start_date` - Fecha de inicio (formato: YYYY-MM-DD)
- `end_date` - Fecha de fin (formato: YYYY-MM-DD)

**Descripción:** Análisis detallado de ventas en un período específico.

**Respuesta:**
```json
{
  "period": {
    "start": "2025-12-01",
    "end": "2025-12-31"
  },
  "summary": {
    "total_orders": 15,
    "total_cost": 3000.00,
    "total_price": 4500.00,
    "total_profit": 1500.00,
    "average_profit": 100.00
  },
  "by_status": {
    "completed": {
      "count": 10,
      "total_cost": 2000.00,
      "total_price": 3000.00,
      "total_profit": 1000.00
    },
    "pending": {
      "count": 5,
      "total_cost": 1000.00,
      "total_price": 1500.00,
      "total_profit": 500.00
    }
  },
  "by_day": {
    "2025-12-01": {
      "count": 2,
      "profit": 200.00
    },
    "2025-12-02": {
      "count": 3,
      "profit": 300.00
    }
  }
}
```

**Ejemplo cURL:**
```bash
curl "http://localhost:8000/api/reports/sales?start_date=2025-12-01&end_date=2025-12-31"
```

---

### 3. **Top Clientes por Utilidad**
```
GET /api/reports/top-customers?limit=10
```

**Parámetros opcionales:**
- `limit` - Número de clientes a mostrar (default: 10)

**Descripción:** Lista de clientes que han generado más utilidad.

**Respuesta:**
```json
{
  "top_customers": [
    {
      "id": 1,
      "name": "Juan Pérez",
      "phone": "123456789",
      "email": "juan@example.com",
      "vehicles_count": 2,
      "orders_count": 5,
      "total_spent": 2500.00,
      "total_profit": 500.00,
      "average_order_value": 500.00
    }
  ]
}
```

**Ejemplo cURL:**
```bash
curl "http://localhost:8000/api/reports/top-customers?limit=5"
```

---

### 4. **Repuestos Más Vendidos**
```
GET /api/reports/top-parts?limit=10
```

**Parámetros opcionales:**
- `limit` - Número de repuestos a mostrar (default: 10)

**Descripción:** Análisis de repuestos más vendidos con rentabilidad.

**Respuesta:**
```json
{
  "top_parts": [
    {
      "id": 1,
      "code": "FLT-001",
      "name": "Filtro de Aceite",
      "current_stock": 45,
      "total_sold": 50,
      "total_cost": 775.00,
      "total_revenue": 1250.00,
      "total_profit": 475.00,
      "profit_margin": 61.29
    }
  ]
}
```

**Ejemplo cURL:**
```bash
curl "http://localhost:8000/api/reports/top-parts?limit=10"
```

---

### 5. **Servicios Más Rentables**
```
GET /api/reports/top-services?limit=10
```

**Parámetros opcionales:**
- `limit` - Número de servicios a mostrar (default: 10)

**Descripción:** Análisis de servicios más realizados y rentables.

**Respuesta:**
```json
{
  "top_services": [
    {
      "name": "Alineación y Balanceo",
      "times_performed": 15,
      "total_cost": 375.00,
      "total_revenue": 750.00,
      "total_profit": 375.00,
      "average_profit": 25.00
    }
  ]
}
```

**Ejemplo cURL:**
```bash
curl "http://localhost:8000/api/reports/top-services?limit=5"
```

---

### 6. **Análisis de Inventario**
```
GET /api/reports/inventory-analysis
```

**Descripción:** Análisis completo del inventario con alertas de stock.

**Respuesta:**
```json
{
  "summary": {
    "total_parts": 5,
    "total_value": 2500.00,
    "potential_revenue": 4000.00
  },
  "stock_levels": {
    "low": {
      "count": 2,
      "parts": [
        {
          "id": 1,
          "code": "FLT-001",
          "name": "Filtro de Aceite",
          "stock": 5,
          "min_stock": 10,
          "suggested_order": 15
        }
      ]
    },
    "medium": {
      "count": 1
    },
    "good": {
      "count": 2
    }
  }
}
```

**Ejemplo cURL:**
```bash
curl http://localhost:8000/api/reports/inventory-analysis
```

---

### 7. **Análisis de Eficiencia**
```
GET /api/reports/efficiency
```

**Descripción:** Análisis de tiempos de entrega y eficiencia operativa.

**Respuesta:**
```json
{
  "summary": {
    "total_completed": 20,
    "on_time": 15,
    "delayed": 5,
    "on_time_percentage": 75.00
  },
  "average_days": {
    "estimated": 3.5,
    "actual": 4.2
  }
}
```

**Ejemplo cURL:**
```bash
curl http://localhost:8000/api/reports/efficiency
```

---

## 🎯 Casos de Uso

### Dashboard para Administración
```bash
# Ver resumen general del negocio
curl http://localhost:8000/api/reports/dashboard
```

### Análisis Mensual de Ventas
```bash
# Reporte del mes actual
curl "http://localhost:8000/api/reports/sales?start_date=2025-12-01&end_date=2025-12-31"
```

### Identificar Mejores Clientes
```bash
# Top 5 clientes
curl "http://localhost:8000/api/reports/top-customers?limit=5"
```

### Control de Inventario
```bash
# Ver repuestos que necesitan reposición
curl http://localhost:8000/api/reports/inventory-analysis
```

### Análisis de Rentabilidad
```bash
# Ver qué repuestos generan más ganancia
curl "http://localhost:8000/api/reports/top-parts?limit=10"

# Ver qué servicios son más rentables
curl "http://localhost:8000/api/reports/top-services?limit=10"
```

### Monitoreo de Eficiencia
```bash
# Ver si se cumplen los tiempos estimados
curl http://localhost:8000/api/reports/efficiency
```

---

## 📊 Integración con Frontend

### Ejemplo con JavaScript/Fetch
```javascript
// Dashboard
async function getDashboard() {
  const response = await fetch('http://localhost:8000/api/reports/dashboard');
  const data = await response.json();
  console.log(data);
}

// Reporte de ventas
async function getSalesReport(startDate, endDate) {
  const response = await fetch(
    `http://localhost:8000/api/reports/sales?start_date=${startDate}&end_date=${endDate}`
  );
  const data = await response.json();
  return data;
}

// Top clientes
async function getTopCustomers(limit = 10) {
  const response = await fetch(
    `http://localhost:8000/api/reports/top-customers?limit=${limit}`
  );
  const data = await response.json();
  return data;
}
```

---

## 🔧 Características Adicionales Agregadas

### 1. **API Resources**
Se creó `WorkOrderResource` para formatear respuestas de órdenes de trabajo con:
- Datos anidados de vehículo y cliente
- Etiquetas de estado en español
- Cálculo de porcentaje de utilidad
- Formato consistente de fechas y números

### 2. **Form Requests**
Se creó `StoreWorkOrderRequest` con:
- Validaciones robustas
- Mensajes de error en español
- Validación de fechas lógicas

### 3. **Observers**
Se creó `WorkOrderObserver` que:
- Genera números de orden automáticamente
- Registra cambios de estado en logs
- Establece fecha de entrega automáticamente
- Registra eventos importantes

---

## 📝 Notas Importantes

1. **Performance**: Los reportes hacen consultas complejas. Para grandes volúmenes de datos, considera agregar caché.

2. **Filtros**: Todos los reportes pueden ser extendidos con más filtros según necesites.

3. **Exportación**: Estos endpoints devuelven JSON. Para exportar a Excel/PDF, necesitarás agregar paquetes adicionales.

4. **Autenticación**: En producción, protege estos endpoints con autenticación.

---

## 🚀 Próximas Mejoras Sugeridas

- [ ] Caché de reportes para mejor performance
- [ ] Exportación a PDF/Excel
- [ ] Gráficos y visualizaciones
- [ ] Reportes programados por email
- [ ] Comparativas entre períodos
- [ ] Predicciones y tendencias

---

**¡Los reportes están listos para usar! 🎉**

Para probar:
```bash
curl http://localhost:8000/api/reports/dashboard
```
