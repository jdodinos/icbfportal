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
  upgrade_d7_taxonomy_term_migrantes
  upgrade_d7_taxonomy_term_transparencia_1519
  upgrade_d7_taxonomy_term_entidad_titular
  upgrade_d7_taxonomy_term_normativa
  upgrade_d7_taxonomy_term_mis_manos_te_ensenan
  upgrade_d7_taxonomy_term_ninos_ninas_y_adolescentes
  upgrade_d7_taxonomy_term_congreso_xxii
  upgrade_d7_taxonomy_term_despublicados
  upgrade_d7_taxonomy_term_para_papas
  upgrade_d7_taxonomy_term_sige
  upgrade_d7_taxonomy_term_programa_adopciones
  upgrade_d7_taxonomy_term_eventos_agenda
  upgrade_d7_taxonomy_term_trd_regional
  upgrade_d7_taxonomy_term_tags_tr_mites
  upgrade_d7_taxonomy_term_eventos
  upgrade_d7_taxonomy_term_periodicity
  upgrade_d7_taxonomy_term_hiring_process_category
  upgrade_d7_taxonomy_term_procedure_type
  upgrade_d7_taxonomy_term_media_folders
  upgrade_d7_taxonomy_term_menu_transparency
  upgrade_d7_taxonomy_term_menu_observatory
  upgrade_d7_taxonomy_term_menu_childhood
  upgrade_d7_taxonomy_term_convocatorias_gesti_n_humana
  upgrade_d7_taxonomy_term_hiring_process_type
  upgrade_d7_taxonomy_term_procedure
  upgrade_d7_taxonomy_term_tipos_de_ubicaci_n
  upgrade_d7_taxonomy_term_file_category
  upgrade_d7_taxonomy_term_secctions
  upgrade_d7_taxonomy_term_departments_municipalities
  upgrade_d7_taxonomy_term_section_labels
  upgrade_d7_taxonomy_term_actas_contentivas_type
  upgrade_d7_taxonomy_term_announcement_status
  upgrade_d7_taxonomy_term_petition_rights_status
  upgrade_d7_taxonomy_term_tipo_de_notificaci_n
  upgrade_d7_taxonomy_term_dependencies
  upgrade_d7_taxonomy_term_sige_process_type
  upgrade_d7_taxonomy_term_locations
  upgrade_d7_taxonomy_term_event_calendar_status
  upgrade_d7_taxonomy_term_forums
  upgrade_d7_taxonomy_term_citizen_category
  upgrade_d7_taxonomy_term_tags
)

for mig_key in "${migrationskey[@]}"; do
  migrationReset "$mig_key"
  migrationRollback "$mig_key"
  migrationImport "$mig_key"
  migrationStatus "$mig_key"
done
