# DVS Tour Reservas

Plugin de WordPress con calendario de reservas para dos tours que comparten un
mismo guía, con redirección al **Botón de Pago del Banco de Chile**.

## Tours configurados

| Tour | Horario |
|---|---|
| Tour Termas | 09:30 – 14:30 |
| Tour Embalse | 15:00 – 17:30 |

**Regla de guía único:** como el guía es el mismo para ambos tours, al quedar
reservado cualquiera de los dos en una fecha, el otro tour se bloquea
automáticamente ese día. El calendario lo muestra al cliente con el mensaje
"el guía está en el otro tour este día". Si más adelante cuentas con un
segundo guía, puedes activar la opción *"Permitir reservar ambos tours el
mismo día"* en los ajustes.

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
