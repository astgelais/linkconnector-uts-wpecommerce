# linkconnector-uts-wpecommerce-plugin
LinkConnector Universal Tracking Solution (UTS) Wordpress plugin for WP eCommerce. This plugin will install LinkConnector tracking code on your WordPress website.

## Features:
- Plugin will automatically install LinkConnector's site-wide landing page code by adding it into the footer (wp_footer).

- Plugin will automatically install LinkConnector's conversion tracking code on the WP eCommerce thank you page (wpsc_pre_transaction_results).

  - The conversion tracking code will provide LinkConnector with the following data:
    - Order ID
    - Sale Amount
    - Coupon Code
    - Discount
    - Currency
    - Product Information (SKU, Name, Price, and Category)

## Implementation Instructions:
1. Add the attached plugin to your WordPress WP eCommerce solution. To do this, go to Plugins -> Add New -> Upload Plugin. Upload the entire .zip file. Once the .zip file is uploaded, activate the plugin by going to Plugins -> Installed Plugins -> Activate. 

2. Add the required LinkConnector identifiers to the LinkConnector UTS Plugin. To do this, go to the LinkConnector UTS Plugin page in the WordPress dashboard. Then enter the LinkConnector Campaign Group ID and Event ID and click ‘Save Changes.' (Your identifiers will be given to you by your LinkConnector Merchant Representative)
