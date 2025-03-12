#!/bin/bash

drushcommand="ddev drush";
# drushcommand='vendor/bin/drush';

migrationRollback() {
  echo "Procesando Rollback para $1"
  $drushcommand migrate:rollback "$1"
}
migrationImport() {
  echo "Procesando Import para $1"
  $drushcommand migrate:import "$1"
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

migrationskey=(
  upgrade_d7_url_alias
)

for mig_key in "${migrationskey[@]}"; do
  migrationReset "$mig_key"
  migrationRollback "$mig_key"
  migrationImport "$mig_key"
  migrationStatus "$mig_key"
done
