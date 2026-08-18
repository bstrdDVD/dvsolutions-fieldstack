# Preguntas Frecuentes (FAQ)

Respuestas a las preguntas más comunes sobre el sitio de Aventura Tourism.

---

## 🎯 General

### ¿Cómo puedo empezar rápidamente?
1. Clonar el repositorio
2. Copiar `.env.example` a `.env`
3. Ejecutar `docker-compose up -d`
4. Esperar 30-60 segundos
5. Acceder a `http://localhost:8080`
6. Usuario: `wordpress` | Contraseña: `wordpresspass`

Ver [README.md](../README.md) para detalles completos.

---

### ¿Qué requisitos técnicos necesito?
- Docker & Docker Compose
- Git
- Navegador moderno (Chrome, Firefox, Safari, Edge)
- Para producción: Servidor Linux, dominio, SSL

---

### ¿Puedo usar esto sin Docker?
Sí, pero necesitarás:
- Servidor Apache/Nginx
- PHP 8.0+
- MySQL 8.0+
- Instalación manual de WordPress

No es recomendado para desarrollo. Docker simplifica mucho.

---

## 🏨 Tours

### ¿Cómo creo un nuevo tour?
1. Ir a Dashboard → Tours → Agregar nuevo
2. Llenar información básica (nombre, descripción, precio)
3. Agregar campos personalizados (duración, dificultad, capacidad)
4. Subir galería de imágenes
5. Publicar

Ver [INSTALACION-PLUGINS.md](./INSTALACION-PLUGINS.md#crear-campos-de-tours) para detalles sobre campos.

---

### ¿Puedo tener dos tours activos simultáneamente?
Sí, completamente. Puedes tener:
- Tour 1 (Trekking): Lunes y Miércoles
- Tour 2 (Escalada): Martes y Jueves

Cada uno con sus propios horarios, precios y capacidades.

---

### ¿Cómo establezco la capacidad máxima por tour?
1. Editar tour
2. Ir a campo "Capacidad Máxima"
3. Ingresar número (ej: 20)
4. Guardar

La disponibilidad se calcula automáticamente basada en reservas.

---

### ¿Puedo cambiar el precio después de crear el tour?
Sí, pero afectará a futuras reservas, no a las existentes. Para cambiar precio de reserva existente, hazlo manualmente en el admin.

---

## 📅 Reservas y Disponibilidad

### ¿Cómo funciona la disponibilidad?
- Cliente selecciona tour
- Sistema muestra calendario con fechas disponibles
- Cliente selecciona fecha y horario
- Se verifica disponibilidad en tiempo real
- Si hay lugar, procede a pago

---

### ¿Qué pasa si un cliente reserva en un horario lleno?
El sistema valida en tiempo real. Si otro cliente llena el último lugar justo cuando este cliente está pagando:
1. Su pago procesa
2. Se crea reserva en lista de espera
3. Se notifica al cliente
4. Si alguien cancela, se le ofrece ese lugar

---

### ¿Puedo cancelar una reserva?
Sí, desde dashboard:
1. Ir a Reservas
2. Seleccionar reserva
3. Hacer clic en "Cancelar"
4. Confirmar

Se reembolsa automáticamente si ya fue pagada.

---

### ¿Puedo cambiar la fecha de una reserva?
Actualmente no hay opción automática. Solución:
1. Cancelar reserva (se devuelve dinero)
2. Cliente crea nueva reserva para nueva fecha
3. Paga nuevamente

Futuro: Agregar cambio de fecha sin re-pago.

---

### ¿Cuál es la política de cancelación?
Por defecto: Cancelación gratuita hasta 24h antes del tour.

Puedes cambiar por tour:
1. Editar tour
2. Agregar campo "Política de Cancelación"
3. Especificar: "Cancelación hasta X horas antes"

---

## 💳 Pagos

### ¿Qué métodos de pago soporta?
Solo WebCheckout de Banco de Chile actualmente, que acepta:
- Tarjetas de crédito (Visa, MasterCard, American Express)
- Tarjetas de débito
- Transferencias desde app móvil

---

### ¿En qué moneda se cobran los tours?
Pesos Chilenos (CLP). El sistema está configurado en CLP.

Para agregar USD u otra moneda:
1. Dashboard → WooCommerce → Configuración
2. Cambiar moneda
3. Actualizar precios de tours

---

### ¿Cuáles son los números de prueba para pagar?

**Visa**
```
Número: 4051885115270061
Vencimiento: 12/27
CVV: 123
RUT: 11.111.111-1
```

**MasterCard**
```
Número: 5186059559590568
Vencimiento: 01/26
CVV: 123
RUT: 11.111.111-1
```

Estos solo funcionan en ambiente **Sandbox** (desarrollo).

---

### ¿Cómo paso a producción con dinero real?
1. Obtener credenciales reales de Banco de Chile
2. Cambiar en `.env`: `BCH_ENVIRONMENT=production`
3. Actualizar `BCH_COMMERCE_CODE` y `BCH_API_KEY`
4. Cambiar URL callback a dominio real
5. Hacer prueba con transacción real (pequeño monto)
6. Verificar que se procesa correctamente

---

### ¿Qué comisión cobra Banco de Chile?
Depende de tu contrato. Típicamente:
- 2-3% del monto transado
- $50-100 por transacción
- Consulta con tu ejecutivo de Banco de Chile

---

### ¿Cómo sé si un pago fue procesado?
El sistema actualiza automáticamente:
1. Reserva cambia de "pendiente" a "pagada"
2. Cliente recibe correo de confirmación
3. Dashboard muestra pago completado
4. Tabla `wp_tour_payments` se actualiza

Si no se actualiza en 10 minutos, contactar soporte.

---

## 📧 Correos

### ¿Por qué no llegan correos?
Verificar:
1. ¿Está WP Mail SMTP configurado? (`WP Mail SMTP → Settings`)
2. ¿Credenciales correctas? (Gmail: contraseña de app, no contraseña regular)
3. ¿Correo de prueba funciona? (Hacer clic en "Test Email")
4. Ver logs: `/var/www/html/wp-content/debug.log`

---

### ¿Qué correos se envían automáticamente?
1. **Confirmación de Reserva**: Cuando se crea reserva
2. **Confirmación de Pago**: Cuando pago se procesa
3. **Recordatorio 24h**: 24 horas antes del tour
4. **Recepción de Cancelación**: Cuando cliente cancela

---

### ¿Puedo personalizar los correos?
Sí:
1. Dashboard → WooCommerce → Emails
2. Editar plantilla
3. Cambiar contenido, colores, logo
4. Guardar

---

## 👥 Usuarios

### ¿Cómo se registra un cliente?
Opción 1: Registro en sitio web
- Ir a "Mi Cuenta"
- Hacer clic en "Registrarse"
- Llenar formulario
- Confirmar email

Opción 2: Registro automático
- Si cliente completa reserva sin cuenta
- Se crea cuenta automáticamente
- Recibe contraseña temporal

---

### ¿Qué datos puedo ver de un cliente?
Como admin:
1. Nombre, email, teléfono
2. Historial de reservas
3. Monto gastado total
4. Comentarios/requisitos especiales

No ves datos de tarjeta (por seguridad, los maneja Banco de Chile).

---

### ¿Cómo reset la contraseña de un cliente?
1. Dashboard → Usuarios
2. Seleccionar usuario
3. Hacer clic en "Enviar enlace de reset"
4. Cliente recibe correo con enlace

---

## 📊 Administración

### ¿Cómo descargo un reporte de reservas?
1. Dashboard → Reservas
2. Filtrar por rango de fechas (si lo necesitas)
3. Arriba a la derecha: "Exportar CSV"
4. Abre en Excel

---

### ¿Cómo veo quién viene a cada tour?
1. Dashboard → Reservas
2. Filtrar por tour
3. Hacer clic en reserva
4. Ver "Nombres de Participantes"
5. Imprimir o exportar lista

---

### ¿Puedo hacer confirmación de asistencia?
No automáticamente. Solución manual:
1. Descargar lista de reservas
2. Llevar a tour
3. Marcar asistentes
4. Actualizar en WordPress

Futuro: Agregar QR code para check-in.

---

## 🔒 Seguridad

### ¿Es seguro procesar pagos aquí?
Sí, porque:
- Datos de tarjeta nunca llegan a tu servidor
- Los maneja directamente Banco de Chile
- Utilizas protocolo seguro (HTTPS/SSL)
- Implementas validaciones en backend

---

### ¿Cómo protejo datos de clientes?
1. Hacer backups regulares
2. Mantener plugins actualizados
3. Cambiar contraseña admin
4. Usar Wordfence Security
5. HTTPS/SSL obligatorio
6. No compartir logs con datos sensibles

---

### ¿Qué pasa con datos si se va el servidor?
Con Docker Compose:
- Base de datos: Volumen persistente en máquina
- Archivos: Volumen persistente en máquina
- No se pierden si reinicia contenedor
- Hacer backup periódico a servicio en nube

---

## 🚀 Producción

### ¿Cuáles son los pasos para lanzar a producción?
1. Revisar [README.md - Deployment](../README.md#deployment-a-producción)
2. Revisar [BANCO-CHILE-INTEGRACION.md - Producción](./BANCO-CHILE-INTEGRACION.md#7-deployment-a-producción)
3. Hacer pruebas con transacción real
4. Monitorear primeros días
5. Ajustar según feedback

---

### ¿Cuánto cuesta mantener el servidor?
Depende de proveedor:
- AWS: $10-50/mes
- Linode: $5-20/mes
- DigitalOcean: $5-20/mes
- GCP: $10-40/mes

Más si usas CDN (CloudFlare: gratis a $20+/mes).

---

### ¿Cómo hago backups?
Opción 1: Plugin (UpdraftPlus)
```
Plugins → Agregar → UpdraftPlus
Configurar: Backup diario a Google Drive
```

Opción 2: Manual
```bash
# Backup de BD
docker-compose exec mysql mysqldump -u wordpress -pwordpresspass wordpress > backup.sql

# Backup de archivos
tar -czf wordpress-backup.tar.gz wordpress/
```

---

## 🤝 Soporte

### ¿A quién contacto si algo falla?
1. **Primer nivel:** Ver FAQ y logs
2. **Desarrollo:** Contactar equipo de desarrollo
3. **Banco de Chile:** Si es problema de pago
4. **Plugin:** Ver soporte del plugin

---

### ¿Dónde veo los logs de errores?
```bash
# En servidor
tail -f /var/www/html/wp-content/debug.log

# En Docker
docker-compose logs wordpress | tail -50
docker-compose logs mysql | tail -50
```

---

### ¿Puedo modificar el código?
Sí, el proyecto es personalizable:
1. Crear rama feature
2. Hacer cambios
3. Probar localmente
4. Commit y push
5. Crear pull request para revisión

Ver estructura en [ARQUITECTURA.md](../ARQUITECTURA.md).

---

## ❓ Preguntas Técnicas

### ¿Qué es Docker?
Contenedor que empaqueta WordPress, MySQL, PHP, Nginx en un solo "box" que funciona igual en tu laptop, servidor de pruebas o producción.

Ventaja: No necesitas instalar cosas manualmente.

---

### ¿Por qué MySQL 8.0?
Versión actual, soportada, rápida. Plugins modernos de WordPress requieren 8.0+.

---

### ¿Puedo usar PostgreSQL en lugar de MySQL?
Tecnicamente sí, pero no es recomendado. WordPress está optimizado para MySQL. Usaría MySQL.

---

### ¿Cuánto espacio en disco necesito?
- WordPress: 300 MB
- Base de datos (vacía): 100 MB
- Imágenes: Depende (10-100 MB inicialmente)
- **Total inicial:** ~500 MB

Por cada 1000 reservas: +50 MB

---

## 📞 ¿Necesitas Más Ayuda?

Si no encuentras respuesta aquí:
1. Revisa [README.md](../README.md)
2. Revisa [ARQUITECTURA.md](../ARQUITECTURA.md)
3. Revisa logs de WordPress
4. Contacta al equipo de desarrollo

---

**Versión:** 1.0.0  
**Última actualización:** Agosto 2026
