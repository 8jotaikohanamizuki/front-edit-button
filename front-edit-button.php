<?php
/**
 * Plugin Name: フロント編集ボタン
 * Plugin URI: https://github.com/8jotaikohanamizuki/front-edit-button
 * Description: ウィジェットエリアにログイン・編集ボタンを配置
 * Version: 1.0.0
 * Author: 奏子
 * Author URI: https://hachijotaiko.tokyo
 * License: GPL v2 or later
 * Text Domain: front-edit-button
 * Domain Path: /languages
 * Update URI: false
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Front_Edit_Button_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'front_edit_button_widget',
            'フロント編集ボタン',
            array( 'description' => 'ログイン・編集ボタンを表示するウィジェット' )
        );
    }
    
    // ウィジェット出力
    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        
        $title = apply_filters( 'widget_title', $instance['title'] ?? '編集' );
        if ( ! empty( $title ) ) {
            echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
        }
        
        $this->render_button();
        
        echo $args['after_widget'];
    }
    
    // 管理画面フォーム
    public function form( $instance ) {
        $title = $instance['title'] ?? '編集';
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                ウィジェットタイトル:
            </label>
            <input
                class="widefat"
                id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                type="text"
                value="<?php echo esc_attr( $title ); ?>"
            />
        </p>
        <?php
    }
    
    // ウィジェット設定保存
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ! empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
        return $instance;
    }
    
    // ボタン描画ロジック
    private function render_button() {
        $output = '<div id="quick-edit-widget">';
        
        if ( current_user_can( 'edit_posts' ) ) {
            // ログイン中 + 編集権限あり → 編集ボタン
            $edit_url = get_edit_post_link();
            $output .= sprintf(
                '<a href="%s" class="button" style="display:inline-block; background:#C1432B; color:#fff; padding:0.4rem 0.8rem; border-radius:4px; text-decoration:none; font-size:0.85rem; font-weight:bold; margin:5px 0;">この記事を編集する</a>',
                esc_url( $edit_url )
            );
        } elseif ( ! is_user_logged_in() ) {
            // 未ログイン → ログインボタン（Google ソーシャルログインへ）
            $current_url = $this->get_current_url();
            $login_url = wp_login_url( $current_url );
            
            $output .= sprintf(
                '<a href="%s" class="button" style="display:inline-block; background:#4A5568; color:#fff; padding:0.4rem 0.8rem; border-radius:4px; text-decoration:none; font-size:0.85rem; font-weight:bold; margin:5px 0;">ログイン</a>',
                esc_url( $login_url )
            );
        }
        // ログイン済み + 権限なし → 何も表示しない
        
        $output .= '</div>';
        
        echo wp_kses_post( $output );
    }
    
    // 現在のURL取得（セキュアに）
    private function get_current_url() {
        $protocol = is_ssl() ? 'https://' : 'http://';
        $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return esc_url_raw( $url );
    }
}

// ウィジェット登録
function register_front_edit_button_widget() {
    register_widget( 'Front_Edit_Button_Widget' );
}
add_action( 'widgets_init', 'register_front_edit_button_widget' );
