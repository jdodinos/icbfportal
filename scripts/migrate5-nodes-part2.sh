#!/bin/bash

# drushcommand="ddev drush";
drushcommand='vendor/bin/drush';

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
  upgrade_d7_node_complete_encargos
  upgrade_d7_node_complete_encuesta_web
  upgrade_d7_node_complete_enlaces_referencia
  upgrade_d7_node_complete_enlaces_transparencia
  upgrade_d7_node_complete_evaluaci_n
  upgrade_d7_node_complete_event_calendar
  upgrade_d7_node_complete_forum
  upgrade_d7_node_complete_glosario
  upgrade_d7_node_complete_hiring_process
  upgrade_d7_node_complete_infografia_observatorio
  upgrade_d7_node_complete_informe_denuncias_anticorrupcion
  upgrade_d7_node_complete_info_contexto
  upgrade_d7_node_complete_instituciones_adopciones
  upgrade_d7_node_complete_isotope_example
  upgrade_d7_node_complete_local_shopping
  upgrade_d7_node_complete_monitoreo_gesti_n
  upgrade_d7_node_complete_multimedia
  upgrade_d7_node_complete_news
  upgrade_d7_node_complete_notifications
  upgrade_d7_node_complete_page
  upgrade_d7_node_complete_panel
  upgrade_d7_node_complete_persona
  upgrade_d7_node_complete_petition_rights
)

for mig_key in "${migrationskey[@]}"; do
  migrationReset "$mig_key"
  migrationRollback "$mig_key"
  migrationImport "$mig_key"
  migrationStatus "$mig_key"
done
