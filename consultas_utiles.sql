-- CONSULTAS SQL ÚTILES PARA EL SISTEMA DE TALLER MECÁNICO

-- ============================================
-- CONSULTAS DE REPORTES
-- ============================================

-- 1. Reporte de utilidades por orden de trabajo
SELECT
    wo.id,
    wo.order_number,
    c.name as cliente,
    v.plate as placa,
    wo.entry_date as fecha_ingreso,
    wo.status as estado,
    wo.total_cost as costo_total,
    wo.total_price as precio_total,
    wo.profit as utilidad,
    CASE
        WHEN wo.total_cost > 0 THEN ROUND((wo.profit / wo.total_cost * 100)::numeric, 2)
        ELSE 0
    END as porcentaje_utilidad
FROM work_orders wo
INNER JOIN vehicles v ON wo.vehicle_id = v.id
INNER JOIN customers c ON v.customer_id = c.id
WHERE wo.deleted_at IS NULL
ORDER BY wo.entry_date DESC;

-- 2. Utilidad total por mes
SELECT
    TO_CHAR(entry_date, 'YYYY-MM') as mes,
    COUNT(*) as total_ordenes,
    SUM(total_cost) as costo_total,
    SUM(total_price) as precio_total,
    SUM(profit) as utilidad_total,
    ROUND(AVG(profit)::numeric, 2) as utilidad_promedio
FROM work_orders
WHERE deleted_at IS NULL
GROUP BY TO_CHAR(entry_date, 'YYYY-MM')
ORDER BY mes DESC;

-- 3. Top 10 clientes por utilidad generada
SELECT
    c.id,
    c.name as cliente,
    c.phone as telefono,
    COUNT(DISTINCT v.id) as vehiculos,
    COUNT(wo.id) as ordenes,
    SUM(wo.profit) as utilidad_total,
    ROUND(AVG(wo.profit)::numeric, 2) as utilidad_promedio
FROM customers c
INNER JOIN vehicles v ON c.id = v.customer_id
INNER JOIN work_orders wo ON v.id = wo.vehicle_id
WHERE c.deleted_at IS NULL AND wo.deleted_at IS NULL
GROUP BY c.id, c.name, c.phone
ORDER BY utilidad_total DESC
LIMIT 10;

-- 4. Repuestos más vendidos
SELECT
    p.id,
    p.code as codigo,
    p.name as repuesto,
    SUM(wop.quantity) as cantidad_vendida,
    SUM(wop.subtotal_cost) as costo_total,
    SUM(wop.subtotal_price) as precio_total,
    SUM(wop.subtotal_price - wop.subtotal_cost) as utilidad_total
FROM parts p
INNER JOIN work_order_parts wop ON p.id = wop.part_id
WHERE p.deleted_at IS NULL
GROUP BY p.id, p.code, p.name
ORDER BY cantidad_vendida DESC;

-- 5. Stock actual de repuestos
SELECT
    code as codigo,
    name as repuesto,
    stock as stock_actual,
    min_stock as stock_minimo,
    CASE
        WHEN stock <= min_stock THEN 'BAJO'
        WHEN stock <= min_stock * 1.5 THEN 'MEDIO'
        ELSE 'OK'
    END as estado_stock,
    purchase_price as precio_compra,
    sale_price as precio_venta,
    (stock * purchase_price) as valor_inventario
FROM parts
WHERE deleted_at IS NULL
ORDER BY
    CASE
        WHEN stock <= min_stock THEN 1
        WHEN stock <= min_stock * 1.5 THEN 2
        ELSE 3
    END,
    stock ASC;

-- 6. Historial de servicios por vehículo
SELECT
    v.plate as placa,
    v.brand as marca,
    v.model as modelo,
    c.name as propietario,
    wo.order_number as orden,
    wo.entry_date as fecha,
    wo.description as descripcion,
    wo.status as estado,
    wo.profit as utilidad
FROM vehicles v
INNER JOIN customers c ON v.customer_id = c.id
LEFT JOIN work_orders wo ON v.id = wo.vehicle_id
WHERE v.deleted_at IS NULL
ORDER BY v.id, wo.entry_date DESC;

-- 7. Órdenes pendientes y en progreso
SELECT
    wo.order_number as orden,
    c.name as cliente,
    c.phone as telefono,
    v.plate as placa,
    v.brand || ' ' || v.model as vehiculo,
    wo.entry_date as fecha_ingreso,
    wo.estimated_delivery_date as fecha_estimada,
    wo.status as estado,
    wo.description as descripcion,
    CURRENT_DATE - wo.entry_date as dias_taller
FROM work_orders wo
INNER JOIN vehicles v ON wo.vehicle_id = v.id
INNER JOIN customers c ON v.customer_id = c.id
WHERE wo.status IN ('pending', 'in_progress')
AND wo.deleted_at IS NULL
ORDER BY wo.entry_date ASC;

-- 8. Análisis de costos por orden
SELECT
    wo.order_number as orden,
    c.name as cliente,
    wo.labor_cost as mano_obra,
    wo.parts_cost as repuestos,
    (SELECT SUM(cost) FROM services WHERE work_order_id = wo.id) as servicios,
    wo.total_cost as costo_total,
    wo.total_price as precio_total,
    wo.profit as utilidad
FROM work_orders wo
INNER JOIN vehicles v ON wo.vehicle_id = v.id
INNER JOIN customers c ON v.customer_id = c.id
WHERE wo.deleted_at IS NULL
ORDER BY wo.entry_date DESC;

-- 9. Compras de repuestos por proveedor
SELECT
    supplier as proveedor,
    COUNT(*) as total_compras,
    SUM(quantity) as cantidad_total,
    SUM(total) as monto_total,
    ROUND(AVG(total)::numeric, 2) as promedio_compra,
    MAX(purchase_date) as ultima_compra
FROM part_purchases
WHERE deleted_at IS NULL
GROUP BY supplier
ORDER BY monto_total DESC;

-- 10. Rentabilidad por tipo de servicio
SELECT
    s.name as servicio,
    COUNT(*) as veces_realizado,
    SUM(s.cost) as costo_total,
    SUM(s.price) as precio_total,
    SUM(s.price - s.cost) as utilidad_total,
    ROUND(AVG(s.price - s.cost)::numeric, 2) as utilidad_promedio
FROM services s
WHERE s.deleted_at IS NULL
GROUP BY s.name
ORDER BY utilidad_total DESC;

-- ============================================
-- CONSULTAS DE GESTIÓN
-- ============================================

-- 11. Vehículos sin mantenimiento reciente (últimos 6 meses)
SELECT
    v.plate as placa,
    v.brand || ' ' || v.model as vehiculo,
    c.name as propietario,
    c.phone as telefono,
    MAX(wo.entry_date) as ultimo_servicio,
    CURRENT_DATE - MAX(wo.entry_date) as dias_sin_servicio
FROM vehicles v
INNER JOIN customers c ON v.customer_id = c.id
LEFT JOIN work_orders wo ON v.id = wo.vehicle_id
WHERE v.deleted_at IS NULL
GROUP BY v.id, v.plate, v.brand, v.model, c.name, c.phone
HAVING MAX(wo.entry_date) < CURRENT_DATE - INTERVAL '6 months'
    OR MAX(wo.entry_date) IS NULL
ORDER BY dias_sin_servicio DESC NULLS FIRST;

-- 12. Repuestos que necesitan reposición
SELECT
    code as codigo,
    name as repuesto,
    stock as stock_actual,
    min_stock as stock_minimo,
    min_stock * 2 - stock as cantidad_sugerida,
    purchase_price as precio_compra,
    (min_stock * 2 - stock) * purchase_price as monto_estimado
FROM parts
WHERE stock <= min_stock
AND deleted_at IS NULL
ORDER BY (min_stock - stock) DESC;

-- 13. Clientes más frecuentes
SELECT
    c.name as cliente,
    c.phone as telefono,
    c.email,
    COUNT(DISTINCT v.id) as vehiculos,
    COUNT(wo.id) as total_ordenes,
    MAX(wo.entry_date) as ultima_visita,
    SUM(wo.total_price) as monto_total_gastado
FROM customers c
LEFT JOIN vehicles v ON c.id = v.customer_id
LEFT JOIN work_orders wo ON v.id = wo.vehicle_id
WHERE c.deleted_at IS NULL
GROUP BY c.id, c.name, c.phone, c.email
HAVING COUNT(wo.id) > 0
ORDER BY total_ordenes DESC;

-- ============================================
-- VISTAS ÚTILES (Crear si es necesario)
-- ============================================

-- Vista de órdenes con información completa
CREATE OR REPLACE VIEW v_work_orders_complete AS
SELECT
    wo.id,
    wo.order_number,
    wo.entry_date,
    wo.estimated_delivery_date,
    wo.actual_delivery_date,
    wo.status,
    wo.description,
    c.id as customer_id,
    c.name as customer_name,
    c.phone as customer_phone,
    v.id as vehicle_id,
    v.plate,
    v.brand,
    v.model,
    v.year,
    wo.labor_cost,
    wo.parts_cost,
    wo.total_cost,
    wo.total_price,
    wo.profit,
    CASE
        WHEN wo.total_cost > 0 THEN ROUND((wo.profit / wo.total_cost * 100)::numeric, 2)
        ELSE 0
    END as profit_percentage
FROM work_orders wo
INNER JOIN vehicles v ON wo.vehicle_id = v.id
INNER JOIN customers c ON v.customer_id = c.id
WHERE wo.deleted_at IS NULL;

-- Vista de inventario valorizado
CREATE OR REPLACE VIEW v_inventory_value AS
SELECT
    p.id,
    p.code,
    p.name,
    p.stock,
    p.min_stock,
    p.purchase_price,
    p.sale_price,
    (p.stock * p.purchase_price) as inventory_value,
    (p.sale_price - p.purchase_price) as unit_profit,
    CASE
        WHEN p.stock <= p.min_stock THEN 'BAJO'
        WHEN p.stock <= p.min_stock * 1.5 THEN 'MEDIO'
        ELSE 'OK'
    END as stock_status
FROM parts p
WHERE p.deleted_at IS NULL;
