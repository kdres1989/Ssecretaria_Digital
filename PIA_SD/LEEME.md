# Secretaría Digital – Institución Educativa Santa Teresa

## Instalación en XAMPP (desde cero)
1. Extrae el zip directamente en `C:\\xampp\\htdocs\\`. Debe quedar `htdocs\\PIA_SD\\index.php`
   (si adentro hay otra carpeta `PIA_SD`, mueve su contenido un nivel arriba).
2. En XAMPP inicia **Apache** y **MySQL**.
3. Abre `http://localhost/phpmyadmin` → pestaña **SQL** → pega todo el contenido de `sql/secretaria_digital.sql` → **Continuar**.
4. Abre `http://localhost/PIA_SD/`.

## Usuarios de prueba
| Tipo | Usuario | Contraseña |
|---|---|---|
| Personal Administrativo | `admin` | `admin123` |
| Estudiante | `estudiante` | `estudiante123` |

El personal administrativo entra al **Panel** (citas, papelería, mensajes y avisos).
Cambia las contraseñas antes de usarlo con datos reales.

## Estructura
```
PIA_SD/
├── index.php, nosotros.php, notificaciones.php
├── login.php, logout.php
├── cita.php, retiro.php, formulario.php   (guardan en la base de datos)
├── admin.php                              (panel del personal administrativo)
├── assets/css, assets/img
├── includes/    (funciones, cabecera, pie)
├── config/conexion.php   (usuario/contraseña de MySQL)
└── sql/secretaria_digital.sql
```

## Base de datos (secretaria_digital)
- `usuarios` – estudiantes y personal administrativo (contraseñas cifradas)
- `citas` – citas de matrícula
- `solicitudes_papeleria` – solicitudes de certificados y paz y salvos
- `mensajes_contacto` – mensajes del formulario de contacto
- `notificaciones` – avisos que se ven en la página de notificaciones
