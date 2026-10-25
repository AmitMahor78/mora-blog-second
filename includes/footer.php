<?php

include "db.php";

// Fetch Site Settings
$settingsQuery = "
    SELECT
        site_name,
        site_description,
        location,
        working_days,
        closing_day,
        copyright_year,
        developer_name,
        developer_url,
        logo_path
    FROM site_settings
    WHERE status = 'active'
    ORDER BY id DESC
    LIMIT 1
";

$settingsResult = mysqli_query($conn, $settingsQuery);

$settings = null;

if ($settingsResult && mysqli_num_rows($settingsResult) > 0) {
    $settings = mysqli_fetch_assoc($settingsResult);
}

?>

<footer class="footer_area">

    <div class="container">

        <div class="footer_inner row">

            <!-- Footer Logo & Information -->
            <div class="col-lg-5 col-md-6 footer_logo">

                <a href="index.php">
                   <img
    src="<?php
        echo htmlspecialchars(
            !empty($settings['logo_path'])
                ? $settings['logo_path']
                : 'images/logo.png',
            ENT_QUOTES,
            'UTF-8'
        );
    ?>"
    alt="<?php echo htmlspecialchars($settings['site_name'] ?? 'Mora', ENT_QUOTES, 'UTF-8'); ?>"
>
                </a>

                <p>
                    <?php echo htmlspecialchars($settings['site_description'] ?? ''); ?>
                </p>

                <address>

                    <span>LOCATION</span>

                    <p>
                        <?php echo htmlspecialchars($settings['location'] ?? ''); ?>
                    </p>

                </address>

            </div>


            <!-- Subscribe -->
            <div class="col-lg-5 col-md-6">

                <div class="subscribe">

                    <h4>Subscribe</h4>

                    <p>
                        Sign up for our mailing list to get latest updates and offers
                    </p>

                    <form method="POST" action="subscribe.php">

                        <div class="input-group">

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                placeholder="Enter your email"
                                required
                            >

                            <div class="input-group-append">

                                <button
                                    type="submit"
                                    class="input-group-text"
                                    style="border: none; background: transparent;"
                                >
                                    <i class="fa fa-paper-plane"></i>
                                </button>

                            </div>

                        </div>

                    </form>


                    <!-- Working Hours -->
                    <h6>

                        Working Hours :
                        <?php
                        echo htmlspecialchars(
                            $settings['working_days'] ?? ''
                        );
                        ?>

                        <span>

                            Close :
                            <?php
                            echo htmlspecialchars(
                                $settings['closing_day'] ?? ''
                            );
                            ?>

                        </span>

                    </h6>

                </div>

            </div>


            <!-- Short Links -->
            <div class="col-lg-2 col-md-3 resources">

                <h4>Short Link</h4>

                <ul class="resources_list">

                    <li>
                        <a href="about.php">About</a>
                    </li>

                    <li>
                        <a href="contact.php">Contact</a>
                    </li>

                   

                    <li>
                        <a href="shop.php">Shop</a>
                    </li>

                </ul>

            </div>

        </div>

    </div>


    <!-- Copyright -->
    <div class="copy_right">

        <p>

            Copyright ©

            <?php
            echo htmlspecialchars(
                $settings['copyright_year'] ?? date('Y')
            );
            ?>

            <a href="index.php">

                <?php
                echo htmlspecialchars(
                    $settings['site_name'] ?? 'Mora'
                );
                ?>

            </a>

            Developed by

            <a
                href="<?php echo htmlspecialchars($settings['developer_url'] ?? '#'); ?>"
                target="_blank"
                rel="noopener noreferrer"
            >

                <?php
                echo htmlspecialchars(
                    $settings['developer_name'] ?? ''
                );
                ?>

            </a>

        </p>

    </div>

</footer>