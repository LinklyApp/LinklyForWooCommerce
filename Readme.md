# Linkly for WooCommerce

![License](https://img.shields.io/badge/License-GPLv3-blue.svg)

Linkly for WooCommerce is a WordPress plugin that allows you to seamlessly integrate your WooCommerce webshop with Linkly.

## Description

This WooCommerce extension enables you to link your webshop with Linkly, providing a streamlined experience for your users.

### Main Features

- Link your webshop with Linkly
- Change your client ID and secret
- Choose a button style for the users of your webshop

## Installation

### Minimum Requirements
- WordPress 5.3 or later
- WooCommerce 6.9.1 or later

**Special Note for Users Cloning or Downloading Directly from GitHub**:

If you've cloned or downloaded the plugin directly from GitHub, the `vendor` folder won't be included. You'll need to generate it manually.

#### Prerequisites for GitHub Users:

-   [PHP 7.4+](https://www.php.net/manual/en/install.php): It is needed to run Composer.
-   [Composer](https://getcomposer.org/doc/00-intro.md): We use Composer to manage all of the dependencies for PHP packages.

After ensuring the above prerequisites:

1. Navigate to the plugin directory and run the command:

    ```bash
    composer install
    ```

2. This will create the `vendor` folder with all required dependencies.
3. (Optional) If you wish to redistribute or move the plugin, you can zip the entire plugin directory, including the newly created `vendor` folder.

Now, proceed with the remaining installation instructions.

### Automatic Installation

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't even need to leave your web browser. To do an automatic install of Linkly for WooCommerce, follow these steps:

1. Log in to your WordPress admin panel
2. Navigate to the Plugins menu and click Add New
3. In the search field type "Linkly for WooCommerce" and click Search Plugins
4. Click Install Now
5. You will be asked if you're sure you want to install the plugin. Click yes and WordPress will automatically complete the installation
6. After installation has finished, click the 'activate plugin' link

### Manual Installation via the WordPress Interface

1. Download the plugin zip file to your computer
2. Go to the WordPress admin panel menu Plugins > Add New
3. Choose upload
4. Upload the plugin zip file, the plugin will now be installed
5. After installation has finished, click the 'activate plugin' link

### Manual Installation via FTP

1. Download the plugin file to your computer and unzip it
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's wp-content/plugins/ directory
3. Activate the plugin from the Plugins menu within the WordPress admin

## Bugs and Contributions

If you find a bug, please report it on the [LinklyApp GitHub repository](https://github.com/LinklyApp/LinklyForWooCommerce/issues?utm_medium=referral&utm_source=wordpress.org&utm_campaign=wp_org_repo_listing).

## License

This project is licensed under the [GPLv3 License](https://www.gnu.org/licenses/gpl-3.0.txt)

---