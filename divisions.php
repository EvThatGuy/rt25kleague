// Division Ranking and Bonus Points Display System
// Last updated by EvThatGuy on 2025-02-13 22:53:30

// Add division and bonus points display to game meta box
add_action('admin_footer', 'add_bonus_points_display_script');
function add_bonus_points_display_script() {
    $screen = get_current_screen();
    if ($screen->post_type !== 'game') return;
    ?>
    <style>
        .division-display {
            margin-top: 10px;
            padding: 8px;
            background: #e9ecef;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 13px;
            color: #495057;
            font-weight: bold;
            text-align: center;
            pointer-events: none;
            user-select: none;
        }
        .bonus-points-display {
            margin-top: 5px;
            padding: 5px;
            background: #f0f9ff;
            border: 1px solid #cce5ff;
            border-radius: 4px;
            font-size: 12px;
            color: #004085;
        }
        .bonus-points-display.active {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .bonus-points-info {
            margin-top: 5px;
            font-size: 11px;
            color: #6c757d;
            font-style: italic;
        }
    </style>
    <script>
    jQuery(document).ready(function($) {
        // Add division display and bonus points elements
        $('.team-section').each(function() {
            $(this).append('<div class="division-display">Division: Not Selected</div>');
            $(this).append('<div class="bonus-points-display">Eligible Bonus Points: 0</div>');
            $(this).append('<div class="bonus-points-info">Add bonus points manually if eligible</div>');
        });

        function updateDivisionDisplay(teamId, displayElement) {
            if (!teamId) {
                displayElement.text('Division: Not Selected');
                return;
            }

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_team_division',
                    team_id: teamId,
                    nonce: '<?php echo wp_create_nonce("get_team_division_nonce"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        displayElement.text('Division: ' + response.data.division);
                    }
                }
            });
        }

        function updateBonusPoints() {
            const team1 = $('#team1').val();
            const team2 = $('#team2').val();
            const score1 = parseInt($('#score1').val()) || 0;
            const score2 = parseInt($('#score2').val()) || 0;

            // Update division displays
            const team1DivDisplay = $('#team1').closest('.team-section').find('.division-display');
            const team2DivDisplay = $('#team2').closest('.team-section').find('.division-display');
            updateDivisionDisplay(team1, team1DivDisplay);
            updateDivisionDisplay(team2, team2DivDisplay);

            if (team1 && team2) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'calculate_bonus_points',
                        team1: team1,
                        team2: team2,
                        score1: score1,
                        score2: score2,
                        nonce: '<?php echo wp_create_nonce("calculate_bonus_points_nonce"); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            const team1Display = $('#team1').closest('.team-section').find('.bonus-points-display');
                            const team2Display = $('#team2').closest('.team-section').find('.bonus-points-display');
                            
                            team1Display.text('Eligible Bonus Points: ' + response.data.team1_bonus);
                            team2Display.text('Eligible Bonus Points: ' + response.data.team2_bonus);

                            if (score1 > score2) {
                                team1Display.addClass('active');
                                team2Display.removeClass('active');
                            } else if (score2 > score1) {
                                team2Display.addClass('active');
                                team1Display.removeClass('active');
                            } else {
                                team1Display.removeClass('active');
                                team2Display.removeClass('active');
                            }
                        }
                    }
                });
            }
        }

        // Update displays when teams or scores change
        $('#team1, #team2, #score1, #score2').on('change input', updateBonusPoints);
    });
    </script>
    <?php
}

// Add AJAX handler for getting team division
add_action('wp_ajax_get_team_division', 'get_team_division_ajax');
function get_team_division_ajax() {
    check_ajax_referer('get_team_division_nonce', 'nonce');

    $team_id = intval($_POST['team_id']);
    $response = array(
        'division' => 'Unknown'
    );

    if ($team_id) {
        $terms = wp_get_post_terms($team_id, 'division');
        if (!empty($terms) && !is_wp_error($terms)) {
            $response = array(
                'division' => $terms[0]->name
            );
        }
    }

    wp_send_json_success($response);
}

// AJAX handler for bonus points calculation
add_action('wp_ajax_calculate_bonus_points', 'ajax_calculate_bonus_points');
function ajax_calculate_bonus_points() {
    check_ajax_referer('calculate_bonus_points_nonce', 'nonce');

    $team1_id = intval($_POST['team1']);
    $team2_id = intval($_POST['team2']);
    $score1 = intval($_POST['score1']);
    $score2 = intval($_POST['score2']);

    $bonus_points = array(
        'team1_bonus' => 0,
        'team2_bonus' => 0
    );

    if ($team1_id && $team2_id) {
        $team1_terms = wp_get_post_terms($team1_id, 'division');
        $team2_terms = wp_get_post_terms($team2_id, 'division');

        if (!empty($team1_terms) && !empty($team2_terms)) {
            $team1_rank = get_term_meta($team1_terms[0]->term_id, 'division_rank', true) ?: 1;
            $team2_rank = get_term_meta($team2_terms[0]->term_id, 'division_rank', true) ?: 1;

            if ($score1 > $score2) {
                if ($team1_rank === $team2_rank) {
                    $bonus_points['team1_bonus'] = 2;
                } elseif ($team1_rank > $team2_rank) {
                    $bonus_points['team1_bonus'] = 3;
                }
            } elseif ($score2 > $score1) {
                if ($team1_rank === $team2_rank) {
                    $bonus_points['team2_bonus'] = 2;
                } elseif ($team2_rank > $team1_rank) {
                    $bonus_points['team2_bonus'] = 3;
                }
            }
        }
    }

    wp_send_json_success($bonus_points);
}
