<?php
if ( ! defined('ABSPATH')) {
	exit;
}

$wpUser = wp_get_current_user();
$userSettings = get_user_meta($wpUser->ID, 'convo_settings', true);
$amazonLinked = isset( $userSettings['amazon']['client_auth']) ? true : false;
?>

<div class="opd-dashboard">
	<?php \ConvoPlugin\partial('partials/navigation'); ?>

    <div class="opd-dashboard-connected p-4">

        <div ng-app="convo.wp">

        
         <script type="text/javascript" src="<?php echo CONVOWP_ASSETS_URL ?>js/vendor.js?76790e09ffce00268514"></script>
        
         <script type="text/javascript" src="<?php echo CONVOWP_ASSETS_URL ?>js/main.js?76790e09ffce00268514"></script>
        


            <script type="text/javascript">
                <?php
                    $user = new \ConvoPlugin\Convo\Wp\AdminUser( $wpUser);
                ?>
                var appModule   =   angular.module('convo.wp');
                appModule.constant( 'CONVO_PUBLIC_API_BASE_URL', '<?php echo CONVO_BASE_URL ?>/wp-json/convo/v1/public');
                appModule.constant( 'CONVO_ADMIN_API_BASE_URL', '<?php echo CONVO_BASE_URL ?>/wp-json/convo/v1');

                appModule.constant( 'WP_NONCE', '<?php echo wp_create_nonce('wp_rest'); ?>');
                appModule.constant( 'WP_USER', {
                    "user_id":"<?php echo $user->getId(); ?>",
                    "name":"<?php echo $user->getName(); ?>",
                    "username":"<?php echo $user->getUsername(); ?>",
                    "email":"<?php echo $user->getEmail(); ?>",
                    "amazon_account_linked":<?php echo $amazonLinked ? 'true' :'false'?>}
                );

            </script>

            <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
            <![endif]-->

            <div>

            <alert-indicator></alert-indicator>
            <loading-indicator></loading-indicator>

            <!-- Page Content -->
            <div class="container opd-product-list" style="min-height: 600px;" ui-view autoscroll="false"></div>

            </div>
        </div>
    </div>
</div>
