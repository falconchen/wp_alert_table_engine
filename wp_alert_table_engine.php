<?php

/*

Plugin Name: WP_ALERT_TABLE_ENGINE

Plugin URI: http://cellmean.com

Description: a tiny tool for Changing the mysql engines of wordpress tables in a click.

Version: 1.0

Author: Falcon

Author URI: http://cellmean.com

License: GPL v2

*/

class WATE
{

    public function __construct()
    {

        add_action('admin_menu', array(&$this, 'admin_menu'), 10, 2);

    }


    public function admin_menu()
    {

        add_submenu_page('tools.php', __('WP ALERT TABLE ENGINE', 'wate'), __('WP ALERT TABLE ENGINE', 'wate'), 'manage_options', __('WP ALERT TABLE ENGINE', 'wate'), array(&$this, 'admin_settings'));
    }

    public function admin_settings()
    {
        global $wpdb;

        $results = array();

        if (isset($_POST['wate_submit']) && !empty($_POST['wate_submit'])) {

            check_admin_referer('wate_settings');

            $tables = $wpdb->get_results("show table status like '%'", ARRAY_A);


            if (isset($_POST['engines']) and !empty($_POST['engines'])) {

                $tables = $wpdb->get_results("show table status like '%'", ARRAY_A);

                $toChangeEngines = array();

                foreach ($tables as $table) {
                    $tableName = $table['Name'];
                    $tableEngine = $table['Engine'];
                    if (isset($_POST['engines'][$tableName]) && $_POST['engines'][$tableName] != $tableEngine) {
                        $toChangeEngines[$tableName] = $_POST['engines'][$tableName];
                    }
                }
                foreach ($toChangeEngines as $tableName => $engineName) {

                    $results[$tableName] = $wpdb->query(sprintf('ALTER TABLE %s engine=%s', $tableName, $engineName));
                }

            }

        }

        $tables = $wpdb->get_results("show table status like '%'", ARRAY_A);

        $enginesAvailable = $wpdb->get_results("show engines", ARRAY_A);


        $showCols = array('Name', 'Engine', 'Rows', 'Create_time', 'Update_time', 'Collation', 'Comment');


        ?>
        <div class="wrap">
            <form action="" method="post">
                <h1><?php _e('WP ALERT TABLE ENGINE', 'wate'); ?></h1>

                <?php if (!empty($results)): ?>

                    <h1 style="font-size:16px;margin: 0;padding: 0;"><?php _e('status', 'wate') ?>:</h1>
                    <ul style="margin:0;padding:0">
                        <?php foreach ($results as $table => $result) : ?>
                            <li><?php echo $table ?> : <?php echo ($result) ? _( 'Success','wate' ) : _( 'Failed','wate' ) ?></li>
                        <?php endforeach; ?>
                    </ul>

                <?php endif ?>

                <strong>
                    <?php _e('Before alert table engine,Please backup your database firstly !', 'wate'); ?>
                </strong>

                <table class="widefat striped">


                    <tbody>

                    <?php foreach ($tables as $key => $tableObj) : ?>
                        <?php if ($key == 0) : ?>
                            <tr>
                                <?php foreach ($showCols as $key) : ?>
                                    <td class="row-title"><?php echo $key; ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endif ?>

                        <tr>

                            <?php foreach ($showCols as $key): ?>

                                <td class="desc">
                                    <?php if ($key == 'Engine'): ?>
                                        <select name="engines[<?php echo $tableObj['Name'] ?>]">
                                            <?php foreach ($enginesAvailable as $engine): ?>
                                                <option
                                                    <?php selected($engine['Engine'], $tableObj[$key]); ?>
                                                    value="<?php echo $engine['Engine']; ?>">
                                                    <?php echo $engine['Engine']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                    <?php else: ?>
                                        <?php echo $tableObj[$key]; ?>
                                    <?php endif; ?>

                                </td>

                            <?php endforeach; ?>
                        </tr>

                    <?php endforeach; ?>

                    </tbody>
                </table>
                <p class="submit">
                    <input type="submit" name="wate_submit" id="wate_submit" class="button-primary"
                           value="<?php _e('Submit', 'wate') ?>"/>
                </p>
                <?php wp_nonce_field("wate_settings"); ?>
            </form>
        </div>
        <?php
    }
}


if (is_admin()) {
    new WATE();
}
