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
<?php
if(isset($_POST['update'])){

    do_action('sync_weekly_event');

    add_settings_error('update-informacion-concursos', 'form-update-informacion-concursos', __('Actualizacion realizada satisfactoriamente', Tipster_TAP::get_instance()->get_plugin_slug()), 'updated');
}
?>
<div class="wrap">

    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
    <?php settings_errors('update-informacion-concursos', false, true); ?>
    <p><?php printf(__('Hacer clic en el boton para actualizar la informacion de los concursos desde <a href="%s">todoapuestas.org</a>', Concursos_TAP::get_instance()->get_plugin_slug()),'http://www.todoapuestas.org'); ?></p>
    <p><?php _e('<strong>NOTA</strong>: Use esta funcionalidad solo para actualizar la informacion de los concursos almacenada en base de datos en caso de ser necesario. De manera automatica la informacion de los concursos se actualiza semanalmente.', Concursos_TAP::get_instance()->get_plugin_slug()) ?></p>
    <form id="form-update-informacion-concursos" method="post" action="<?php admin_url( 'admin.php?page='.Concursos_TAP::get_instance()->get_plugin_slug()."/update-information?settings-updated=1" ) ?>">
        <input type="hidden" name="update" value="1">
        <p class="submit">
            <input type="submit" id="upgrade" value="<?php _e('Actualizar informacion',  Concursos_TAP::get_instance()->get_plugin_slug())?> &raquo;"/>
        </p>
    </form>

</div>