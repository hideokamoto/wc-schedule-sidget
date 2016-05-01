<?php
class WordCamp_Scheduler_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'WordCamp_Scheduler_Widget',
			__( 'WordCamp Schedule List Widget', 'wc-schedule-widget' ),
			array( 'description' => __( 'WordCamp List', 'wc-schedule-widget' ), )
		);
	}

	public function widget( $args, $instance ) {
		$html = $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			$html .= $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		if ( empty( $instance['count'] ) ) {
			$instance['count'] = '-1';
		}
		$camp_list = $this->_get_wordcamp_list();

		$html .= "<ul id='wc-schedule-widget-postlist' >";
		$i = 1;
		foreach ( $camp_list as $camp ) {
			$link = $camp['link'];
			$html .= '<li><dl>';
			$html .= '<dt>Title</dt>';
			$html .= "<dd>{$camp['title']}</dd>";
			$html .= $this->_get_post_meta_html( $camp['post_meta']);
			$html .= '<dt>Site URL</dt>';
			$html .= "<dd><a href='{$link}' target='_blank'>{$link}</a></dd>";
			$html .= '</dl></li>';
			if ( $instance['count'] !== '-1' && (int) $instance['count'] == $i ) {
				break;
			}
			$i++;
		}
		$html .= '</ul>';
		echo $html. $args['after_widget'];
	}

	private function _get_post_meta_html( $meta_list ) {
		$html = '';
		foreach ( $meta_list as $post_meta ) {
			switch ( $post_meta['key'] ) {
				case 'Start Date (YYYY-mm-dd)':
				case 'End Date (YYYY-mm-dd)':
					$key = explode( '(YYYY-mm-dd)', $post_meta['key'] )[0];
					$value = date('Y-m-d', (int) $post_meta['value'] );
					break;

				case 'URL':
					$key = 'WordCamp URL';
					$url = esc_url( $post_meta['value'] );
					$value = "<a href={$url} target='_blank'>{$url}</a>";
					break;

				case 'Website URL':
					$key = 'Venue Website URL';
					$url = esc_url( $post_meta['value'] );
					$value = "<a href={$url} target='_blank'>{$url}</a>";
					break;

				default:
					$key = $post_meta['key'];
					$value = $post_meta['value'];
					break;
			}
			$html .= "<dt>{$key}</dt>";
			$html .= "<dd>{$value}</dd>";
		}
		return $html;
}

	private function _get_wordcamp_list() {
		$url = 'https://central.wordcamp.org/wp-json/posts?type=wordcamp';
		$url = $url. '&filter[posts_per_page]=100';
		$result = wp_remote_get( $url );
		if ( is_wp_error( $result ) ) {
			return 'Fail to Load List.';
		}
		$camp_list = $this->_parse_wc_list( $result['body'] );
		return $camp_list;
	}

	private function _parse_wc_list( $body ) {
		$camp_list = json_decode( $body , true );
		foreach ( $camp_list as $key => $camp ) {
			foreach ( $camp['post_meta'] as $meta ) {
				if ( 'Start Date (YYYY-mm-dd)' === $meta['key'] ) {
					if ( time() <=  $meta['value'] ) {
						$camp_list[ $key ]['start_datetime'] = $meta['value'];
					} else {
						unset( $camp_list[ $key ] );
					}
					break;
				}
			}
		}
		$date_list = array_column($camp_list,'start_datetime');
		array_multisort( $date_list, SORT_ASC, $camp_list );
		return $camp_list;
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'WordCamp Schedule List Widget', 'wc-schedule-widget' );
		$count = ! empty( $instance['count'] ) ? $instance['count'] : -1;
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Show Count', 'wc-schedule-widget' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="text" value="<?php echo esc_attr( $count ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['count'] = ( ! empty( $new_instance['count'] ) ) ? strip_tags( $new_instance['count'] ) : -1;

		return $instance;
	}

}
