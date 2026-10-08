# Mein Blog

## Arranque local

Necesitas Docker Desktop con Docker Compose. Desde la carpeta del proyecto ejecuta:

```powershell
docker compose up --build
```

Abre <http://localhost:8081>. El servicio `app` ejecuta PHP con Apache y el servicio `database` ejecuta PostgreSQL. En el primer arranque, PostgreSQL crea el esquema y carga los usuarios, artículos y comentarios desde `database/init/`. Puedes cambiar el puerto con la variable `APP_PORT`.

El login permite elegir un usuario de prueba mientras `APP_ENV=development` y `ENABLE_TEST_LOGIN=true`, como queda configurado en Compose. Las cuentas locales `user11` a `user15` comparten la contraseña `test1234`.

Para detener los servicios conserva la base de datos:

```powershell
docker compose down
```

Los scripts de `database/init/` solo se ejecutan cuando PostgreSQL inicializa un volumen vacío. Para eliminar la base local y volver a crearla desde la semilla:

```powershell
docker compose down --volumes
docker compose up --build
```

`down --volumes` elimina todos los datos guardados en el volumen local.