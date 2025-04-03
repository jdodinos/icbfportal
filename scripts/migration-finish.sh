#!/bin/bash
# Validate paramters
if [ -z "$1" ]; then
  echo "Error: Debes escribir una opción válida. Usa 'rollback' o 'import'."
  exit 1
fi

# drushcommand="ddev drush";
drushcommand='vendor/bin/drush';

oper='';
if [ "$1" = "rollback" ]; then
  oper="rollback"
elif [ "$1" = "import" ]; then
  oper="import"
elif [ "$1" = "status" ]; then
  oper="status"
elif [ "$1" = "reset" ]; then
  oper="reset-status"
elif [ "$1" = "messages" ]; then
  oper="messages"
else
  echo "Error: Comando desconocido. Usa 'rollback' o 'import'."
  exit 1
fi

migrationRollback() {
  echo "Procesando Rollback para $1"
  $drushcommand migrate:rollback "$1"
}
migrationImport() {
  echo "Procesando Import para $1"
  $drushcommand migrate:import "$1" --update
}
migrationStatus() {
  echo "Procesando Status para $1"
  $drushcommand migrate:status "$1"
}
migrationReset() {
  echo "Procesando Reset Status para $1"
  $drushcommand migrate:reset-status "$1"
}
migrationMessages() {
  echo "Procesando Messages para $1"
  $drushcommand migrate:messages "$1"
}

if [ -n "$2" ]; then
  migrationskey=("$2")
else
  migrationskey=(
    # Despues de migrar contenidos
    upgrade_d7_google_analytics_user_settings
  )
fi

for mig_key in "${migrationskey[@]}"; do
  if [ "$1" = "rollback" ]; then
    migrationReset "$mig_key"
    migrationRollback "$mig_key"
  elif [ "$1" = "import" ]; then
    migrationReset "$mig_key"
    migrationRollback "$mig_key"
    migrationImport "$mig_key"
    migrationStatus "$mig_key"
  elif [ "$1" = "status" ]; then
    migrationStatus "$mig_key"
  elif [ "$1" = "reset" ]; then
    migrationReset "$mig_key"
  elif [ "$1" = "messages" ]; then
    migrationMessages "$mig_key"
  fi
done
