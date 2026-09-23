# SGI - Sistema de Gestión de Inventarios

Sistema integral multi-empresa para la administración centralizada de inventarios, control de activos y trazabilidad operativa para el grupo corporativo (**Nodo**, **NAO**, **Grat**, **Durga** y empresas asociadas).

---

## Características Principales

- **Gestión Multi-Empresa y Ubicaciones:** Control segmentado por razones sociales internas y externas, bodegas, sucursales y áreas operativas.
- **Clasificación Integral de Productos:**
  - **Activos Fijos:** Equipos de cómputo, mobiliario, maquinaria y bienes duraderos con número de serie, factura y responsable asignado.
  - **Consumibles:** Insumos operativos, suministros y papelería con control de existencias mínimas y máximas.
  - **Compra-Venta:** Mercancía destinada a comercialización, proyectos y transacciones con clientes.
  - **Vehículos:** Flotilla corporativa y asignaciones vehiculares.
- **Cartas Responsivas Institucionales:**
  - Emisión de formatos oficiales con sellos, firmas digitales y tablas detalladas de bienes.
  - Descarga instantánea en PDF y en Word (.docx) editable para formalización jurídica y administrativa.
- **Etiquetado y Códigos de Barras:** Generación de etiquetas con código de barras estandarizado y soporte para lectores o escáneres ópticos.
- **Seguridad y Permisos Dinámicos:** Arquitectura de roles (Super Admin, Almacenistas, Auxiliares) con control granular por categoría, empresa y ubicación sincronizado en tiempo real.
- **Plantillas y Reportería:** Exportación e importación de catálogos mediante hojas de cálculo en Excel.

---

## Stack Tecnológico

- **Backend:** PHP 8.2+ / Laravel 11
- **Frontend:** Blade Templates, Livewire, Bootstrap 5, FontAwesome / Bootstrap Icons
- **Documentación y Reportes:** DomPDF, PhpWord, PhpSpreadsheet
- **Base de Datos:** MySQL 8.4+
- **Control de Versiones:** Git / GitHub

---

## Requisitos del Entorno

1. Servidor web local (Laragon, XAMPP o Docker)
2. PHP >= 8.2 con extensiones `pdo_mysql`, `zip`, `gd`, `xml`, `mbstring`
3. Composer >= 2.0
4. MySQL >= 8.0

---

## Instalación y Puesta en Marcha

```bash
# 1. Clonar el repositorio
git clone https://github.com/tlacomulcos751-web/SGI.git
cd SGI

# 2. Instalar dependencias PHP
composer install

# 3. Configurar variables de entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env y migrar
php artisan migrate

# 5. Iniciar servidor de desarrollo
php artisan serve
```

---

© Sistema de Gestión de Inventarios - Grupo Nodo
