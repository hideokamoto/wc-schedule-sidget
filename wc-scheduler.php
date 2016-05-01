<?php
/**
 * Plugin Name: WC Schedule Widget
 * Version: 0.1.0
 * Description: Show WordCamp Event List Widget
 * Author: hideokamoto
 * Author URI: https://profiles.wordpress.org/hideokamoto
 * Plugin URI: https://github.com/hideokamoto/wc-schedule-sidget
 * Text Domain: wc-schedule-widget
 * Domain Path: /languages
 * @package Wc-scheduler
 */
 require_once( dirname( __FILE__ ).'/includes/class/widget.php' );

function register_wc_schedule_widget() {
  register_widget( 'WordCamp_Scheduler_Widget' );
}
add_action( 'widgets_init', 'register_wc_schedule_widget' );
