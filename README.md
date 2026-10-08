# Mein Blog

## Arranque local

Necesitas Docker Desktop con Docker Compose. Desde la carpeta del proyecto ejecuta:

```powershell
docker compose up --build
```

Abre <http://localhost:8081>. Un solo contenedor ejecuta PHP con Apache y PostgreSQL. Cada vez que arranca, PostgreSQL se crea de nuevo en `/tmp` y carga el esquema, los usuarios, artículos y comentarios desde `database/init/`. Puedes cambiar el puerto publicado con la variable `APP_PORT`.

El login permite elegir un usuario de prueba mientras `APP_ENV=development` y `ENABLE_TEST_LOGIN=true`, como queda configurado en Compose. Las cuentas locales `user11` a `user15` comparten la contraseña `test1234`.

Para detener el contenedor:

```powershell
docker compose down
```

Los datos creados durante una sesión no se conservan después de reiniciar el contenedor; cada inicio vuelve al contenido de la semilla.

## Render

Crea un servicio web de tipo **Docker** que use este repositorio y su `Dockerfile`. No hace falta añadir una base de datos gestionada ni configurar variables `DB_*`: PostgreSQL arranca dentro del mismo contenedor y escucha solo en `127.0.0.1`. Render detecta Apache en el puerto 80. La base se regenera desde los SQL de `database/init/` en cada inicio del servicio.