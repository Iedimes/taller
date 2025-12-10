# 🔐 GUÍA DE AUTENTICACIÓN - SISTEMA TALLER MECÁNICO

## 📋 RESUMEN

El sistema ahora tiene **dos modos de acceso**:

1. **Sin Autenticación** (Actual) - Acceso libre a todos los endpoints
2. **Con Autenticación** (Opcional) - Sistema de login con tokens

---

## 🌐 MODO ACTUAL: SIN AUTENTICACIÓN

### ✅ Acceso Directo
Actualmente **NO necesitas usuario ni contraseña** para acceder a la API.

```bash
# Funciona directamente
curl http://localhost:8000/api/customers
curl http://localhost:8000/api/work-orders
curl http://localhost:8000/api/reports/dashboard
```

**Todos los endpoints están abiertos** para facilitar el desarrollo y pruebas.

---

## 🔐 MODO OPCIONAL: CON AUTENTICACIÓN

Si quieres usar autenticación, ahora tienes disponible un sistema completo con Laravel Sanctum.

### 👤 USUARIOS CREADOS

Se han creado 2 usuarios de prueba:

| Usuario | Email | Contraseña | Rol |
|---------|-------|------------|-----|
| Administrador | `admin@taller.com` | `admin123` | Admin |
| Usuario Demo | `demo@taller.com` | `demo123` | Usuario |

---

## 🚀 ENDPOINTS DE AUTENTICACIÓN

### 1. **Registrar Nuevo Usuario**
```
POST /api/register
```

**Body (JSON):**
```json
{
  "name": "Nuevo Usuario",
  "email": "nuevo@taller.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Respuesta:**
```json
{
  "message": "Usuario registrado exitosamente",
  "user": {
    "id": 3,
    "name": "Nuevo Usuario",
    "email": "nuevo@taller.com"
  },
  "access_token": "1|abc123...",
  "token_type": "Bearer"
}
```

**Ejemplo cURL:**
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Nuevo Usuario",
    "email": "nuevo@taller.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

---

### 2. **Login (Iniciar Sesión)**
```
POST /api/login
```

**Body (JSON):**
```json
{
  "email": "admin@taller.com",
  "password": "admin123"
}
```

**Respuesta:**
```json
{
  "message": "Login exitoso",
  "user": {
    "id": 1,
    "name": "Administrador",
    "email": "admin@taller.com"
  },
  "access_token": "2|xyz789...",
  "token_type": "Bearer"
}
```

**Ejemplo cURL:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@taller.com",
    "password": "admin123"
  }'
```

**💡 Guarda el `access_token` que recibes, lo necesitarás para las siguientes peticiones.**

---

### 3. **Obtener Usuario Autenticado**
```
GET /api/me
```

**Headers:**
```
Authorization: Bearer {tu_token_aqui}
```

**Respuesta:**
```json
{
  "user": {
    "id": 1,
    "name": "Administrador",
    "email": "admin@taller.com"
  }
}
```

**Ejemplo cURL:**
```bash
curl http://localhost:8000/api/me \
  -H "Authorization: Bearer 2|xyz789..."
```

---

### 4. **Logout (Cerrar Sesión)**
```
POST /api/logout
```

**Headers:**
```
Authorization: Bearer {tu_token_aqui}
```

**Respuesta:**
```json
{
  "message": "Logout exitoso"
}
```

**Ejemplo cURL:**
```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer 2|xyz789..."
```

---

## 🔒 CÓMO PROTEGER LOS ENDPOINTS

Si quieres que los endpoints requieran autenticación, edita `routes/api.php`:

### Opción 1: Proteger Todos los Endpoints
```php
// Envolver todas las rutas en middleware auth:sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('vehicles', VehicleController::class);
    // ... resto de rutas
});
```

### Opción 2: Proteger Solo Algunos Endpoints
```php
// Rutas públicas (sin autenticación)
Route::get('customers', [CustomerController::class, 'index']);
Route::get('parts', [PartController::class, 'index']);

// Rutas protegidas (requieren autenticación)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('customers', [CustomerController::class, 'store']);
    Route::put('customers/{customer}', [CustomerController::class, 'update']);
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy']);
});
```

---

## 📝 FLUJO COMPLETO DE AUTENTICACIÓN

### 1. Registrar o Hacer Login
```bash
# Login con usuario existente
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@taller.com","password":"admin123"}'
```

**Respuesta:**
```json
{
  "access_token": "2|abc123xyz789..."
}
```

### 2. Usar el Token en Peticiones
```bash
# Guardar el token en una variable (Bash)
TOKEN="2|abc123xyz789..."

# Usar el token en peticiones
curl http://localhost:8000/api/customers \
  -H "Authorization: Bearer $TOKEN"
```

### 3. Cerrar Sesión
```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer $TOKEN"
```

---

## 🔧 USANDO POSTMAN

### 1. Login
- **Method:** POST
- **URL:** `http://localhost:8000/api/login`
- **Body (JSON):**
```json
{
  "email": "admin@taller.com",
  "password": "admin123"
}
```
- Copia el `access_token` de la respuesta

### 2. Configurar Token en Postman
- Ve a la pestaña **Authorization**
- Type: **Bearer Token**
- Token: Pega el `access_token`

### 3. Hacer Peticiones
Ahora todas tus peticiones incluirán automáticamente el token.

---

## 🌐 USANDO JAVASCRIPT/FETCH

```javascript
// 1. Login
async function login(email, password) {
  const response = await fetch('http://localhost:8000/api/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password })
  });
  
  const data = await response.json();
  // Guardar token en localStorage
  localStorage.setItem('token', data.access_token);
  return data;
}

// 2. Hacer peticiones autenticadas
async function getCustomers() {
  const token = localStorage.getItem('token');
  
  const response = await fetch('http://localhost:8000/api/customers', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  
  return await response.json();
}

// 3. Logout
async function logout() {
  const token = localStorage.getItem('token');
  
  await fetch('http://localhost:8000/api/logout', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  localStorage.removeItem('token');
}

// Uso
await login('admin@taller.com', 'admin123');
const customers = await getCustomers();
await logout();
```

---

## ⚙️ CONFIGURACIÓN ACTUAL

### Estado Actual de las Rutas:
```
✅ Rutas de Autenticación: PÚBLICAS
   • POST /api/register
   • POST /api/login

✅ Rutas Protegidas con Auth:
   • POST /api/logout
   • GET /api/me
   • GET /api/user

⚠️  Rutas de Negocio: SIN PROTECCIÓN (acceso libre)
   • Todas las rutas de customers, vehicles, parts, etc.
   • Todas las rutas de reportes
```

**Para cambiar esto, edita:** `routes/api.php`

---

## 🎯 RECOMENDACIONES

### Para Desarrollo:
- ✅ Mantener rutas abiertas (como está ahora)
- ✅ Facilita pruebas y desarrollo

### Para Producción:
- 🔒 Proteger todas las rutas con `auth:sanctum`
- 🔒 Implementar roles y permisos
- 🔒 Agregar rate limiting
- 🔒 Usar HTTPS

---

## 📊 RESUMEN

### Acceso Actual (Sin Auth):
```bash
# Funciona directamente
curl http://localhost:8000/api/customers
```

### Acceso con Auth (Opcional):
```bash
# 1. Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@taller.com","password":"admin123"}'

# 2. Guardar token de la respuesta
TOKEN="tu_token_aqui"

# 3. Usar token en peticiones
curl http://localhost:8000/api/customers \
  -H "Authorization: Bearer $TOKEN"
```

---

## 🔑 CREDENCIALES DE ACCESO

### Usuarios Disponibles:

**Administrador:**
- Email: `admin@taller.com`
- Password: `admin123`

**Usuario Demo:**
- Email: `demo@taller.com`
- Password: `demo123`

---

## ❓ PREGUNTAS FRECUENTES

### ¿Necesito autenticarme para usar la API?
**No**, actualmente todos los endpoints están abiertos. La autenticación es opcional.

### ¿Cómo protejo los endpoints?
Edita `routes/api.php` y envuelve las rutas con `Route::middleware('auth:sanctum')->group(...)`.

### ¿Cuánto dura el token?
Por defecto, los tokens no expiran. Puedes configurar expiración en `config/sanctum.php`.

### ¿Puedo crear más usuarios?
Sí, usa el endpoint `POST /api/register` o crea usuarios directamente en la base de datos.

---

**¡Sistema de autenticación listo! 🎉**

**Modo actual:** Acceso libre (sin autenticación)
**Modo opcional:** Sistema de login con tokens disponible
