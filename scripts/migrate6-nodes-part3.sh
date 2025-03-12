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
  upgrade_d7_node_complete_photo_gallery2_event
  upgrade_d7_node_complete_poll
  upgrade_d7_node_complete_portfolio_tramite
  upgrade_d7_node_complete_process
  upgrade_d7_node_complete_rendicion_de_cuentas
  upgrade_d7_node_complete_sector_studies
  upgrade_d7_node_complete_sede_local
  upgrade_d7_node_complete_services_faq
  upgrade_d7_node_complete_summon
  upgrade_d7_node_complete_summon_sdg
  upgrade_d7_node_complete_tramites_vus
  upgrade_d7_node_complete_transparency
  upgrade_d7_node_complete_venta_de_bienes
  upgrade_d7_node_complete_webform
)

for mig_key in "${migrationskey[@]}"; do
  migrationReset "$mig_key"
  migrationRollback "$mig_key"
  migrationImport "$mig_key"
  migrationStatus "$mig_key"
done
