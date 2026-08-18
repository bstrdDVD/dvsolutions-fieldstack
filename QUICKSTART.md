# ⚡ Guía de Inicio Rápido

Pasos esenciales para tener el sitio funcionando en 5 minutos.

---

## 1️⃣ Clonar y Configurar

```bash
# Clonar repo
git clone <repo-url>
cd dvsolutions-fieldstack

# Preparar variables de entorno
cp .env.example .env

# Editar .env (opcional, valores por defecto funcionan para desarrollo)
# nano .env
```

---

## 2️⃣ Iniciar Docker

```bash
# Iniciar contenedores
docker-compose up -d

# Esperar 30-60 segundos...
# Ver progreso
docker-compose logs -f wordpress

# Cuando veas "apache2 -D FOREGROUND", está listo
# Presionar Ctrl+C para salir de logs
```

---

## 3️⃣ Acceder a WordPress

**URL:** http://localhost:8080

**Credenciales:**
```
Usuario: wordpress
Contraseña: wordpresspass
```

---

## 4️⃣ Instalar Plugins Esenciales

1. Dashboard → Plugins → Agregar nuevo
2. Buscar e instalar:
   - **WooCommerce**
   - **Advanced Custom Fields**
   - **Booking Calendar** o **BooklyPress**
   - **WP Mail SMTP**
   - **Wordfence Security**

3. Activar cada plugin

---

## 5️⃣ Configurar Banco de Chile

1. Dashboard → WooCommerce → Configuración → Pagos
2. Habilitar "Banco de Chile - WebCheckout"
3. Llenar:
   - Código Comercio: `597055555540` (test)
   - API Key: Tu API key
   - Secret Key: Tu secret key
   - Ambiente: `Desarrollo (Sandbox)`

4. Guardar

---

## 6️⃣ Crear Primer Tour

1. Dashboard → Tours → Agregar nuevo
2. Título: "Mi Primer Tour"
3. Descripción: "Descripción del tour"
4. Precio: 89000 (CLP)
5. Campos adicionales (ACF):
   - Duración: "6 horas"
   - Dificultad: "Moderada"
   - Capacidad Máxima: 15

6. Publicar

---

## 7️⃣ Crear Horarios para Tour

1. Editar tour
2. Ir a sección "Horarios"
3. Agregar horario:
   - Día: Lunes
   - Inicio: 08:00 AM
   - Fin: 02:00 PM
   - Capacidad: 15
   - Activo: Sí

4. Guardar

---

## 8️⃣ Probar Flujo Completo

### Cliente:
1. Ir a página de tours (http://localhost:8080)
2. Seleccionar tour
3. Seleccionar fecha y horario
4. Ingresar datos
5. Proceder a pago

### Pago Test:
- Usar tarjeta: `4051885115270061`
- Vencimiento: `12/27`
- CVV: `123`
- RUT: `11.111.111-1`

### Admin:
1. Dashboard → Reservas
2. Ver reserva creada
3. Ver estado: Pendiente → Pagada

---

## 📚 Documentación Completa

Después de la configuración inicial:

- [**README.md**](./README.md) - Documentación detallada
- [**ARQUITECTURA.md**](./ARQUITECTURA.md) - Diseño técnico
- [**docs/BANCO-CHILE-INTEGRACION.md**](./docs/BANCO-CHILE-INTEGRACION.md) - Pagos
- [**docs/API-REFERENCIA.md**](./docs/API-REFERENCIA.md) - API REST
- [**docs/INSTALACION-PLUGINS.md**](./docs/INSTALACION-PLUGINS.md) - Plugins
- [**docs/FAQ.md**](./docs/FAQ.md) - Preguntas frecuentes

---

## 🛠️ Comandos Útiles

```bash
# Ver estado
docker-compose ps

# Ver logs
docker-compose logs wordpress
docker-compose logs mysql

# Detener todo
docker-compose down

# Reiniciar
docker-compose restart

# Entrar a contenedor WordPress
docker-compose exec wordpress bash

# Acceder a MySQL
docker-compose exec mysql mysql -u wordpress -pwordpresspass wordpress
```

---

## ✅ Checklist Rápido

- [ ] Docker instalado y funcionando
- [ ] Repo clonado
- [ ] `.env` copiado de `.env.example`
- [ ] Contenedores iniciados (`docker-compose up -d`)
- [ ] WordPress accesible en http://localhost:8080
- [ ] Plugins instalados y activados
- [ ] Banco de Chile configurado
- [ ] Primer tour creado
- [ ] Horarios del tour configurados
- [ ] Prueba de pago completada

---

## 🆘 Si Algo No Funciona

1. **WordPress no accesible:**
   ```bash
   docker-compose logs wordpress | tail -50
   # Esperar más tiempo, puede tomar 60 segundos
   ```

2. **Correos no funcionan:**
   - Ir a WP Mail SMTP → Settings
   - Hacer clic en "Test Email"
   - Ver error si hay

3. **Pago no funciona:**
   - Verificar credenciales de Banco de Chile
   - Ver logs: `/var/www/html/wp-content/debug.log`

4. **Base de datos con problemas:**
   ```bash
   docker-compose down
   docker volume rm dvsolutions-fieldstack_mysql_data
   docker-compose up -d
   # Reinicia limpio
   ```

---

## 🚀 Próximos Pasos

1. Crear 2do tour
2. Configurar correos automáticos
3. Personalizar diseño del sitio
4. Configurar domain + SSL
5. Lanzar a producción

Ver [README.md](./README.md) para deployment.

---

**¿Preguntas?** Consulta [FAQ.md](./docs/FAQ.md)

**Versión:** 1.0.0  
**Actualizado:** Agosto 2026
