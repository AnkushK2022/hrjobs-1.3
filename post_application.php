<?php

ob_start(); // Start output buffering
if (!defined('ABSPATH')) {
    exit;
}
global $wpdb;
$jobs_table = $wpdb->prefix . 'job_applications'; 

// Handle deletion of application
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['application_id'])) {
    $application_id = intval($_GET['application_id']); // Ensure the ID is an integer
    // Delete the application from the wpcorp_job_applications table
    $wpdb->delete($jobs_table, ['id' => $application_id]); // Delete the application
    // Redirect to avoid resubmission of the delete action
    wp_redirect(admin_url('admin.php?page=hrjobs&tab=applications'));
    exit; // Ensure the script stops executing after the redirect
}

// Output HTML
?>
<h2><?php esc_html_e('Posted Applications', 'hrjobs'); ?></h2>

<h2 class="nav-tab-wrapper">
    <a class="nav-tab <?php echo $current_tab == 'applications' ? 'nav-tab-active' : ''; ?>">All Applications</a>
</h2>

<div class="tab-content">
    <?php hrjobs_list_jobs($wpdb, $jobs_table); ?>
</div>

<?php
// Function to list job applications
function hrjobs_list_jobs($wpdb, $jobs_table)
{
    // Initialize selected_country variable
    $selected_country = isset($_POST['filter_country']) ? $_POST['filter_country'] : '';
    ?>
    <form method="POST" style="margin-bottom: 20px;">
        <label for="filter_country">Filter by Applications:</label>
        <select name="filter_country" id="filter_country">
            <option value="">All Countries</option>
            <option value="kuwait" <?php selected($selected_country, 'kuwait'); ?>>Kuwait</option>
            <option value="qatar" <?php selected($selected_country, 'qatar'); ?>>Qatar</option>
            <option value="oman" <?php selected($selected_country, 'oman'); ?>>Oman</option>
            <option value="uae" <?php selected($selected_country, 'uae'); ?>>UAE</option>
        </select>
        <input type="submit" value="Filter" class="button">
    </form>

    <?php
    // Prepare the SQL query based on the selected country
    $job_ids = [];
    if (!empty($selected_country)) {
        // If a specific country is selected, filter jobs by that country
        $country_sql = $wpdb->prepare("SELECT id FROM wpcorp_jobs WHERE country = %s", $selected_country);
        // Fetch job IDs for the selected country
        $job_ids = $wpdb->get_col($country_sql); // Get an array of job IDs
    }

    // Query to fetch job applications
    $query = "SELECT * FROM $jobs_table";
    // Fetch the applications
    $applications = $wpdb->get_results($query);

    // Prepare an array to store filtered applications
    $filtered_applications = [];

    if ($applications) {
        foreach ($applications as $application) {
            if (empty($selected_country) || in_array($application->job_id, $job_ids)) {
                $filtered_applications[] = $application; 
            }
        }
    }
    ?>
    <table class="widefat">
        <thead>
            <tr>
                <th>Job ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Birthday</th>
                <th>Document</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($filtered_applications)) {
                foreach ($filtered_applications as $application) { ?>
                    <tr>
                        <td><?php echo esc_html($application->job_id); ?></td>
                        <td><?php echo esc_html($application->first_name); ?></td>
                        <td><?php echo esc_html($application->last_name); ?></td>
                        <td><?php echo esc_html($application->email); ?></td>
                        <td><?php echo esc_html($application->phone); ?></td>
                        <td><?php echo esc_html($application->birthday); ?></td>
                        <td><a href="<?php echo esc_url($application->file); ?>" target="_blank">Download</a></td>
                        <td>
                            <a href="?page=hrjobs&tab=applications&action=delete&application_id=<?php echo esc_html($application->id); ?>" onclick="return confirm('Are you sure you want to delete this application?');">Delete</a>
                        </td>
                    </tr>
                <?php }
            } else { ?>
                <tr>
                    <td colspan="8" style="text-align: center;">No applications found for the selected country.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php
}
ob_end_flush(); // End output buffering

?>
