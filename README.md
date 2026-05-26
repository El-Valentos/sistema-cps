# Sistema Integral de Pago CPS
## Caja Petrolera de Salud — Cochabamba, Bolivia

---

## 🚀 INSTALACIÓN RÁPIDA (XAMPP/MySQL)

### Requisitos
- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js (solo si deseas compilar assets, no requerido)

### Pasos

**1. Copiar el proyecto a htdocs**
```bash
# Copiar la carpeta cps-pagos-sistema a C:\xampp\htdocs\
```

**2. Abrir una terminal en la carpeta del proyecto y ejecutar:**

```bash
# Instalar dependencias PHP
composer install

# Copiar y configurar el entorno
cp .env.example .env
php artisan key:generate
```

**3. Configurar la base de datos en `.env`:**
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cps_pagos
DB_USERNAME=root
DB_PASSWORD=           # tu password de MySQL (en XAMPP suele estar vacío)
```

**4. Crear la base de datos en phpMyAdmin:**
```sql
CREATE DATABASE cps_pagos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**5. Ejecutar migraciones y seeder:**
```bash
php artisan migrate --seed
```

**6. Crear enlace de almacenamiento:**
```bash
php artisan storage:link
```

**7. Levantar servidores:**
```bash
php artisan serve
```

```bash (otra terminal)
npm intall 
npm run dev 
```

**8. Abrir en el navegador:** http://localhost:8000

---

## 👤 USUARIOS DE PRUEBA

| Email | Contraseña | Rol |
|-------|-----------|-----|
| admin@cps.bo | Admin1234! | Super Admin |
| tesoreria@cps.bo | Tesorer1a! | Tesorería |
| contabilidad@cps.bo | Contab1l! | Contabilidad |

---

## 🔄 FLUJO DEL SISTEMA

```
Tesorería          Contabilidad       Caja
   │                    │               │
   ▼                    ▼               ▼
Crea Orden  ──────►  Genera      ──►  Entrega
de Pago      Aprueba  Cheque         Cheque al
             y Envía                 Beneficiario
```

## 📋 MÓDULOS

- **Dashboard** — Estadísticas por rol
- **Órdenes de Pago** — CRUD + aprobación + PDF
- **Cheques** — Generación, impresión, anulación
- **Tracking** — Seguimiento de estados en tiempo real
- **Beneficiarios** — Gestión de personas/empresas
- **Reportes** — PDF y CSV por período

## 🛠️ COMANDOS ÚTILES

```bash
# Limpiar caché
php artisan optimize:clear

# Ver rutas
php artisan route:list

# Recrear todo (¡borra todos los datos!)
php artisan migrate:fresh --seed
```
