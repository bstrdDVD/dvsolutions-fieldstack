# Arquitectura - Sitio de Turismo Aventura WordPress

## Visión General
Sitio web de turismo aventura con sistema de reservas integrado, calendario de disponibilidad y pagos mediante WebCheckout de Banco de Chile.

### Características Principales
- **Tours Dinámicos**: Soporte para múltiples tours con horarios distintos
- **Sistema de Reservas**: Calendario interactivo con disponibilidad en tiempo real
- **Pagos Seguros**: Integración con WebCheckout de Banco de Chile
- **Gestión de Clientes**: Base de datos de reservantes con historial
- **Confirmaciones**: Correos automáticos de reserva y confirmación de pago

---

## Stack Tecnológico

### Core
- **CMS**: WordPress 6.4+
- **PHP**: 8.0+
- **Base de Datos**: MySQL 8.0 / MariaDB 10.5+
- **Servidor**: Apache/Nginx con SSL

### Plugins Principales
1. **WooCommerce** - Gestión de productos/tours y pagos
2. **Booking Calendar Pro** - Sistema de reservas y calendario
3. **Banco de Chile Payment Gateway** - Integración de pagos
4. **Advanced Custom Fields (ACF)** - Campos personalizados para tours
5. **Contact Form 7** - Formularios de contacto
6. **Yoast SEO** - Optimización de motores de búsqueda
7. **WP Mail SMTP** - Correos confiables
8. **MonsterInsights** - Analytics

### Tema Base
- Tema personalizado basado en Astra o Neve (responsive, SEO-friendly)

---

## Estructura de Directorios

```
/wordpress/
├── wp-content/
│   ├── themes/
│   │   └── adventure-tourism/
│   │       ├── css/
│   │       ├── js/
│   │       ├── template-parts/
│   │       └── functions.php
│   ├── plugins/
│   │   ├── bch-payment-gateway/
│   │   ├── tour-management/
│   │   └── reservas-calendario/
│   └── uploads/
├── wp-config.php
├── docker-compose.yml
├── .env.example
└── README.md
```

---

## Datos y Estructura de Base de Datos

### Tours (Post Type: `tour`)
```sql
- ID
- Nombre del Tour
- Descripción
- Precio
- Duración
- Nivel de Dificultad
- Imágenes
- Capacidad Máxima
- Requisitos de Edad
- Incluye (equipo, guía, etc.)
```

### Horarios de Tours (Custom Table: `tour_schedules`)
```sql
- ID
- Tour ID
- Día de Semana (0-6)
- Hora de Inicio
- Hora de Fin
- Capacidad Máxima del Horario
- Activo (Sí/No)
```

### Reservas (Custom Table: `reservations`)
```sql
- ID
- Cliente ID
- Tour ID
- Schedule ID
- Fecha de Reserva
- Cantidad de Personas
- Nombres de Participantes
- Email
- Teléfono
- Estado (pendiente, confirmada, pagada, cancelada)
- Monto Total
- Referencia Pago
- Fecha Creación
```

### Pagos (Custom Table: `tour_payments`)
```sql
- ID
- Reserva ID
- Monto
- Moneda (CLP)
- Estado (pendiente, procesando, completado, fallido)
- ID Transacción Banco
- Fecha de Pago
- Comprobante
```

---

## Integración Banco de Chile (WebCheckout)

### Flujo de Pagos
1. Cliente completa reserva
2. Se genera carrito en WooCommerce
3. Redirección a WebCheckout de Banco de Chile
4. Cliente completa pago
5. Banco notifica resultado vía webhook
6. Sistema confirma reserva y envía comprobante

### Credenciales Necesarias
```
- Código Comercio: [Tu código]
- Llave Secreta: [Tu llave]
- URL Test: https://webcheckout.transbank.cl
- URL Producción: https://webpay.transbank.cl
```

### Endpoints Webhook
```
POST /wp-json/adventure-tourism/v1/payment-callback
- Valida firma del banco
- Actualiza estado de reserva
- Envía correos
```

---

## Flujos Principales

### 1. Reserva de Tour
```
Cliente selecciona tour
    ↓
Selecciona fecha y horario
    ↓
Ingresa cantidad de personas y datos
    ↓
Revisa total
    ↓
Procede a pago (WebCheckout)
    ↓
Pago completado
    ↓
Confirmación por correo + acceso a panel
```

### 2. Gestión Administrativa
```
Admin accede a dashboard
    ↓
Ve reservas pendientes/confirmadas
    ↓
Puede descargar lista de asistencia
    ↓
Confirma o cancela reservas
    ↓
Envía recordatorios 24h antes
```

---

## Seguridad

### Implementación
- SSL/TLS obligatorio
- Validación de todas las entradas
- Protección CSRF en formularios
- Rate limiting en endpoints de API
- Encriptación de datos de tarjeta (delegado a Banco)
- Logs de todas las transacciones
- Backups automáticos de BD

### Cumplimiento
- PDPA (Protección de Datos Chile)
- PCI DSS (solo credenciales por Banco de Chile)
- RGPD (si hay clientes EU)

---

## Fases de Implementación

### Fase 1: Setup Inicial
- [ ] Instalación y configuración de WordPress
- [ ] Instalación de plugins base
- [ ] Configuración de tema personalizado
- [ ] Setup de base de datos

### Fase 2: Gestión de Tours
- [ ] Post Type personalizado para Tours
- [ ] Custom Post Type UI
- [ ] ACF para campos adicionales
- [ ] Galería de imágenes

### Fase 3: Sistema de Reservas
- [ ] Plugin de calendario
- [ ] Tabla de horarios
- [ ] Cálculo de disponibilidad
- [ ] Sistema de carrito

### Fase 4: Pagos
- [ ] Plugin gateway Banco de Chile
- [ ] Configuración de credenciales
- [ ] Testing en ambiente sandbox
- [ ] Webhooks de notificación

### Fase 5: Panel de Cliente
- [ ] Página de mis reservas
- [ ] Descarga de comprobantes
- [ ] Historial de tours
- [ ] Cambio de datos de contacto

### Fase 6: Panel Administrativo
- [ ] Dashboard de reservas
- [ ] Reportes de ocupación
- [ ] Exportación de datos
- [ ] Gestión de horarios

### Fase 7: Optimización y Lanzamiento
- [ ] Testing E2E
- [ ] Optimización de performance
- [ ] SEO
- [ ] Lanzamiento a producción

---

## Roadmap Futuro
- Integración con redes sociales
- App móvil nativa
- Sistema de reseñas y calificaciones
- Marketing automation (emailings)
- Programa de lealtad
- Integración con plataformas de tour (Viator, GetYourGuide)

---

## Referencias Técnicas
- [WordPress Codex](https://developer.wordpress.org/plugins/intro/)
- [WooCommerce Docs](https://woocommerce.com/documentation/)
- [WebCheckout Banco de Chile](https://www.transbank.cl/desarrolladores/webpay/productos)
- [ACF Documentation](https://www.advancedcustomfields.com/resources/)
