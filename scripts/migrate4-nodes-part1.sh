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
  upgrade_d7_node_complete_acta_contentiva
  upgrade_d7_node_complete_agencies_adoptions_colombian
  upgrade_d7_node_complete_announcement
  upgrade_d7_node_complete_article
  upgrade_d7_node_complete_articles_iin
  upgrade_d7_node_complete_articles_portal_web
  upgrade_d7_node_complete_articulo_mis_manos_te_ensenan
  upgrade_d7_node_complete_articulo_nna
  upgrade_d7_node_complete_banner_home
  upgrade_d7_node_complete_blog
  upgrade_d7_node_complete_bot_n_home
  upgrade_d7_node_complete_curriculum_vitae
  upgrade_d7_node_complete_datos_ni_os_migrantes
  upgrade_d7_node_complete_document
  upgrade_d7_node_complete_documentos_contrataci_n
  upgrade_d7_node_complete_documentos_de_normativa
  upgrade_d7_node_complete_documento_de_convocatoria
  upgrade_d7_node_complete_documento_en_construccion
  upgrade_d7_node_complete_documento_multiple
  upgrade_d7_node_complete_document_microsite
)

for mig_key in "${migrationskey[@]}"; do
  migrationReset "$mig_key"
  migrationRollback "$mig_key"
  migrationImport "$mig_key"
  migrationStatus "$mig_key"
done
