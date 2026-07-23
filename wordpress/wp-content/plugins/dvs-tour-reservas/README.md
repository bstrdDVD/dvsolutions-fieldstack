# DVS Tour Reservas

Plugin de WordPress con calendario de reservas para dos tours que comparten un
mismo guía, con redirección al **Botón de Pago del Banco de Chile** y
calendario **trilingüe (español / inglés / portugués)**.

Pensado para integrarse en el sitio existente de CuatriYesoTour
(WordPress): solo se instala el plugin y se inserta el shortcode en una
página; no hay que cambiar el tema ni rediseñar el sitio.

## Tours configurados

| Tour | Horario |
|---|---|
| Tour Termas Valle de Colina | 09:30 – 14:30 |
| Tour Embalse El Yeso | 15:00 – 17:30 |

**Disponibilidad por día y cupo de motos:**

- Cada tour se ofrece ciertos **días de la semana** (configurable). Por defecto:
  - **Sábado:** Termas + Embalse
  - **Domingo:** solo Termas
  - Resto de la semana: cerrado
- **Festivos:** puedes habilitar fechas puntuales (solo Termas).
- **Fechas cerradas:** puedes bloquear días concretos (ej. un sábado que no operarás).
- **Cupo:** cada tour tiene un cupo de **motos por día** (3 por defecto). Se
  aceptan reservas hasta agotarlo; varias reservas comparten el mismo cupo.
  El cliente reserva por **motos** (cada moto para hasta 2 personas).

Todo esto se configura en **Tours → Ajustes → Días de operación y cupo**.

## Integración con WooCommerce + Banchile Pagos (recomendado)

Desde la v1.1 el calendario se cobra a través de **WooCommerce** y su pasarela
(**Banchile Pagos**), en vez del enlace de pago fijo. El flujo real es:

```
Cliente elige fecha + tour en el calendario
        ↓
Se crea la reserva (pendiente) → BLOQUEA el día para el otro tour (guía único)
        ↓
Se crea un pedido de WooCommerce con el producto del tour, la fecha y los datos
        ↓
El cliente paga en Banchile Pagos
        ↓
Banchile confirma por webhook → el pedido pasa a "pagado"
        ↓
La reserva pasa a "pagada" (queda confirmada)
```

Si el pago se **cancela, falla o se reembolsa**, la reserva se cancela sola y
**el día se libera** para ambos tours.

### Cómo activarlo (después de certificar Banchile)

1. Ten los dos productos de WooCommerce creados (uno por tour) y **márcalos
   como "Virtual"** (no requieren envío).
2. Ve a **Tours → Ajustes → Cobro con WooCommerce**:
   - Activa **"Usar WooCommerce"**.
   - En **Producto — Tour Termas** y **Producto — Tour Embalse**, elige el
     producto correspondiente del desplegable.
3. Crea una página "Reservas" con el shortcode `[dvs_tour_calendario]` y apunta
   ahí tus botones "Reservar Ahora".
4. En **WooCommerce → Ajustes → Pagos**, deja **Banchile Pagos** activo, y
   asegúrate de permitir el pago como invitado.

Cada reserva genera un pedido normal de WooCommerce (visible en
**WooCommerce → Pedidos**) con la fecha, el tour y el código de reserva.

> **Nota sobre precios:** el pedido usa el precio del producto por unidad
> (cantidad 1). El número de personas se guarda como dato informativo en el
> pedido. Si necesitas que el precio dependa de la cantidad de personas/motos,
> se puede ajustar en una iteración posterior.

## Instalación

1. Copia la carpeta `dvs-tour-reservas` dentro de `wp-content/plugins/` de tu
   instalación de WordPress (o comprímela en un `.zip` y súbela desde
   **Plugins → Añadir nuevo → Subir plugin**).
2. Activa **DVS Tour Reservas** en el panel de plugins. Al activarse se crea
   automáticamente la tabla de reservas.
3. Ve a **Tours → Ajustes** y configura:
   - **URL de pago — Tour Termas** y **URL de pago — Tour Embalse**: los
     enlaces de cobro que genera tu portal del Banco de Chile (Botón de Pago).
   - Precios de referencia (se muestran al cliente en el calendario).
   - Correo donde recibirás el aviso de cada reserva nueva.
4. Crea una página (por ejemplo "Reservas") e inserta el shortcode:

   ```
   [dvs_tour_calendario]
   ```

## Traductor de todo el sitio (botón flotante 🌐)

Al activar el plugin, aparece un **botón flotante 🌐 en la esquina superior
derecha de todas las páginas** del sitio. Al pulsarlo se despliegan los
idiomas 🇨🇱 Español · 🇬🇧 English · 🇧🇷 Português, y al elegir uno **todo el
contenido visible de la página se traduce al instante**, sin recargar:
menú, portada, fichas de los tours, beneficios, restricciones, contacto y
pie de página. El plugin incluye el diccionario completo con los textos
actuales de cuatriyesotour.com.

- La elección del visitante se recuerda en su navegador y se aplica en todas
  las páginas que visite.
- Si agregas o cambias textos en la web y quedan sin traducir, entra a
  **Tours → Ajustes → Frases adicionales** y añade una línea por frase con el
  formato `texto español || inglés || portugués`. No hace falta tocar código.
- El botón se puede desactivar desde los mismos ajustes.
- El botón flotante y el calendario de reservas están sincronizados: cambiar
  el idioma en uno cambia también el otro.

## Idiomas del calendario de reservas

El calendario incluye su propio selector **ES / EN / PT** (se oculta
automáticamente cuando el botón flotante del sitio está activo, para no
duplicar).
Todos los textos visibles (calendario, tours, formulario, mensajes de
disponibilidad y errores) cambian de idioma al instante, sin recargar la
página, y la elección del visitante se recuerda en su navegador. No se
necesita WPML, Polylang ni ningún otro plugin de traducción.

- El correo de confirmación al cliente se envía **en el idioma que usó** al
  reservar; el correo de aviso al negocio llega siempre en español e indica
  el idioma del cliente (útil para saber cómo atenderlo).
- El idioma inicial se puede fijar por página con el atributo del shortcode:
  `[dvs_tour_calendario idioma="en"]` (valores: `es`, `en`, `pt`). Así puedes
  tener una página `/booking` en inglés y otra `/reservas` en español usando
  el mismo plugin.
- Los textos están centralizados en `includes/class-dvs-tr-i18n.php` por si
  quieres ajustar alguna traducción o el nombre de un tour.

## Flujo de reserva

1. El cliente elige un día disponible en el calendario.
2. Elige el tour (solo se muestran habilitados los que el guía puede realizar
   ese día) y completa sus datos: nombre, correo, teléfono y número de
   personas.
3. Al confirmar, la reserva queda registrada como **pendiente** (lo que ya
   bloquea el día para el otro tour) y el cliente es redirigido al Botón de
   Pago del Banco de Chile.
4. Tú recibes un correo con los datos y el código de reserva; el cliente
   recibe un correo de confirmación con su código.

## Gestión de reservas

En **Tours → Reservas** verás todas las reservas con su estado:

- **pendiente**: registrada, esperando confirmación del pago. Bloquea el cupo.
- **pagada**: confirmada. Bloquea el cupo.
- **cancelada**: libera el día para ambos tours.

> **Importante:** el Botón de Pago del Banco de Chile no notifica
> automáticamente a la web cuando el cliente paga. Cuando veas el abono en tu
> banco (el correo de reserva incluye el código para cruzarlo), marca la
> reserva como **pagada**. Si el cliente no paga, cancélala para liberar la
> fecha. Opcionalmente puedes definir en los ajustes un tiempo máximo de
> retención del cupo sin pago (en minutos) para que las reservas pendientes
> caduquen solas.

## Detalles técnicos

- Tabla propia `wp_dvs_tr_reservas` creada con `dbDelta`.
- La creación de reservas usa una transacción con `SELECT … FOR UPDATE` para
  evitar dobles reservas simultáneas del mismo día.
- Endpoints AJAX públicos protegidos con nonce (`dvs_tr_disponibilidad`,
  `dvs_tr_reservar`) y validación/sanitización de todos los campos.
- Sin dependencias externas: JavaScript y CSS propios, compatibles con
  cualquier tema.
- Solo se pueden reservar fechas futuras dentro del rango de anticipación
  configurado (90 días por defecto).
