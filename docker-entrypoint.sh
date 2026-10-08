#!/bin/sh
set -eu

PGDATA=/tmp/blog-postgres
POSTGRES_BIN=/usr/lib/postgresql/17/bin

rm -rf "$PGDATA"
install -d -o postgres -g postgres "$PGDATA"

su postgres -s /bin/sh -c "$POSTGRES_BIN/initdb -D '$PGDATA' --auth-local=trust --auth-host=trust"
su postgres -s /bin/sh -c "$POSTGRES_BIN/pg_ctl -D '$PGDATA' -o '-h 127.0.0.1 -p 5432' -w start"

su postgres -s /bin/sh -c "$POSTGRES_BIN/psql --set=ON_ERROR_STOP=1 --username=postgres --dbname=postgres --set=app_user=blog --set=app_password=blog_local_password --set=app_database=blog" <<'SQL'
CREATE ROLE :"app_user" LOGIN PASSWORD :'app_password';
CREATE DATABASE :"app_database" OWNER :"app_user";
SQL

for seed_file in /var/www/html/database/init/*.sql; do
    su postgres -s /bin/sh -c "$POSTGRES_BIN/psql --set=ON_ERROR_STOP=1 --username=blog --dbname=blog --file='$seed_file'"
done

exec "$@"