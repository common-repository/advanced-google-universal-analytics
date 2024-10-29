<?php
/*
  Plugin Name: Advanced Google Universal Analytics
  Description: Enter the tracking code for Google Universal Analytics OR Global site tag (gtag.js) - Google Analytics, in your wordpress site by simply putting your ID in the settings. You can also choose which role or user not will be tracked. Choose if you want track admin panel (/wp-admin/) or  not, and can put 2nd tracking number (useful in some cases, example if you put the 2nd track in all site in one server, can track the total user in real-time)
  Author: StefanoAI
  Author URI: https://www.stefanoai.com
  Text Domain: advanced-google-universal-analytics
  Domain Path: /lang
  Version: 1.0.3
  Author URI: https://www.stefanoai.com
 */

class AdvancedGoogleUniversalAnalytics {

    function __construct() {
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('wp_ajax_find_user', array($this, 'wp_ajax_find_user'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        $header_footer = get_option('AI_GoogleUniversalAnalytics_headerfooter');
        $wp_panel = get_option('AI_GoogleUniversalAnalytics_wp-panel');
        if ($header_footer == "header") {
            add_action('wp_head', array($this, 'track_code'), 999);
        } else {
            add_action('wp_footer', array($this, 'track_code'), 999);
        }
        if ($wp_panel) {
            add_action('admin_head', array($this, 'track_code'));
            add_action('login_head', array($this, 'track_code'));
        }
    }

    function load_textdomain() {
        load_plugin_textdomain('advanced-google-universal-analytics', FALSE, plugin_dir_path('/advanced-google-universal-analytics/AdvancedGoogleUniversalAnalytics.php') . 'lang/');
    }

    function admin_init() {
        register_setting("AI-GoogleUniversalAnalytics", "AI_GoogleUniversalAnalytics_ID");
        register_setting("AI-GoogleUniversalAnalytics", "AI_GoogleUniversalAnalytics_anonymizeUA");
        register_setting("AI-GoogleUniversalAnalytics", "AI_GoogleUniversalAnalytics_ID2");
        register_setting("AI-GoogleUniversalAnalytics", "AI_GoogleUniversalAnalytics_anonymizeUA2");
        register_setting("AI-GoogleUniversalAnalytics", "AI_GoogleUniversalAnalytics_type");
        register_setting("AI-GoogleUniversalAnalytics", "AI_GoogleUniversalAnalytics_domain");
        register_setting("AI-GoogleUniversalAnalytics", "AI_GoogleUniversalAnalytics_headerfooter");
        register_setting("AI-GoogleUniversalAnalytics", "AI_GoogleUniversalAnalytics_wp-panel");
        register_setting("AI-GoogleUniversalAnalytics", "AI_GoogleUniversalAnalytics_track");
    }

    function admin_menu() {
        add_submenu_page('options-general.php', 'Google Universal Analytics', "Advanced Google Universal Analytics", 'manage_options', 'AI-AdvancedGoogleUniversalAnalytics', array($this, 'page'));
    }

    function page() {
        wp_enqueue_style('jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery-ui-autocomplete');
        wp_enqueue_script('jquery-ui-button');
        ?><style>
            #StefanoAI-GUA div.line{
                display: block;
                padding: 2px;
            }
            #StefanoAI-GUA div.line label{
                display: inline-block;
                width: 300px;
            }
            #StefanoAI-GUA div.line tiny{
                font-size: 10px;
            }
            #StefanoAI-GUA div.line input,#StefanoAI-GUA div.line select{
                width: 150px;
            }
            div.user{
                position: relative;
                border: 1px solid #ddd;
                display: inline-block;
                -webkit-border-radius: 3px 3px 3px 3px;
                border-radius: 3px 3px 3px 3px;
                vertical-align: middle;
                line-height: 30px;
                overflow: hidden;
                min-width: 250px;
                margin: 5px;
            }
            div.user h3{
                margin: 0px;
                padding: 0px;
                display: block;
                text-align: center;
                background-color: #444;
                color: #fff;
                cursor: default;
            }
            div.user .left span.label,div.user .right span.label{
                font-size: 10px;
                min-width: 60px;
                display: inline-block;
            }
            div.user .left span.value,div.user .right span.value{
                font-weight: bold;
                display: inline-block;
            }
            div.user .left,div.user .right{
                display: inline-block;
                vertical-align: top;
                padding: 4px;
            }
            div.user .left>div,div.user .right>div{
                display: block;
                line-height: 20px;
            }
            div.user input.delete{
                display: block;
                width: 100%;
            }
            div#roles div.role{
                padding: 5px;
                margin-right: 10px;
                margin-left: 10px;
                display: inline-block;
            }
            #StefanoAI-GUA .users_ div.line label{
                width: auto;
            }
            #StefanoAI-GUA .users_ div.line input{
                width: 250px;
            }
            #StefanoAI-GUA div.line input[type=checkbox],
            #StefanoAI-GUA div.line input[type=radio]{
                width: auto;
            }
            #StefanoAI-GUA .users_ div.line{
                margin-bottom: 20px;
            }
        </style>
        <div class="wrap">
            <h2>Google Universal Analytics</h2>
            <div id="StefanoAI-GUA">
                <form method="post" action="options.php">
                    <div id="tabs">
                        <ul>
                            <li><a href="#tabs-1"><?php esc_html_e("AIGUA Settings", 'advanced-google-universal-analytics'); ?></a></li>
                            <li><a href="#tabs-2"><?php esc_html_e("AIGUA No tracking Roles", 'advanced-google-universal-analytics'); ?></a></li>
                            <li><a href="#tabs-3"><?php esc_html_e("AIGUA No tracking users", 'advanced-google-universal-analytics'); ?></a></li>
                        </ul>
                        <?php
                        settings_fields('AI-GoogleUniversalAnalytics');
                        do_settings_sections('AI-GoogleUniversalAnalytics');
                        $trackid = get_option('AI_GoogleUniversalAnalytics_ID');
                        $anonymizeUA = get_option('AI_GoogleUniversalAnalytics_anonymizeUA');
                        $trackid2 = get_option('AI_GoogleUniversalAnalytics_ID2');
                        $anonymizeUA2 = get_option('AI_GoogleUniversalAnalytics_anonymizeUA2');
                        $type = get_option('AI_GoogleUniversalAnalytics_type');
                        $domain = get_option('AI_GoogleUniversalAnalytics_domain');
                        $headerfooter = get_option('AI_GoogleUniversalAnalytics_headerfooter');
                        $wp_panel = get_option('AI_GoogleUniversalAnalytics_wp-panel');
                        $tmpusers = get_option('AI_GoogleUniversalAnalytics_track');
                        ?>
                        <div id="tabs-1">
                            <div class="line">
                                <label for="trackid"><?php esc_html_e('AIGUA TRACKING ID', 'advanced-google-universal-analytics'); ?></label>
                                <input id="trackid" type="text" name="AI_GoogleUniversalAnalytics_ID" value="<?php echo esc_attr($trackid) ?>" />
                            </div>
                            <div class="line">
                                <label for="anonymousetrackid"><?php esc_html_e('Anonymize IP', 'advanced-google-universal-analytics'); ?></label>
                                <input id="trackid" type="checkbox" name="AI_GoogleUniversalAnalytics_anonymizeUA" value="1" <?php echo $anonymizeUA == "1" ? 'checked' : ''; ?> />
                            </div>
                            <div class="line">
                                <label for="type"><?php esc_html_e('AIGUA Type', 'advanced-google-universal-analytics'); ?></label>
                                <input id="type_gua" class="AIGUA_type" type="radio" name="AI_GoogleUniversalAnalytics_type" value="gua" <?php echo empty($type) || $type == 'gua' ? 'checked' : '' ?> />
                                <label for="type_gua">Google Analytics</label><br/>
                            </div>
                            <div class="line">
                                <label for="type"></label>
                                <input id="type_gtag" class="AIGUA_type" type="radio" name="AI_GoogleUniversalAnalytics_type" value="gtag" <?php echo $type == 'gtag' ? 'checked' : '' ?> />
                                <label for="type_gtag">Global site tag (gtag.js) - Google Analytics</label>
                            </div>
                            <div class="line">
                                <label for="trackid_2"><?php esc_html_e("AIGUA Additional Tracking ID", 'advanced-google-universal-analytics'); ?></label>
                                <input id="trackid_2" type="text" name="AI_GoogleUniversalAnalytics_ID2" value="<?php echo esc_attr($trackid2) ?>" />
                            </div>
                            <div class="line">
                                <label for="anonymousetrackid"><?php esc_html_e('Anonymize IP for additional tracking', 'advanced-google-universal-analytics'); ?></label>
                                <input id="trackid" type="checkbox" name="AI_GoogleUniversalAnalytics_anonymizeUA2" value="1" <?php echo $anonymizeUA2 == "1" ? 'checked' : ''; ?> />
                            </div>
                            <div class="line domain">
                                <label for="domain"><?php esc_html_e('AIGUA Domain', 'advanced-google-universal-analytics'); ?></label>
                                <input id="domain" type="text" name="AI_GoogleUniversalAnalytics_domain" value="<?php echo esc_attr($domain) ?>" />
                            </div>
                            <div class="line">
                                <label for="loading"><?php esc_html_e('AIGUA Loading on', 'advanced-google-universal-analytics'); ?></label>
                                <select id="loading" name="AI_GoogleUniversalAnalytics_headerfooter">
                                    <option value="header" <?php echo $headerfooter == 'header' ? 'selected' : '' ?>>Header</option>
                                    <option value="footer" <?php echo $headerfooter == 'footer' ? 'selected' : '' ?>>Footer</option>
                                </select>
                            </div>
                            <div class="line">
                                <label for="wp-panel"><?php esc_html_e('AIGUA Loading on WP panel', 'advanced-google-universal-analytics'); ?></label>
                                <input id="wp-panel" type="checkbox" name="AI_GoogleUniversalAnalytics_wp-panel" value="1" <?php echo!empty($wp_panel) ? 'checked' : ''; ?> />
                            </div>
                        </div>
                        <div id="tabs-2">
                            <div id="roles">
                                <?php
                                global $wp_roles;
                                $all_roles = $wp_roles->roles;
                                if (!empty($all_roles)) {
                                    foreach ($all_roles as $role => $v) {
                                        ?>
                                        <div class="role">
                                            <input id="AI_GoogleUniversalAnalytics_track_<?php echo esc_attr($role) ?>" type="checkbox" value="1" name="AI_GoogleUniversalAnalytics_track[notrack_roles][<?php echo esc_attr($role) ?>]" <?php echo is_array($tmpusers) && !empty($tmpusers['notrack_roles'][$role]) ? 'checked' : ''; ?>>
                                            <label for="AI_GoogleUniversalAnalytics_track_<?php echo esc_attr($role) ?>"><?php echo esc_attr($role) ?></label>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div id="tabs-3">
                            <div class="users_">
                                <div class="line">
                                    <label for="find_user"><?php esc_html_e('AIGUA Find user', 'advanced-google-universal-analytics'); ?></label>
                                    <input type="text" name="find_user" id="find_user" />
                                </div>
                                <div id="users"><?php
                                    if (!empty($tmpusers['notrack_users'])) {
                                        $users = array();
                                        foreach ($tmpusers['notrack_users'] as $t => $v) {
                                            $u = get_user_by('id', $t);
                                            $users[$u->user_login] = $u;
                                        }
                                        if (!empty($users)) {
                                            ksort($users);
                                            foreach ($users as $user) {
                                                ?>
                                                <div class='user'>
                                                    <h3><?php echo $user->user_login ?></h3>
                                                    <input type='hidden' name='AI_GoogleUniversalAnalytics_track[notrack_users][<?php echo $user->ID ?>]' value='1' />
                                                    <div class="left">
                                                        <?php echo get_avatar($user->ID); ?>
                                                    </div>
                                                    <div class="right">
                                                        <div>
                                                            <span class="label">Firstname</span>
                                                            <span class="value"><?php echo get_user_meta($user->ID, 'first_name', true); ?></span>
                                                        </div>
                                                        <div>
                                                            <span class="label">Lastname</span>
                                                            <span class="value"><?php echo get_user_meta($user->ID, 'last_name', true); ?></span>
                                                        </div>
                                                    </div>
                                                    <input type="button" class='delete button-secondary' value="delete" />
                                                </div>
                                                <?php
                                            }
                                        }
                                    }
                                    ?></div>
                            </div>
                        </div>
                    </div>
                    <?php submit_button(); ?>
                </form>
            </div>
        </div>
        <script type="text/javascript">
            function AITGA() {
                var selected = jQuery("input.AIGUA_type:checked").val();
                if (selected == 'gtag') {
                    jQuery("div.line.domain").css('display', 'none');
                } else {
                    jQuery("div.line.domain").css('display', 'block');
                }
            }
            function AIUG_prepare_delete() {
                jQuery("#users .delete").each(function () {
                    if (jQuery(this).attr('hasJS') !== '1') {
                        jQuery(this).click(function () {
                            jQuery(this).closest("div.user").remove();
                        });
                    }
                });
            }
            jQuery(document).ready(function () {
                jQuery("#tabs").tabs();
                jQuery("#find_user").autocomplete({
                    source: function (request, response) {
                        jQuery.ajax({
                            url: ajaxurl,
                            dataType: 'json',
                            type: 'POST',
                            data: {
                                action: "find_user",
                                style: "full",
                                maxRows: 10,
                                request: request.term
                            },
                            success: function (data) {
                                response(jQuery.map(data.users, function (item) {
                                    return {
                                        label: item.firstname + " " + item.lastname + " " + item.email,
                                        name: item.firstname + " " + item.lastname + " " + item.nickname,
                                        email: item.email,
                                        id: item.id,
                                        value: ''
                                    };
                                }));
                            }
                        });
                    },
                    minLength: 1,
                    select: function (event, user) {
                        jQuery("#users").append("<div class='user'><input type='hidden' name='AI_GoogleUniversalAnalytics_track[notrack_users][" + user.item.id + "]' value='1' />" + user.item.name + "<div class='delete button-secondary'>delete</div></div>");
                        AIUG_prepare_delete();
                    },
                    open: function () {
                        jQuery(this).removeClass("ui-corner-all").addClass("ui-corner-top");
                    },
                    close: function () {
                        jQuery(this).removeClass("ui-corner-top").addClass("ui-corner-all");
                    }
                });

                jQuery("#type_gtag,#type_gua").change(function () {
                    AITGA();
                });
                AITGA();
                AIUG_prepare_delete();
            });

        </script>
        </div>
        <?php
    }

    function wp_ajax_find_user() {
        $return = array('find' => $_POST['request'], 'users' => array());

        $users = new \WP_User_Query(array(
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'first_name',
                    'value' => $_POST['request'],
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'last_name',
                    'value' => $_POST['request'],
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'nickname',
                    'value' => $_POST['request'],
                    'compare' => 'LIKE'
                )
            )
        ));
        $user = get_user_by('id', $_POST['request']);
        if (!empty($user)) {
            $users->results[] = $user;
        }
        $user = get_user_by('email', $_POST['request']);
        if (!empty($user)) {
            $users->results[] = $user;
        }
        if (!empty($users->results)) {
            foreach ($users->results as $user) {
                $return['users'][] = array(
                    'nickname' => $user->data->user_login,
                    'firstname' => get_user_meta($user->data->ID, 'first_name', true),
                    'lastname' => get_user_meta($user->data->ID, 'last_name', true),
                    'email' => $user->data->user_email,
                    'id' => $user->data->ID,
                    'user' => $user
                );
            }
        }
        echo json_encode($return);
        exit;
    }

    function track_code() {
        $users = get_option('AI_GoogleUniversalAnalytics_track');
        $current_user = wp_get_current_user();
        $roles = array();
        foreach ($current_user->roles as $role) {
            $roles[$role] = 1;
        }
        foreach ($roles as $k => $v) {
            if (!empty($users['notrack_roles'][$k]) && $users['notrack_roles'][$k] == "1") {
                return;
            }
        }
        if (!empty($users['notrack_users'][$current_user->ID]) && $users['notrack_users'][$current_user->ID] == "1") {
            return;
        }
        $type = get_option('AI_GoogleUniversalAnalytics_type');
        $UA = get_option('AI_GoogleUniversalAnalytics_ID');
        $anonymizeUA = get_option('AI_GoogleUniversalAnalytics_anonymizeUA') == "1" ? true : false;
        $UA2 = get_option('AI_GoogleUniversalAnalytics_ID2');
        $anonymizeUA2 = get_option('AI_GoogleUniversalAnalytics_anonymizeUA2') == "1" ? true : false;
        $domain = get_option('AI_GoogleUniversalAnalytics_domain', 'auto');
        if (empty($domain)) {
            $domain = 'auto';
        }
        if (!empty($UA2) && preg_match('/^(UA|G)\-[0-9A-Za-z]+[-]*[0-9A-Za-z]*$/', $UA2)) {
            if (empty($type) || $type == 'gua') {
                $anonymizeUA2 = $anonymizeUA2 ? ",{'anonymizeIp': true}" : '';
                $UA2 = "
    ga('create', '$UA2', 'auto', {'name':'track2'});
    ga('track2.send','pageview' $anonymizeUA2);
";
            } else if ($type == 'gtag') {
                $anonymizeUA2 = $anonymizeUA2 ? ",{'anonymize_ip': true}" : '';
                $UA2 = "
    gtag('config', '$UA2' $anonymizeUA2);
";
            }
        } else {
            $UA2 = "";
        }

        if (empty($type) || $type == 'gua') {
            $anonymizeUA = $anonymizeUA ? ",{'anonymizeIp': true}" : '';
            if (preg_match('/^(UA|G)\-[0-9A-Za-z]+[-]*[0-9A-Za-z]*$/', $UA) && preg_match('/^[^\']+$/', $domain)) {
                echo <<<JS
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '$UA', '$domain');
  ga('send', 'pageview' $anonymizeUA);
  $UA2
</script>
JS;
            }
        } else if ($type == 'gtag') {
            $anonymizeUA = $anonymizeUA ? ",{'anonymize_ip': true}" : '';
            if (preg_match('/^(UA|G)\-[0-9A-Za-z]+[-]*[0-9A-Za-z]*$/', $UA) && preg_match('/^[^\']+$/', $domain)) {
                echo <<<JS
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=$UA"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '$UA' $anonymizeUA);
  $UA2
</script>

JS;
            }
        }
    }

}

new AdvancedGoogleUniversalAnalytics();
