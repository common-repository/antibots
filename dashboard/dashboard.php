<?php
/**
 * @author William Sergio Minozzi
 * @copyright 2020
 * */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="antibots-steps0">
    <div class="antibots-block-title">
     <?php  esc_attr_e("Anti Bots Plugin Status","antibots"); ?>
    </div>
    <div class="antibots-help-container100">
    <?php
    $antibots_active = sanitize_text_field(get_option('antibots_is_active', ''));
        $antibots_active = strtolower($antibots_active);
        if ($antibots_active == 'yes') {
            echo '<img src="' . ANTIBOTSURL . '/assets/images/lock-xxl.png" style="text-align:center; width: 60px;margin: 10px 0 auto;"  />';
            echo '<h3 style="color:green; margin-top:10px;">'.esc_attr__("Protection Enabled","antibots") .'</h3>';
        } else {
            echo '<img src="' . ANTIBOTSURL . '/assets/images/unlock-icon-red-small.png" style="text-align:center; max-width: 40px;margin: 10px 0 auto;"  />';
            echo '<h3 style="color:red; margin-top:10px;">'.esc_attr__("Protection Disabled","antibots").'</h3>';
            echo esc_attr__('Go to Dashboard => Anti Bots => ',"antibots"); 
            echo 'Settings =>'; 
            echo esc_attr__('General Settings (tab)',"antibots"); 
            echo '<br />';
            echo esc_attr__('to enable it.',"antibots"); 
            echo '<br />';
            echo '<br />';
        }
        //
        //
        //
        ?>
    </div> <!-- "Container 1 " -->
</div> <!-- "antibots-steps0 -->
<div id="antibots-services3">
    <div class="antibots-help-container1">
        <div class="antibots-help-column antibots-help-column-1">
            <img alt="aux" src="<?php echo ANTIBOTSURL ?>assets/images/service_configuration.png" />
            <div class="bill-dashboard-titles">
                <?php 
                esc_attr_e('Start Up Guide and Settings','antibots');
                ?>
                </div>
            <br /><br />
             <?php  esc_attr_e("Just click Settings in the left menu (Anti Bots).","antibots"); ?>
            <br />
              <?php  esc_attr_e("Dashboard => Anti Bots => Settings","antibots"); ?>
            <br />
            <?php $site = ANTIBOTSHOMEURL . "admin.php?page=settings-anti-bots"; ?>
            <a href="<?php echo esc_url($site); ?>" class="button button-primary"><?php  
            esc_attr_e('Go', 'antibots');
            ?></a>
            <br /><br />
        </div> <!-- "Column1">  -->
        <div class="antibots-help-column antibots-help-column-2">
            <img alt="aux" src="<?php echo ANTIBOTSURL ?>assets/images/support.png" />
            <div class="bill-dashboard-titles"><?php  esc_attr_e("OnLine Guide, Support, Faq...","antibots"); ?></div>
            <br /><br />

              <?php 
                 esc_attr_e("You will find our complete and updated OnLine guide, faqs page, link to support and more in our site.","antibots"); 
               ?>
            <br />

            <?php 
               $site = "http://antibotsplugin.com"; 
            ?>
            <a href="<?php echo esc_url($site); ?>" class="button button-primary"> <?php  
            esc_attr_e('Go', 'antibots');
            ?>
            </a>
        </div> <!-- "columns 2">  -->
        <div class="antibots-help-column antibots-help-column-3">
            <img alt="aux" src="<?php echo ANTIBOTSURL ?>assets/images/system_health.png" />
            <div class="bill-dashboard-titles"> <?php  esc_attr_e("Troubleshooting Guide","antibots"); ?></div>
            <br />
            <?php  esc_attr_e("Bots showing in your statistics tool, Use old WP version, Low memory, some plugin with Javascript error are some possible problems.","antibots"); ?>
            <br /><br />
            <a href="http://siterightaway.net/troubleshooting/" class="button button-primary"><?php esc_attr_e('Troubleshooting Page', 'antibots');?></a>
        </div> <!-- "Column 3">  -->
    </div> <!-- "Container1 ">  -->
</div> <!-- "services"> -->
<div id="antibots-services3">
    <div class="antibots-help-container1">
        <div class="antibots-help-2column antibots-help-column-1">
            <h3><?php  esc_attr_e("Top Bots Blocked Last 15 Days","antibots"); ?></h3>
            <?php require_once "topips.php"; ?>
        </div>
        <div class="antibots-help-2column antibots-help-column-1">
            <h3><?php  esc_attr_e("Top Bots Blocked Last 24 Hours","antibots"); ?></h3>
            <?php require_once "topips_24.php"; ?>
        </div>
        <div class="antibots-help-2column antibots-help-column-2">
            <h3><?php  esc_attr_e("Bots / Human Visits","antibots"); ?></h3>
            <br />
            <?php require_once "botsgraph_pie2.php"; ?>
            <br /><br />
        </div> <!-- "Column 3">  -->
    </div> <!-- "Container1"> -->
</div> <!-- "Services"> -->
<div id="antibots-services3">
    <div class="antibots-help-container1">
        <div class="antibots-help-2column antibots-help-column-2">
            <h3><?php  esc_attr_e("Total Bots Blocked Last 15 Days","antibots"); ?></h3>
            <br />
            <?php require_once "botsgraph.php"; ?>
            <center><?php  esc_attr_e("Days","antibots"); ?></center>
        </div> <!-- "Column 3">  -->
        <div class="antibots-help-2column antibots-help-column-2">
            <h3><?php  esc_attr_e("Total Bots Blocked Last 12 Hours","antibots"); ?></h3>
            <br />
            <?php require_once "botsgraph_24.php"; 
            if ($totrow > 0) {
                ?>
                <center><?php  esc_attr_e("Hours","antibots"); ?></center>
                <?php


            }

                 ?>

        </div> <!-- "Column 3">  -->
    </div> <!-- "Container1"> -->
</div> <!-- "Services"> -->
<center>
    <h4> <?php  esc_attr_e("With our plugin, many blocked bots will give up of attack your site !","antibots"); ?>
    </h4>
</center>