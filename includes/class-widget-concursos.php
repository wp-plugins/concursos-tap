<?php

if(!class_exists('Epic_ConcursosTAP_Widget')){
    class Epic_ConcursosTAP_Widget extends WP_Widget {

        /**
         * Constructor.
         *
         * @return Epic_Concursos_Widget
         */
        public function __construct() {
            $plugin = Concursos_TAP::get_instance();
            $this->plugin_slug = $plugin->get_plugin_slug();

            parent::__construct( 'widget_epic_concursos', __( 'Concursos', $this->plugin_slug ), array(
                    'classname'   => 'widget_epic_concursos',
                    'description' => __( 'Use este widget para mostrar los concursos publicados.', $this->plugin_slug ),
                ) );
        }

        /**
         * Deals with the settings when they are saved by the admin. Here is
         * where any validation should be dealt with.
         *
         * @param array  An array of new settings as submitted by the admin
         * @param array  An array of the previous settings
         * @return array The validated and (if necessary) amended settings
         **/
        public function update( $new_instance, $old_instance )
        {
            $instance = $old_instance;
            $instance['title'] = isset($new_instance['title']) ? $new_instance['title'] : $old_instance['title'];
            $instance['width'] = isset($new_instance['width']) ? $new_instance['width'] : $old_instance['width'];
            $instance['height'] = isset($new_instance['height']) ? $new_instance['height'] : $old_instance['height'];
            return $instance;
        }

        /**
         * Displays the form for this widget on the Widgets page of the WP Admin area.
         *
         * @param array  An array of the current settings for this widget
         * @return void
         **/
        public function form( $instance )
        {
            $title = isset($instance['title']) ? $instance['title'] : __('Concursos',$this->plugin_slug);
            $width = isset($instance['width']) ? $instance['width'] : 258;
            $height = isset($instance['height']) ? $instance['height'] : 430;
            ?>
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Titulo de la columna', $this->plugin_slug ); ?>:</label>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $title;?>"/>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Ancho de las imagenes', $this->plugin_slug ); ?>:</label>
                <input type="text" id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" value="<?php echo $width;?>"/>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e( 'Alto de las imagenes', $this->plugin_slug ); ?>:</label>
                <input type="text" id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" value="<?php echo $height;?>"/>
            </p>
<?php   }

        /**
         * Front-end display of widget.
         *
         * @see WP_Widget::widget()
         *
         * @param array $args     Widget arguments.
         * @param array $instance Saved values from database.
         */
        public function widget( $args, $instance ) {
            $concursos_tap_concursos = get_option('concursos_tap_concursos');
            $destination = wp_upload_dir();
            $width = $instance['width'];
            $height = $instance['height'];

            if(array_key_exists('before_widget', $args)) echo $args['before_widget']; ?>
            <h2><?php echo $instance['title']; ?></h2><?php
            if(!empty($concursos_tap_concursos)): ?>
            <div id="concursoCarousel" class="carousel slide" data-interval="5000">
                <!-- Carousel items -->
                <div class="carousel-inner"><?php
                    $i = 0;
                    foreach($concursos_tap_concursos as $concurso):
                        $active = $i > 0 ? "" : "active";
                        $image = $this->image_resize($concurso['path'], $width, $height, false, null, $destination['path']);
                        if($image instanceof WP_Error){
                            $image = $concurso['path'];
                        }else{
                            $image = $destination['url'].'/'.wp_basename($image);
                        }?>
                    <div class="<?php echo $active ?> item">
                        <a href="<?php echo esc_url($concurso['url']) ?>" title="<?php echo $concurso['concursos'];  ?>" target="_blank" rel="bookmark">
                            <img src="<?php echo $image;?>" alt="<?php echo $concurso['concursos']; ?>" title="<?php echo $concurso['concursos']; ?>" class="img-responsive center-block" style="width: <?php echo $width?>; height: <?php echo $height?>;">
                        </a>
                        </div><?php
                        $i++;
                    endforeach; ?>
                </div>
                <!-- Carousel nav -->
                <a class="carousel-control left" href="#concursoCarousel" data-slide="prev">&lsaquo;</a>
                <a class="carousel-control right" href="#concursoCarousel" data-slide="next">&rsaquo;</a>
            </div><?php
            else: ?>
            <p><?php _e('No hay concursos publicados', $this->plugin_slug)?></p><?php
            endif;
            if(array_key_exists('after_widget', $args)) echo $args['after_widget'];

            wp_reset_postdata();
        }

        /**
         * Scale down an image to fit a particular size and save a new copy of the image.
         *
         * The PNG transparency will be preserved using the function, as well as the
         * image type. If the file going in is PNG, then the resized image is going to
         * be PNG. The only supported image types are PNG, GIF, and JPEG.
         *
         * Some functionality requires API to exist, so some PHP version may lose out
         * support. This is not the fault of WordPress (where functionality is
         * downgraded, not actual defects), but of your PHP version.
         *
         * @param string $file Image file path.
         * @param int $max_w Maximum width to resize to.
         * @param int $max_h Maximum height to resize to.
         * @param bool $crop Optional. Whether to crop image or resize.
         * @param string $suffix Optional. File suffix.
         * @param string $dest_path Optional. New image file path.
         * @param int $jpeg_quality Optional, default is 90. Image quality percentage.
         * @return mixed WP_Error on failure. String with new destination path.
         */
        public function image_resize( $file, $max_w, $max_h, $crop = false, $suffix = null, $dest_path = null, $jpeg_quality = 90 ) {

            $editor = wp_get_image_editor( $file );
            if ( is_wp_error( $editor ) )
                return $editor;
            $editor->set_quality( $jpeg_quality );

            $resized = $editor->resize( $max_w, $max_h, $crop );
            if ( is_wp_error( $resized ) )
                return $resized;

            $dest_file = $editor->generate_filename( $suffix, $dest_path );
            $saved = $editor->save( $dest_file );

            if ( is_wp_error( $saved ) )
                return $saved;

            return $dest_file;
        }
    }
}
