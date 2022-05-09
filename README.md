# Yotpo Reviews

**Contributors:** gataf \
**Donate link:** https://www.seanrsullivan.com \
**Tags:** comments, yotpo, woocommerce, product reviews \
**Requires at least:** 5.0 \
**Tested up to:** 5.9 \
**Requires PHP:** 7.1 \
**Stable tag:** 1.5.2 \
**License:** GPLv2 or later \
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html

Import Yotpo reviews to use the native WooCommerce reviews.

## Description

This plugin allows you to use your Yotpo reviews as native WooCommerce reviews and ratings.

Utilizing the Yotpo API and the WooCommerce REST API, you can import reviews from the Yotpo platform. This will allow you to remove all third-party scripts for Yotpo to improve page speed on your site.

## Installation

Either install through your WordPress installation or manually add the plugin

1. Upload `yotpo-reviews.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

Once the plugin is activated and installed.

1. Head to Settings > Yotpo Reviews
1. Input your Yotpo app ID and secret key.
1. Select how you would like your products identified.
1. Input your WooCommerce client key and secret key.

## Frequently Asked Questions

### What's the point of this plugin?

I was not a fan of how many times the Yotpo embeds were being called on my pages. For example, on the product listing page, an embed would be called for each product listed to show their rating stars. So by default, it was being called 12 times. Not good for site speed. So, utilizing Yotpo's and the WooCommerce's APIs, I was able to import the product reviews from Yotpo into WooCommerce so that the native comment/review/rating functionality could be leveraged instead of leaning on third-party scripts.

### How do I use it?

There are several ways to utilize this plugin. After inputting all of the needed keys, you will need to do an initial import if you have existing reviews on Yotpo. From there you can go in and manually get reviews from the past 24 hours. For a more automated process, each time a review is posted, a webhook from Yotpo is fired which will automatically add any reviews from the previous 24 hours.

I do suggest going in every now-and-then and manually grabbing reviews just to make sure you have what you need. If you have reviews that are "rejected", the next time an import is run, they will be removed from the WooCommerce reviews.

### Do I need a Yotpo account?

Yes. You can sign up for a free account by [clicking here](https://accounts.yotpo.com/#/signup).

### Where do I get my Yotpo app ID and secret key?

Please see this [Yotpo Help Center article](https://support.yotpo.com/en/article/finding-your-yotpo-app-key-and-secret-key).

### Where do I get my WooCommerce REST API keys?

Please see the [WooCommerce Documentation](https://woocommerce.com/document/woocommerce-rest-api/).

## Changelog

### 1.5.2

* Fixed issue with incorrect function name causing fatal error.

### 1.5.1

* Made it so order import will use the parent SKU to avoid missing products.

### 1.5.0

* Added in ability to send orders to Yotpo.
* Kept keys in their input fields so blanks aren't submitted when updating settings.

### 1.0.0

* Initial plugin creation.

## Upgrade Notice

### 1.0.0

Initial plugin creation. Install and use!
