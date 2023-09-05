# WPC FIO CSV WooCommerce Plugin

A WooCommerce extension to accept CSV files and match transactions with WooCommerce orders based on unique VS codes. Plugin was created, when FIO banka API was disabled, during to DDOS.

## Features

-   Admin menu item under WooCommerce for easy CSV uploads.
-   Reads CSV files with semicolon-delimited values.
-   Automatically matches CSV transaction data to WooCommerce orders using the VS field.
-   Updates order status based on the transaction amount and order total.
-   Displays paired and unpaired orders after processing.

## Installation

1.  Download or clone this repository.
2.  Upload the `wpc-fio-csv` directory to your WordPress installation's `wp-content/plugins` directory.
3.  Activate the plugin through the 'Plugins' menu in WordPress.
4.  Access the feature via WooCommerce's submenu named "WPC FIO CSV".

## Usage

1.  Navigate to the "WPC FIO CSV" submenu under WooCommerce in your WordPress dashboard.
2.  Upload your semicolon-delimited CSV file with transaction data.
3.  Submit the file for processing.
4.  View paired and unpaired orders on the results page.

## Requirements

-   WordPress
-   WooCommerce
-   PHP 7+ (recommended)

## Contribute

I welcome contributions
