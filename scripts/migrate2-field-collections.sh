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
  upgrade_d7_field_collection_post_document_plus
  upgrade_d7_field_collection_summoned
  upgrade_d7_field_collection_children_defendant
  upgrade_d7_field_collection_glazed_content_design
  upgrade_d7_field_collection_multimedia_collection
  upgrade_d7_field_collection_local_staff_list
  upgrade_d7_field_collection_bookpost_group
  upgrade_d7_field_collection_enlaces_referidos
  upgrade_d7_field_collection_revisions_post_document_plus
  upgrade_d7_field_collection_revisions_bookpost_group
  upgrade_d7_field_collection_revisions_enlaces_referidos
  upgrade_d7_field_collection_revisions_multimedia_collection
  upgrade_d7_field_collection_revisions_local_staff_list
  upgrade_d7_field_collection_revisions_summoned
  upgrade_d7_field_collection_revisions_children_defendant
)

for mig_key in "${migrationskey[@]}"; do
  migrationReset "$mig_key"
  # migrationRollback "$mig_key"
  migrationImport "$mig_key"
  migrationStatus "$mig_key"
done
