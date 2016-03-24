<?php
/*
 * Plugin Name: Culture24
 * Plugin URI: http://github.com/zenlan
 * Version: 1.0.2
 * Description: Connect to the Culture24 API
 * Author: Monique Szpak for the Imperial War Museums
 * Author URI: http://zenlan.com
 * License: MIT
 */

define('CULTURE24__MINIMUM_WP_VERSION', '3.3');
define('CULTURE24__VERSION', '0.1.0');

// @TODO This is app entry point,... implement psr4 autoloader, create WPCulture24 object
// These includes will be handled by autoloader, they can be instantiated within WPCulture24 obj
$pwd = dirname(__FILE__);
require_once $pwd . '/culture24.api.php';
require_once $pwd . '/culture24.class.php';
require_once $pwd . '/culture24.event.php';
require_once $pwd . '/culture24.venue.php';

// LB
require_once $pwd . '/ol-functions.php';

// @TODO Move everything below this line elsewhere
/**
 *
 */
function culture24_check_admin() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
}

/**
 *
 * @param type $filename
 */
function culture24_include_file($filename) {
    if (file_exists(dirname(__FILE__) . '/' . $filename)) {
        include( dirname(__FILE__) . '/' . $filename );
    } else {
        _e('<p>Failed to find ' . $filename . '</p>', 'culture24');
    }
}

/**
 * Admin class
 */
class culture24admin {
    public $settings = array('theme', 'url', 'version', 'key', 'tag', 'epp', 'vfp', 'vpp');
    public $settings_prefix = 'c24api_';

    public function __construct() {
        if (is_admin()) {
            add_action('admin_menu', array($this, 'admin_menu'));
            add_action('admin_init', array($this, 'admin_init'));
        }
    }

    public function admin_menu() {
        add_menu_page('Culture24 Options', 'Culture24', 'manage_options', 'culture24', array($this, 'admin_options'));
        add_submenu_page('culture24', 'Events', 'Events', 'manage_options', 'events', array($this, 'admin_events'));
        add_submenu_page('culture24', 'Venues', 'Venues', 'manage_options', 'venues', array($this, 'admin_venues'));
        add_submenu_page('culture24', 'HTML', 'HTML', 'manage_options', 'html', array($this, 'admin_html'));
        add_submenu_page('culture24', 'Dates', 'Dates', 'manage_options', 'dates', array($this, 'admin_dates'));
    }

    public function admin_options() {
        culture24_include_file('culture24.admin.php');
    }

    public function admin_dates() {
        culture24_include_file('culture24.admin.dates.php');
    }

    public function admin_html() {
        culture24_include_file('culture24.admin.html.php');
    }

    public function admin_events() {
        culture24_include_file('culture24.admin.events.php');
    }

    public function admin_venues() {
        culture24_include_file('culture24.admin.venues.php');
    }

    public function admin_init() {
        register_setting('c24_options_group_api', 'c24', array($this, 'c24_save_settings'));
        add_settings_section(
            'settings_api', 'API Settings', array($this, 'print_section_info'), 'c24-settings-api'
        );
        add_settings_field(
            'c24_api_theme', 'Theme', array($this, 'create_field_api_theme'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_url', 'Base URL', array($this, 'create_field_api_url'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_version', 'Version', array($this, 'create_field_api_version'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_key', 'Key', array($this, 'create_field_api_key'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_tag', 'Default search tag(s)', array($this, 'create_field_api_tag'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_epp', 'Events per page', array($this, 'create_field_api_epp'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_efp', 'Events front page', array($this, 'create_field_api_efp'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_vpp', 'Partners per page', array($this, 'create_field_api_vpp'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_venue_id', 'Venue ID', array($this, 'create_field_api_venue_id'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_epp', 'Events per page', array($this, 'create_field_api_epp'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_efp', 'Event ID front page', array($this, 'create_field_api_efp'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_vpp', 'Partners per page', array($this, 'create_field_api_vpp'), 'c24-settings-api', 'settings_api'
        );
    }

    /**
     * Create the theme selector field
     *
     * @return void
     */
    public function create_field_api_theme() {
        $current = get_option('c24api_theme', 'default-theme');
        $themes = glob(__DIR__ . '/themes/*', GLOB_ONLYDIR);
?>
    <select name="c24[theme]">
        <?php foreach ($themes as $theme) : ?>
            <?php $theme = basename($theme); ?>
            <option value="<?php echo $theme; ?>" <?php echo ($theme == $current ? 'selected="selected"' : ''); ?>><?php echo $theme; ?></option>
        <?php endforeach; ?>
    </select>
<?php
    }

    public function create_field_api_url() {
?>
    <input type="text" id="input_c24api_url" name="c24[url]" value="<?php echo get_option('c24api_url', 'http://www.culture24.org.uk/api/rest/v'); ?>" size="128" />
<?php
    }

    public function create_field_api_version() {
?>
    <input type="text" id="input_c24api_version" name="c24[version]" value="<?php echo get_option('c24api_version', '1'); ?>" size="2" />
<?php
    }

    public function create_field_api_key() {
?>
    <input type="text" id="input_c24api_key" name="c24[key]" value="<?php echo get_option('c24api_key', ''); ?>" size="32" />
<?php
    }

    public function create_field_api_tag() {
?>
    <input type="text" id="input_c24api_tag" name="c24[tag]" value="<?php echo get_option('c24api_tag', ''); ?>" size="128" />
<?php
    }

    public function create_field_api_venue_id() {
?>
    <input type="text" id="input_c24api_venue_id" name="c24[venue_id]" value="<?php echo get_option('c24api_venue_id', ''); ?>" size="128" />
<?php
    }

    public function create_field_api_epp() {
?>
    <input type="text" id="input_c24api_epp" name="c24[epp]" value="<?php echo get_option('c24api_epp', '10'); ?>" size="2" />
<?php
    }

    public function create_field_api_efp() {
?>
    <input type="text" id="input_c24api_efp" name="c24[efp]" value="<?php echo get_option('c24api_efp', ''); ?>" size="8" />
<?php
    }

    public function create_field_api_vpp() {
?>
    <input type="text" id="input_c24api_vpp" name="c24[vpp]" value="<?php echo get_option('c24api_vpp', '25'); ?>" size="2" />
<?php
    }

    public function print_section_info() {
        print 'Use shortcode [c24page]. Default tag(s) to filter all searchs. Leave blank, enter one word/phrase
            or comma delimited list of words/phrases.';
    }

    public function c24_save_settings($input) {
        $prefix = $this->settings_prefix;
        foreach ($this->settings as $v) {
            if (isset($input[$v])) {
                if (get_option($prefix . $v) === FALSE) {
                    add_option($prefix . $v, $input[$v]);
                } else {
                    update_option($prefix . $v, $input[$v]);
                }
            }
        }
        if (isset($input['venue_id'])) {
            if (get_option('c24api_venue_id') === FALSE) {
                add_option('c24api_venue_id', $input['venue_id']);
            } else {
                update_option('c24api_venue_id', $input['venue_id']);
            }
        }
        if (isset($input['epp'])) {
            if (get_option('c24api_epp') === FALSE) {
                add_option('c24api_epp', $input['epp']);
            } else {
                update_option('c24api_epp', $input['epp']);
            }
        }
        if (isset($input['efp'])) {
            if (get_option('c24api_efp') === FALSE) {
                add_option('c24api_efp', $input['efp']);
            } else {
                update_option('c24api_efp', $input['efp']);
            }
        }
        if (isset($input['vpp'])) {
            if (get_option('c24api_vpp') === FALSE) {
                add_option('c24api_vpp', $input['vpp']);
            } else {
                update_option('c24api_vpp', $input['vpp']);
            }
        }
        return $input;
    }

    public function get_option($option, $default=NULL) {
        return get_option($this->settings_prefix . $option, $default);
    }

}

$c24admin = new culture24admin();
require_once $pwd . '/themes/' . $c24admin->get_option('theme', 'default-theme') . '/functions.php';
// MISC USEFUL FUNCS

function c24_view_event_debug($obj, $full = FALSE) {
    $result = $obj->get_last_request() . '<br />'
        . 'status: ' . $obj->get_message() . '<br />'
        . 'total: ' . $obj->get_found() . '<br />'
        . 'validation errors: ' . print_r($obj->get_validation_errors(), 1) . '<br />';
    if ($full) {
        $result .= print_r($obj->get_records(), 1) . '<br /><br />';
    }
    return $result;
}

/**
 * Format dates
 *
 * @param array $date_array
 * @return array of formatted date strings
 */
function c24_format_dates($dates, $format = 'd F Y') {
    if (empty($dates)) {
        return '';
    } else {
        $unique = array();
        foreach ($dates as $k => $v) {
            $start = strtotime(str_replace('/', '-', $v->startDate));
            $end = strtotime(str_replace('/', '-', $v->endDate));
            if ($start == $end) {
                if (!in_array($start, $unique)) {
                    $dates[$k] = date($format, $start);
                    $unique[] = $start;
                } else {
                    unset($dates[$k]);
                }
            } else {
                $dates[$k] = date($format, $start) . ' - ' . date($format, $end);
            }
        }
    }
    return $dates;
}

/**
 * Is it a daily event?
 *
 * @param array $dates
 * @return boolean
 */
function c24_is_date_daily($dates) {
    if (isset($dates['value2']) && $dates['value2'] !== $dates['value']) {
        return TRUE;
    }
    return FALSE;
}

/**
 * Is it a one-day event?
 *
 * @param array $dates
 * @return boolean
 */
function c24_is_date_single($dates) {
    if (!isset($dates['value2']) || $dates['value2'] == $dates['value']) {
        if (!isset($dates['rrule'])) {
            return TRUE;
        }
    }
    return FALSE;
}

/*
 * LOOKUP DATA
 *
 * NOTES ON FILTERS IN QUERIES
 *
 * 1. At first glance it looks like the API can search on multiple filters
 * but it will only match on the first one, e.g.
 * &q.audience=Any+age&q.audience=Family+friendly matches on 'Any age' only.
 *
 * 2. Region values are held in the contentTag array in an implicit hierarchy.
 * Up to 3 values may or may not be present in any order and any positions in
 * the array, e.g.
 * contentTag: Array ( [0] => History and Heritage [1] => World War I [2] => England [3] => East of England [4] => Hertfordshire )
 * contentTag: Array ( [0] => England [1] => History and Heritage [2] => East of England [3] => World War I [4] => Hertfordshire )
 * contentTag: Array ( [0] => History and Heritage [1] => World War I [2] => England [3] => Hertfordshire )
 *
 * The API seems to execute a free text search for each of the terms across the
 * contentTags which means that "East of England" also matches
 * "England" + "South East", in other words it appears to match each of the
 * terms across all of the tags e.g.
 * a museum tagged Kent, South East and England turns up in the results for an "East of England" query
 */

/**
 *
 * @return array
 */
function c24_regions() {
    return array(
        'East Midlands',
        'East of England',
        'London',
        'North East',
        'North West',
        'Northern Ireland',
        'Scotland',
        'South East',
        'South West',
        'Wales',
        'West Midlands',
        'Yorkshire',
    );
}

/**
 *
 * @return array
 */
function c24_foreignparts() {
    return array(
        'Australia',
        'Austria',
        'Belgium ',
        'Bosnia And Herzegovina',
        'Canada',
        'Denmark',
        'Finland',
        'France',
        'Germany',
        'Hungary',
        'Iran',
        'Ireland',
        'Italy',
        'Namibia',
        'Netherlands',
        'New Zealand',
        'Poland',
        'Portugal',
        'Russian Federation',
        'Serbia',
        'Singapore',
        'Slovakia',
        'Slovenia',
        'South Africa',
        'Spain',
        'Sweden',
        'Switzerland',
        'Trinidad and Tobago',
        'United States',
    );
}

/**
 *
 * @return array
 */
function c24_audiences() {
    return array(
        'Any age',
        'Family friendly',
        '0-4',
        '5-6',
        '7-10',
        '11-13',
        '14-15',
        '16-17',
        '18+',
    );
}

/**
 *
 * @return array
 */
function c24_types() {
    return array(
        'Late opening',
        'Lecture',
        'Exhibition (permanent)',
        'Exhibition (temporary)',
        'Event',
        'Guided tour',
        'Living history or re-enactment',
        'Performance ',
        'Seasonal event ',
        'Storytelling session',
        'Workshop or activity session',
    );
}
