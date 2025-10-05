## Documentación API: Categorías

Base URL: http://127.0.0.1:8000/api/v1/categories


## 1 Listar todas las categorías

GET /api/v1/categories

## Headers:

Authorization: Bearer <token>

## Request Body: 

Ninguno

## Response 200:

{
    "success": true,
    "message": "Listado de categorías",
    "data": [
        {
            "id": 7,
            "name": "Recursos",
            "description": "Materiales",
            "status": "active",
            "created_at": "2025-10-03T05:06:26.000000Z",
            "updated_at": "2025-10-03T05:06:26.000000Z"
        },
        {
            "id": 3,
            "name": "Electrónica y Tecnología1",
            "description": "Todo tipo de productos electrónicos",
            "status": "active",
            "created_at": "2025-10-03T04:25:16.000000Z",
            "updated_at": "2025-10-03T05:05:27.000000Z"
        },
        {
            "id": 2,
            "name": "Hogar",
            "description": "Artículos para el hogar",
            "status": "active",
            "created_at": "2025-10-03T04:24:42.000000Z",
            "updated_at": "2025-10-03T04:24:42.000000Z"
        },
        {
            "id": 1,
            "name": "Electrónica",
            "description": "Productos electrónicos",
            "status": "active",
            "created_at": "2025-10-03T04:24:30.000000Z",
            "updated_at": "2025-10-03T04:24:30.000000Z"
        }
    ]
}

## 2 Crear una categoría

POST /api/v1/categories

## Headers:

Content-Type: application/json
Authorization: Bearer <token>

## Request Body:

{
  "name": "Tecnología1",
  "description": "Productos informáticos",
  "status": "active"
}

## Response 201:

{
    "success": true,
    "message": "Categoría creada exitosamente",
    "data": {
        "id": 7,
        "name": "Recursos",
        "description": "Materiales",
        "status": "active",
        "created_at": "2025-10-03T05:06:26.000000Z",
        "updated_at": "2025-10-03T05:06:26.000000Z"
    }
}

## 3 Ver una categoría específica

GET /api/v1/categories/3

## Headers:

Authorization: Bearer <token>

## Request Body: 

Ninguno

## Response 200:

{
    "success": true,
    "message": "Detalle de la categoría",
    "data": {
        "id": 2,
        "name": "Hogar",
        "description": "Artículos para el hogar",
        "status": "active",
        "created_at": "2025-10-03T04:24:42.000000Z",
        "updated_at": "2025-10-03T04:24:42.000000Z"
    }
}

## 4 Actualizar una categoría

PUT /api/v1/categories/3

## Headers:

Content-Type: application/json
Authorization: Bearer <token>

## Request Body:

{
  "name": "Electrónica y Tecnología",
  "description": "Todo tipo de productos electrónicos",
  "status": "active"
}

## Response 200:

{
    "success": true,
    "message": "Categoría actualizada exitosamente",
    "data": {
        "id": 3,
        "name": "Electrónica y Tecnología1",
        "description": "Todo tipo de productos electrónicos",
        "status": "active",
        "created_at": "2025-10-03T04:25:16.000000Z",
        "updated_at": "2025-10-03T05:05:27.000000Z"
    }
}

## 5 Eliminar una categoría

DELETE /api/v1/categories/6

## Headers:

Authorization: Bearer <token>

## Request Body: 

Ninguno

## Response 200:

{
    "success": true,
    "message": "Categoría eliminada exitosamente"
}
