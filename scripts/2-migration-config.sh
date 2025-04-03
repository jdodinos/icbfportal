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
    upgrade_action_settings
    upgrade_d7_action
    upgrade_d7_advagg_mod_settings
    upgrade_d7_advagg_settings
    upgrade_d7_captcha_points
    upgrade_d7_captcha_settings
    upgrade_d7_dblog_settings
    upgrade_d7_entityqueue
    # upgrade_d7_file_private
    upgrade_d7_filter_settings
    upgrade_d7_global_theme_settings
    upgrade_d7_image_settings
    upgrade_d7_image_styles
    upgrade_d7_metatag_defaults
    upgrade_d7_metatag_settings
    upgrade_d7_node_settings
    upgrade_d7_pathauto_settings
    upgrade_d7_recaptcha_settings
    upgrade_d7_search404_settings
    upgrade_d7_search_page
    upgrade_d7_search_settings
    upgrade_d7_smtp_settings
    upgrade_d7_system_authorize
    upgrade_d7_system_cron
    upgrade_d7_system_date
    upgrade_d7_system_file
    upgrade_d7_system_mail
    upgrade_d7_system_performance
    upgrade_d7_theme_settings
    upgrade_d7_user_flood
    upgrade_d7_user_mail
    upgrade_d7_user_settings
    upgrade_file_settings
    upgrade_fontawesome_settings
    upgrade_locale_settings
    upgrade_mail_system_settings
    upgrade_menu_settings
    upgrade_statistics_settings
    upgrade_system_image
    upgrade_system_image_gd
    upgrade_system_logging
    upgrade_system_maintenance
    upgrade_system_rss
    upgrade_system_site
    upgrade_taxonomy_manager_settings
    upgrade_taxonomy_settings
    upgrade_text_settings
    upgrade_update_settings
    upgrade_d7_taxonomy_vocabulary
    upgrade_d7_field_collection_type
    upgrade_d7_vote_type
    upgrade_fivestar_vote_type
    upgrade_d7_node_type
    upgrade_d7_comment_type
    upgrade_block_content_type
    upgrade_d7_bean_block_type
    upgrade_block_content_body_field
    upgrade_block_content_entity_display
    upgrade_block_content_entity_form_display
    upgrade_d7_field
    upgrade_d7_metatag_field_taxonomy_term
    upgrade_d7_metatag_field_instance_taxonomy_term_forums
    upgrade_d7_metatag_field_instance_widget_settings_taxonomy_term_forums
    upgrade_d7_metatag_field_instance_taxonomy_term_locations
    upgrade_d7_metatag_field_instance_widget_settings_taxonomy_term_locations
    upgrade_d7_metatag_field_instance_taxonomy_term_sige_process_type
    upgrade_d7_metatag_field_instance_widget_settings_taxonomy_term_sige_process_type
    upgrade_d7_metatag_field_instance_taxonomy_term_secctions
    upgrade_d7_metatag_field_instance_widget_settings_taxonomy_term_secctions
    upgrade_d7_metatag_field_instance_taxonomy_term_procedure
    upgrade_d7_metatag_field_instance_widget_settings_taxonomy_term_procedure
    upgrade_d7_metatag_field_instance_taxonomy_term_para_papas
    upgrade_d7_metatag_field_instance_widget_settings_taxonomy_term_para_papas
    upgrade_cacheexclude_settings
    upgrade_d7_filter_format
    upgrade_d7_user_role
    upgrade_d7_autologout_settings
    upgrade_d7_autologout_roles
    upgrade_language
    upgrade_default_language
    upgrade_user_picture_field
    upgrade_user_picture_field_instance
    upgrade_user_picture_entity_display
    upgrade_user_picture_entity_form_display
    upgrade_d7_metatag_field_user
    upgrade_d7_metatag_field_instance_user
    upgrade_d7_metatag_field_instance_widget_settings_user
    upgrade_d7_comment_field
    upgrade_d7_comment_field_instance
    upgrade_d7_metatag_field_node
    upgrade_d7_metatag_field_instance_node_acta_contentiva
    upgrade_d7_metatag_field_instance_widget_settings_node_acta_contentiva
    upgrade_d7_metatag_field_instance_node_agencies_adoptions_colombian
    upgrade_d7_metatag_field_instance_widget_settings_node_agencies_adoptions_colombian
    upgrade_d7_metatag_field_instance_node_announcement
    upgrade_d7_metatag_field_instance_widget_settings_node_announcement
    upgrade_d7_metatag_field_instance_node_article
    upgrade_d7_metatag_field_instance_widget_settings_node_article
    upgrade_d7_metatag_field_instance_node_articles_iin
    upgrade_d7_metatag_field_instance_widget_settings_node_articles_iin
    upgrade_d7_metatag_field_instance_node_articles_portal_web
    upgrade_d7_metatag_field_instance_widget_settings_node_articles_portal_web
    upgrade_d7_metatag_field_instance_node_articulo_mis_manos_te_ensenan
    upgrade_d7_metatag_field_instance_widget_settings_node_articulo_mis_manos_te_ensenan
    upgrade_d7_metatag_field_instance_node_banner_home
    upgrade_d7_metatag_field_instance_widget_settings_node_banner_home
    upgrade_d7_metatag_field_instance_node_bot_n_home
    upgrade_d7_metatag_field_instance_widget_settings_node_bot_n_home
    upgrade_d7_metatag_field_instance_node_curriculum_vitae
    upgrade_d7_metatag_field_instance_widget_settings_node_curriculum_vitae
    upgrade_d7_metatag_field_instance_node_datos_ni_os_migrantes
    upgrade_d7_metatag_field_instance_widget_settings_node_datos_ni_os_migrantes
    upgrade_d7_metatag_field_instance_node_document
    upgrade_d7_metatag_field_instance_widget_settings_node_document
    upgrade_d7_metatag_field_instance_node_documentos_contrataci_n
    upgrade_d7_metatag_field_instance_widget_settings_node_documentos_contrataci_n
    upgrade_d7_metatag_field_instance_node_documentos_de_normativa
    upgrade_d7_metatag_field_instance_widget_settings_node_documentos_de_normativa
    upgrade_d7_metatag_field_instance_node_documento_de_convocatoria
    upgrade_d7_metatag_field_instance_widget_settings_node_documento_de_convocatoria
    upgrade_d7_metatag_field_instance_node_documento_en_construccion
    upgrade_d7_metatag_field_instance_widget_settings_node_documento_en_construccion
    upgrade_d7_metatag_field_instance_node_documento_multiple
    upgrade_d7_metatag_field_instance_widget_settings_node_documento_multiple
    upgrade_d7_metatag_field_instance_node_document_microsite
    upgrade_d7_metatag_field_instance_widget_settings_node_document_microsite
    upgrade_d7_metatag_field_instance_node_encargos
    upgrade_d7_metatag_field_instance_widget_settings_node_encargos
    upgrade_d7_metatag_field_instance_node_encuesta_web
    upgrade_d7_metatag_field_instance_widget_settings_node_encuesta_web
    upgrade_d7_metatag_field_instance_node_enlaces_transparencia
    upgrade_d7_metatag_field_instance_widget_settings_node_enlaces_transparencia
    upgrade_d7_metatag_field_instance_node_evaluaci_n
    upgrade_d7_metatag_field_instance_widget_settings_node_evaluaci_n
    upgrade_d7_metatag_field_instance_node_event_calendar
    upgrade_d7_metatag_field_instance_widget_settings_node_event_calendar
    upgrade_d7_metatag_field_instance_node_glosario
    upgrade_d7_metatag_field_instance_widget_settings_node_glosario
    upgrade_d7_metatag_field_instance_node_hiring_process
    upgrade_d7_metatag_field_instance_widget_settings_node_hiring_process
    upgrade_d7_metatag_field_instance_node_infografia_observatorio
    upgrade_d7_metatag_field_instance_widget_settings_node_infografia_observatorio
    upgrade_d7_metatag_field_instance_node_info_contexto
    upgrade_d7_metatag_field_instance_widget_settings_node_info_contexto
    upgrade_d7_metatag_field_instance_node_instituciones_adopciones
    upgrade_d7_metatag_field_instance_widget_settings_node_instituciones_adopciones
    upgrade_d7_metatag_field_instance_node_local_shopping
    upgrade_d7_metatag_field_instance_widget_settings_node_local_shopping
    upgrade_d7_metatag_field_instance_node_monitoreo_gesti_n
    upgrade_d7_metatag_field_instance_widget_settings_node_monitoreo_gesti_n
    upgrade_d7_metatag_field_instance_node_multimedia
    upgrade_d7_metatag_field_instance_widget_settings_node_multimedia
    upgrade_d7_metatag_field_instance_node_news
    upgrade_d7_metatag_field_instance_widget_settings_node_news
    upgrade_d7_metatag_field_instance_node_notifications
    upgrade_d7_metatag_field_instance_widget_settings_node_notifications
    upgrade_d7_metatag_field_instance_node_page
    upgrade_d7_metatag_field_instance_widget_settings_node_page
    upgrade_d7_metatag_field_instance_node_persona
    upgrade_d7_metatag_field_instance_widget_settings_node_persona
    upgrade_d7_metatag_field_instance_node_petition_rights
    upgrade_d7_metatag_field_instance_widget_settings_node_petition_rights
    upgrade_d7_metatag_field_instance_node_photo_gallery2_event
    upgrade_d7_metatag_field_instance_widget_settings_node_photo_gallery2_event
    upgrade_d7_metatag_field_instance_node_process
    upgrade_d7_metatag_field_instance_widget_settings_node_process
    upgrade_d7_metatag_field_instance_node_rendicion_de_cuentas
    upgrade_d7_metatag_field_instance_widget_settings_node_rendicion_de_cuentas
    upgrade_d7_metatag_field_instance_node_sector_studies
    upgrade_d7_metatag_field_instance_widget_settings_node_sector_studies
    upgrade_d7_metatag_field_instance_node_sede_local
    upgrade_d7_metatag_field_instance_widget_settings_node_sede_local
    upgrade_d7_metatag_field_instance_node_services_faq
    upgrade_d7_metatag_field_instance_widget_settings_node_services_faq
    upgrade_d7_metatag_field_instance_node_summon
    upgrade_d7_metatag_field_instance_widget_settings_node_summon
    upgrade_d7_metatag_field_instance_node_summon_sdg
    upgrade_d7_metatag_field_instance_widget_settings_node_summon_sdg
    upgrade_d7_metatag_field_instance_node_tramites_vus
    upgrade_d7_metatag_field_instance_widget_settings_node_tramites_vus
    upgrade_d7_metatag_field_instance_node_transparency
    upgrade_d7_metatag_field_instance_widget_settings_node_transparency
    upgrade_d7_metatag_field_instance_node_webform
    upgrade_d7_metatag_field_instance_widget_settings_node_webform
    upgrade_d7_comment_entity_display
    upgrade_d7_comment_entity_form_display
    upgrade_d7_comment_entity_form_display_subject
    upgrade_d7_view_modes
    upgrade_d7_forum_settings
    upgrade_d7_google_analytics_settings
    upgrade_d7_user
    upgrade_d7_language_types
    upgrade_d7_language_negotiation_settings
    upgrade_d7_menu
    upgrade_d7_metatag_field_file
    upgrade_d7_node_title_label # Faltan 27
    upgrade_d7_pathauto_patterns # Faltan 2
    upgrade_d7_webform
    upgrade_d7_scheduler_settings
    upgrade_d7_metatag_field_instance_taxonomy_term_sige_process_type
    upgrade_d7_metatag_field_instance_widget_settings_taxonomy_term_forums
    upgrade_d7_metatag_field_instance_widget_settings_taxonomy_term_locations
    upgrade_d7_metatag_field_instance_widget_settings_taxonomy_term_para_papas
    upgrade_d7_metatag_field_instance_widget_settings_taxonomy_term_procedure
    upgrade_d7_metatag_field_instance_widget_settings_taxonomy_term_secctions
    upgrade_d7_metatag_field_instance_widget_settings_taxonomy_term_sige_process_type
    upgrade_d7_taxonomy_term_actas_contentivas_type
    upgrade_d7_field_group_node_announcement
    upgrade_d7_field_group_node_article
    upgrade_d7_field_group_node_articles_iin
    upgrade_d7_field_group_node_articles_portal_web
    upgrade_d7_field_group_node_articulo_mis_manos_te_ensenan
    upgrade_d7_field_group_node_articulo_nna
    upgrade_d7_field_group_node_document
    upgrade_d7_field_group_node_enlaces_transparencia
    upgrade_d7_field_group_node_evaluaci_n
    upgrade_d7_field_group_node_event_calendar
    upgrade_d7_field_group_node_info_contexto
    upgrade_d7_field_group_node_instituciones_adopciones
    upgrade_d7_field_group_node_news
    upgrade_d7_field_group_node_page
    upgrade_d7_field_group_node_persona
    upgrade_d7_field_group_node_photo_gallery2_event
    upgrade_d7_field_group_node_process
    upgrade_d7_field_group_node_services_faq
    upgrade_d7_field_group_node_summon
    upgrade_d7_field_group_node_tramites_vus
    upgrade_d7_field_group_node_transparency
    upgrade_d7_field_group_taxonomy_term_locations
    upgrade_d7_field_group_taxonomy_term_procedure
    upgrade_d7_field_group_taxonomy_term_programa_adopciones
    upgrade_d7_field_group_taxonomy_term_secctions
    upgrade_d7_rdf_mapping
    upgrade_language_prefixes_and_domains
    upgrade_d7_language_content_comment_settings
    upgrade_d7_field_instance
    upgrade_d7_field_instance_widget_settings
    upgrade_d7_field_formatter_settings
    upgrade_d7_field_formatter_class
    upgrade_d7_menu_links

    # Pendiente de revision
    upgrade_d7_language_content_settings
    # upgrade_d7_views_migration

    # No migrar
    # # upgrade_d7_language_content_menu_settings
    # # upgrade_node_translation_menu_links
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
