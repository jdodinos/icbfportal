#!/bin/sh

# Modulos Drupal para migracion
ddev drush en -y migrate_upgrade
ddev drush en -y migrate_drupal_ui
ddev drush en -y migrate_plus
ddev drush en -y migrate_tools
ddev drush en -y migrate_upgrade
ddev drush en -y migrate_devel
ddev drush en -y views_migration
ddev drush en -y migrate_url2link
ddev drush en -y migrate_file_to_media
ddev drush en -y webform_migrate
ddev drush en -y field_group_migrate

# Modulos que estaban en el sitio Drupal 7
ddev drush en -y address
ddev drush en -y telephone
ddev drush en -y admin_toolbar
ddev drush en -y adminimal_admin_toolbar
ddev drush en -y audit_log
ddev drush en -y auto_entitylabel
# ddev drush en -y backup_migrate
ddev drush en -y better_exposed_filters
ddev drush en -y block_class
ddev drush en -y captcha
ddev drush en -y fullcalendar_view
ddev drush en -y colorbox
ddev drush en -y context
ddev drush en -y context_ui
ddev drush en -y diff
ddev drush en -y entityqueue
ddev drush en -y config_split
ddev drush en -y feeds
ddev drush en -y field_formatter_class
ddev drush en -y field_group
ddev drush en -y field_permissions
ddev drush en -y paragraphs
ddev drush en -y file_resup
ddev drush en -y flood_control
ddev drush en -y fontawesome
ddev drush en -y forum


ddev drush en -y fivestar
ddev drush en -y advagg_bundler
ddev drush en -y advagg_mod
ddev drush en -y advagg
ddev drush en -y autologout
ddev drush en -y cacheexclude
ddev drush en -y charts
ddev drush en -y computed_field
ddev drush en -y search_api_db
ddev drush en -y geocoder
ddev drush en -y geocoder_autocomplete
ddev drush en -y geofield
ddev drush en -y google_analytics_counter
ddev drush en -y charts_google
ddev drush en -y hacked
ddev drush en -y charts_highcharts
ddev drush en -y imce
ddev drush en -y image_field_caption
ddev drush en -y inline_entity_form
ddev drush en -y insert
ddev drush en -y leaflet
ddev drush en -y libraries
ddev drush en -y login_emailusername
ddev drush en -y mailsystem
ddev drush en -y media
ddev drush en -y media_bulk_upload
ddev drush en -y metatag
ddev drush en -y metatag_facebook
ddev drush en -y metatag_google_cse
ddev drush en -y metatag_google_plus
ddev drush en -y metatag_mobile
ddev drush en -y metatag_twitter_cards
ddev drush en -y metatag_verification
ddev drush en -y metatag_views
ddev drush en -y metatag_favicons
ddev drush en -y metatag_hreflang
ddev drush en -y module_filter
ddev drush en -y nice_menus
ddev drush en -y panels
ddev drush en -y pathauto
ddev drush en -y plupload
ddev drush en -y publishcontent
ddev drush en -y purge
ddev drush en -y quicktabs
ddev drush en -y roleassign
ddev drush en -y rules
ddev drush en -y smtp
ddev drush en -y search404
ddev drush en -y search_api
ddev drush en -y search_api_glossary
ddev drush en -y search_api_autocomplete
ddev drush en -y search_api_sorts
ddev drush en -y search_api_spellcheck
ddev drush en -y seckit
ddev drush en -y security_review
ddev drush en -y slick
ddev drush en -y slick_ui
ddev drush en -y slick_devel
ddev drush en -y slick_extras
ddev drush en -y slick_views
ddev drush en -y search_api_solr
ddev drush en -y statistics
ddev drush en -y taxonomy_manager
ddev drush en -y taxonomy_menu
ddev drush en -y term_reference_tree
ddev drush en -y token
ddev drush en -y varnish
ddev drush en -y video
ddev drush en -y view_unpublished
ddev drush en -y views
ddev drush en -y views_autocomplete_filters
ddev drush en -y views_bootstrap
ddev drush en -y views_bulk_operations
ddev drush en -y views_conditional
ddev drush en -y views_contextual_filters_or
ddev drush en -y views_data_export
ddev drush en -y views_field_view
ddev drush en -y views_infinite_scroll
ddev drush en -y views_natural_sort
ddev drush en -y views_slideshow
ddev drush en -y views_fieldsets
ddev drush en -y votingapi
ddev drush en -y webform
ddev drush en -y webform_ui
ddev drush en -y xmlsitemap
ddev drush en -y youtube
ddev drush en -y geophp
ddev drush en -y recaptcha
ddev drush en -y advagg_css_minify
ddev drush en -y advagg_js_minify
ddev drush en -y devel
ddev drush en -y config_translation
ddev drush en -y content_translation
ddev drush en -y dashboard
ddev drush en -y date_popup
ddev drush en -y menu_link_attributes
ddev drush en -y metatag_open_graph
ddev drush en -y metatag_open_graph_products
ddev drush en -y sitemap
ddev drush en -y views_timelinejs
ddev drush en -y facets
ddev drush en -y facets_exposed_filters

ddev drush en -y field_validation
ddev drush en -y google_analytics
ddev drush en -y shs
ddev drush en -y image_effects
ddev drush en -y webform_analysis
ddev drush en -y taxonomy_menu_ui
ddev drush en -y scheduler
ddev drush en -y bootstrap_quicktabs
ddev drush en -y views_flexbox
ddev drush en -y doe
ddev drush en -y bootstrap_layouts
ddev drush en -y bootstrap_layout_builder
ddev drush en -y panelizer
ddev drush en -y menu_item_extras
ddev drush en -y job_scheduler
ddev drush en -y we_megamenu
ddev drush en -y page_manager
ddev drush en -y php
ddev drush en -y poll
ddev drush en -y rdf
ddev drush en -y easy_breadcrumb
ddev drush en -y pdf_using_mpdf
ddev drush en -y page_manager_ui
ddev drush en -y purge_ui
ddev drush en -y geocoder_geofield
ddev drush en -y geofield_map
ddev drush en -y datetime_range
