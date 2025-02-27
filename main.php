/**
 * Plugin Name: Team Management System
 * Description: Manages teams, games, divisions, and standings
 * Version: 1.0.0
 * Last updated by EvThatGuy on 2025-02-05 03:35:58
 */

// PART 1: CORE SETUP AND POST TYPES
// =================================

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TEAM_MANAGER_VERSION', '1.0.0');
define('TEAM_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Initialize plugin hooks
function initialize_team_management_system() {
    // Register post types and taxonomies
    create_game_post_type();
    register_team_post_type();
    register_division_taxonomy();
    
    // Add meta boxes
    add_action('add_meta_boxes', 'add_game_meta_box');
    add_action('add_meta_boxes', 'add_team_meta_box');
    
    // Save post data
    add_action('save_post_game', 'save_game_meta_box_data');
    add_action('save_post_team', 'save_team_meta_box_data');
}
add_action('init', 'initialize_team_management_system', 0);

// Game Post Type Registration
if (!function_exists('create_game_post_type')) {
    function create_game_post_type() {
        register_post_type('game', [
            'labels' => [
                'name' => __('Games'),
                'singular_name' => __('Game'),
                'menu_name' => __('Games'),
                'add_new' => __('Add New Game'),
                'add_new_item' => __('Add New Game'),
                'edit_item' => __('Edit Game'),
                'new_item' => __('New Game'),
                'view_item' => __('View Game'),
                'view_items' => __('View Games'),
                'search_items' => __('Search Games'),
                'not_found' => __('No games found'),
                'not_found_in_trash' => __('No games found in trash'),
                'all_items' => __('All Games'),
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'games'],
            'supports' => ['title', 'custom-fields'],
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-calendar-alt',
            'menu_position' => 6,
        ]);
    }
}

// Team Post Type with Enhanced Features
if (!function_exists('register_team_post_type')) {
    function register_team_post_type() {
        $labels = array(
            'name'                  => 'Teams',
            'singular_name'         => 'Team',
            'menu_name'            => 'Teams',
            'add_new'              => 'Add New Team',
            'add_new_item'         => 'Add New Team',
            'edit_item'            => 'Edit Team',
            'new_item'             => 'New Team',
            'view_item'            => 'View Team',
            'search_items'         => 'Search Teams',
            'not_found'            => 'No teams found',
            'not_found_in_trash'   => 'No teams found in trash',
            'all_items'            => 'All Teams',
            'featured_image'        => 'Team Logo',
            'set_featured_image'    => 'Set team logo',
            'remove_featured_image' => 'Remove team logo',
            'use_featured_image'    => 'Use as team logo',
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'has_archive'         => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-groups',
            'hierarchical'        => false,
            'supports'            => array(
                'title',           // Enable team name editing
                'editor',          // Enable team description
                'thumbnail',       // Enable team logo
                'excerpt',         // Enable short description
                'custom-fields',
                'revisions'
            ),
            'rewrite'            => array('slug' => 'teams'),
            'capability_type'     => 'post',
            'show_in_rest'        => true,
            'template'            => array(
                array('core/paragraph', array(
                    'placeholder' => 'Enter team description...'
                ))
            ),
        );

        register_post_type('team', $args);
    }
}
/**
 * Part 2: Taxonomies and Meta Boxes
 * Last updated by EvThatGuy on 2025-02-13 18:59:30
 */

// Add Team Meta Box
if (!function_exists('add_team_meta_box')) {
    function add_team_meta_box() {
        add_meta_box(
            'team_details',
            'Team Details',
            'team_meta_box_callback',
            'team',
            'normal',
            'high'
        );
    }
}

// Add Game Meta Box
if (!function_exists('add_game_meta_box')) {
    function add_game_meta_box() {
        add_meta_box(
            'game_details',
            'Game Details',
            'game_meta_box_callback',
            'game',
            'normal',
            'high'
        );
    }
}

// Register all meta boxes
function register_all_meta_boxes() {
    add_game_meta_box();
    add_team_meta_box();
    
    // Add Division Meta Box
    add_meta_box(
        'division-select',
        'Select Division',
        'render_division_select',
        'team',
        'side',
        'high'
    );

    // Remove unwanted meta boxes
    remove_meta_box('teamsize', 'team', 'normal');
    remove_meta_box('manualpoints', 'team', 'normal');
}
add_action('add_meta_boxes', 'register_all_meta_boxes');

// Division Taxonomy
if (!function_exists('register_division_taxonomy')) {
    function register_division_taxonomy() {
        $labels = array(
            'name'              => 'Divisions',
            'singular_name'     => 'Division',
            'search_items'      => 'Search Divisions',
            'all_items'         => 'All Divisions',
            'edit_item'         => 'Edit Division',
            'update_item'       => 'Update Division',
            'add_new_item'      => 'Add New Division',
            'new_item_name'     => 'New Division Name',
            'menu_name'         => 'Divisions'
        );

        register_taxonomy(
            'division',
            'team',
            array(
                'labels'            => $labels,
                'hierarchical'      => true,
                'public'           => true,
                'show_ui'          => true,
                'show_admin_column' => true,
                'show_in_nav_menus' => true,
                'query_var'         => true,
                'rewrite'           => array('slug' => 'division'),
                'show_in_rest'      => true,
                'capabilities'      => array(
                    'manage_terms'  => 'edit_posts',
                    'edit_terms'    => 'edit_posts',
                    'delete_terms'  => 'edit_posts',
                    'assign_terms'  => 'edit_posts'
                )
            )
        );
    }
    add_action('init', 'register_division_taxonomy');
}

function render_division_select($post) {
    // Get current division
    $current_terms = get_the_terms($post->ID, 'division');
    $current_division = ($current_terms && !is_wp_error($current_terms)) ? $current_terms[0]->term_id : '';

    // Get all divisions
    $divisions = get_terms(array(
        'taxonomy' => 'division',
        'hide_empty' => false,
        'orderby' => 'name'
    ));

    if (!is_wp_error($divisions)) {
        wp_nonce_field('save_division_select', 'division_select_nonce');
        ?>
        <select name="team_division" id="team_division" style="width: 100%;">
            <option value="">Select Division</option>
            <?php foreach ($divisions as $division) : ?>
                <option value="<?php echo esc_attr($division->term_id); ?>" 
                    <?php selected($current_division, $division->term_id); ?>>
                    <?php echo esc_html($division->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }
}

// Save division selection
add_action('save_post_team', 'save_team_division', 10, 2);

function save_team_division($post_id, $post) {
    // Skip autosaves
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check if this is a revision
    if (wp_is_post_revision($post_id)) {
        return;
    }

    // Verify nonce
    if (!isset($_POST['division_select_nonce']) || !wp_verify_nonce($_POST['division_select_nonce'], 'save_division_select')) {
        return;
    }

    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save division if set
    if (isset($_POST['team_division'])) {
        $division_id = absint($_POST['team_division']);
        if ($division_id > 0) {
            wp_set_object_terms($post_id, array($division_id), 'division');
        } else {
            wp_set_object_terms($post_id, array(), 'division');
        }
    }
}

// Game Meta Box Callback
function game_meta_box_callback($post) {
    wp_nonce_field('game_meta_box', 'game_meta_box_nonce');

    // Get existing values
    $team1_id = get_post_meta($post->ID, '_team1', true);
    $team2_id = get_post_meta($post->ID, '_team2', true);
    $score1 = get_post_meta($post->ID, '_score1', true) ?: '0';
    $score2 = get_post_meta($post->ID, '_score2', true) ?: '0';
    $points1 = get_post_meta($post->ID, '_points1', true) ?: '0';
    $points2 = get_post_meta($post->ID, '_points2', true) ?: '0';

    // Get all teams
    $teams = get_posts(array(
        'post_type' => 'team',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ));
    ?>
    <div class="game-meta-box">
        <div class="team-section">
            <label for="team1">Team 1:</label>
            <select name="team1" id="team1" required>
                <option value="">Select Team</option>
                <?php foreach ($teams as $team) : ?>
                    <option value="<?php echo esc_attr($team->ID); ?>" 
                        <?php selected($team1_id, $team->ID); ?>>
                        <?php echo esc_html($team->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="score1">Score Team 1:</label>
            <input type="number" 
                   name="score1" 
                   id="score1" 
                   value="<?php echo esc_attr($score1); ?>" 
                   min="0" 
                   required>

            <label for="points1">Points Team 1:</label>
            <input type="number" 
                   name="points1" 
                   id="points1" 
                   value="<?php echo esc_attr($points1); ?>" 
                   step="0.1" 
                   required>
        </div>

        <div class="team-section">
            <label for="team2">Team 2:</label>
            <select name="team2" id="team2" required>
                <option value="">Select Team</option>
                <?php foreach ($teams as $team) : ?>
                    <option value="<?php echo esc_attr($team->ID); ?>" 
                        <?php selected($team2_id, $team->ID); ?>>
                        <?php echo esc_html($team->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="score2">Score Team 2:</label>
            <input type="number" 
                   name="score2" 
                   id="score2" 
                   value="<?php echo esc_attr($score2); ?>" 
                   min="0" 
                   required>

            <label for="points2">Points Team 2:</label>
            <input type="number" 
                   name="points2" 
                   id="points2" 
                   value="<?php echo esc_attr($points2); ?>" 
                   step="0.1" 
                   required>
        </div>
    </div>

    <style>
        .game-meta-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 15px;
        }
        .team-section {
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .team-section label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        .team-section select,
        .team-section input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .team-section input[type="number"] {
            width: 100px;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        $('#team1, #team2').on('change', function() {
            const team1 = $('#team1').val();
            const team2 = $('#team2').val();
            
            if (team1 && team2 && team1 === team2) {
                alert('Teams must be different');
                $(this).val('');
            }
        });
    });
    </script>
    <?php
}

// Team Meta Box Callback
function team_meta_box_callback($post) {
    wp_nonce_field('team_meta_box', 'team_meta_box_nonce');

    // Get existing values
    $team_code = get_post_meta($post->ID, '_team_code', true);
    $captain_name = get_post_meta($post->ID, '_captain_name', true);
    $captain_email = get_post_meta($post->ID, '_captain_email', true);
    $team_size = get_post_meta($post->ID, '_team_size', true);
    $manual_points = get_post_meta($post->ID, '_manual_points', true);
    ?>
    <div class="team-meta-box">
        <div class="field-group">
            <label for="team_code">Team Code:</label>
            <input type="text" 
                   id="team_code" 
                   name="team_code" 
                   value="<?php echo esc_attr($team_code); ?>"
                   placeholder="Enter team code">
        </div>

        <div class="field-group">
            <label for="captain_name">Captain Name:</label>
            <input type="text" 
                   id="captain_name" 
                   name="captain_name" 
                   value="<?php echo esc_attr($captain_name); ?>"
                   placeholder="Enter captain's name">
        </div>

        <div class="field-group">
            <label for="captain_email">Captain Email:</label>
            <input type="email" 
                   id="captain_email" 
                   name="captain_email" 
                   value="<?php echo esc_attr($captain_email); ?>"
                   placeholder="Enter captain's email">
        </div>

        <div class="field-group">
            <label for="team_size">Team Size:</label>
            <input type="number" 
                   id="team_size" 
                   name="team_size" 
                   value="<?php echo esc_attr($team_size); ?>"
                   min="1" 
                   placeholder="Enter team size">
        </div>

        <div class="field-group">
            <label for="manual_points">Manual Points Adjustment:</label>
            <input type="number" 
                   id="manual_points" 
                   name="manual_points" 
                   value="<?php echo esc_attr($manual_points); ?>"
                   step="0.1" 
                   placeholder="Enter manual points adjustment">
        </div>
    </div>

    <style>
        .team-meta-box {
            padding: 15px;
        }
        .field-group {
            margin-bottom: 15px;
        }
        .field-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .field-group input {
            width: 100%;
            max-width: 400px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
    <?php
}

// Save team meta box data
add_action('save_post_team', 'save_team_meta_box_data');

function save_team_meta_box_data($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (!isset($_POST['team_meta_box_nonce']) || 
        !wp_verify_nonce($_POST['team_meta_box_nonce'], 'team_meta_box')) {
        return;
    }

    $fields = array(
        '_team_code' => array('key' => 'team_code', 'sanitize' => 'sanitize_text_field'),
        '_captain_name' => array('key' => 'captain_name', 'sanitize' => 'sanitize_text_field'),
        '_captain_email' => array('key' => 'captain_email', 'sanitize' => 'sanitize_email'),
        '_team_size' => array('key' => 'team_size', 'sanitize' => 'absint'),
        '_manual_points' => array('key' => 'manual_points', 'sanitize' => 'floatval')
    );

    foreach ($fields as $meta_key => $field) {
        if (isset($_POST[$field['key']])) {
            $value = call_user_func($field['sanitize'], $_POST[$field['key']]);
            update_post_meta($post_id, $meta_key, $value);
        }
    }

    // Update standings if manual points changed
    if (isset($_POST['manual_points'])) {
        update_team_standings($post_id);
    }
}/**
 * Part 3: Game Meta Box and Game Management
 * Last updated by EvThatGuy on 2025-02-13 18:50:36
 */

// Callback for the game meta box with improved team selection and validation
if (!function_exists('game_meta_box_callback')) {
    function game_meta_box_callback($post) {
        wp_nonce_field('game_meta_box', 'game_meta_box_nonce');

        // Get existing values with defaults
        $team1 = get_post_meta($post->ID, '_team1', true);
        $team2 = get_post_meta($post->ID, '_team2', true);
        $score1 = get_post_meta($post->ID, '_score1', true) ?: '0';
        $points1 = get_post_meta($post->ID, '_points1', true) ?: '0';
        $score2 = get_post_meta($post->ID, '_score2', true) ?: '0';
        $points2 = get_post_meta($post->ID, '_points2', true) ?: '0';

        // Get all teams with proper query
        $teams = get_posts([
            'post_type' => 'team',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ]);

        ?>
        <style>
            .game-meta-box {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                padding: 15px;
            }
            .team-section {
                background: #f9f9f9;
                padding: 15px;
                border-radius: 5px;
                border: 1px solid #ddd;
            }
            .team-section label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }
            .team-section select,
            .team-section input {
                width: 100%;
                padding: 8px;
                margin-bottom: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .team-section input:focus,
            .team-section select:focus {
                border-color: #2271b1;
                box-shadow: 0 0 0 1px #2271b1;
                outline: none;
            }
            .error-message {
                color: #dc3232;
                font-size: 12px;
                margin-top: 5px;
                display: none;
            }
        </style>

        <div class="game-meta-box">
            <div class="team-section">
                <label for="team1">Team 1:</label>
                <select name="team1" id="team1" required>
                    <option value="">Select Team</option>
                    <?php foreach ($teams as $team): ?>
                        <option value="<?php echo esc_attr($team->ID); ?>" 
                                <?php selected($team1, $team->ID); ?>>
                            <?php echo esc_html($team->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="team1-error" class="error-message">Please select Team 1</div>

                <label for="score1">Score for Team 1:</label>
                <input type="number" 
                       name="score1" 
                       id="score1" 
                       value="<?php echo esc_attr($score1); ?>" 
                       required 
                       min="0"
                       step="1">
                <div id="score1-error" class="error-message">Score must be 0 or greater</div>

                <label for="points1">Points for Team 1:</label>
                <input type="number" 
                       name="points1" 
                       id="points1" 
                       value="<?php echo esc_attr($points1); ?>" 
                       required 
                       step="0.1">
                <div id="points1-error" class="error-message">Points must be a valid number</div>
            </div>

            <div class="team-section">
                <label for="team2">Team 2:</label>
                <select name="team2" id="team2" required>
                    <option value="">Select Team</option>
                    <?php foreach ($teams as $team): ?>
                        <option value="<?php echo esc_attr($team->ID); ?>" 
                                <?php selected($team2, $team->ID); ?>>
                            <?php echo esc_html($team->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="team2-error" class="error-message">Please select Team 2</div>

                <label for="score2">Score for Team 2:</label>
                <input type="number" 
                       name="score2" 
                       id="score2" 
                       value="<?php echo esc_attr($score2); ?>" 
                       required 
                       min="0"
                       step="1">
                <div id="score2-error" class="error-message">Score must be 0 or greater</div>

                <label for="points2">Points for Team 2:</label>
                <input type="number" 
                       name="points2" 
                       id="points2" 
                       value="<?php echo esc_attr($points2); ?>" 
                       required 
                       step="0.1">
                <div id="points2-error" class="error-message">Points must be a valid number</div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let formChanged = false;

            // Function to validate form
            function validateGameForm() {
                let isValid = true;
                const team1 = $('#team1').val();
                const team2 = $('#team2').val();
                const score1 = $('#score1').val();
                const score2 = $('#score2').val();
                const points1 = $('#points1').val();
                const points2 = $('#points2').val();

                // Reset error messages
                $('.error-message').hide();

                // Validate teams
                if (!team1) {
                    $('#team1-error').show();
                    isValid = false;
                }
                if (!team2) {
                    $('#team2-error').show();
                    isValid = false;
                }
                if (team1 && team2 && team1 === team2) {
                    $('#team2-error').text('Teams must be different').show();
                    isValid = false;
                }

                // Validate scores
                if (score1 < 0 || !Number.isInteger(Number(score1))) {
                    $('#score1-error').show();
                    isValid = false;
                }
                if (score2 < 0 || !Number.isInteger(Number(score2))) {
                    $('#score2-error').show();
                    isValid = false;
                }

                // Validate points
                if (isNaN(points1) || points1 === '') {
                    $('#points1-error').show();
                    isValid = false;
                }
                if (isNaN(points2) || points2 === '') {
                    $('#points2-error').show();
                    isValid = false;
                }

                return isValid;
            }

            // Prevent selecting same team
            $('#team1, #team2').on('change', function() {
                formChanged = true;
                validateGameForm();
            });

            // Validate on score/points change
            $('#score1, #score2, #points1, #points2').on('input', function() {
                formChanged = true;
                validateGameForm();
            });

            // Warn about unsaved changes
            $(window).on('beforeunload', function() {
                if (formChanged) {
                    return 'You have unsaved changes. Are you sure you want to leave?';
                }
            });

            // Clear warning when saving
            $('form').on('submit', function() {
                if (validateGameForm()) {
                    $(window).off('beforeunload');
                } else {
                    return false;
                }
            });
        });
        </script>
        <?php
    }
}

// Save game meta data with simplified handling
if (!function_exists('save_game_meta_box_data')) {
    function save_game_meta_box_data($post_id) {
        // Skip autosave and basic security
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!isset($_POST['game_meta_box_nonce'])) return;
        if (!wp_verify_nonce($_POST['game_meta_box_nonce'], 'game_meta_box')) return;
        if (!current_user_can('edit_post', $post_id)) return;

        // Get team IDs
        $team1_id = isset($_POST['team1']) ? absint($_POST['team1']) : 0;
        $team2_id = isset($_POST['team2']) ? absint($_POST['team2']) : 0;

        // Validate teams
        if (!$team1_id || !$team2_id || $team1_id === $team2_id) return;

        // Save meta data
        $meta_data = array(
            '_team1' => $team1_id,
            '_team2' => $team2_id,
            '_score1' => isset($_POST['score1']) ? max(0, absint($_POST['score1'])) : 0,
            '_score2' => isset($_POST['score2']) ? max(0, absint($_POST['score2'])) : 0,
            '_points1' => isset($_POST['points1']) ? floatval($_POST['points1']) : 0,
            '_points2' => isset($_POST['points2']) ? floatval($_POST['points2']) : 0,
            '_last_modified' => '2025-02-13 18:50:36',
            '_last_modified_by' => 'EvThatGuy'
        );

        foreach ($meta_data as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }

        // Set title only once for new posts
        $current_title = get_the_title($post_id);
        if (empty($current_title) || $current_title === 'Auto Draft') {
            $team1_name = get_the_title($team1_id);
            $team2_name = get_the_title($team2_id);
            $game_number = wp_count_posts('game')->publish + 1;
            
            $title = sprintf(
                '%s vs %s - Game %d (%s)',
                $team1_name,
                $team2_name,
                $game_number,
                date('Y-m-d')
            );

            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $title,
                'post_name' => sanitize_title($title)
            ));
        }

        // Update team points asynchronously
        wp_schedule_single_event(time() + 1, 'update_team_points_async', array($team1_id));
        wp_schedule_single_event(time() + 2, 'update_team_points_async', array($team2_id));
    }
}
add_action('save_post_game', 'save_game_meta_box_data');

// Background update functions
add_action('update_team_points_async', 'update_team_points_background');

function update_team_points_background($team_id) {
    if (!$team_id) return;

    try {
        // Calculate points
        $manual_points = floatval(get_post_meta($team_id, '_manual_points', true)) ?: 0;
        $game_points = calculate_points_from_games($team_id);
        $total_points = $manual_points + $game_points;

        // Update points
        update_post_meta($team_id, '_total_points', $total_points);

        // Update division standings in the background
        $division_terms = wp_get_post_terms($team_id, 'division');
        if (!empty($division_terms) && !is_wp_error($division_terms)) {
            wp_schedule_single_event(
                time(), 
                'update_division_standings_async', 
                [$division_terms[0]->slug]
            );
        }
    } catch (Exception $e) {
        error_log('Team points background update error: ' . $e->getMessage());
    }
}

add_action('update_division_standings_async', 'update_division_standings_background');

function update_division_standings_background($division_slug) {
    if (!$division_slug) return;

    try {
        // Clear relevant caches
        $cache_key = 'division_rankings_' . $division_slug;
        wp_cache_delete($cache_key);
        wp_cache_delete('team_standings_display');
        wp_cache_delete('standings_api_data');
    } catch (Exception $e) {
        error_log('Division standings background update error: ' . $e->getMessage());
    }
}
/**
 * Part 4: Points Calculation System
 * Last updated by EvThatGuy on 2025-02-05 03:39:45
 */

// Core points calculation function
if (!function_exists('update_team_standings')) {
    function update_team_standings($team_id) {
        if (!$team_id) {
            return false;
        }

        try {
            // Get all points with error handling
            $manual_points = floatval(get_post_meta($team_id, '_manual_points', true)) ?: 0;
            $game_points = calculate_points_from_games($team_id);
            
            if ($game_points === false) {
                throw new Exception('Error calculating game points');
            }

            $total_points = $manual_points + $game_points;

            // Update total points with validation
            if ($total_points < 0) {
                $total_points = 0; // Ensure points don't go negative
            }
            
            update_post_meta($team_id, '_total_points', $total_points);

            // Get and update team's division standings
            $division_terms = wp_get_post_terms($team_id, 'division');
            if (!empty($division_terms) && !is_wp_error($division_terms)) {
                foreach ($division_terms as $division) {
                    update_division_standings($division->slug);
                }
            }

            // Log the update for tracking
            $update_log = array(
                'timestamp' => current_time('mysql'),
                'manual_points' => $manual_points,
                'game_points' => $game_points,
                'total_points' => $total_points
            );
            update_post_meta($team_id, '_points_update_log', $update_log);

            return true;
        } catch (Exception $e) {
            error_log('Team standings update error: ' . $e->getMessage());
            return false;
        }
    }
}

// Calculate points from games with improved error handling
if (!function_exists('calculate_points_from_games')) {
    function calculate_points_from_games($team_id) {
        if (!$team_id) {
            return false;
        }

        try {
            $points = 0;
            $games_cache_key = 'team_games_' . $team_id;
            $games = wp_cache_get($games_cache_key);

            if (false === $games) {
                $games = get_posts([
                    'post_type' => 'game',
                    'posts_per_page' => -1,
                    'meta_query' => [
                        'relation' => 'OR',
                        [
                            'key' => '_team1',
                            'value' => $team_id,
                            'compare' => '=',
                        ],
                        [
                            'key' => '_team2',
                            'value' => $team_id,
                            'compare' => '=',
                        ],
                    ],
                    'fields' => 'ids', // Only get IDs for better performance
                ]);
                
                wp_cache_set($games_cache_key, $games, '', HOUR_IN_SECONDS);
            }

            foreach ($games as $game_id) {
                $team1 = get_post_meta($game_id, '_team1', true);
                $team2 = get_post_meta($game_id, '_team2', true);
                $points1 = floatval(get_post_meta($game_id, '_points1', true));
                $points2 = floatval(get_post_meta($game_id, '_points2', true));

                if ($team1 == $team_id) {
                    $points += $points1;
                } elseif ($team2 == $team_id) {
                    $points += $points2;
                }
            }

            return $points;
        } catch (Exception $e) {
            error_log('Points calculation error: ' . $e->getMessage());
            return false;
        }
    }
}

// Update Team Points with transaction-like behavior
if (!function_exists('update_team_points')) {
    function update_team_points($team_id, $points_from_game) {
        global $wpdb;
        
        if (!$team_id) {
            return false;
        }

        try {
            // Start transaction-like behavior
            $wpdb->query('START TRANSACTION');

            $manual_points = floatval(get_post_meta($team_id, '_manual_points', true)) ?: 0;
            $calculated_points = calculate_points_from_games($team_id);
            
            if ($calculated_points === false) {
                throw new Exception('Error calculating points');
            }

            $total_points = $manual_points + $calculated_points;

            // Update with validation
            if ($total_points < 0) {
                $total_points = 0;
            }

            $updated = update_post_meta($team_id, '_total_points', $total_points);
            
            if ($updated === false) {
                throw new Exception('Failed to update total points');
            }

            // Update division standings
            $division_terms = wp_get_post_terms($team_id, 'division');
            if (!empty($division_terms) && !is_wp_error($division_terms)) {
                $division_slug = $division_terms[0]->slug;
                update_division_standings($division_slug);
            }

            $wpdb->query('COMMIT');
            return true;

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log('Team points update error: ' . $e->getMessage());
            return false;
        }
    }
}

// Recalculation System with backup
if (!function_exists('recalculate_all_points')) {
    function recalculate_all_points() {
        try {
            // Backup current points
            $backup = array();
            $teams = get_posts([
                'post_type' => 'team',
                'posts_per_page' => -1,
                'fields' => 'ids',
            ]);

            foreach ($teams as $team_id) {
                $backup[$team_id] = [
                    'total_points' => get_post_meta($team_id, '_total_points', true) ?: 0,
                    'manual_points' => get_post_meta($team_id, '_manual_points', true) ?: 0,
                    'timestamp' => current_time('mysql')
                ];
            }

            // Store backup with version control
            $backup_version = time();
            update_option('team_points_backup_' . $backup_version, $backup);
            update_option('latest_points_backup_version', $backup_version);

            // Reset and recalculate points
            foreach ($teams as $team_id) {
                $result = update_team_standings($team_id);
                if (!$result) {
                    throw new Exception('Failed to update standings for team ' . $team_id);
                }
            }

            return $backup_version;

        } catch (Exception $e) {
            error_log('Points recalculation error: ' . $e->getMessage());
            return false;
        }
    }
}

// Undo Recalculation with version control
if (!function_exists('undo_recalculate_points')) {
    function undo_recalculate_points($version = null) {
        try {
            if (!$version) {
                $version = get_option('latest_points_backup_version');
            }

            if (!$version) {
                throw new Exception('No backup version found');
            }

            $backup = get_option('team_points_backup_' . $version);
            if (empty($backup)) {
                throw new Exception('Backup not found');
            }

            foreach ($backup as $team_id => $points) {
                update_post_meta($team_id, '_total_points', $points['total_points']);
                update_post_meta($team_id, '_manual_points', $points['manual_points']);
            }

            return true;

        } catch (Exception $e) {
            error_log('Undo recalculation error: ' . $e->getMessage());
            return false;
        }
    }
}

// Update division standings with cache management
if (!function_exists('update_division_standings')) {
    function update_division_standings($division_slug) {
        if (!$division_slug) {
            return false;
        }

        try {
            $cache_key = 'division_teams_' . $division_slug;
            $teams = wp_cache_get($cache_key);

            if (false === $teams) {
                $teams = get_posts([
                    'post_type' => 'team',
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                    'tax_query' => [
                        [
                            'taxonomy' => 'division',
                            'field' => 'slug',
                            'terms' => $division_slug,
                        ],
                    ],
                ]);

                wp_cache_set($cache_key, $teams, '', HOUR_IN_SECONDS);
            }

            foreach ($teams as $team_id) {
                update_team_standings($team_id);
            }

            return true;

        } catch (Exception $e) {
            error_log('Division standings update error: ' . $e->getMessage());
            return false;
        }
    }
}
/**
 * Part 5: Standings Display and Admin Interface
 * Last updated by EvThatGuy on 2025-02-05 03:56:45
 */

// Helper function to get division rankings - sorted by points
function get_division_rankings() {
    $cache_key = 'division_rankings';
    $rankings = wp_cache_get($cache_key);

    if (false === $rankings) {
        $divisions = get_terms([
            'taxonomy' => 'division',
            'hide_empty' => false // Show all divisions
        ]);

        $rankings = [];

        foreach ($divisions as $division) {
            // Get ALL teams in this division
            $teams = get_posts([
                'post_type' => 'team',
                'posts_per_page' => -1,
                'tax_query' => [
                    [
                        'taxonomy' => 'division',
                        'field' => 'term_id',
                        'terms' => $division->term_id
                    ]
                ]
            ]);

            // Create array of teams with their points for sorting
            $division_teams = [];
            foreach ($teams as $team) {
                $manual_points = floatval(get_post_meta($team->ID, '_manual_points', true)) ?: 0;
                $game_points = calculate_points_from_games($team->ID);
                $total_points = $manual_points + $game_points;

                $division_teams[] = [
                    'team_id' => $team->ID,
                    'total_points' => $total_points,
                    'name' => $team->post_title // For tie-breaking
                ];
            }

            // Sort teams by points (highest to lowest) and then alphabetically
            usort($division_teams, function($a, $b) {
                if ($a['total_points'] !== $b['total_points']) {
                    return $b['total_points'] - $a['total_points'];
                }
                return strcasecmp($a['name'], $b['name']); // Alphabetical if points are tied
            });

            // Assign rankings
            $rank = 1;
            foreach ($division_teams as $team) {
                $rankings[$division->slug][$team['team_id']] = $rank++;
            }
        }

        wp_cache_set($cache_key, $rankings, '', 300);
    }

    return $rankings;
}

// Update the beginning of your display_team_standings function to this:
function display_team_standings() {
    $cache_key = 'team_standings_display';
    $output = wp_cache_get($cache_key);

    if (false === $output) {
        // Get all divisions
        $divisions = get_terms([
            'taxonomy' => 'division',
            'hide_empty' => false // Show all divisions
        ]);

        if (is_wp_error($divisions)) {
            error_log('Error fetching divisions: ' . $divisions->get_error_message());
            return 'Error loading standings.';
        }

        // Get division rankings
        $division_rankings = get_division_rankings();

       // Get ALL teams without any filtering or point requirements
$teams = get_posts([
    'post_type' => 'team',
    'posts_per_page' => -1,  // Get all teams
    'orderby' => 'title',    // Initial sort by title
    'order' => 'ASC',        // Alphabetical order
    'nopaging' => true       // Ensure we get ALL teams
]);

// Ensure all teams have point values (even if 0)
foreach ($teams as $team) {
    $team_id = $team->ID;
    
    // Get existing points or default to 0
    $manual_points = get_post_meta($team_id, '_manual_points', true);
    $total_points = get_post_meta($team_id, '_total_points', true);
    
    // If points don't exist, initialize them to 0
    if ($manual_points === '') {
        update_post_meta($team_id, '_manual_points', 0);
    }
    if ($total_points === '') {
        update_post_meta($team_id, '_total_points', 0);
    }
}

        // Prepare team data with error handling
        $team_data = [];
        foreach ($teams as $team) {
            try {
                $team_id = $team->ID;
                $manual_points = floatval(get_post_meta($team_id, '_manual_points', true)) ?: 0;
                $game_points = calculate_points_from_games($team_id);
                $total_points = $manual_points + $game_points;
                
                // Get division information
                $division_terms = wp_get_post_terms($team_id, 'division');
                $division_slug = !empty($division_terms) ? $division_terms[0]->slug : 'unassigned';
                $division_name = !empty($division_terms) ? $division_terms[0]->name : 'Unassigned';

                $team_data[] = [
                    'id' => $team_id,
                    'name' => $team->post_title,
                    'manual_points' => $manual_points,
                    'game_points' => $game_points,
                    'total_points' => $total_points,
                    'division_slug' => $division_slug,
                    'division_name' => $division_name,
                    'logo' => get_the_post_thumbnail($team_id, 'thumbnail', ['class' => 'team-logo-img']),
                    'games_played' => count_team_games($team_id),
                ];
            } catch (Exception $e) {
                error_log('Error processing team data: ' . $e->getMessage());
                continue;
            }
        }

        // Sort teams by total points first, then by name
        usort($team_data, function($a, $b) {
            $points_diff = $b['total_points'] - $a['total_points'];
            if ($points_diff == 0) {
                return strcasecmp($a['name'], $b['name']);
            }
            return $points_diff;
        });

        ob_start();
        ?>
        <div class="team-standings-container">
            <!-- Division Selector -->
            <div class="division-selector-container">
                <select id="division-selector" class="division-dropdown">
                    <option value="all">All Divisions</option>
                    <?php foreach ($divisions as $division): ?>
                        <option value="<?php echo esc_attr($division->slug); ?>">
                            <?php echo esc_html($division->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Standings Cards with Loading State -->
            <div id="standings-loading" style="display: none;">
                <div class="loading-spinner"></div>
            </div>
            
            <div class="standings-cards">
                <?php 
                $rank = 1;
                foreach ($team_data as $team): 
                    $logo = $team['logo'] ?: '<img src="' . plugins_url('assets/default-logo.png', __FILE__) . '" class="team-logo-img" alt="Default Logo">';
                ?>
                    <div class="team-card" data-division="<?php echo esc_attr($team['division_slug']); ?>">
                        <div class="rank-badges">
                            <div class="overall-rank" title="Overall Rank">#<?php echo $rank; ?></div>
                            <div class="division-rank" title="Division Rank">
                                Div: #<?php echo isset($division_rankings[$team['division_slug']][$team['id']]) ? 
                                    $division_rankings[$team['division_slug']][$team['id']] : '-'; ?>
                            </div>
                        </div>
                        <div class="team-logo"><?php echo $logo; ?></div>
                        <div class="team-info">
                            <h3 class="team-name"><?php echo esc_html($team['name']); ?></h3>
                            <div class="team-division"><?php echo esc_html($team['division_name']); ?></div>
                            <div class="team-stats">
                                <span class="total-points"><?php echo number_format($team['total_points'], 1); ?> pts</span>
                                <span class="games-played"><?php echo $team['games_played']; ?> games</span>
                            </div>
                        </div>
                    </div>
                <?php 
                    $rank++;
                endforeach; 
                ?>
            </div>
        </div>

        <style>
            .team-standings-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }

            .division-selector-container {
                text-align: center;
                margin-bottom: 30px;
            }

            .division-dropdown {
                padding: 10px 20px;
                font-size: 16px;
                border: 1px solid #ddd;
                border-radius: 4px;
                background-color: #fff;
                cursor: pointer;
                min-width: 200px;
                max-width: 100%;
            }

            .standings-cards {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
                padding: 10px;
            }

            .team-card {
                position: relative;
                background: #fff;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                padding: 20px;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .team-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            }

            .loading-spinner {
                border: 4px solid #f3f3f3;
                border-radius: 50%;
                border-top: 4px solid #3498db;
                width: 40px;
                height: 40px;
                animation: spin 1s linear infinite;
                margin: 20px auto;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            .rank-badges {
                position: absolute;
                top: 10px;
                left: 10px;
                display: flex;
                gap: 5px;
            }

            .overall-rank, .division-rank {
                background: #b40c4c;
                color: white;
                padding: 5px 8px;
                border-radius: 15px;
                font-weight: bold;
                font-size: 0.9em;
            }

            .division-rank {
                background: #2271b1;
            }

            .team-logo {
                margin-bottom: 15px;
            }

            .team-logo-img {
                width: 100px;
                height: 100px;
                object-fit: contain;
                border-radius: 50%;
                background: #f8f8f8;
            }

            .team-info {
                text-align: center;
            }

            .team-name {
                margin: 0 0 5px 0;
                color: #333;
                font-size: 1.2em;
            }

            .team-division {
                color: #666;
                font-size: 0.9em;
                margin-bottom: 10px;
            }

            .team-stats {
                display: flex;
                justify-content: center;
                gap: 15px;
                margin-top: 10px;
            }

            .total-points {
                color: #b40c4c;
                font-weight: bold;
                font-size: 1.1em;
            }

            .games-played {
                color: #666;
                font-size: 0.9em;
            }

            @media (max-width: 768px) {
                .standings-cards {
                    grid-template-columns: 1fr;
                }
                
                .team-card {
                    margin: 10px 0;
                }
            }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Show loading state
            function showLoading() {
                $('#standings-loading').show();
                $('.standings-cards').addClass('loading');
            }

            // Hide loading state
            function hideLoading() {
                $('#standings-loading').hide();
                $('.standings-cards').removeClass('loading');
            }

            // Division selector functionality with loading states
            $('#division-selector').change(function() {
                showLoading();
                const selectedDivision = $(this).val();
                
                setTimeout(function() {
                    $('.team-card').each(function() {
                        if (selectedDivision === 'all' || $(this).data('division') === selectedDivision) {
                            $(this).fadeIn(300);
                        } else {
                            $(this).fadeOut(300);
                        }
                    });
                    hideLoading();
                }, 300);
            });

            // Initialize tooltips if available
            if (typeof tippy !== 'undefined') {
                tippy('.team-card', {
                    content: element => {
                        const name = $(element).find('.team-name').text();
                        const points = $(element).find('.total-points').text();
                        return `${name}<br>${points}`;
                    },
                    allowHTML: true,
                    placement: 'top',
                });
            }

            // Refresh standings periodically
            setInterval(function() {
                $.post(ajaxurl, {
                    action: 'refresh_standings'
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    }
                });
            }, 300000); // Refresh every 5 minutes
        });
        </script>
        <?php
        $output = ob_get_clean();
        
        // Cache the output for 5 minutes
        wp_cache_set($cache_key, $output, '', 300);
    }

    return $output;
}
add_shortcode('team_standings', 'display_team_standings');
// Add admin menu page for manual points entry under Games menu
function add_team_points_admin_page() {
    add_submenu_page(
        'edit.php?post_type=game', // Parent slug (Games menu)
        'Team Points Manager',      // Page title
        'Team Points',             // Menu title
        'manage_options',          // Capability required
        'team-points-manager',     // Menu slug
        'render_team_points_manager_page' // Callback function
    );
}
add_action('admin_menu', 'add_team_points_admin_page');

// Render the points manager admin page
function render_team_points_manager_page() {
    // Process form submission
    if (isset($_POST['save_points']) && check_admin_referer('save_team_points')) {
        foreach ($_POST['manual_points'] as $team_id => $points) {
            $team_id = intval($team_id);
            $manual_points = floatval($points);
            
            // Save manual points
            update_post_meta($team_id, '_manual_points', $manual_points);
            
            // Update total points
            $game_points = calculate_points_from_games($team_id);
            $total_points = $manual_points + $game_points;
            update_post_meta($team_id, '_total_points', $total_points);

            // Log the update
            $log_entry = array(
                'timestamp' => current_time('mysql'),
                'user' => wp_get_current_user()->user_login,
                'manual_points' => $manual_points,
                'total_points' => $total_points
            );
            update_post_meta($team_id, '_points_update_log', $log_entry);
        }
        echo '<div class="notice notice-success"><p>Points updated successfully!</p></div>';
    }

    // Get all divisions
    $divisions = get_terms([
        'taxonomy' => 'division',
        'hide_empty' => false
    ]);

    ?>
    <div class="wrap">
        <h1>Team Points Manager</h1>
        <p>Manage manual points for all teams. Points from games will be automatically added to these manual points.</p>

        <form method="post" action="">
            <?php wp_nonce_field('save_team_points'); ?>
            
            <div class="tablenav top">
                <div class="alignleft actions">
                    <select id="division-filter">
                        <option value="">All Divisions</option>
                        <?php foreach ($divisions as $division): ?>
                            <option value="<?php echo esc_attr($division->slug); ?>">
                                <?php echo esc_html($division->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Team</th>
                        <th>Division</th>
                        <th>Manual Points</th>
                        <th>Game Points</th>
                        <th>Total Points</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $teams = get_posts([
                        'post_type' => 'team',
                        'posts_per_page' => -1,
                        'orderby' => 'title',
                        'order' => 'ASC'
                    ]);

                    foreach ($teams as $team):
                        $team_id = $team->ID;
                        $manual_points = floatval(get_post_meta($team_id, '_manual_points', true));
                        $game_points = calculate_points_from_games($team_id);
                        $total_points = $manual_points + $game_points;
                        $division_terms = wp_get_post_terms($team_id, 'division');
                        $division = !empty($division_terms) ? $division_terms[0]->name : 'Unassigned';
                        $division_slug = !empty($division_terms) ? $division_terms[0]->slug : '';
                        $log = get_post_meta($team_id, '_points_update_log', true);
                        $last_updated = isset($log['timestamp']) ? $log['timestamp'] : 'Never';
                    ?>
                        <tr class="team-row" data-division="<?php echo esc_attr($division_slug); ?>">
                            <td><?php echo esc_html($team->post_title); ?></td>
                            <td><?php echo esc_html($division); ?></td>
                            <td>
                                <input type="number" 
                                       name="manual_points[<?php echo $team_id; ?>]" 
                                       value="<?php echo esc_attr($manual_points); ?>" 
                                       step="0.1" 
                                       class="small-text">
                            </td>
                            <td><?php echo number_format($game_points, 1); ?></td>
                            <td><?php echo number_format($total_points, 1); ?></td>
                            <td><?php echo esc_html($last_updated); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p class="submit">
                <input type="submit" name="save_points" class="button button-primary" value="Save All Points">
            </p>
        </form>
    </div>

    <style>
        .team-row input[type="number"] {
            width: 80px;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        $('#division-filter').change(function() {
            var division = $(this).val();
            if (division) {
                $('.team-row').hide();
                $('.team-row[data-division="' + division + '"]').show();
            } else {
                $('.team-row').show();
            }
        });
    });
    </script>
    <?php
}
// Add custom columns to games admin screen
function add_game_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['game_teams'] = 'Teams';
            $new_columns['game_score'] = 'Score';
            $new_columns['game_points'] = 'Points';
            $new_columns['game_date'] = 'Game Date';
        }
    }
    return $new_columns;
}
add_filter('manage_game_posts_columns', 'add_game_columns');

// Fill custom columns with game data
function fill_game_columns($column, $post_id) {
    switch ($column) {
        case 'game_teams':
            $team1_id = get_post_meta($post_id, '_team1', true);
            $team2_id = get_post_meta($post_id, '_team2', true);
            
            $team1_name = $team1_id ? get_the_title($team1_id) : 'TBD';
            $team2_name = $team2_id ? get_the_title($team2_id) : 'TBD';
            
            echo esc_html($team1_name . ' vs ' . $team2_name);
            break;
            
        case 'game_score':
            $score1 = get_post_meta($post_id, '_score1', true);
            $score2 = get_post_meta($post_id, '_score2', true);
            
            if ($score1 !== '' && $score2 !== '') {
                echo esc_html($score1 . ' - ' . $score2);
            } else {
                echo '- vs -';
            }
            break;
            
        case 'game_points':
            $points1 = get_post_meta($post_id, '_points1', true);
            $points2 = get_post_meta($post_id, '_points2', true);
            
            if ($points1 !== '' && $points2 !== '') {
                echo esc_html($points1 . ' | ' . $points2);
            } else {
                echo '-';
            }
            break;
            
        case 'game_date':
            $game_date = get_post_meta($post_id, '_game_date', true);
            echo $game_date ? esc_html(date('Y-m-d', strtotime($game_date))) : '-';
            break;
    }
}
add_action('manage_game_posts_custom_column', 'fill_game_columns', 10, 2);

// Make columns sortable
function make_game_columns_sortable($columns) {
    $columns['game_date'] = 'game_date';
    return $columns;
}
add_filter('manage_edit-game_sortable_columns', 'make_game_columns_sortable');

// Handle custom column sorting
function game_custom_orderby($query) {
    if (!is_admin()) return;
    
    $orderby = $query->get('orderby');
    
    if ('game_date' === $orderby) {
        $query->set('meta_key', '_game_date');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'game_custom_orderby');
// AJAX handler for standings refresh
add_action('wp_ajax_refresh_standings', 'refresh_standings_ajax');
add_action('wp_ajax_nopriv_refresh_standings', 'refresh_standings_ajax');

function refresh_standings_ajax() {
    // Clear standings cache
    wp_cache_delete('team_standings_display');
    wp_send_json_success();
}

// Helper function to count team games with caching
function count_team_games($team_id) {
    $cache_key = 'team_games_count_' . $team_id;
    $count = wp_cache_get($cache_key);

    if (false === $count) {
        $games = get_posts([
            'post_type' => 'game',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => '_team1',
                    'value' => $team_id,
                    'compare' => '=',
                ],
                [
                    'key' => '_team2',
                    'value' => $team_id,
                    'compare' => '=',
                ],
            ],
        ]);
        
        $count = count($games);
        wp_cache_set($cache_key, $count, '', HOUR_IN_SECONDS);
    }
    
    return $count;
}
/**
 * Part 6: Admin Settings and REST API Integration
 * Last updated by EvThatGuy on 2025-02-05 03:42:05
 */

// Register REST API endpoints for standings
add_action('rest_api_init', function() {
    register_rest_route('team-standings/v1', '/standings', [
        'methods' => 'GET',
        'callback' => 'get_standings_data',
        'permission_callback' => function() {
            return true; // Public endpoint
        }
    ]);

    // Protected endpoints
    register_rest_route('team-standings/v1', '/update-points', [
        'methods' => 'POST',
        'callback' => 'update_team_points_api',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        }
    ]);
});

// Standings data endpoint
function get_standings_data(WP_REST_Request $request) {
    try {
        // Clear output buffer
        ob_clean();
        
        $cache_key = 'standings_api_data';
        $standings = wp_cache_get($cache_key);

        if (false === $standings) {
            $teams = get_posts([
                'post_type' => 'team',
                'posts_per_page' => -1,
                'orderby' => 'meta_value_num',
                'meta_key' => '_total_points',
                'order' => 'DESC'
            ]);

            $standings = [];
            foreach ($teams as $team) {
                $team_id = $team->ID;
                $division_terms = wp_get_post_terms($team_id, 'division');
                
                $standings[] = [
                    'id' => $team_id,
                    'name' => $team->post_title,
                    'points' => floatval(get_post_meta($team_id, '_total_points', true)),
                    'division' => !empty($division_terms) ? $division_terms[0]->name : 'Unassigned',
                    'games_played' => count_team_games($team_id),
                    'logo_url' => get_the_post_thumbnail_url($team_id, 'thumbnail'),
                ];
            }

            wp_cache_set($cache_key, $standings, '', 300); // Cache for 5 minutes
        }

        return new WP_REST_Response($standings, 200);
    } catch (Exception $e) {
        return new WP_Error('standings_error', $e->getMessage(), ['status' => 500]);
    }
}

// Protected endpoint for updating points
function update_team_points_api(WP_REST_Request $request) {
    try {
        $team_id = $request->get_param('team_id');
        $points = $request->get_param('points');

        if (!$team_id || !is_numeric($points)) {
            return new WP_Error('invalid_params', 'Invalid parameters', ['status' => 400]);
        }

        $result = update_team_points($team_id, floatval($points));
        
        if ($result) {
            return new WP_REST_Response(['success' => true], 200);
        } else {
            throw new Exception('Failed to update points');
        }
    } catch (Exception $e) {
        return new WP_Error('update_error', $e->getMessage(), ['status' => 500]);
    }
}

// Admin Settings Page
add_action('admin_menu', 'register_team_management_settings');

function register_team_management_settings() {
    add_submenu_page(
        'edit.php?post_type=team',
        'Team Management Settings',
        'Settings',
        'manage_options',
        'team-management-settings',
        'render_team_management_settings'
    );
}

function render_team_management_settings() {
    // Save settings
    if (isset($_POST['team_management_settings_nonce']) && 
        wp_verify_nonce($_POST['team_management_settings_nonce'], 'team_management_settings')) {
        
        $settings = [
            'points_decimal_places' => absint($_POST['points_decimal_places'] ?? 1),
            'auto_recalculate' => isset($_POST['auto_recalculate']),
            'cache_duration' => absint($_POST['cache_duration'] ?? 300),
            'show_division_filter' => isset($_POST['show_division_filter']),
            'enable_api' => isset($_POST['enable_api']),
        ];

        update_option('team_management_settings', $settings);
        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }

    // Get current settings
    $settings = get_option('team_management_settings', [
        'points_decimal_places' => 1,
        'auto_recalculate' => true,
        'cache_duration' => 300,
        'show_division_filter' => true,
        'enable_api' => false,
    ]);
    ?>
    <div class="wrap">
        <h1>Team Management Settings</h1>

        <form method="post" action="">
            <?php wp_nonce_field('team_management_settings', 'team_management_settings_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">Points Decimal Places</th>
                    <td>
                        <input type="number" 
                               name="points_decimal_places" 
                               value="<?php echo esc_attr($settings['points_decimal_places']); ?>"
                               min="0" 
                               max="2">
                        <p class="description">Number of decimal places to display for points (0-2)</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Auto Recalculate</th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   name="auto_recalculate"
                                   <?php checked($settings['auto_recalculate']); ?>>
                            Automatically recalculate standings after games
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Cache Duration</th>
                    <td>
                        <input type="number" 
                               name="cache_duration" 
                               value="<?php echo esc_attr($settings['cache_duration']); ?>"
                               min="60" 
                               step="60">
                        <p class="description">Cache duration in seconds (minimum 60)</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Show Division Filter</th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   name="show_division_filter"
                                   <?php checked($settings['show_division_filter']); ?>>
                            Show division filter in standings display
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Enable API</th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   name="enable_api"
                                   <?php checked($settings['enable_api']); ?>>
                            Enable REST API endpoints
                        </label>
                        <p class="description">Allows external access to standings data via REST API</p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" 
                       class="button button-primary" 
                       value="Save Settings">
                <button type="button" 
                        class="button" 
                        onclick="if(confirm('Clear all caches?')) location.href='<?php echo wp_nonce_url(admin_url('admin-post.php?action=clear_team_caches'), 'clear_team_caches'); ?>'">
                    Clear Caches
                </button>
            </p>
        </form>

        <div class="card">
            <h2>API Documentation</h2>
            <p>Access standings data via the following endpoints:</p>
            <code>GET /wp-json/team-standings/v1/standings</code>
            <p>Protected endpoints (requires authentication):</p>
            <code>POST /wp-json/team-standings/v1/update-points</code>
        </div>
    </div>
    <?php
}

// Handle cache clearing
add_action('admin_post_clear_team_caches', 'clear_team_management_caches');

function clear_team_management_caches() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'clear_team_caches')) {
        wp_die('Invalid nonce');
    }

    // Clear all related caches
    wp_cache_delete('standings_api_data');
    wp_cache_delete('team_standings_display');
    
    // Clear transients
    delete_transient('team_list_for_games');

    // Redirect back with message
    wp_redirect(add_query_arg('message', 'caches-cleared', wp_get_referer()));
    exit;
}

// Add success message
add_action('admin_notices', 'team_management_admin_notices');

function team_management_admin_notices() {
    if (isset($_GET['message']) && $_GET['message'] === 'caches-cleared') {
        echo '<div class="notice notice-success"><p>All caches have been cleared successfully!</p></div>';
    }
}
/**
 * Part 8 (Simplified): Team Statistics
 * Last updated by EvThatGuy on 2025-02-05 03:46:42
 */

// Calculate team statistics - simplified version
function calculate_team_statistics($team_id) {
    $cache_key = 'team_stats_' . $team_id;
    $stats = wp_cache_get($cache_key);

    if (false === $stats) {
        $games = get_posts([
            'post_type' => 'game',
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => '_team1',
                    'value' => $team_id,
                    'compare' => '=',
                ],
                [
                    'key' => '_team2',
                    'value' => $team_id,
                    'compare' => '=',
                ],
            ],
        ]);

        $stats = [
            'total_games' => 0,
            'wins' => 0,
            'losses' => 0,
            'last_five' => [],
            'total_points' => floatval(get_post_meta($team_id, '_total_points', true))
        ];

        foreach ($games as $game) {
            $team1 = get_post_meta($game->ID, '_team1', true);
            $team2 = get_post_meta($game->ID, '_team2', true);
            $score1 = intval(get_post_meta($game->ID, '_score1', true));
            $score2 = intval(get_post_meta($game->ID, '_score2', true));

            if ($team1 == $team_id) {
                $our_score = $score1;
                $their_score = $score2;
            } else {
                $our_score = $score2;
                $their_score = $score1;
            }

            $stats['total_games']++;

            if ($our_score > $their_score) {
                $stats['wins']++;
                $result = 'W';
            } else {
                $stats['losses']++;
                $result = 'L';
            }

            // Add to last five games
            array_unshift($stats['last_five'], $result);
            if (count($stats['last_five']) > 5) {
                array_pop($stats['last_five']);
            }
        }

        wp_cache_set($cache_key, $stats, '', HOUR_IN_SECONDS);
    }

    return $stats;
}

// Add statistics meta box to team display
add_action('add_meta_boxes', 'add_team_statistics_meta_box');

function add_team_statistics_meta_box() {
    add_meta_box(
        'team_statistics',
        'Team Statistics',
        'render_team_statistics',
        'team',
        'side',
        'high'
    );
}

function render_team_statistics($post) {
    $stats = calculate_team_statistics($post->ID);
    ?>
    <style>
        .team-stats-container {
            padding: 10px;
        }
        .stat-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }
        .stat-row:last-child {
            border-bottom: none;
        }
        .stat-label {
            font-weight: bold;
        }
        .last-five {
            display: flex;
            gap: 5px;
        }
        .result-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .result-W { background: #d4edda; color: #155724; }
        .result-L { background: #f8d7da; color: #721c24; }
    </style>
    <div class="team-stats-container">
        <div class="stat-row">
            <span class="stat-label">Total Points:</span>
            <span><?php echo number_format($stats['total_points'], 1); ?></span>
        </div>
        <div class="stat-row">
            <span class="stat-label">Record:</span>
            <span><?php echo "{$stats['wins']}-{$stats['losses']}"; ?></span>
        </div>
        <div class="stat-row">
            <span class="stat-label">Win %:</span>
            <span>
                <?php 
                $win_percentage = $stats['total_games'] ? 
                    round(($stats['wins'] / $stats['total_games']) * 100, 1) : 0;
                echo $win_percentage . '%';
                ?>
            </span>
        </div>
        <div class="stat-row">
            <span class="stat-label">Last 5:</span>
            <div class="last-five">
                <?php foreach ($stats['last_five'] as $result): ?>
                    <span class="result-badge result-<?php echo $result; ?>">
                        <?php echo $result; ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
}

// Add dashboard widget for quick overview
add_action('wp_dashboard_setup', 'add_team_standings_dashboard_widget');

function add_team_standings_dashboard_widget() {
    wp_add_dashboard_widget(
        'team_standings_dashboard_widget',
        'Team Standings Overview',
        'render_team_standings_widget'
    );
}

function render_team_standings_widget() {
    // Get cached widget data
    $cache_key = 'dashboard_standings_widget';
    $widget_data = wp_cache_get($cache_key);

    if (false === $widget_data) {
        // Get top 5 teams
        $teams = get_posts([
            'post_type' => 'team',
            'posts_per_page' => 5,
            'meta_key' => '_total_points',
            'orderby' => 'meta_value_num',
            'order' => 'DESC'
        ]);

        $widget_data = [];
        foreach ($teams as $team) {
            $team_id = $team->ID;
            $stats = calculate_team_statistics($team_id);
            $widget_data[] = [
                'name' => $team->post_title,
                'points' => $stats['total_points'],
                'record' => "{$stats['wins']}-{$stats['losses']}",
                'division' => get_team_division_name($team_id)
            ];
        }

        wp_cache_set($cache_key, $widget_data, '', 300); // Cache for 5 minutes
    }

    ?>
    <style>
        .standings-widget-table {
            width: 100%;
            border-collapse: collapse;
        }
        .standings-widget-table th,
        .standings-widget-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .standings-widget-table tr:last-child td {
            border-bottom: none;
        }
        .standings-widget-points {
            font-weight: bold;
            color: #2271b1;
        }
    </style>
    <table class="standings-widget-table">
        <thead>
            <tr>
                <th>Team</th>
                <th>Division</th>
                <th>Record</th>
                <th>Points</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($widget_data as $team): ?>
                <tr>
                    <td><?php echo esc_html($team['name']); ?></td>
                    <td><?php echo esc_html($team['division']); ?></td>
                    <td><?php echo esc_html($team['record']); ?></td>
                    <td class="standings-widget-points">
                        <?php echo number_format($team['points'], 1); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p class="description">
        <a href="<?php echo admin_url('edit.php?post_type=team'); ?>">
            View all teams →
        </a>
    </p>
    <?php
}
/**
 * Part 9: Frontend Game Display
 * Last updated by EvThatGuy on 2025-02-06 00:47:36
 */

add_shortcode('display_all_games', 'display_all_games_shortcode');

function display_all_games_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => -1,
        'team' => '',
        'division' => '',
        'orderby' => 'date',
        'order' => 'DESC'
    ), $atts);

    $args = array(
        'post_type' => 'game',
        'posts_per_page' => $atts['limit'],
        'orderby' => $atts['orderby'],
        'order' => $atts['order']
    );

    $games = get_posts($args);

    ob_start();
    ?>
    <div class="team-management-games-list">
        <?php if (empty($games)): ?>
            <p class="no-games">No games found.</p>
        <?php else: ?>
            <div class="games-grid">
                <?php foreach ($games as $game): 
                    $team1_id = get_post_meta($game->ID, '_team1', true);
                    $team2_id = get_post_meta($game->ID, '_team2', true);
                    $score1 = get_post_meta($game->ID, '_score1', true);
                    $score2 = get_post_meta($game->ID, '_score2', true);
                    $points1 = get_post_meta($game->ID, '_points1', true);
                    $points2 = get_post_meta($game->ID, '_points2', true);
                ?>
                    <div class="game-card">
                        <div class="game-header">
                            <div class="game-date">
                                <?php echo get_the_date('', $game->ID); ?>
                            </div>
                            <?php 
                            if ($team1_id && $team2_id) {
                                $team1_divisions = wp_get_post_terms($team1_id, 'division');
                                if (!empty($team1_divisions)) {
                                    echo '<div class="game-division">' . esc_html($team1_divisions[0]->name) . '</div>';
                                }
                            }
                            ?>
                        </div>

                        <div class="game-teams">
                            <div class="team team1">
                                <?php if ($team1_id): ?>
                                    <div class="team-name">
                                        <?php echo get_the_title($team1_id); ?>
                                    </div>
                                    <?php if (has_post_thumbnail($team1_id)): ?>
                                        <?php echo get_the_post_thumbnail($team1_id, 'thumbnail', array('class' => 'team-logo')); ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <div class="game-result">
                                <?php if ($score1 !== '' && $score2 !== ''): ?>
                                    <div class="score">
                                        <span><?php echo esc_html($score1); ?></span>
                                        <span class="score-separator">-</span>
                                        <span><?php echo esc_html($score2); ?></span>
                                    </div>
                                    <?php if ($points1 !== '' && $points2 !== ''): ?>
                                        <div class="points">
                                            <span class="points-label">Points:</span>
                                            <div class="points-values">
                                                <span><?php echo esc_html($points1); ?></span>
                                                <span class="points-separator">|</span>
                                                <span><?php echo esc_html($points2); ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="upcoming">vs</div>
                                <?php endif; ?>
                            </div>

                            <div class="team team2">
                                <?php if ($team2_id): ?>
                                    <?php if (has_post_thumbnail($team2_id)): ?>
                                        <?php echo get_the_post_thumbnail($team2_id, 'thumbnail', array('class' => 'team-logo')); ?>
                                    <?php endif; ?>
                                    <div class="team-name">
                                        <?php echo get_the_title($team2_id); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .team-management-games-list {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .games-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 10px;
        }

        .game-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px;
            margin-bottom: 15px;
        }

        .game-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .game-date {
            font-weight: bold;
            color: #666;
        }

        .game-division {
            background: #2271b1;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
        }

        .game-teams {
            display: grid;
            grid-template-columns: 2fr 1fr 2fr;
            gap: 10px;
            align-items: center;
        }

        .team {
            text-align: center;
        }

        .team-logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
            margin: 5px auto;
        }

        .team-name {
            font-weight: bold;
            font-size: 0.9em;
        }

        .game-result {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }

        .score {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .score-separator {
            color: #666;
        }

        .points {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 0.9em;
            color: #666;
        }

        .points-label {
            font-size: 0.8em;
            color: #666;
            margin-bottom: 2px;
        }

        .points-values {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .points-separator {
            color: #666;
        }

        .upcoming {
            font-size: 1.2em;
            color: #666;
        }

        @media (max-width: 768px) {
            .games-grid {
                grid-template-columns: 1fr;
            }
            
            .game-teams {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .game-result {
                order: -1;
                margin: 10px 0;
            }
        }
    </style>
    <?php
    return ob_get_clean();
}
/**
 * Part 10: Helper Functions and Dashboard Widgets
 * Last updated by EvThatGuy on 2025-02-07 02:06:26
 */

function get_team_division_name($team_id) {
    $terms = get_the_terms($team_id, 'division');
    if ($terms && !is_wp_error($terms)) {
        return $terms[0]->name;
    }
    return 'Unassigned';
}
/**
 * Part 10: Helper Functions
 * Last updated by EvThatGuy on 2025-02-07 02:10:05
 */

if (!function_exists('get_team_division_name')) {
    function get_team_division_name($team_id) {
        $terms = get_the_terms($team_id, 'division');
        if ($terms && !is_wp_error($terms)) {
            return $terms[0]->name;
        }
        return 'Unassigned';
    }
}
