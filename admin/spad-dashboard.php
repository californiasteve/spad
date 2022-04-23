<?php
 
/* Admin dashboard of the SPAD plugin */

function spad_plugin_settings()
{
    //register our settings
    register_setting('spad-plugin-settings-group', 'spad_timezone');
    register_setting('spad-plugin-settings-group', 'custom_css_spad');
}

add_action('admin_init', 'spad_plugin_settings');

function fetch_spad_plugin_page()
{
    ?>
    <div class="wrap">
        <h1>Fetch SPAD Plugin Settings</h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('spad-plugin-settings-group');
            do_settings_sections('spad-plugin-settings-group');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Timezone</th>
                    <td>
                        <select style="display:inline;" id="spad_timezone" name="spad_timezone">
                            <option value="">Select A Timezone</option>
                            <?php
                            $timezones_array = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
                            foreach ($timezones_array as $tzItem) {
                                if ($tzItem == get_option('spad_timezone')) { ?>
                                    <option selected="selected" value="<?php echo $tzItem; ?>"><?php echo $tzItem; ?></option>
                                <?php } else { ?>
                                    <option value="<?php echo $tzItem; ?>"><?php echo $tzItem; ?></option>
                                <?php }
                            } ?>
                        </select>
                        <p class="description">Choose the timezone for the SPAD Display. Defaults to America/Los_Angeles.</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Custom CSS</th>
                    <td>
                        <textarea id="custom_css_spad" name="custom_css_spad" cols="100" rows="10"><?php echo get_option('custom_css_spad'); ?></textarea>
                    </td>
                </tr>
            </table>
            <?php  submit_button(); ?>
        </form>
   </div>
<?php }

// End SPAD Settings Page Function
?>
