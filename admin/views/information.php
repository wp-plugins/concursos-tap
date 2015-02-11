<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Concursos_TAP
 * @author    Alain Sanchez <asanchezg@inetzwerk.com>
 * @license   GPL-2.0+
 * @link      http://www.inetzwerk.com
 * @copyright 2014 Alain Sanchez
 */
?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

    <p><?php printf(__('Este plugin permite visualizar los concursos publicados en <a href="%s">todoapuestas.org</a>', Concursos_TAP::get_instance()->get_plugin_slug()),'http://www.todoapuestas.org')?></p>
    <p><?php printf(__('Al realizar la instalacion y especificamente la activacion de este plugin se ha guardado en base de datos la informacion de los concursos publicados en <a href="%s">todoapuestas.org</a>.', Concursos_TAP::get_instance()->get_plugin_slug()),'http://www.todoapuestas.org')?></p>
    <h3><?php _e('Visualizar concursos', Concursos_TAP::get_instance()->get_plugin_slug()) ?></h3>
    <p><?php printf(__('Para visualizar los concursos publicados en <a href="%s">todoapuestas.org</a>, debe:', Concursos_TAP::get_instance()->get_plugin_slug()), 'http://www.todoapuestas.org') ?></p>
    <ol style="list-style: decimal;">
        <li><?php _e('Ir a Apariencia &raquo; Widgets', Concursos_TAP::get_instance()->get_plugin_slug()) ?></li>
        <li><?php _e('Localizar el widget <strong>Concursos</strong>', Concursos_TAP::get_instance()->get_plugin_slug()) ?></li>
        <li><?php _e('Asignar el widget preferiblemente a la <strong>Zona Columna Izquierda</strong> o <strong>Zona Columna Derecha</strong> o ambas, segun lo necesite.', Concursos_TAP::get_instance()->get_plugin_slug()) ?></li>
    </ol>
    <p><?php _e('<strong>NOTA</strong>: El widget <strong>Concursos</strong> incluye opciones de configuracion que le permitiran personalizar el titulo del bloque a visualizar asi como el ancho y alto por defecto a utilizar para la imagen a visualizar asociada a cada concurso.', Concursos_TAP::get_instance()->get_plugin_slug()) ?></p>
    <h3><?php _e('Actualizacion de los concursos', Concursos_TAP::get_instance()->get_plugin_slug()) ?></h3>
    <ul style="list-style: disc; margin-left: 2em;">
        <li>
            <h4><?php _e('Automatico') ?></h4>
            <p><?php _e('De manera automatica la informacion de los concursos se actualiza semanalmente.', Concursos_TAP::get_instance()->get_plugin_slug()) ?></p>
        </li>
        <li>
            <h4><?php _e('Manual') ?></h4>
            <?php
            $url_option_update_page = admin_url("admin.php?page=".Concursos_TAP::get_instance()->get_plugin_slug()."/update-information");
            $url_plugin_page = admin_url("admin.php?page=".Concursos_TAP::get_instance()->get_plugin_slug());
            ?>
            <p><?php printf(__('Cuando necesite realizar una actualizacion de la informacion de los concursos almacenada en base de datos, debe hacer clic en la opcion de menu <a href="%s">Actualizar</a> de la seccion <a href="%s">Concursos TAP</a> que aparece en el menu de la izquierda. Cuando le aparezca la pagina correspondiente debe realizar las indicaciones que se muestran.', Concursos_TAP::get_instance()->get_plugin_slug()), $url_option_update_page, $url_plugin_page ) ?></p>
        </li>
    </ul>
</div>
