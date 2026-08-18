# Aventura Tourism - Sitio Web de Turismo Aventura

Sistema completo de WordPress para gestionar tours de aventura con integración de reservas, calendario de disponibilidad y pagos seguros mediante WebCheckout de Banco de Chile.

## 📋 Características

✅ **Gestión de Tours Dinámicos** - Múltiples tours con horarios distintos  
✅ **Sistema de Reservas con Calendario** - Disponibilidad en tiempo real  
✅ **Pagos Seguros** - WebCheckout de Banco de Chile integrado  
✅ **Panel de Cliente** - Historial de reservas y comprobantes  
✅ **Panel Administrativo** - Dashboard de ocupación y reportes  
✅ **Correos Automáticos** - Confirmaciones y recordatorios  
✅ **Responsive Design** - Acceso desde cualquier dispositivo  

---

## 🚀 Inicio Rápido

### Requisitos Previos
- Docker & Docker Compose
- Git
- Navegador web moderno

### Instalación (5 minutos)

1. **Clonar el repositorio**
```bash
git clone <repo-url>
cd dvsolutions-fieldstack
git checkout claude/wordpress-adventure-tourism-site-idlgjf
```

2. **Configurar variables de entorno**
```bash
cp .env.example .env
# Editar .env con tus credenciales de Banco de Chile
nano .env
```

3. **Iniciar contenedores**
```bash
docker-compose up -d
```

4. **Esperar a que se inicie WordPress** (aprox. 30-60 segundos)
```bash
docker-compose logs -f wordpress
# Buscar: "apache2 -D FOREGROUND"
```

5. **Acceder a WordPress**
```
URL: http://localhost:8080
Usuario: wordpress
Contraseña: wordpresspass
```

6. **Acceder a PhpMyAdmin** (opcional)
```
URL: http://localhost:8081
Usuario: wordpress
Contraseña: wordpresspass
```

---

## 🔧 Configuración Inicial

### 1. Instalación de Plugins

Una vez dentro de WordPress:

1. Ir a **Plugins → Agregar nuevo**
2. Buscar e instalar los siguientes plugins:
   - **WooCommerce** - Gestión de productos y pagos
   - **ACF Pro** (Advanced Custom Fields) - Campos personalizados
   - **Booking Calendar** - Sistema de reservas
   - **Contact Form 7** - Formularios
   - **WP Mail SMTP** - Correos confiables
   - **Yoast SEO** - Optimización SEO
   - **Akismet** - Protección contra spam
   - **Wordfence Security** - Seguridad

3. Activar todos los plugins

### 2. Configurar Banco de Chile (WebCheckout)

#### Sandbox (Desarrollo)
1. Ir a **Configuración → Banco de Chile**
2. Seleccionar ambiente: **Desarrollo (Sandbox)**
3. Completar credenciales:
   - Código de Comercio: `597055555540` (código de prueba)
   - API Key: `tu_api_key_test`
   - Secret Key: `tu_secret_key_test`

#### Producción
Una vez en producción, cambiar a ambiente **Producción** y actualizar credenciales reales.

### 3. Configurar WooCommerce

1. Ir a **WooCommerce → Configuración**
2. Pestaña **General**:
   - Moneda: CLP (Pesos Chilenos)
   - País/Región: Chile
3. Pestaña **Pagos**:
   - Habilitar "Banco de Chile - WebCheckout"
   - Ingresar credenciales nuevamente aquí si es necesario

### 4. Configurar Correos (SMTP)

1. Ir a **WP Mail SMTP**
2. Seleccionar proveedor: Gmail o SendGrid
3. Configurar credenciales (ver `.env` para referencia)
4. Enviar correo de prueba

---

## 📝 Crear Tours

### Método 1: A través de WordPress Admin

1. Ir a **Tours → Agregar nuevo**
2. Completar campos:
   - **Título**: Nombre del tour
   - **Descripción**: Detalles del tour
   - **Precio**: Precio por persona en CLP
   - **Duración**: 2 horas, 4 horas, día completo, etc.
   - **Dificultad**: Fácil, Moderada, Difícil, Experto
   - **Capacidad Máxima**: Número de personas
   - **Requisitos de Edad**: Si aplica
   - **Galería de Imágenes**: Fotos del tour
   - **Incluye**: Qué está incluido (guía, equipo, etc.)

3. Guardar como borrador

### Método 2: API REST

```bash
curl -X POST http://localhost:8080/wp-json/wp/v2/tour \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "title": "Trekking a la Laguna Azul",
    "content": "Descripción completa del tour...",
    "acf": {
      "price": 89000,
      "duration": "6 horas",
      "difficulty": "moderada",
      "max_capacity": 15
    }
  }'
```

---

## 🗓️ Configurar Horarios de Tours

### Crear Horarios para un Tour

1. Ir a **Tours → [Nombre del Tour] → Horarios**
2. Hacer clic en **Agregar Horario**
3. Completar:
   - **Día de Semana**: Lunes a Domingo (puedes repetir para múltiples horarios)
   - **Hora de Inicio**: 08:00 AM
   - **Hora de Fin**: 02:00 PM
   - **Capacidad Máxima**: 20 personas
   - **Activo**: Marcar para habilitarlo

4. Guardar

### Ejemplo: Tour con 2 Horarios Distintos

**Tour 1: Trekking Laguna Azul**
- Horario 1: Lunes 8:00 AM - 2:00 PM (20 personas)
- Horario 2: Miércoles 8:00 AM - 2:00 PM (20 personas)
- Horario 3: Sábado 8:00 AM - 2:00 PM (25 personas)

**Tour 2: Escalada en Roca** (agregado posteriormente)
- Horario 1: Martes 9:00 AM - 1:00 PM (12 personas)
- Horario 2: Jueves 9:00 AM - 1:00 PM (12 personas)

---

## 💳 Flujo de Reservas y Pagos

### 1. Cliente Realiza Reserva

1. Cliente accede a página del tour
2. Selecciona una fecha y horario disponible
3. Ingresa cantidad de personas
4. Completa datos personales (nombre, email, teléfono)
5. Revisa total a pagar
6. Hace clic en "Proceder al Pago"

### 2. Pago con WebCheckout

1. Redirección a Banco de Chile
2. Cliente ingresa datos de tarjeta de crédito/débito
3. Banco procesa pago
4. Respuesta de resultado

### 3. Confirmación

1. Sistema recibe notificación de Banco
2. Reserva se marca como "Pagada"
3. Se envía correo de confirmación con:
   - Número de reserva
   - Detalles del tour
   - Instrucciones previas
   - Comprobante de pago
4. Cliente accede a "Mis Reservas" en su perfil

---

## 📊 Panel Administrativo

### Dashboard

Acceder a **Dashboard → Aventura Tourism** para ver:
- Reservas pendientes de confirmación
- Ingresos totales del mes
- Tasa de ocupación por tour
- Tours más populares

### Gestionar Reservas

1. Ir a **Reservas**
2. Filtrar por:
   - Estado (Pendiente, Confirmada, Pagada, Cancelada)
   - Fecha
   - Tour
3. Acciones disponibles:
   - Ver detalles
   - Confirmar/Cancelar reserva
   - Descargar lista de asistencia
   - Enviar recordatorio
   - Ver comprobante de pago

### Reportes

1. Ir a **Reportes**
2. Generar reportes por:
   - Rango de fechas
   - Tour específico
   - Estado de pago
   - Descargar como CSV/Excel

---

## 🔒 Seguridad y Cumplimiento

### Implementado

✅ SSL/TLS obligatorio (HTTPS)  
✅ Validación de todas las entradas  
✅ Protección CSRF  
✅ Rate limiting en endpoints  
✅ Logs de transacciones  
✅ Encriptación de datos sensibles  
✅ Cumplimiento PDPA (Chile)  

### Checklist de Seguridad Producción

- [ ] Cambiar contraseña admin WordPress
- [ ] Instalar y configurar Wordfence Security
- [ ] Habilitar autenticación de dos factores
- [ ] Configurar backups automáticos
- [ ] Configurar HTTPS con certificado SSL real
- [ ] Revisar permisos de archivos (644/755)
- [ ] Deshabilitar editor de temas/plugins
- [ ] Cambiar prefijo de base de datos
- [ ] Remover usuarios de prueba
- [ ] Validar todas las credenciales del Banco

---

## 📞 Soporte Banco de Chile

### Documentación WebCheckout
- [Portal de Desarrolladores](https://www.transbank.cl/desarrolladores/webpay/productos)
- [API Reference](https://www.transbank.cl/desarrolladores/webpay/documentacion)
- [Testing Guide](https://www.transbank.cl/desarrolladores/webpay/productos/test)

### Números de Prueba (Sandbox)
**Tarjeta Visa de Prueba**
- Número: 4051885115270061
- Mes: 12
- Año: 27
- CVV: 123
- RUT: 11.111.111-1

**Tarjeta MasterCard de Prueba**
- Número: 5186059559590568
- Mes: 01
- Año: 26
- CVV: 123

---

## 🛠️ Estructura del Proyecto

```
/
├── ARQUITECTURA.md              # Documentación técnica
├── README.md                     # Este archivo
├── docker-compose.yml            # Orquestación de contenedores
├── .env.example                  # Variables de entorno (copiar a .env)
├── php.ini                       # Configuración PHP
│
├── wordpress/                    # Código de WordPress
│   ├── wp-content/
│   │   ├── themes/
│   │   │   └── adventure-tourism/
│   │   │       ├── css/
│   │   │       ├── js/
│   │   │       ├── template-parts/
│   │   │       └── functions.php
│   │   ├── plugins/
│   │   │   ├── tour-management/      # Plugin personalizado de tours
│   │   │   ├── bch-payment-gateway/  # Plugin de pago Banco de Chile
│   │   │   └── reservas-calendario/  # Plugin de reservas
│   │   └── uploads/
│   │
│   └── wp-config.php
│
├── sql/
│   └── init.sql                  # Script de inicialización de BD
│
└── docs/
    ├── api-reference.md          # Documentación de API
    ├── banco-chile-integration.md # Guía de integración Banco de Chile
    └── faq.md                     # Preguntas frecuentes
```

---

## 🚀 Deployment a Producción

### Requisitos
- Servidor Linux con Docker
- Dominio con SSL
- Dirección IP estática
- Banda ancha suficiente

### Pasos

1. **Preparar servidor**
```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Clonar repositorio
git clone <repo-url> /opt/aventura-tourism
cd /opt/aventura-tourism
```

2. **Configurar SSL**
```bash
# Usar Let's Encrypt con certbot
sudo certbot certonly --standalone -d tudominio.com
```

3. **Configurar .env**
```bash
# Editar variables de producción
cp .env.example .env
nano .env
# Cambiar todas las URLs a dominio real
# Cambiar BCH_ENVIRONMENT a "production"
# Ingresar credenciales reales de Banco de Chile
```

4. **Iniciar servicios**
```bash
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

5. **Configurar backup automático**
```bash
# Ver instrucciones en docs/backup-strategy.md
```

---

## 📱 Características Futuras

- [ ] Aplicación móvil iOS/Android
- [ ] Sistema de puntos de lealtad
- [ ] Integración con plataformas (Viator, GetYourGuide)
- [ ] Chat en vivo con soporte
- [ ] Reseñas y calificaciones
- [ ] Descuentos para grupos
- [ ] Programación automática de recordatorios
- [ ] Análisis predictivo de ocupación

---

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Crear un branch feature: `git checkout -b feature/mi-feature`
2. Hacer commit: `git commit -am 'Agregar feature'`
3. Push: `git push origin feature/mi-feature`
4. Abrir Pull Request

---

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Ver LICENSE para detalles.

---

## 📧 Contacto y Soporte

**Para soporte técnico:**
- Email: support@aventura-tourism.com
- Teléfono: +56-XXXX-XXXX
- Sistema de tickets: https://soporte.aventura-tourism.com

**Para cambios en Banco de Chile:**
- Ejecutivo Banco: +56-XXXX-XXXX
- Email ejecutivo: ejecutivo@bch.cl

---

## ⚠️ IMPORTANTE - Antes de Lanzar a Producción

- [ ] Revisar ARQUITECTURA.md completamente
- [ ] Cambiar todas las contraseñas por defecto
- [ ] Validar configuración de Banco de Chile con credenciales reales
- [ ] Realizar pruebas de pago en sandbox
- [ ] Revisar logs de transacciones
- [ ] Configurar backups automáticos
- [ ] Implementar monitoreo de sitio
- [ ] Documentar procedimientos de soporte
- [ ] Capacitar equipo de administración
- [ ] Realizar prueba de carga
- [ ] Obtener certificado SSL válido
- [ ] Implementar WAF (Web Application Firewall)

---

**Versión:** 1.0.0  
**Última actualización:** Agosto 2026  
**Estado:** En desarrollo
