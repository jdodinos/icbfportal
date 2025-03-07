#!/bin/bash

# Validate paramters
if [ -z "$1" ]; then
  exit 1
fi

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
  ddev drush migrate:rollback "$1"
}
migrationImport() {
  echo "Procesando Import para $1"
  ddev drush migrate:import "$1"
}
migrationStatus() {
  echo "Procesando Status para $1"
  ddev drush migrate:status "$1"
}
migrationReset() {
  echo "Procesando Reset Status para $1"
  ddev drush migrate:reset-status "$1"
}
migrationMessages() {
  echo "Procesando Messages para $1"
  ddev drush migrate:messages "$1"
}

if [ -n "$2" ]; then
  migrationskey=("$2")
else
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
    upgrade_d7_custom_block
    upgrade_d7_block
    upgrade_d7_bean_block
    upgrade_d7_node_complete_acta_contentiva
    upgrade_d7_node_complete_agencies_adoptions_colombian
    upgrade_d7_node_complete_announcement
    upgrade_d7_field_collection_post_document_plus
    upgrade_d7_field_collection_summoned
    upgrade_d7_field_collection_children_defendant
    upgrade_d7_field_collection_glazed_content_design
    upgrade_d7_field_collection_multimedia_collection
    upgrade_d7_field_collection_local_staff_list
    upgrade_d7_field_collection_bookpost_group
    upgrade_d7_field_collection_enlaces_referidos
    upgrade_d7_field_collection_revisions_post_document_plus
    upgrade_d7_node_complete_article
    upgrade_d7_node_complete_articles_iin
    upgrade_d7_node_complete_articles_portal_web
    upgrade_d7_field_collection_revisions_bookpost_group
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
    upgrade_d7_node_complete_encargos
    upgrade_d7_node_complete_encuesta_web
    upgrade_d7_node_complete_enlaces_referencia
    upgrade_d7_field_collection_revisions_enlaces_referidos
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
    upgrade_d7_field_collection_revisions_multimedia_collection
    upgrade_d7_node_complete_news
    upgrade_d7_node_complete_notifications
    upgrade_d7_node_complete_page
    upgrade_d7_node_complete_panel
    upgrade_d7_node_complete_persona
    upgrade_d7_node_complete_petition_rights
    upgrade_d7_node_complete_photo_gallery2_event
    upgrade_d7_node_complete_poll
    upgrade_d7_node_complete_portfolio_tramite
    upgrade_d7_node_complete_process
    upgrade_d7_node_complete_rendicion_de_cuentas
    upgrade_d7_node_complete_sector_studies
    upgrade_d7_field_collection_revisions_local_staff_list
    upgrade_d7_node_complete_sede_local
    upgrade_d7_node_complete_services_faq
    upgrade_d7_field_collection_revisions_summoned
    upgrade_d7_field_collection_revisions_children_defendant
    upgrade_d7_node_complete_summon
    upgrade_d7_node_complete_summon_sdg
    upgrade_d7_node_complete_tramites_vus
    upgrade_d7_node_complete_transparency
    upgrade_d7_node_complete_venta_de_bienes
    upgrade_d7_node_complete_webform
    upgrade_d7_webform_submission
    upgrade_d7_vote_node_articulo_nna
    upgrade_d7_vote_node_news
    upgrade_d7_comment

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
  elif [ "$1" = "status" ]; then
    migrationStatus "$mig_key"
  elif [ "$1" = "reset" ]; then
    migrationReset "$mig_key"
  elif [ "$1" = "messages" ]; then
    migrationMessages "$mig_key"
  fi
done
