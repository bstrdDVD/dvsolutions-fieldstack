# API REST - Referencia Completa

Documentación de endpoints REST para gestionar tours, reservas y pagos.

---

## 📋 Base de Datos

**URL Base:** `https://tudominio.com/wp-json/adventure-tourism/v1`

**Autenticación:** JWT Token (Bearer)

---

## 1. Tours (Productos)

### 1.1 Listar Tours

**GET** `/tours`

**Parámetros Query:**
```
?page=1&per_page=10&status=publish&orderby=date&order=desc
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Trekking Laguna Azul",
      "description": "Descripción del tour...",
      "price": 89000,
      "duration": "6 horas",
      "difficulty": "moderada",
      "max_capacity": 15,
      "min_age": 12,
      "includes": ["Guía profesional", "Equipo de seguridad"],
      "images": ["url1.jpg", "url2.jpg"],
      "featured_image": "url.jpg",
      "rating": 4.8,
      "reviews_count": 42,
      "schedules": [
        {
          "id": 1,
          "day": "lunes",
          "start_time": "08:00",
          "end_time": "14:00",
          "capacity": 15
        }
      ]
    }
  ],
  "total": 5,
  "pages": 1
}
```

---

### 1.2 Obtener Detalle de Tour

**GET** `/tours/{id}`

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Trekking Laguna Azul",
    "description": "Descripción completa...",
    "price": 89000,
    "duration": "6 horas",
    "difficulty": "moderada",
    "max_capacity": 15,
    "min_age": 12,
    "includes": ["Guía profesional", "Equipo de seguridad", "Almuerzo"],
    "requirements": "Buena condición física",
    "meeting_point": "Hotel Central, Piso 1",
    "cancellation_policy": "Cancelación hasta 24h antes",
    "images": ["url1.jpg", "url2.jpg", "url3.jpg"],
    "featured_image": "url.jpg",
    "rating": 4.8,
    "reviews_count": 42,
    "available_dates": [
      "2026-08-20",
      "2026-08-22",
      "2026-08-23"
    ],
    "schedules": [
      {
        "id": 1,
        "day": "lunes",
        "start_time": "08:00",
        "end_time": "14:00",
        "capacity": 15,
        "available": 8
      }
    ],
    "reviews": [
      {
        "author": "Juan Pérez",
        "rating": 5,
        "comment": "Increíble experiencia",
        "date": "2026-08-15"
      }
    ]
  }
}
```

---

### 1.3 Crear Tour (Admin)

**POST** `/tours`

**Requiere:** Admin authentication

**Cuerpo:**
```json
{
  "title": "Escalada en Roca",
  "content": "Descripción del tour...",
  "price": 120000,
  "duration": "8 horas",
  "difficulty": "avanzado",
  "max_capacity": 12,
  "min_age": 16,
  "includes": ["Guía profesional", "Equipo completo"],
  "requirements": "Experiencia previa recomendada",
  "meeting_point": "Base de Montaña",
  "cancellation_policy": "Cancelación hasta 48h antes",
  "featured_image_id": 123
}
```

**Respuesta:** (201 Created)
```json
{
  "success": true,
  "data": {
    "id": 2,
    "title": "Escalada en Roca",
    "message": "Tour creado exitosamente"
  }
}
```

---

## 2. Horarios de Tours

### 2.1 Listar Horarios de un Tour

**GET** `/tours/{tour_id}/schedules`

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "tour_id": 1,
      "day_of_week": 1,
      "day_name": "lunes",
      "start_time": "08:00",
      "end_time": "14:00",
      "max_capacity": 15,
      "available": 8,
      "is_active": true
    },
    {
      "id": 2,
      "tour_id": 1,
      "day_of_week": 3,
      "day_name": "miércoles",
      "start_time": "08:00",
      "end_time": "14:00",
      "max_capacity": 15,
      "available": 12,
      "is_active": true
    }
  ]
}
```

---

### 2.2 Crear Horario para Tour (Admin)

**POST** `/tours/{tour_id}/schedules`

**Requiere:** Admin authentication

**Cuerpo:**
```json
{
  "day_of_week": 0,
  "start_time": "08:00",
  "end_time": "14:00",
  "max_capacity": 15,
  "is_active": true
}
```

**Respuesta:** (201 Created)
```json
{
  "success": true,
  "data": {
    "id": 3,
    "message": "Horario creado exitosamente"
  }
}
```

---

## 3. Disponibilidad y Calendario

### 3.1 Obtener Calendario de Disponibilidad

**GET** `/availability/calendar/{tour_id}`

**Parámetros Query:**
```
?month=8&year=2026
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "tour_id": 1,
    "month": 8,
    "year": 2026,
    "dates": {
      "2026-08-18": {
        "available": false,
        "reason": "Cerrado"
      },
      "2026-08-19": {
        "available": false,
        "reason": "No hay horario"
      },
      "2026-08-20": {
        "available": true,
        "schedules": [
          {
            "schedule_id": 1,
            "start_time": "08:00",
            "end_time": "14:00",
            "available_spots": 8
          }
        ]
      }
    }
  }
}
```

---

### 3.2 Verificar Disponibilidad de Fecha

**GET** `/availability/check`

**Parámetros Query:**
```
?tour_id=1&date=2026-08-20&num_persons=5
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "available": true,
    "tour_id": 1,
    "date": "2026-08-20",
    "requested_persons": 5,
    "available_spots": 8,
    "schedules": [
      {
        "schedule_id": 1,
        "start_time": "08:00",
        "end_time": "14:00",
        "price": 89000,
        "total_price": 445000
      }
    ]
  }
}
```

---

## 4. Reservas

### 4.1 Crear Reserva

**POST** `/reservations`

**Requiere:** Autenticación (usuario logueado o email)

**Cuerpo:**
```json
{
  "tour_id": 1,
  "schedule_id": 1,
  "reservation_date": "2026-08-20",
  "num_persons": 5,
  "participant_names": [
    {"name": "Juan Pérez", "age": 35},
    {"name": "María García", "age": 32},
    {"name": "Carlos López", "age": 28},
    {"name": "Ana Martínez", "age": 26},
    {"name": "Pedro González", "age": 40}
  ],
  "email": "juan@example.com",
  "phone": "+56912345678",
  "special_requirements": "Una persona tiene movilidad reducida",
  "contact_preference": "email"
}
```

**Respuesta:** (201 Created)
```json
{
  "success": true,
  "data": {
    "reservation_id": 42,
    "confirmation_token": "abc123def456",
    "status": "pendiente",
    "total_amount": 445000,
    "currency": "CLP",
    "payment_url": "https://tudominio.com/pagar/42",
    "message": "Reserva creada exitosamente. Completa el pago para confirmar.",
    "expires_at": "2026-08-20T15:00:00"
  }
}
```

---

### 4.2 Obtener Reserva

**GET** `/reservations/{id}`

**Requiere:** Autenticación

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "id": 42,
    "tour_id": 1,
    "tour_title": "Trekking Laguna Azul",
    "reservation_date": "2026-08-20",
    "schedule": {
      "start_time": "08:00",
      "end_time": "14:00"
    },
    "num_persons": 5,
    "participant_names": [
      {"name": "Juan Pérez", "age": 35},
      {"name": "María García", "age": 32},
      {"name": "Carlos López", "age": 28},
      {"name": "Ana Martínez", "age": 26},
      {"name": "Pedro González", "age": 40}
    ],
    "email": "juan@example.com",
    "phone": "+56912345678",
    "total_amount": 445000,
    "status": "pagada",
    "payment_status": "completado",
    "confirmation_token": "abc123def456",
    "confirmed_at": "2026-08-18T10:30:00",
    "created_at": "2026-08-18T09:15:00",
    "confirmation_url": "https://tudominio.com/confirmacion/abc123def456",
    "cancellation_available": true,
    "cancellation_deadline": "2026-08-19T00:00:00"
  }
}
```

---

### 4.3 Listar Mis Reservas

**GET** `/reservations`

**Requiere:** Autenticación

**Parámetros Query:**
```
?page=1&per_page=10&status=pagada&sort=-created_at
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 42,
      "tour_id": 1,
      "tour_title": "Trekking Laguna Azul",
      "reservation_date": "2026-08-20",
      "num_persons": 5,
      "total_amount": 445000,
      "status": "pagada",
      "created_at": "2026-08-18T09:15:00"
    },
    {
      "id": 41,
      "tour_id": 2,
      "tour_title": "Escalada en Roca",
      "reservation_date": "2026-09-15",
      "num_persons": 3,
      "total_amount": 360000,
      "status": "confirmada",
      "created_at": "2026-08-10T14:22:00"
    }
  ],
  "total": 2,
  "pages": 1
}
```

---

### 4.4 Cancelar Reserva

**POST** `/reservations/{id}/cancel`

**Requiere:** Autenticación

**Cuerpo:**
```json
{
  "reason": "Problema de salud",
  "comments": "Nos arrepentimos de la compra"
}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "reservation_id": 42,
    "status": "cancelada",
    "refund_amount": 445000,
    "refund_status": "procesando",
    "message": "Reserva cancelada. El reembolso se procesará en 5-7 días hábiles.",
    "refund_date_expected": "2026-08-25"
  }
}
```

---

## 5. Pagos

### 5.1 Iniciar Pago

**POST** `/payments/initiate`

**Requiere:** Autenticación

**Cuerpo:**
```json
{
  "reservation_id": 42,
  "amount": 445000,
  "currency": "CLP",
  "payment_method": "webcheckout"
}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "payment_id": 123,
    "reservation_id": 42,
    "amount": 445000,
    "status": "pendiente",
    "redirect_url": "https://webpay-test.transbank.cl/...",
    "token": "abc123...",
    "expires_at": "2026-08-18T11:15:00"
  }
}
```

---

### 5.2 Confirmar Pago (Webhook)

**POST** `/payments/callback` (Bancochile → Tu servidor)

**Cuerpo:**
```json
{
  "transaction_id": "12345678",
  "buy_order": "42",
  "amount": 445000,
  "response_code": 0,
  "response_message": "Transacción aprobada",
  "timestamp": "2026-08-18T10:30:45"
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Pago procesado correctamente"
}
```

---

### 5.3 Obtener Estado de Pago

**GET** `/payments/{payment_id}`

**Requiere:** Autenticación

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "reservation_id": 42,
    "amount": 445000,
    "currency": "CLP",
    "status": "completado",
    "payment_method": "webcheckout",
    "transaction_id": "12345678",
    "bank_response_code": "00",
    "bank_response_message": "Transacción aprobada",
    "payment_date": "2026-08-18T10:30:45",
    "receipt_url": "https://tudominio.com/comprobante/123"
  }
}
```

---

## 6. Usuarios

### 6.1 Registrarse

**POST** `/auth/register`

**Cuerpo:**
```json
{
  "email": "usuario@example.com",
  "password": "SecurePassword123",
  "first_name": "Juan",
  "last_name": "Pérez",
  "phone": "+56912345678"
}
```

**Respuesta:** (201 Created)
```json
{
  "success": true,
  "data": {
    "user_id": 5,
    "email": "usuario@example.com",
    "first_name": "Juan",
    "last_name": "Pérez",
    "token": "eyJhbGciOiJIUzI1NiIs...",
    "message": "Usuario registrado exitosamente. Revisa tu email para confirmar."
  }
}
```

---

### 6.2 Login

**POST** `/auth/login`

**Cuerpo:**
```json
{
  "email": "usuario@example.com",
  "password": "SecurePassword123"
}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "user_id": 5,
    "email": "usuario@example.com",
    "first_name": "Juan",
    "last_name": "Pérez",
    "token": "eyJhbGciOiJIUzI1NiIs...",
    "expires_in": 86400
  }
}
```

---

### 6.3 Obtener Perfil

**GET** `/users/me`

**Requiere:** Autenticación

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "user_id": 5,
    "email": "usuario@example.com",
    "first_name": "Juan",
    "last_name": "Pérez",
    "phone": "+56912345678",
    "avatar_url": "https://...",
    "total_reservations": 2,
    "total_spent": 805000,
    "member_since": "2026-06-15",
    "last_tour": "2026-09-15"
  }
}
```

---

### 6.4 Actualizar Perfil

**PUT** `/users/me`

**Requiere:** Autenticación

**Cuerpo:**
```json
{
  "first_name": "Juan Carlos",
  "phone": "+56912345679"
}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "message": "Perfil actualizado exitosamente"
  }
}
```

---

## 7. Reportes (Admin)

### 7.1 Dashboard

**GET** `/admin/dashboard`

**Requiere:** Admin

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_revenue": 5340000,
      "total_reservations": 24,
      "total_customers": 18,
      "occupancy_rate": 0.85
    },
    "recent_reservations": [
      {
        "id": 42,
        "tour_title": "Trekking Laguna Azul",
        "customer_name": "Juan Pérez",
        "amount": 445000,
        "status": "pagada",
        "date": "2026-08-18"
      }
    ],
    "revenue_by_tour": [
      {
        "tour_id": 1,
        "tour_title": "Trekking Laguna Azul",
        "revenue": 3560000,
        "reservations": 8
      }
    ]
  }
}
```

---

### 7.2 Reporte de Ocupación

**GET** `/admin/reports/occupancy`

**Parámetros Query:**
```
?start_date=2026-08-01&end_date=2026-08-31&tour_id=1
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "period": "2026-08",
    "tour_id": 1,
    "tour_title": "Trekking Laguna Azul",
    "total_capacity": 120,
    "total_reserved": 85,
    "occupancy_rate": 0.708,
    "dates": [
      {
        "date": "2026-08-20",
        "day": "miércoles",
        "capacity": 15,
        "reserved": 12,
        "occupancy": 0.80
      }
    ]
  }
}
```

---

### 7.3 Reporte de Ingresos

**GET** `/admin/reports/revenue`

**Parámetros Query:**
```
?start_date=2026-08-01&end_date=2026-08-31
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "period": "2026-08",
    "total_revenue": 5340000,
    "total_transactions": 24,
    "average_transaction": 222500,
    "by_tour": [
      {
        "tour_id": 1,
        "tour_title": "Trekking Laguna Azul",
        "revenue": 3560000,
        "transactions": 8,
        "average": 445000
      }
    ],
    "by_status": {
      "pagado": 5340000,
      "pendiente": 0,
      "fallido": 0
    }
  }
}
```

---

## Códigos de Error

```json
{
  "400": "Solicitud inválida",
  "401": "No autorizado",
  "403": "Acceso denegado",
  "404": "Recurso no encontrado",
  "409": "Conflicto (ej: disponibilidad)",
  "422": "Validación fallida",
  "500": "Error del servidor",
  "503": "Servicio no disponible"
}
```

---

## Ejemplos con cURL

### Crear Reserva

```bash
curl -X POST https://tudominio.com/wp-json/adventure-tourism/v1/reservations \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "tour_id": 1,
    "schedule_id": 1,
    "reservation_date": "2026-08-20",
    "num_persons": 5,
    "participant_names": [
      {"name": "Juan Pérez", "age": 35}
    ],
    "email": "juan@example.com",
    "phone": "+56912345678"
  }'
```

### Obtener Disponibilidad

```bash
curl -X GET "https://tudominio.com/wp-json/adventure-tourism/v1/availability/check?tour_id=1&date=2026-08-20&num_persons=5"
```

### Login

```bash
curl -X POST https://tudominio.com/wp-json/adventure-tourism/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "usuario@example.com",
    "password": "SecurePassword123"
  }'
```

---

**Versión:** 1.0.0  
**Última actualización:** Agosto 2026
