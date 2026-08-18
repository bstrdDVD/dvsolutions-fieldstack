# Integración Banco de Chile - WebCheckout

Guía completa para integrar WebCheckout de Banco de Chile en el sistema de reservas.

---

## 📋 Tabla de Contenidos

1. [Conceptos Base](#conceptos-base)
2. [Setup Inicial](#setup-inicial)
3. [Flujo de Pago](#flujo-de-pago)
4. [Implementación Técnica](#implementación-técnica)
5. [Webhooks y Callbacks](#webhooks-y-callbacks)
6. [Testing en Sandbox](#testing-en-sandbox)
7. [Deployment a Producción](#deployment-a-producción)
8. [Troubleshooting](#troubleshooting)

---

## 1. Conceptos Base

### ¿Qué es WebCheckout?

WebCheckout es la solución de pago online del Banco de Chile (operado por Transbank) que permite:
- Aceptar pagos de tarjetas de crédito y débito
- Transacciones seguras con autenticación
- Conciliación automática
- Reportes detallados

### Ventajas
✅ Seguridad PCI DSS - No mantienes datos de tarjeta  
✅ Soporte multi-moneda (CLP, USD, etc)  
✅ Tasa de conversión alta  
✅ Soporte en español  
✅ Integraciones con plugins populares  

### Actores del Flujo
- **Cliente**: Realiza la compra
- **Tu Sitio (Merchant)**: Muestra productos y redirecciona a pago
- **WebCheckout**: Interfaz de pago segura
- **Banco de Chile/Transbank**: Procesa transacción
- **Tu Backend**: Recibe notificación y confirma pago

---

## 2. Setup Inicial

### 2.1 Obtener Credenciales

1. **Contactar Banco de Chile**
   - Ejecutivo asignado: [Ver credenciales en equipo]
   - Solicitar: Código de Comercio y API Key

2. **Credenciales Sandbox (Pruebas)**
   ```
   Código Comercio: 597055555540
   API Key: tu_api_key_test
   Secret Key: tu_secret_key_test
   URL: https://webpay-test.transbank.cl
   ```

3. **Credenciales Producción (Real)**
   ```
   Código Comercio: [Tu código real]
   API Key: [Tu API Key real]
   Secret Key: [Tu Secret Key real]
   URL: https://webpay.transbank.cl
   ```

### 2.2 Configurar en WordPress

1. Ir a **Configuración → Banco de Chile**

2. Llenar formulario:
   ```
   Ambiente: [Desarrollo/Producción]
   Código de Comercio: 597055555540
   API Key: tu_api_key_test
   Secret Key: tu_secret_key_test
   URL Callback: http://localhost:8080/wp-json/adventure-tourism/v1/payment-callback
   Email Notificación: admin@aventura-tourism.com
   ```

3. **Guardar y Probar Conexión**

---

## 3. Flujo de Pago

### 3.1 Diagrama de Flujo

```
┌─────────────┐
│   Cliente   │
└──────┬──────┘
       │
       ▼
┌──────────────────────────┐
│ 1. Tu Sitio - Reserva    │
│ - Selecciona tour        │
│ - Ingresa datos          │
│ - Revisa total           │
└──────────────┬───────────┘
               │
               ▼
┌──────────────────────────┐
│ 2. Iniciar Pago          │
│ - Crear orden en BD      │
│ - Generar token          │
│ - Redireccionar a BCH    │
└──────────────┬───────────┘
               │
               ▼
┌──────────────────────────┐
│ 3. WebCheckout Banco     │
│ - Selecciona método pago │
│ - Ingresa tarjeta/datos  │
│ - Autenticación (3DSecure)
└──────────────┬───────────┘
               │
       ┌───────┴────────┐
       ▼                ▼
   APROBADO         RECHAZADO
       │                │
       ▼                ▼
  ┌────────┐      ┌────────────┐
  │ Pago OK│      │ Pago Error │
  └────┬───┘      └────┬───────┘
       │               │
       └───────┬───────┘
               ▼
    ┌──────────────────────┐
    │ 4. Webhook Callback  │
    │ - Confirmar pago     │
    │ - Actualizar BD      │
    │ - Enviar correo      │
    └──────────────────────┘
```

### 3.2 Estados de Transacción

```
PENDIENTE
├── Cliente inicia pago
└── Esperando confirmación del Banco

PROCESANDO
├── En proceso en WebCheckout
└── Usuario completando datos

APROBADO
├── Pago procesado exitosamente
├── Reserva confirmada
└── Correo enviado a cliente

RECHAZADO
├── Tarjeta rechazada o error
├── Cliente puede reintentar
└── Notificación de error a cliente

REEMBOLSO
├── Cliente solicita cancelación
├── Se devuelve dinero a tarjeta
└── Notificación a cliente

EXPIRADO
├── Pago no completado en tiempo límite
└── Reserva se cancela automáticamente
```

---

## 4. Implementación Técnica

### 4.1 Crear Plugin Personalizado

Crear archivo: `wp-content/plugins/bch-payment-gateway/bch-payment-gateway.php`

```php
<?php
/**
 * Plugin Name: Banco de Chile Payment Gateway
 * Plugin URI: https://aventura-tourism.com/
 * Description: Integración de WebCheckout de Banco de Chile
 * Version: 1.0.0
 * Author: Aventura Tourism
 * Author URI: https://aventura-tourism.com/
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('BCH_PAYMENT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BCH_PAYMENT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Includes
require_once BCH_PAYMENT_PLUGIN_DIR . 'includes/class-bch-gateway.php';
require_once BCH_PAYMENT_PLUGIN_DIR . 'includes/class-bch-api.php';
require_once BCH_PAYMENT_PLUGIN_DIR . 'includes/class-bch-webhook.php';
require_once BCH_PAYMENT_PLUGIN_DIR . 'includes/functions.php';

// Activación/Desactivación
register_activation_hook(__FILE__, 'bch_payment_activate');
register_deactivation_hook(__FILE__, 'bch_payment_deactivate');

function bch_payment_activate() {
    // Crear tablas si no existen
    require_once BCH_PAYMENT_PLUGIN_DIR . 'includes/install.php';
    bch_create_tables();
}

function bch_payment_deactivate() {
    // Limpiar
}

// Agregar gateway a WooCommerce
add_filter('woocommerce_payment_gateways', function($gateways) {
    $gateways[] = 'WC_BCH_Gateway';
    return $gateways;
});

// Registrar endpoints
add_action('rest_api_init', function() {
    register_rest_route('adventure-tourism/v1', '/payment-callback', array(
        'methods' => 'POST',
        'callback' => 'bch_payment_callback',
        'permission_callback' => '__return_true',
    ));
});

// Cargar textdomain
add_action('plugins_loaded', function() {
    load_plugin_textdomain(
        'bch-payment-gateway',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages/'
    );
});
```

### 4.2 Clase Principal de Gateway

Crear: `includes/class-bch-gateway.php`

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_BCH_Gateway extends WC_Payment_Gateway {
    
    public function __construct() {
        $this->id = 'bch_webcheckout';
        $this->icon = '';
        $this->has_fields = false;
        $this->method_title = 'Banco de Chile - WebCheckout';
        $this->method_description = 'Pago seguro con tarjeta de crédito';
        
        // Cargar configuración
        $this->init_form_fields();
        $this->init_settings();
        
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->commerce_code = $this->get_option('commerce_code');
        $this->api_key = $this->get_option('api_key');
        $this->secret_key = $this->get_option('secret_key');
        $this->environment = $this->get_option('environment');
        
        // Hooks
        add_action(
            'woocommerce_update_options_payment_gateways_' . $this->id,
            array($this, 'process_admin_options')
        );
    }
    
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => 'Habilitar',
                'type' => 'checkbox',
                'label' => 'Habilitar Banco de Chile - WebCheckout',
                'default' => 'yes',
            ),
            'title' => array(
                'title' => 'Título',
                'type' => 'text',
                'description' => 'Título mostrado al cliente',
                'default' => 'Pago con Tarjeta de Crédito/Débito',
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => 'Descripción',
                'type' => 'textarea',
                'description' => 'Descripción del método de pago',
                'default' => 'Pago seguro con Banco de Chile',
            ),
            'environment' => array(
                'title' => 'Ambiente',
                'type' => 'select',
                'options' => array(
                    'sandbox' => 'Desarrollo (Sandbox)',
                    'production' => 'Producción',
                ),
                'default' => 'sandbox',
            ),
            'commerce_code' => array(
                'title' => 'Código de Comercio',
                'type' => 'password',
                'description' => 'Código de comercio proporcionado por Banco de Chile',
                'desc_tip' => true,
            ),
            'api_key' => array(
                'title' => 'API Key',
                'type' => 'password',
                'description' => 'API Key de tu cuenta en WebCheckout',
                'desc_tip' => true,
            ),
            'secret_key' => array(
                'title' => 'Secret Key',
                'type' => 'password',
                'description' => 'Secret Key para firmar peticiones',
                'desc_tip' => true,
            ),
        );
    }
    
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        
        try {
            // Crear transacción en Banco de Chile
            $api = new BCH_API($this->settings);
            $response = $api->initiate_payment($order);
            
            if ($response['success']) {
                // Guardar referencia de transacción
                $order->add_meta('_bch_transaction_id', $response['transaction_id']);
                $order->add_meta('_bch_token', $response['token']);
                
                // Guardar en tabla de logs
                bch_log_transaction($order_id, $response);
                
                // Redireccionar a WebCheckout
                return array(
                    'result' => 'success',
                    'redirect' => $response['redirect_url'],
                );
            } else {
                // Error en iniciación de pago
                wc_add_notice(
                    'Error al procesar pago: ' . $response['message'],
                    'error'
                );
                return array('result' => 'failure');
            }
            
        } catch (Exception $e) {
            wc_add_notice('Error al procesar tu pago: ' . $e->getMessage(), 'error');
            return array('result' => 'failure');
        }
    }
}

// Registrar clase con WooCommerce
add_action('plugins_loaded', function() {
    if (class_exists('WC_Payment_Gateway')) {
        WC_Payment_Gateway::$bch_gateway_loaded = true;
    }
});
```

### 4.3 API de Banco de Chile

Crear: `includes/class-bch-api.php`

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

class BCH_API {
    
    private $commerce_code;
    private $api_key;
    private $secret_key;
    private $base_url;
    
    public function __construct($settings) {
        $this->commerce_code = $settings['commerce_code'];
        $this->api_key = $settings['api_key'];
        $this->secret_key = $settings['secret_key'];
        
        $environment = $settings['environment'] ?? 'sandbox';
        $this->base_url = $environment === 'production'
            ? 'https://webpay.transbank.cl'
            : 'https://webpay-test.transbank.cl';
    }
    
    /**
     * Iniciar pago en WebCheckout
     */
    public function initiate_payment($order) {
        $endpoint = $this->base_url . '/api/webpay/create_transaction';
        
        $payload = array(
            'commerce_code' => $this->commerce_code,
            'amount' => $order->get_total(),
            'buy_order' => $order->get_id(),
            'session_id' => $order->get_id(),
            'return_url' => home_url('/wp-json/adventure-tourism/v1/payment-callback'),
            'final_url' => home_url('/checkout/order-received/' . $order->get_id()),
        );
        
        $signature = $this->generate_signature($payload);
        $payload['signature'] = $signature;
        
        try {
            $response = wp_remote_post($endpoint, array(
                'method' => 'POST',
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->api_key,
                ),
                'body' => json_encode($payload),
                'timeout' => 30,
                'sslverify' => true,
            ));
            
            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }
            
            $body = json_decode(wp_remote_retrieve_body($response), true);
            
            if (isset($body['token']) && isset($body['redirect_url'])) {
                return array(
                    'success' => true,
                    'token' => $body['token'],
                    'transaction_id' => $body['transaction_id'] ?? null,
                    'redirect_url' => $body['redirect_url'],
                );
            } else {
                return array(
                    'success' => false,
                    'message' => $body['message'] ?? 'Error desconocido',
                );
            }
            
        } catch (Exception $e) {
            error_log('BCH API Error: ' . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Error conectando con Banco de Chile',
            );
        }
    }
    
    /**
     * Obtener estado de transacción
     */
    public function get_transaction_status($token) {
        $endpoint = $this->base_url . '/api/webpay/get_transaction_status';
        
        $payload = array(
            'token' => $token,
            'commerce_code' => $this->commerce_code,
        );
        
        $signature = $this->generate_signature($payload);
        $payload['signature'] = $signature;
        
        try {
            $response = wp_remote_post($endpoint, array(
                'method' => 'POST',
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->api_key,
                ),
                'body' => json_encode($payload),
            ));
            
            return json_decode(wp_remote_retrieve_body($response), true);
            
        } catch (Exception $e) {
            error_log('BCH Status Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generar firma para seguridad
     */
    private function generate_signature($payload) {
        $payload_json = json_encode($payload);
        return hash_hmac('sha256', $payload_json, $this->secret_key);
    }
    
    /**
     * Verificar firma de webhook
     */
    public function verify_webhook_signature($signature, $body) {
        $expected_signature = hash_hmac('sha256', $body, $this->secret_key);
        return hash_equals($expected_signature, $signature);
    }
}
```

---

## 5. Webhooks y Callbacks

### 5.1 Endpoint de Callback

Crear: `includes/class-bch-webhook.php`

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

class BCH_Webhook {
    
    public static function handle_callback() {
        // Obtener datos del POST
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar firma
        $signature = isset($_SERVER['HTTP_X_SIGNATURE'])
            ? $_SERVER['HTTP_X_SIGNATURE']
            : null;
        
        if (!$signature) {
            http_response_code(400);
            die(json_encode(array('error' => 'Missing signature')));
        }
        
        // Obtener API
        $gateway = new WC_BCH_Gateway();
        $api = new BCH_API($gateway->settings);
        
        // Verificar firma
        if (!$api->verify_webhook_signature($signature, file_get_contents('php://input'))) {
            http_response_code(403);
            die(json_encode(array('error' => 'Invalid signature')));
        }
        
        // Procesar pago
        self::process_payment_result($data);
        
        // Confirmar recepción
        http_response_code(200);
        die(json_encode(array('success' => true)));
    }
    
    private static function process_payment_result($data) {
        $order_id = $data['buy_order'] ?? null;
        $status = $data['response_code'] ?? null;
        $transaction_id = $data['transaction_id'] ?? null;
        
        if (!$order_id) {
            error_log('BCH Webhook: Missing order ID');
            return;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            error_log('BCH Webhook: Order not found: ' . $order_id);
            return;
        }
        
        // Códigos de respuesta del Banco
        // 0 = Aprobado
        // Otros = Error
        
        if ($status === 0) {
            // Pago aprobado
            $order->payment_complete($transaction_id);
            $order->add_order_note(sprintf(
                'Pago aprobado por Banco de Chile. Transaction ID: %s',
                $transaction_id
            ));
            
            // Actualizar reserva a pagada
            update_post_meta($order_id, '_payment_method', 'bch_webcheckout');
            update_post_meta($order_id, '_transaction_id', $transaction_id);
            
            // Enviar correo de confirmación
            self::send_confirmation_email($order);
            
            // Actualizar reserva en tabla personalizada
            self::update_reservation_status($order_id, 'pagada');
            
        } else {
            // Pago rechazado
            $order->update_status('failed');
            $order->add_order_note(sprintf(
                'Pago rechazado por Banco de Chile. Código: %s',
                $status
            ));
            
            // Actualizar reserva a fallida
            self::update_reservation_status($order_id, 'fallida');
        }
        
        // Registrar en logs
        self::log_webhook($order_id, $data);
    }
    
    private static function send_confirmation_email($order) {
        // Implementar envío de correo
        // Usar WP Mail SMTP o función propia
    }
    
    private static function update_reservation_status($order_id, $status) {
        global $wpdb;
        
        $wpdb->update(
            $wpdb->prefix . 'reservations',
            array('status' => $status),
            array('reservation_id' => $order_id)
        );
    }
    
    private static function log_webhook($order_id, $data) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'transaction_logs',
            array(
                'order_id' => $order_id,
                'event_type' => 'payment_webhook',
                'response_data' => json_encode($data),
                'created_at' => current_time('mysql'),
            )
        );
    }
}

// Registrar endpoint REST
add_action('rest_api_init', function() {
    register_rest_route('adventure-tourism/v1', '/payment-callback', array(
        'methods' => 'POST',
        'callback' => array('BCH_Webhook', 'handle_callback'),
        'permission_callback' => '__return_true',
    ));
});
```

---

## 6. Testing en Sandbox

### 6.1 Tarjetas de Prueba

**VISA**
```
Número: 4051885115270061
Mes: 12
Año: 27
CVV: 123
RUT: 11.111.111-1
```

**MASTERCARD**
```
Número: 5186059559590568
Mes: 01
Año: 26
CVV: 123
RUT: 11.111.111-1
```

### 6.2 Procedimiento de Testing

1. **Crear reserva de prueba**
   - Ir al sitio en http://localhost:8080
   - Seleccionar tour y fecha
   - Completar datos

2. **Proceder al pago**
   - Se redirecciona a WebCheckout de prueba
   - Ingresar datos de tarjeta de prueba
   - Completar autenticación

3. **Verificar en Banco de Chile**
   - Acceder a portal de Banco de Chile
   - Ver transacción en listado
   - Verificar estado

4. **Verificar en tu BD**
   - Revisar tabla `wp_reservations`
   - Revisar tabla `wp_tour_payments`
   - Revisar tabla `wp_transaction_logs`

### 6.3 Casos de Test

```
Caso 1: Pago exitoso
- Seleccionar tour
- Ingresar tarjeta de prueba correcta
- Completar autenticación
- Resultado: Reserva pagada

Caso 2: Tarjeta rechazada
- Seleccionar tour
- Ingresar tarjeta rechazada
- Resultado: Pago fallido, se puede reintentar

Caso 3: Timeout
- Iniciar pago
- No completar en 10 minutos
- Resultado: Sesión expirada

Caso 4: Webhook callback
- Simular webhook desde Banco de Chile
- Verificar que reserva se actualiza
- Verificar que correo se envía
```

---

## 7. Deployment a Producción

### 7.1 Checklist Producción

```
ANTES DEL LANZAMIENTO
- [ ] Cambiar ambiente a "Producción" en configuración
- [ ] Ingresar credenciales reales de Banco de Chile
- [ ] Cambiar URL de callback a dominio real
- [ ] Obtener certificado SSL válido
- [ ] Configurar backup automático
- [ ] Configurar monitoreo
- [ ] Revisar todas las credenciales
- [ ] Realizar prueba de pago con dinero real
- [ ] Documentar procedimientos
- [ ] Capacitar equipo

SEGURIDAD
- [ ] Cambiar contraseña admin WordPress
- [ ] Habilitar SSL/TLS forzado
- [ ] Deshabilitar editor de temas/plugins
- [ ] Instalar Wordfence Security
- [ ] Implementar WAF (Web Application Firewall)
- [ ] Cambiar prefijo de BD
- [ ] Revisar permisos de archivos
```

### 7.2 Variables de Entorno Producción

```bash
BCH_ENVIRONMENT=production
BCH_COMMERCE_CODE=tu_codigo_real
BCH_API_KEY=tu_api_key_real
BCH_SECRET_KEY=tu_secret_key_real
BCH_SANDBOX_URL=https://webpay.transbank.cl
SITE_URL=https://tudominio.com
```

---

## 8. Troubleshooting

### Problema: "Conexión rechazada con Banco de Chile"

**Causas posibles:**
- Credenciales incorrectas
- Ambiente incorrecto (sandbox vs producción)
- URL callback inválida
- Firewall bloqueando conexión

**Solución:**
```bash
# Verificar credenciales en WordPress
# Verificar URL en navegador: https://webpay-test.transbank.cl

# Ver logs
tail -f /var/www/html/wp-content/debug.log | grep BCH
```

### Problema: "Webhook no se recibe"

**Causas posibles:**
- URL callback incorrecta
- SSL inválido en URL callback
- Firewall bloqueando POST entrante
- Endpoint no registrado correctamente

**Solución:**
```bash
# Verificar endpoint
curl -X POST https://tudominio.com/wp-json/adventure-tourism/v1/payment-callback \
  -H "Content-Type: application/json" \
  -d '{"test":"true"}'

# Ver logs de WordPress
```

### Problema: "Firma de webhook inválida"

**Causas:**
- Secret Key incorreto
- Datos modificados en tránsito
- Algoritmo de firma diferente

**Solución:**
```php
// Verificar secret key en .env
// Verificar algoritmo: debe ser SHA256
// Revertir cambios recientes en código de verificación
```

### Problema: "Transacción no se refleja en BD"

**Causas:**
- Webhook llegó pero no se procesó
- Error en la función de actualización
- Transacción duplicada

**Solución:**
```bash
# Ver logs de transacción
SELECT * FROM wp_transaction_logs WHERE event_type = 'payment_webhook' ORDER BY created_at DESC LIMIT 10;

# Verificar errores PHP
tail -f /var/www/html/wp-content/debug.log
```

---

## Contacto Soporte

**Banco de Chile / Transbank:**
- Sitio: https://www.transbank.cl
- Correo: soporte@transbank.cl
- Teléfono: +56-2-2656-4000
- Portal Desarrolladores: https://www.transbank.cl/desarrolladores

**Tu Equipo de Desarrollo:**
- Email: dev@aventura-tourism.com
- Slack: #aventura-payment

---

**Versión:** 1.0.0  
**Última actualización:** Agosto 2026
