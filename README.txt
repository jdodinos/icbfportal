# Instalacion sitios web con DDEV

## Instalacion del sitio ICBF Portal WEB.
*Estos sitios se han versionados con archivos de configuración de DDev por tanto debes tener instalado en tu sistema Docker y DDev. Para una más facil instalacion de los sitios es recomendable una terminal linux, si estas trabajando con Windows utiliza en WSL2 de Windows para tener tu maquina Windows en tu computadora.*

#### Configuracion WSL2 en Windows
https://learn.microsoft.com/es-es/windows/wsl/install

#### Instalacion de Docker usando los repositorios de APT.
https://docs.docker.com/engine/install/ubuntu/

#### Instalacion de DDev.
 https://ddev.readthedocs.io/en/stable/users/install/ddev-installation/#ddev-installation-linux

## Instalación del sitio Drupal 10.
*Al llegar a este punto ya debes contar con **WSL2**, **Docker** y **DDev**
Descarga el repositorio e ingresa al folder **icbfportal**, Sigue los siguientes pasos para instalar el sitio:*
1. Ingresa al folder **icbfportal**, este folder contiene los archivos principales del sitio Drupal 10 del portal web. `cd icbfportal`
2. Ejecuta el comando `ddev start` desde la terminal.
3. Descarga el backup de la base de datos y colocala en la carpeta **icbfportal**
4. Ejecuta la instalación de las dependencias de Composer. `ddev composer install`
5. Importa la base de datos a tu sitio `ddev import-db --file=./{nombredelbackup}`

Despues de ejecutar **ddev start**, aparecerá un mensaje con las url del sitio web creado.

------------


Para iniciar los sitios cuando sea necesario debes ingresar al folder del sitio y ejecutar `ddev start`.


## Migracion del sitio desde cero.

1. Descargar el Repositorio desde GIT.
    git clone
2. Ejecutar La instalacion de composer
    composer install && composer update
3. Importar la base de datos (DB)
    ddev import-db --file=./backup.sql.gz
4. Ejecutar script de instalacion de modulos contrib
    bash scripts/script-install-contribs.sh
5. Borrar caché de Drupal.
    ddev drush cr
6. Ejecutar la migracion de componentes.
    ddev drush --yes migrate:upgrade --legacy-db-key='migrate' --legacy-root='http://icbf.ddev.site' --configure-only
7. Eliminar configuracion actual de migraciones personalizadas.
    ddev drush config-delete migrate_plus.migration.upgrade_d7_autologout_settings && ddev drush config-delete migrate_plus.migration.upgrade_d7_comment_type && ddev drush config-delete migrate_plus.migration.upgrade_d7_field && ddev drush config-delete migrate_plus.migration.upgrade_d7_field_instance && ddev drush config-delete migrate_plus.migration.upgrade_d7_field_instance_widget_settings && ddev drush config-delete migrate_plus.migration.upgrade_d7_taxonomy_vocabulary && ddev drush config-delete migrate_plus.migration.upgrade_d7_view_modes && ddev drush config-delete migrate_plus.migration.upgrade_fontawesome_settings && ddev drush config-delete migrate_plus.migration.upgrade_taxonomy_manager_settings
8. Instalar el modulo de personalizacion de Migración
    ddev drush en -y icbf_migrations
9. Borrar cache de Drupal.
    ddev drush cr
10. Ejecutar script de migracion de configuración:
    bash scripts/script-migration-config.sh import
11. Ejecutar script de migracion de contenidos:
    bash scripts/script-migration-config.sh import
12. eliminar configuracion del Theme Bootstrap traido en la importacion.
    ddev drush config-delete bootstrap.settings
13. Instalar el theme contribuido Bootstrap
    ddev drush theme-install bootstrap
