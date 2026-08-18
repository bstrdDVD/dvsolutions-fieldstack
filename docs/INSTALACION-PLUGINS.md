# Guía de Instalación de Plugins

Instrucciones paso a paso para instalar y configurar todos los plugins necesarios.

---

## 📦 Plugins Requeridos

### 1. WooCommerce (Obligatorio)
**Versión:** 8.0+  
**Función:** Gestión de productos, carrito y pagos

#### Instalación
1. Dashboard → Plugins → Agregar nuevo
2. Buscar "WooCommerce"
3. Instalar por WooThemes
4. Activar

#### Configuración Inicial
1. Ir a WooCommerce → Configuración
2. Pestaña General:
   - Moneda: CLP (Pesos Chilenos)
   - País: Chile
   - Ubicación de la tienda: Tu ciudad
3. Pestaña Pagos:
   - Habilitar métodos necesarios
4. Guardar cambios

---

### 2. Advanced Custom Fields Pro (ACF Pro)
**Versión:** 6.0+  
**Función:** Campos personalizados para tours

#### Instalación
1. Dashboard → Plugins → Agregar nuevo
2. Buscar "Advanced Custom Fields"
3. Instalar por Elliot Condon
4. Activar

#### Licencia
- Versión gratis: Suficiente para inicio
- Versión Pro: $99/año (recomendado para profesional)
  1. Ir a ACF → Configuración
  2. Ingresar licencia
  3. Activar

#### Crear Campos de Tours
1. ACF → Groups (Grupos de campos)
2. Crear nuevo grupo: "Tour Details"
3. Agregar campos:

```
- Precio (number)
- Duración (text)
- Dificultad (select: Fácil, Moderada, Difícil, Experto)
- Capacidad Máxima (number)
- Edad Mínima (number)
- Incluye (repeater)
- Requisitos (text area)
- Punto de Encuentro (text)
- Política de Cancelación (wysiwyg)
```

---

### 3. Booking Calendar (o BooklyPress)
**Versión:** Latest  
**Función:** Sistema de reservas con calendario

#### Instalación Opción A: Booking Calendar
1. Dashboard → Plugins → Agregar nuevo
2. Buscar "Booking Calendar"
3. Instalar por Andrey Snezhko
4. Activar

#### Instalación Opción B: BooklyPress (Recomendado)
1. Dashboard → Plugins → Agregar nuevo
2. Buscar "BooklyPress"
3. Instalar por Bookly
4. Activar

#### Configuración
1. Dashboard → Bookly Settings
2. Configurar:
   - Zona horaria: Santiago
   - Duración de slots: 30 minutos
   - Visualización: Mostrar capacidad
3. Crear servicios (tours):
   - Nombre: "Trekking Laguna Azul"
   - Duración: 360 minutos (6 horas)
   - Precio: 89000
4. Crear calendario de disponibilidad

---

### 4. Contact Form 7
**Versión:** 5.7+  
**Función:** Formularios de contacto

#### Instalación
1. Dashboard → Plugins → Agregar nuevo
2. Buscar "Contact Form 7"
3. Instalar por Takayuki Miyoshi
4. Activar

#### Crear Formularios
1. Contact → Forms
2. Crear formulario: "Contacto General"
3. Código formulario:

```
<div>
  <label> Nombre (requerido)
    [text* your-name] </label>
</div>

<div>
  <label> Correo (requerido)
    [email* your-email] </label>
</div>

<div>
  <label> Asunto (requerido)
    [text* your-subject] </label>
</div>

<div>
  <label> Mensaje (requerido)
    [textarea* your-message] </label>
</div>

<div>
  [submit "Enviar"]
</div>
```

4. Pestaña Mail:
   - To: [correo admin]
   - Subject: Nuevo mensaje de [your-name]

---

### 5. WP Mail SMTP
**Versión:** 3.0+  
**Función:** Correos confiables

#### Instalación
1. Dashboard → Plugins → Agregar nuevo
2. Buscar "WP Mail SMTP"
3. Instalar por WPForms
4. Activar

#### Configuración Gmail
1. Dashboard → WP Mail SMTP → Settings
2. From Email: correo@aventura-tourism.com
3. From Name: Aventura Tourism
4. Mailer: Gmail
5. Conectar cuenta Gmail:
   - Hacer clic en "Connect with Gmail"
   - Autorizar acceso
6. Probar: Enviar correo de prueba

#### Configuración SendGrid (Alternativa)
1. Dashboard → WP Mail SMTP → Settings
2. Mailer: SendGrid
3. API Key: [Tu API Key de SendGrid]
4. From Email: correo@aventura-tourism.com
5. Guardar

---

### 6. Yoast SEO
**Versión:** 21.0+  
**Función:** Optimización para motores de búsqueda

#### Instalación
1. Dashboard → Plugins → Agregar nuevo
2. Buscar "Yoast SEO"
3. Instalar por Team Yoast
4. Activar

#### Configuración
1. Dashboard → Yoast SEO → Dashboard
2. Ir a Configuración del Sitio
3. Completar información general:
   - Nombre organización
   - Logo
   - Redes sociales
4. Configurar por tipo de contenido:
   - Tours: Mostrar en resultados búsqueda
   - Posts: Mostrar en resultados búsqueda

#### Optimizar Tours
Para cada tour:
1. Editar tour
2. Ir a sección Yoast SEO
3. Completar:
   - Palabra clave principal
   - Meta descripción
   - Verificar semáforo (verde = bueno)

---

### 7. Wordfence Security
**Versión:** 7.5+  
**Función:** Seguridad del sitio

#### Instalación
1. Dashboard → Plugins → Agregar nuevo
2. Buscar "Wordfence Security"
3. Instalar por Wordfence
4. Activar

#### Configuración Básica
1. Dashboard → Wordfence → Firewall
2. Habilitar:
   - Firewall de aplicación web
   - Protección de fuerza bruta
3. Dashboard → Wordfence → Scan
   - Ejecutar primer escaneo

---

### 8. Akismet Anti-Spam
**Versión:** 5.0+  
**Función:** Protección contra spam

#### Instalación
1. Dashboard → Plugins → Agregar nuevo
2. Buscar "Akismet Anti-Spam"
3. Instalar por Automattic
4. Activar

#### Configuración
1. Ir a Akismet Account
2. Crear cuenta gratuita o premium
3. Copiar API Key
4. Pegar en WordPress

---

### 9. WP Rocket (Opcional pero Recomendado)
**Versión:** 3.11+  
**Función:** Cache y optimización de performance
**Costo:** $39-199 anuales

#### Instalación
1. Descargar desde wprd.ocket.com (requiere licencia)
2. Plugins → Agregar nuevo → Subir plugin
3. Activar

#### Configuración
1. Dashboard → WP Rocket → Settings
2. Habilitar:
   - Cache
   - Minificación de CSS/JS
   - Lazy loading de imágenes
3. Guardar

---

### 10. MonsterInsights (Google Analytics)
**Versión:** 8.0+  
**Función:** Análisis de tráfico

#### Instalación
1. Dashboard → Plugins → Agregar nuevo
2. Buscar "MonsterInsights"
3. Instalar por MonsterInsights
4. Activar

#### Configuración
1. Dashboard → MonsterInsights
2. Conectar con Google Analytics:
   - Hacer clic en "Connect with Google Analytics"
   - Autorizar
3. Seleccionar propiedad de Analytics
4. Listo - comenzará a registrar datos

---

## ✅ Checklist de Instalación

```
BÁSICO
- [ ] WooCommerce instalado y configurado
- [ ] ACF instalado con campos de tours
- [ ] Sistema de reservas configurado
- [ ] Banco de Chile integrado

CORREOS Y NOTIFICACIONES
- [ ] WP Mail SMTP configurado
- [ ] Correos de prueba funcionando
- [ ] Contact Form 7 funcionando

SEGURIDAD
- [ ] Wordfence activado y escaneado
- [ ] Akismet configurado
- [ ] SSL/HTTPS funcionando

MARKETING
- [ ] Yoast SEO configurado
- [ ] MonsterInsights conectado
- [ ] Sitemaps generados

PERFORMANCE
- [ ] WP Rocket configurado (si se usa)
- [ ] Imágenes optimizadas
- [ ] Cache habilitada
```

---

## 🔧 Troubleshooting

### Problema: Conflicto entre plugins
**Síntoma:** Errores JavaScript o estilos rotos

**Solución:**
1. Desactivar todos los plugins
2. Activar de uno en uno
3. Encontrar el culpable
4. Reportar al desarrollador o buscar alternativa

### Problema: Correos no se envían
**Síntoma:** Formularios se envían pero no llegan correos

**Solución:**
```bash
# Verificar en log de WordPress
tail -f /var/www/html/wp-content/debug.log | grep "SMTP\|mail"

# Verificar configuración SMTP
- Host: smtp.gmail.com
- Puerto: 587
- Seguridad: TLS
- Verificar credenciales
```

### Problema: Reservas no aparecen en calendario
**Síntoma:** Se crea reserva pero no se muestra en calendario

**Solución:**
1. Verificar que plugin de calendario está activo
2. Verificar que tour está vinculado a servicio en Bookly
3. Verificar que fecha está en rango de disponibilidad
4. Limpiar cache (si WP Rocket está activo)

---

## 📞 Soporte Plugin

| Plugin | Sitio | Email | Chat |
|--------|-------|-------|------|
| WooCommerce | woocommerce.com | support@woocommerce.com | Sí |
| ACF Pro | advancedcustomfields.com | support@advancedcustomfields.com | Sí |
| BooklyPress | bookly.com | support@bookly.com | Sí |
| WP Mail SMTP | wpforms.com | support@wpforms.com | Sí |
| Yoast SEO | yoast.com | support@yoast.com | No |
| Wordfence | wordfence.com | support@wordfence.com | Sí |

---

**Versión:** 1.0.0  
**Última actualización:** Agosto 2026
