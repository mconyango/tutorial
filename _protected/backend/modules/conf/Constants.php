<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/11/23
 * Time: 3:57 PM
 */

namespace backend\modules\conf;


class Constants
{
    //resources
    const RES_SETTINGS = 'CONF_SETTINGS';
    const RES_JOB_MANAGER = 'CONF_JOB_MANAGER';
    //menu
    const MENU_SETTINGS = 'SETTINGS';
    const SUBMENU_CURRENCY = 'CURRENCY';
    const SUBMENU_EMAIL = 'EMAIL';
    const SUBMENU_ORG_STRUCTURE = 'ORG_STRUCTURE';
    const MENU_BANKS = 'BANKS';
    //system settings
    const SECTION_SYSTEM = 'system';
    const KEY_APP_NAME = 'app_name';
    const KEY_COMPANY_NAME = 'company_name';
    const KEY_COMPANY_EMAIL = 'company_email';
    const KEY_CURRENCY = 'currency_id';
    const KEY_ITEMS_PER_PAGE = 'items_per_page';
    const KEY_DEFAULT_TIMEZONE = 'default_timezone';
    const KEY_COUNTRY_ID = 'country_id';
    //email settings
    const SECTION_EMAIL = 'email';
    const KEY_EMAIL_HOST = 'email_host';
    const KEY_EMAIL_PORT = 'email_port';
    const KEY_EMAIL_USERNAME = 'email_username';
    const KEY_EMAIL_PASSWORD = 'email_password';
    const KEY_EMAIL_SECURITY = 'email_security';
    const KEY_EMAIL_THEME = 'email_theme';
    //google map
    const SECTION_GOOGLE_MAP = 'google_map';
    const KEY_GOOGLE_MAP_API_KEY = 'google_map_api_key';
    const KEY_GOOGLE_MAP_DEFAULT_CENTER = 'google_map_default_map_center';
    const KEY_GOOGLE_MAP_DEFAULT_MAP_TYPE = 'google_map_default_map_type';
    const KEY_GOOGLE_MAP_CROWD_MAP_ZOOM = 'google_map_crowd_map_zoom';
    const KEY_GOOGLE_MAP_SINGLE_VIEW_ZOOM = 'google_map_single_view_zoom';
}