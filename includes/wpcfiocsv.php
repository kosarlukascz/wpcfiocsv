<?php

if (!class_exists('wpcfiocsv')) {
    class wpcfiocsv
    {

        private static $instance; //singleton instance
        private $prefix = 'wpcfiocsv';

        public function __construct()
        {

            add_action('admin_menu', array($this, 'wpc_fio_csv_menu'));
            add_action('init', array($this, 'wpc_fio_process_csv'));
        }

        public static function get_instance()
        {
            if (self::$instance === null) {
                self::$instance = new self;
            }

            return self::$instance;
        }

        public function wpc_fio_process_csv()
        {
            if (isset($_POST['upload_csv']) && !empty($_FILES['wpc_csv_file'])) {
                $this->wpc_handle_csv_file($_FILES['wpc_csv_file']);
            }

        }

        public function wpc_handle_csv_file($file)
        {
            if ($file['type'] !== 'text/csv') {
                echo '<div class="notice notice-error"><p>Invalid file type. Please upload a CSV file.</p></div>';
                return;
            }

            $csv_rows = file($file['tmp_name'], FILE_IGNORE_NEW_LINES);
            $csv_array = [];

            foreach ($csv_rows as $row) {
                $csv_array[] = str_getcsv($row, ';', '"');
            }

            if (!empty($csv_array)) {
                $this->wpc_process_csv_array($csv_array);
            } else {
                echo '<div class="notice notice-error"><p>CSV file is empty or invalid.</p></div>';
            }
        }


        public function wpc_process_csv_array($array)
        {
            // Extract headers from the first row
            $headers = array_shift($array);

            // Convert the rest of the rows to associative arrays using the headers as keys
            $associative_array = array_map(function ($row) use ($headers) {
                return array_combine($headers, $row);
            }, $array);

            $this->process_csv_lines($associative_array);
            exit;
        }

        public function process_csv_lines($obj)
        {
            $paried = [];
            $unpaired = [];
            foreach ($obj as $transaction) {


                $order_id = $transaction['VS'];
                if (!empty($order_id)) {
                    $order = wc_get_order($order_id);
                    if ($order) {
                        //order value
                        $order_value = $order->get_total();
                        if ($order_value >= $transaction['Objem']) {
                            $paried[] = $order_id;
                            //if order status is not processing, completed, cancelled or refunded, update it to processing
                            if (!in_array($order->get_status(), array('processing', 'completed', 'cancelled', 'refunded'))) {

                                $order->update_status('processing');
                                $order->add_order_note(
                                    sprintf(
                                        'Payment completed via FIO bank. Transaction ID: %s',
                                        $transaction['Protiúčet'] . '/' . $transaction['Kód banky'] . '/' . $transaction['Poznámka']));
                            } else {
								echo 'Order already marked as paid '. $order_id.PHP_EOL;
							}
                        } else {
                            $order->add_order_note(
                                sprintf(
                                    'Payment failed via FIO bank. Transaction ID: %s',
                                    $transaction['Protiúčet'] . '/' . $transaction['Kód banky'] . '/' . $transaction['Poznámka']));
                            $unparied[] = $order_id;
                        }
                        $order->save();
                    }
                }


            }
            echo '<div class="notice notice-success"><p>CSV file processed.</p></div>';
            echo '<h3>Paired orders</h3>';
            prettyprint($paried);
            echo '<h3>Unpaired orders</h3>';
            prettyprint($unparied);
            exit;
        }

        public
        function wpc_fio_csv_menu()
        {
            add_submenu_page('woocommerce', 'WPC FIO CSV', 'WPC FIO CSV', 'manage_woocommerce', 'wpc-fio-csv', array($this, 'wpc_fio_csv_page_callback'));
        }

        public
        function wpc_fio_csv_page_callback()
        {
            ?>
            <div class="wrap">
                <h2>WPC FIO CSV</h2>
                <form method="post" enctype="multipart/form-data">
                    <input type="file" name="wpc_csv_file" accept=".csv">
                    <input type="submit" name="upload_csv" class="button button-primary" value="Upload">
                </form>
            </div>
            <?php
        }
    }

    if (!function_exists('prettyprint')) {
        function prettyprint($arr)
        {
            echo '<pre>';
            print_r($arr);
            echo '</pre>';
        }
    }
}
?>