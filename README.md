# API de Gestión de Pedidos con Eventos

API REST desarrollada con **Laravel 11** para gestionar un sistema de pedidos, usuarios y productos. Está diseñada aplicando buenas prácticas, desacoplamiento por eventos, control de stock seguro y protección de rutas.

---

## 🛠️ Cómo está construida la API (Lo que incluye)

### 1. Base de Datos e Integridad
- **Modelos y Migraciones:** Configuración de las tablas `User`, `Product`, `Order` y `OrderItem` con sus relaciones, claves foráneas e integridad mediante `onDelete('cascade')`.
- **Reglas de Negocio:** 
  - Control para que el stock nunca sea negativo.
  - El campo `unit_price` se copia directamente del precio actual del producto al crear la línea de pedido en la base de datos, evitando que se manipulen los precios desde el JSON enviado.

### 2. Seguridad y Middleware Personalizado
- **Autenticación:** Gestión segura de tokens con **Laravel Sanctum**.
- **Middleware `CheckOrderOwner`:** Capa de seguridad personalizada para las rutas de **Detalle** (`GET`) y **Cancelación** (`PUT`). Verifica que el usuario autenticado sea el dueño real del pedido; si intenta acceder a un pedido ajeno, el sistema lo corta con un `403 Forbidden`.

### 3. Lógica Automatizada con Observers y Eventos
Para dejar el flujo lo más limpio y automatizado posible, he combinado ambas estrategias:
- **`OrderItemObserver`:** Se encarga de escuchar cuando se crea, actualiza o borra una línea de pedido para recalcular y actualizar el `total` de la orden automáticamente.
- **Eventos y Listeners:** Al guardar un pedido se dispara el evento `OrderCreated`. El listener `DecrementProductStock` lo recibe para restar las unidades correspondientes del inventario.

### 4. Validaciones y Mensajes en Castellano
- **Form Requests (`StoreOrderRequest`):** Centraliza la validación para asegurar que el pedido traiga ítems correctos, que los productos existan y que las cantidades sean válidas (mínimo 1).
- **Traducción de Errores:** He personalizado el método `messages()` para que todas las respuestas de error de validación salgan directamente en un **castellano claro y amigable**, en lugar del inglés por defecto del framework.
- **Control de Cancelaciones:** El endpoint comprueba que el pedido esté estrictamente en estado `pending` antes de anularlo. Si ya está completado o cancelado, rebota la petición con un `422`.

### 5. Estructura y Bonus
- **API Resources (`OrderResource`):** Estructura las respuestas JSON quitando datos innecesarios y formateando la información de manera limpia.
- **Transacciones de Base de Datos:** Todo el proceso de crear el pedido y descontar el stock está protegido con `DB::transaction()`. Si nos quedamos sin existencias en mitad del proceso, se lanza una excepción, se hace un **Rollback automático** y la base de datos se queda impecable (sin pedidos fantasma).
- **Query Scopes:** He añadido el scope local `pending()` en el modelo `Order` para poder filtrar las órdenes pendientes de forma mucho más limpia en el código.


- **Transacciones de Base de Datos:** Todo el proceso de crear el pedido y descontar el stock está protegido con `DB::transaction()`. Si nos quedamos sin existencias en mitad del proceso, se lanza una excepción, se hace un **Rollback automático** y la base de datos se queda impecable (sin pedidos fantasma).
- **Query Scopes:** He añadido el scope local `pending()` en el modelo `Order` para poder filtrar las órdenes pendientes de forma mucho más limpia en el código.
- **Tests Automatizados:** Implementación de *Feature Tests* para validar el flujo completo de creación de pedidos, asegurando que la respuesta HTTP y la integridad del stock sean correctas tras cada cambio.
- **Optimización (Caché):** Arquitectura preparada para el uso de `Cache::remember` en el listado de productos, con una estrategia de invalidación mediante `ProductObserver` para garantizar que los datos siempre estén actualizados.

---

## 🚀 Cómo arrancar el proyecto localmente (Laravel Sail / Docker)

Para levantar el entorno rápido con Docker, sigue estos pasos desde la terminal:

1. **Clonar el repositorio y entrar en la carpeta:**
   ```bash
   git clone https://github.com/franj-dev/api-gestion-pedidos.git
   cd api-gestion-pedidos
   ```

2. **Instalar dependencias de Composer:**
   Si no tienes Composer instalado en tu máquina local, no pasa nada; puedes correr este contenedor temporal para instalarlas:
   ```bash
   docker run --rm \
       -u "$(id -u):$(id -g)" \
       -v "$(pwd):/var/www/html" \
       -w /var/www/html \
       laravelsail/php82-composer:latest \
       composer install
   ```

3. **Configurar el archivo de entorno:**
   Crea tu copia local del `.env` (las credenciales reales no se suben al repositorio por seguridad):
   ```bash
   cp .env.example .env
   ```

4. **Levantar los contenedores con Sail:**
   ```bash
   ./vendor/bin/sail up -d
   ```

5. **Generar la clave de la app y cargar la base de datos:**
   ```bash
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate --seed
   ```
   *Nota: El seeder creará unos cuantos usuarios y productos con stock de prueba para que puedas testear los endpoints directamente.*


---

## 📌 Catálogo de Endpoints de la API

*Recuerda añadir la cabecera `Authorization: Bearer <tu_token>` en los endpoints protegidos.*

| Método | URI | Descripción | Acceso |
| :--- | :--- | :--- | :--- |
| **POST** | `/api/register` | Registro de nuevos usuarios | Público |
| **POST** | `/api/login` | Login (Devuelve el token de Sanctum) | Público |
| **GET** | `/api/orders` | Listado de pedidos del usuario autenticado | Protegido |
| **POST** | `/api/orders` | Crear un pedido con sus ítems (Valida stock automáticamente) | Protegido |
| **GET** | `/api/orders/{id}` | Ver detalle de un pedido (Solo si te pertenece) | Protegido + Middleware |
| **PUT** | `/api/orders/{id}/cancel` | Cancelar un pedido (Solo si está 'pending' y es tuyo) | Protegido + Middleware |

## 🧪 Cómo ejecutar las pruebas (Tests)

Para asegurar la calidad del flujo de pedidos y la integridad de los datos, he incluido un *Feature Test* que valida la creación de pedidos y el descuento automático de stock.

Puedes ejecutar las pruebas con el siguiente comando:

```bash
./vendor/bin/sail artisan test
```

### Ejemplos para probar con Postman

#### 🛍️ Crear un pedido (`POST /api/orders`)
Solo hace falta mandar los IDs y las cantidades, la API se encarga del resto:
```json
{
    "items": [
        {
            "product_id": 1,
            "quantity": 2
        }
    ]
}
```

#### ❌ Ejemplo de respuesta de error (Datos no válidos o sin stock)
Así responde la API gracias a las validaciones personalizadas en castellano:
```json
{
    "message": "Los datos proporcionados no son válidos.",
    "errors": {
        "items.0.product_id": [
            "El producto seleccionado no existe en nuestro catálogo."
        ],
        "items.0.quantity": [
            "La cantidad mínima para generar un pedido es de 1 unidad."
        ]
    }
}
```