#!/bin/bash
set -x

# Variables de color
RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m' # Sin color

# Archivo de log para registrar la salida de las migraciones.
LOGFILE="migraciones.log"

# Registra el inicio del proceso con la fecha.
echo "=== Inicio de migraciones: $(date) ===" >> "$LOGFILE"

# Función para ejecutar una migración, capturar la salida y registrar si se completó o no.
run_migration() {
  MIGRATION="$1"
  echo "=== Ejecutando migración: $MIGRATION ===" | tee -a "$LOGFILE"
  
  # Captura la salida y el código de salida del comando.
  output=$(vendor/bin/drush migrate:import "$MIGRATION" 2>&1)
  exit_status=$?
  
  # Registra la salida del comando.
  echo "$output" | tee -a "$LOGFILE"
  
  # Evalúa el código de salida y muestra el resultado, coloreando el mensaje de error en rojo.
  if [ $exit_status -eq 0 ]; then
    echo -e "${GREEN}Migración '$MIGRATION' completada exitosamente.${NC}" | tee -a "$LOGFILE"
  else
    echo -e "${RED}Migración '$MIGRATION' fallida.${NC}" | tee -a "$LOGFILE"
  fi
}

# Lista de migraciones a ejecutar
run_migration "upgrade_d7_file"
run_migration "upgrade_d7_user"
run_migration "upgrade_d7_node_complete_banner_home"
run_migration "upgrade_d7_node_complete_document"
run_migration "upgrade_d7_node_complete_encargos"
run_migration "upgrade_d7_node_complete_enlaces_transparencia"
run_migration "upgrade_d7_node_complete_evaluaci_n"
run_migration "upgrade_d7_node_complete_event_calendar"
run_migration "upgrade_d7_node_complete_notifications"
run_migration "upgrade_d7_node_complete_petition_rights"
run_migration "upgrade_d7_node_complete_process"
run_migration "upgrade_d7_node_complete_sede_local"
run_migration "upgrade_d7_field_collection_revisions_summoned"
run_migration "upgrade_d7_field_collection_revisions_children_defendant"
run_migration "upgrade_d7_node_complete_summon"
run_migration "upgrade_d7_node_complete_summon_sdg"
run_migration "upgrade_d7_node_complete_tramites_vus"
run_migration "upgrade_d7_node_complete_transparency"
run_migration "upgrade_d7_comment"
run_migration "upgrade_d7_field_formatter_settings"
run_migration "upgrade_d7_field_instance_widget_settings"
run_migration "upgrade_d7_language_content_comment_settings"
run_migration "upgrade_d7_language_content_settings"
run_migration "upgrade_d7_metatag_field_instance_node_enlaces_referencia"
run_migration "upgrade_d7_node_title_label"
run_migration "upgrade_d7_pathauto_patterns"
run_migration "upgrade_d7_taxonomy_term_secctions"
run_migration "upgrade_d7_url_alias"
run_migration "upgrade_d7_webform_submission"
run_migration "upgrade_node_translation_menu_links"
run_migration "upgrade_statistics_node_counter"
run_migration "upgrade_statistics_node_translation_counter"
run_migration "upgrade_d7_megamenu_links"

# Registra el final del proceso.
echo "=== Fin de migraciones: $(date) ===" >> "$LOGFILE"
