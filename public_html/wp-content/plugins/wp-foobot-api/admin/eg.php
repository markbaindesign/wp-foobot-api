<?php

add_action( 'admin_menu', 'stp_api_add_admin_menu' );
add_action( 'admin_init', 'stp_api_settings_init' );

function stp_api_add_admin_menu(  ) {
    add_options_page( 'Settings API Page', 'Settings API Page', 'manage_options', 'settings-api-page', 'stp_api_options_page' );
}

function stp_api_settings_init(  ) {
    register_setting( 'stpPlugin', 'stp_api_settings' );
    add_settings_section(
        'stp_api_stpPlugin_section',
        __( 'Our Section Title', 'wordpress' ),
        'stp_api_settings_section_callback',
        'stpPlugin'
    );

    add_settings_field(
        'stp_api_text_field_0',
        __( 'Our Field 0 Title', 'wordpress' ),
        'stp_api_text_field_0_render',
        'stpPlugin',
        'stp_api_stpPlugin_section'
    );

    add_settings_field(
        'stp_api_select_field_1',
        __( 'Our Field 1 Title', 'wordpress' ),
        'stp_api_select_field_1_render',
        'stpPlugin',
        'stp_api_stpPlugin_section'
    );
}

function stp_api_text_field_0_render(  ) {
    $options = get_option( 'stp_api_settings' );
    ?>
    <input type='text' name='stp_api_settings[stp_api_text_field_0]' value='<?php echo $options['stp_api_text_field_0']; ?>'>
    <?php
}

function stp_api_select_field_1_render(  ) {
    $options = get_option( 'stp_api_settings' );
    ?>
    <select name='stp_api_settings[stp_api_select_field_1]'>
        <option value='1' <?php selected( $options['stp_api_select_field_1'], 1 ); ?>>Option 1</option>
        <option value='2' <?php selected( $options['stp_api_select_field_1'], 2 ); ?>>Option 2</option>
    </select>

<?php
}

function stp_api_settings_section_callback(  ) {
    echo __( 'This Section Description', 'wordpress' );
}

function stp_api_options_page(  ) {
    ?>
    <form action='options.php' method='post'>

        <h2>Sitepoint Settings API Admin Page</h2>

        <?php
        settings_fields( 'stpPlugin' );
        do_settings_sections( 'stpPlugin' );
        submit_button();
        ?>

    </form>
    <?php
}