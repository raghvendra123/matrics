<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* modules/custom/matrics_dashboard/templates/dashboard-manager.html.twig */
class __TwigTemplate_417166a65fb23edb5ac6fbf6ac0bf6310c2ec8654467211a138357c38d709873 extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        $context["roles"] = twig_get_attribute($this->env, $this->source, ($context["user"] ?? null), "getroles", [0 => true], "method", false, false, true, 1);
        // line 2
        echo "<div class=\"filter-mobile-head\"><h2>Filter</h2></div>
<div class=\"page-topForm page-topForm-mobile\" style=\"width:100%\">
    ";
        // line 4
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["blocks"] ?? null), 4, $this->source), "html", null, true);
        echo "
</div>
<!-- Dashboard Content Start -->
<div class=\"dashboard-content\" id=\"box-content\" style=\"clear:both;\">
  <div class=\"page-header \">
    <div class=\"page-header__inner\">
      <h1>";
        // line 10
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["dashboard_title"] ?? null), 10, $this->source), "html", null, true);
        echo " ";
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["dashboard_title_url"] ?? null), 10, $this->source), "html", null, true);
        echo "</h1>
      ";
        // line 11
        if (((((($__internal_compile_0 = ($context["roles"] ?? null)) && is_array($__internal_compile_0) || $__internal_compile_0 instanceof ArrayAccess ? ($__internal_compile_0[0] ?? null) : null) == "administrator") || ((($__internal_compile_1 = ($context["roles"] ?? null)) && is_array($__internal_compile_1) || $__internal_compile_1 instanceof ArrayAccess ? ($__internal_compile_1[0] ?? null) : null) == "mnager")) || ((($__internal_compile_2 = ($context["roles"] ?? null)) && is_array($__internal_compile_2) || $__internal_compile_2 instanceof ArrayAccess ? ($__internal_compile_2[0] ?? null) : null) == "tms_admins_"))) {
            echo " 
        <a href=\"/manage/tiles\" class=\"use-ajax manage_dashboard\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">   Manage Dashboard   </a>
      ";
        }
        // line 14
        echo "    </div>
  </div>
  <div class=\"row card-parent customCard-width\" style=\"display: flex; display: -webkit-flex;\">
    <!-- Card  Start -->
    ";
        // line 18
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_3 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_3) || $__internal_compile_3 instanceof ArrayAccess ? ($__internal_compile_3[0] ?? null) : null), "status", [], "any", false, false, true, 18) == 1)) {
            // line 19
            echo "        ";
            // line 20
            echo "        ";
            // line 21
            echo "        ";
            // line 22
            echo "        ";
            // line 23
            echo "        ";
            // line 24
            echo "        ";
            // line 25
            echo "                        
        ";
            // line 27
            echo "        ";
            // line 28
            echo "        ";
            // line 29
            echo "        ";
            // line 30
            echo "        ";
            // line 31
            echo "        ";
            // line 32
            echo "        ";
            // line 33
            echo "        <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, (($__internal_compile_4 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_4) || $__internal_compile_4 instanceof ArrayAccess ? ($__internal_compile_4[0] ?? null) : null), "tile_order", [], "any", false, false, true, 33), 33, $this->source), "html", null, true);
            echo "\">
          <a href=\"/dashboard-tile/course_booked\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
            <div class=\"card--inner\">
              <div class=\"card-content\">
                <h3>Booked Course</h3>
                <p>";
            // line 38
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_5 = ($context["items"] ?? null)) && is_array($__internal_compile_5) || $__internal_compile_5 instanceof ArrayAccess ? ($__internal_compile_5["course_booked"] ?? null) : null), 38, $this->source), "html", null, true);
            echo "</p>
              </div>
              <div class=\"card-icon\">
                <img src=\"/themes/custom/matrics/img/booking.png\" alt=\"\">
              </div>
            </div>
          </a>
        </div>
    ";
        }
        // line 47
        echo "    <!-- Card  End -->
    <!-- Card  Start -->
    ";
        // line 49
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_6 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_6) || $__internal_compile_6 instanceof ArrayAccess ? ($__internal_compile_6[1] ?? null) : null), "status", [], "any", false, false, true, 49) == 1)) {
            // line 50
            echo "      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, (($__internal_compile_7 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_7) || $__internal_compile_7 instanceof ArrayAccess ? ($__internal_compile_7[1] ?? null) : null), "tile_order", [], "any", false, false, true, 50), 50, $this->source), "html", null, true);
            echo "\">
        <a href=\"/dashboard-tile/booking_completed\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Booking completed</h3>
              <p>";
            // line 55
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_8 = ($context["items"] ?? null)) && is_array($__internal_compile_8) || $__internal_compile_8 instanceof ArrayAccess ? ($__internal_compile_8["complete_count"] ?? null) : null), 55, $this->source), "html", null, true);
            echo "</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/booking_confirm.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    ";
        }
        // line 64
        echo "    <!-- Card  End -->
    
    <!-- Card  Start -->
    ";
        // line 67
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_9 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_9) || $__internal_compile_9 instanceof ArrayAccess ? ($__internal_compile_9[2] ?? null) : null), "status", [], "any", false, false, true, 67) == 1)) {
            // line 68
            echo "      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, (($__internal_compile_10 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_10) || $__internal_compile_10 instanceof ArrayAccess ? ($__internal_compile_10[2] ?? null) : null), "tile_order", [], "any", false, false, true, 68), 68, $this->source), "html", null, true);
            echo "\">
        <a href=\"/dashboard-tile/booking_cancelled_by_customer\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Bookings cancelled by customer</h3>
              <p>";
            // line 73
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_11 = ($context["items"] ?? null)) && is_array($__internal_compile_11) || $__internal_compile_11 instanceof ArrayAccess ? ($__internal_compile_11["cacelled_count_customer"] ?? null) : null), 73, $this->source), "html", null, true);
            echo "</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/cancel_icon.png\" alt=\"\">
              ";
            // line 78
            echo "            </div>
          </div>
        </a>
      </div>
    ";
        }
        // line 83
        echo "    <!-- Card  End -->
    <!-- Card  Start -->
    ";
        // line 85
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_12 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_12) || $__internal_compile_12 instanceof ArrayAccess ? ($__internal_compile_12[3] ?? null) : null), "status", [], "any", false, false, true, 85) == 1)) {
            // line 86
            echo "      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, (($__internal_compile_13 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_13) || $__internal_compile_13 instanceof ArrayAccess ? ($__internal_compile_13[3] ?? null) : null), "tile_order", [], "any", false, false, true, 86), 86, $this->source), "html", null, true);
            echo "\">
        <a href=\"/dashboard-tile/booking_cancelled_by_provider\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Booking cancelled by training provider</h3>
              <p>";
            // line 91
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_14 = ($context["items"] ?? null)) && is_array($__internal_compile_14) || $__internal_compile_14 instanceof ArrayAccess ? ($__internal_compile_14["cacelled_count_provider"] ?? null) : null), 91, $this->source), "html", null, true);
            echo "</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/cancel_icon.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    ";
        }
        // line 100
        echo "    <!-- Card  End -->
    
    <!-- Card  Start -->
    ";
        // line 103
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_15 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_15) || $__internal_compile_15 instanceof ArrayAccess ? ($__internal_compile_15[4] ?? null) : null), "status", [], "any", false, false, true, 103) == 1)) {
            // line 104
            echo "      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, (($__internal_compile_16 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_16) || $__internal_compile_16 instanceof ArrayAccess ? ($__internal_compile_16[4] ?? null) : null), "tile_order", [], "any", false, false, true, 104), 104, $this->source), "html", null, true);
            echo "\">
        <a href=\"/dashboard-tile/total_spend_TMS\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Total spend – TMS charges </h3>
              <p>£";
            // line 109
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_17 = ($context["items"] ?? null)) && is_array($__internal_compile_17) || $__internal_compile_17 instanceof ArrayAccess ? ($__internal_compile_17["cmt_fee_courses"] ?? null) : null), 109, $this->source), "html", null, true);
            echo "</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/pound-currency.jpg\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    ";
        }
        // line 118
        echo "    <!-- Card  End -->
    <!-- Card  Start -->
    ";
        // line 120
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_18 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_18) || $__internal_compile_18 instanceof ArrayAccess ? ($__internal_compile_18[5] ?? null) : null), "status", [], "any", false, false, true, 120) == 1)) {
            // line 121
            echo "      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, (($__internal_compile_19 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_19) || $__internal_compile_19 instanceof ArrayAccess ? ($__internal_compile_19[5] ?? null) : null), "tile_order", [], "any", false, false, true, 121), 121, $this->source), "html", null, true);
            echo "\">
        <a href=\"/dashboard-tile/total_spend_Customer\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Total spend – training courses</h3>
              <p>£";
            // line 126
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_20 = ($context["items"] ?? null)) && is_array($__internal_compile_20) || $__internal_compile_20 instanceof ArrayAccess ? ($__internal_compile_20["customer_fee_courses"] ?? null) : null), 126, $this->source), "html", null, true);
            echo "</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/pound-currency.jpg\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    ";
        }
        // line 135
        echo "    <!-- Card  End -->
    
    <!-- Card  Start -->
    ";
        // line 138
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_21 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_21) || $__internal_compile_21 instanceof ArrayAccess ? ($__internal_compile_21[6] ?? null) : null), "status", [], "any", false, false, true, 138) == 1)) {
            // line 139
            echo "      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, (($__internal_compile_22 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_22) || $__internal_compile_22 instanceof ArrayAccess ? ($__internal_compile_22[6] ?? null) : null), "tile_order", [], "any", false, false, true, 139), 139, $this->source), "html", null, true);
            echo "\">
        <a href=\"javascript:void(0);\" class=\"card\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Total spend – cancellation fees</h3>
              <p>£";
            // line 144
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_23 = ($context["items"] ?? null)) && is_array($__internal_compile_23) || $__internal_compile_23 instanceof ArrayAccess ? ($__internal_compile_23["cancellation_fees_courses"] ?? null) : null), 144, $this->source), "html", null, true);
            echo "
              </p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/pound-currency.jpg\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    ";
        }
        // line 154
        echo "    <!-- Card  End -->
    
    <!-- Card  Start -->
    ";
        // line 157
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_24 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_24) || $__internal_compile_24 instanceof ArrayAccess ? ($__internal_compile_24[7] ?? null) : null), "status", [], "any", false, false, true, 157) == 1)) {
            // line 158
            echo "      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, (($__internal_compile_25 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_25) || $__internal_compile_25 instanceof ArrayAccess ? ($__internal_compile_25[7] ?? null) : null), "tile_order", [], "any", false, false, true, 158), 158, $this->source), "html", null, true);
            echo "\">
        <a href=\"/dashboard-tile/course_duration\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Number of training days</h3>
              <p>";
            // line 163
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_26 = ($context["items"] ?? null)) && is_array($__internal_compile_26) || $__internal_compile_26 instanceof ArrayAccess ? ($__internal_compile_26["course_duration"] ?? null) : null), 163, $this->source), "html", null, true);
            echo "</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/icon1.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    ";
        }
        // line 172
        echo "    <!-- Card  End -->
    
    <!-- Card  Start -->
    ";
        // line 175
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_27 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_27) || $__internal_compile_27 instanceof ArrayAccess ? ($__internal_compile_27[8] ?? null) : null), "status", [], "any", false, false, true, 175) == 1)) {
            // line 176
            echo "      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, (($__internal_compile_28 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_28) || $__internal_compile_28 instanceof ArrayAccess ? ($__internal_compile_28[8] ?? null) : null), "tile_order", [], "any", false, false, true, 176), 176, $this->source), "html", null, true);
            echo "\">
        <a href=\"/training-management\" class=\"card\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Training providers used</h3>
              ";
            // line 181
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, views_embed_view("courses_list", "block_3"), "html", null, true);
            echo "
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/profile-setting.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    ";
        }
        // line 190
        echo "    <!-- Card  End -->
    
    <!-- Card  Start -->
    ";
        // line 193
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_29 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_29) || $__internal_compile_29 instanceof ArrayAccess ? ($__internal_compile_29[9] ?? null) : null), "status", [], "any", false, false, true, 193) == 1)) {
            // line 194
            echo "      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, (($__internal_compile_30 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_30) || $__internal_compile_30 instanceof ArrayAccess ? ($__internal_compile_30[9] ?? null) : null), "tile_order", [], "any", false, false, true, 194), 194, $this->source), "html", null, true);
            echo "\">
        <a href=\"/supplier-portal\" class=\"card\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Approved training providers</h3>
              <p>";
            // line 199
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_31 = ($context["items"] ?? null)) && is_array($__internal_compile_31) || $__internal_compile_31 instanceof ArrayAccess ? ($__internal_compile_31["approved_training_provider"] ?? null) : null), 199, $this->source), "html", null, true);
            echo "</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/profile-setting.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    ";
        }
        // line 208
        echo "    <!-- Card  End -->
    
    <!-- Card  Start -->
     
    ";
        // line 212
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_32 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_32) || $__internal_compile_32 instanceof ArrayAccess ? ($__internal_compile_32[10] ?? null) : null), "status", [], "any", false, false, true, 212) == 1)) {
            // line 213
            echo "      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, (($__internal_compile_33 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_33) || $__internal_compile_33 instanceof ArrayAccess ? ($__internal_compile_33[10] ?? null) : null), "tile_order", [], "any", false, false, true, 213), 213, $this->source), "html", null, true);
            echo "\">
        <a href=\"/dashboard-tile/course_passed\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Courses passed</h3>
              <p>";
            // line 218
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_34 = ($context["items"] ?? null)) && is_array($__internal_compile_34) || $__internal_compile_34 instanceof ArrayAccess ? ($__internal_compile_34["course_passed"] ?? null) : null), 218, $this->source), "html", null, true);
            echo "</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/icon1.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    ";
        }
        // line 227
        echo "    <!-- Card  End -->
    
    <!-- Card  Start -->
    ";
        // line 230
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_35 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_35) || $__internal_compile_35 instanceof ArrayAccess ? ($__internal_compile_35[11] ?? null) : null), "status", [], "any", false, false, true, 230) == 1)) {
            // line 231
            echo "      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, (($__internal_compile_36 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_36) || $__internal_compile_36 instanceof ArrayAccess ? ($__internal_compile_36[11] ?? null) : null), "tile_order", [], "any", false, false, true, 231), 231, $this->source), "html", null, true);
            echo "\">
        <a href=\"/dashboard-tile/course_failed\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Courses failed</h3>
              <p>";
            // line 236
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_37 = ($context["items"] ?? null)) && is_array($__internal_compile_37) || $__internal_compile_37 instanceof ArrayAccess ? ($__internal_compile_37["course_failed"] ?? null) : null), 236, $this->source), "html", null, true);
            echo "</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/icon1.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    ";
        }
        // line 245
        echo "    <!-- Card  End -->
    
    <!-- Card  Start -->
    ";
        // line 248
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_38 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_38) || $__internal_compile_38 instanceof ArrayAccess ? ($__internal_compile_38[12] ?? null) : null), "status", [], "any", false, false, true, 248) == 1)) {
            // line 249
            echo "        ";
            // line 250
            echo "        ";
            // line 251
            echo "        ";
            // line 252
            echo "        ";
            // line 253
            echo "        ";
            // line 254
            echo "        ";
            // line 255
            echo "        ";
            // line 256
            echo "        ";
            // line 257
            echo "        ";
            // line 258
            echo "        ";
            // line 259
            echo "        ";
            // line 260
            echo "        ";
            // line 261
            echo "        ";
            // line 262
            echo "    ";
        }
        // line 263
        echo "    <!-- Card  End -->
    
    
    
    <!-- Card  Start -->
    ";
        // line 268
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_39 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_39) || $__internal_compile_39 instanceof ArrayAccess ? ($__internal_compile_39[13] ?? null) : null), "status", [], "any", false, false, true, 268) == 1)) {
            // line 269
            echo "      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, (($__internal_compile_40 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_40) || $__internal_compile_40 instanceof ArrayAccess ? ($__internal_compile_40[13] ?? null) : null), "tile_order", [], "any", false, false, true, 269), 269, $this->source), "html", null, true);
            echo "\">
        <a href=\"/dashboard-tile/employee\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Employees registered</h3>
              <p>";
            // line 274
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_41 = ($context["items"] ?? null)) && is_array($__internal_compile_41) || $__internal_compile_41 instanceof ArrayAccess ? ($__internal_compile_41["employees_registered"] ?? null) : null), 274, $this->source), "html", null, true);
            echo "</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/profile-setting.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    ";
        }
        // line 283
        echo "    <!-- Card  End -->
    
    
    <!-- Card  Start -->
    ";
        // line 287
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_42 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_42) || $__internal_compile_42 instanceof ArrayAccess ? ($__internal_compile_42[14] ?? null) : null), "status", [], "any", false, false, true, 287) == 1)) {
            // line 288
            echo "      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, (($__internal_compile_43 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_43) || $__internal_compile_43 instanceof ArrayAccess ? ($__internal_compile_43[14] ?? null) : null), "tile_order", [], "any", false, false, true, 288), 288, $this->source), "html", null, true);
            echo "\">
        <a href=\"/certification\" class=\"card\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Certificates issued</h3>
              <p>";
            // line 293
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_44 = ($context["items"] ?? null)) && is_array($__internal_compile_44) || $__internal_compile_44 instanceof ArrayAccess ? ($__internal_compile_44["certificate_issued"] ?? null) : null), 293, $this->source), "html", null, true);
            echo "</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/icon1.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    ";
        }
        // line 302
        echo "    <!-- Card  End -->
    
    <!-- Card  Start -->
    ";
        // line 305
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_45 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_45) || $__internal_compile_45 instanceof ArrayAccess ? ($__internal_compile_45[15] ?? null) : null), "status", [], "any", false, false, true, 305) == 1)) {
            // line 306
            echo "      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, (($__internal_compile_46 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_46) || $__internal_compile_46 instanceof ArrayAccess ? ($__internal_compile_46[15] ?? null) : null), "tile_order", [], "any", false, false, true, 306), 306, $this->source), "html", null, true);
            echo "\">
        <a href=\"/training-management\" class=\"card\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Certificates pending</h3>
              <p>";
            // line 311
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_47 = ($context["items"] ?? null)) && is_array($__internal_compile_47) || $__internal_compile_47 instanceof ArrayAccess ? ($__internal_compile_47["certificate_pending"] ?? null) : null), 311, $this->source), "html", null, true);
            echo "</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/icon1.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    ";
        }
        // line 320
        echo "    <!-- Card  End -->
    
    <!-- Card  Start -->
    ";
        // line 323
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_48 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_48) || $__internal_compile_48 instanceof ArrayAccess ? ($__internal_compile_48[16] ?? null) : null), "status", [], "any", false, false, true, 323) == 1)) {
            // line 324
            echo "      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, (($__internal_compile_49 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_49) || $__internal_compile_49 instanceof ArrayAccess ? ($__internal_compile_49[16] ?? null) : null), "tile_order", [], "any", false, false, true, 324), 324, $this->source), "html", null, true);
            echo "\">
        <a href=\"/training-management\" class=\"card\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Training gap score</h3>
              <p>";
            // line 329
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_50 = ($context["items"] ?? null)) && is_array($__internal_compile_50) || $__internal_compile_50 instanceof ArrayAccess ? ($__internal_compile_50["training_gap_score"] ?? null) : null), 329, $this->source), "html", null, true);
            echo "%</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/icon1.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    ";
        }
        // line 338
        echo "    <!-- Card  End -->
    
    <!-- Card  Start -->
    ";
        // line 341
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_51 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_51) || $__internal_compile_51 instanceof ArrayAccess ? ($__internal_compile_51[17] ?? null) : null), "status", [], "any", false, false, true, 341) == 1)) {
            // line 342
            echo "      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, (($__internal_compile_52 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_52) || $__internal_compile_52 instanceof ArrayAccess ? ($__internal_compile_52[17] ?? null) : null), "tile_order", [], "any", false, false, true, 342), 342, $this->source), "html", null, true);
            echo "\">
        <a href=\"/training-management\" class=\"card\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Training gap count</h3>
              <p>";
            // line 347
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_53 = ($context["items"] ?? null)) && is_array($__internal_compile_53) || $__internal_compile_53 instanceof ArrayAccess ? ($__internal_compile_53["training_gap_count"] ?? null) : null), 347, $this->source), "html", null, true);
            echo "</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/icon1.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    ";
        }
        // line 356
        echo "    <!-- Card  End -->
    
    <!-- Card  Start -->
    ";
        // line 359
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_54 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_54) || $__internal_compile_54 instanceof ArrayAccess ? ($__internal_compile_54[18] ?? null) : null), "status", [], "any", false, false, true, 359) == 1)) {
            // line 360
            echo "      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, (($__internal_compile_55 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_55) || $__internal_compile_55 instanceof ArrayAccess ? ($__internal_compile_55[18] ?? null) : null), "tile_order", [], "any", false, false, true, 360), 360, $this->source), "html", null, true);
            echo "\">
        <a href=\"/training-management\" class=\"card\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>To Be Actioned</h3>
              <p>";
            // line 365
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_56 = ($context["items"] ?? null)) && is_array($__internal_compile_56) || $__internal_compile_56 instanceof ArrayAccess ? ($__internal_compile_56["to_be_actioned"] ?? null) : null), 365, $this->source), "html", null, true);
            echo "</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/icon1.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    ";
        }
        // line 374
        echo "    <!-- Card  End -->
    
    <!-- Card  Start -->
    ";
        // line 377
        if ((twig_get_attribute($this->env, $this->source, (($__internal_compile_57 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_57) || $__internal_compile_57 instanceof ArrayAccess ? ($__internal_compile_57[19] ?? null) : null), "status", [], "any", false, false, true, 377) == 1)) {
            // line 378
            echo "      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, (($__internal_compile_58 = ($context["tile_status"] ?? null)) && is_array($__internal_compile_58) || $__internal_compile_58 instanceof ArrayAccess ? ($__internal_compile_58[19] ?? null) : null), "tile_order", [], "any", false, false, true, 378), 378, $this->source), "html", null, true);
            echo "\">
        <a href=\"/dashboard-tile/course_expired\"  class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\" >
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Course Expired</h3>
              <p>";
            // line 383
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_59 = ($context["items"] ?? null)) && is_array($__internal_compile_59) || $__internal_compile_59 instanceof ArrayAccess ? ($__internal_compile_59["course_expired"] ?? null) : null), 383, $this->source), "html", null, true);
            echo "</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/icon1.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    ";
        }
        // line 392
        echo "    <!-- Card  End -->
  </div>
  <div class=\"row card-parent customCard-width\">
    ";
        // line 395
        if ((($__internal_compile_60 = ($context["chart"] ?? null)) && is_array($__internal_compile_60) || $__internal_compile_60 instanceof ArrayAccess ? ($__internal_compile_60["booking"] ?? null) : null)) {
            // line 396
            echo "      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        ";
            // line 397
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_61 = ($context["chart"] ?? null)) && is_array($__internal_compile_61) || $__internal_compile_61 instanceof ArrayAccess ? ($__internal_compile_61["booking"] ?? null) : null), 397, $this->source), "html", null, true);
            echo "
        ";
            // line 399
            echo "      </div>
    ";
        }
        // line 401
        echo "    
    ";
        // line 402
        if ((($__internal_compile_62 = ($context["chart"] ?? null)) && is_array($__internal_compile_62) || $__internal_compile_62 instanceof ArrayAccess ? ($__internal_compile_62["booking_cancel"] ?? null) : null)) {
            // line 403
            echo "      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        ";
            // line 404
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_63 = ($context["chart"] ?? null)) && is_array($__internal_compile_63) || $__internal_compile_63 instanceof ArrayAccess ? ($__internal_compile_63["booking_cancel"] ?? null) : null), 404, $this->source), "html", null, true);
            echo "
      </div>
    ";
        }
        // line 407
        echo "    
    ";
        // line 408
        if ((($__internal_compile_64 = ($context["chart"] ?? null)) && is_array($__internal_compile_64) || $__internal_compile_64 instanceof ArrayAccess ? ($__internal_compile_64["total_spend"] ?? null) : null)) {
            // line 409
            echo "      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        ";
            // line 410
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_65 = ($context["chart"] ?? null)) && is_array($__internal_compile_65) || $__internal_compile_65 instanceof ArrayAccess ? ($__internal_compile_65["total_spend"] ?? null) : null), 410, $this->source), "html", null, true);
            echo "
      </div>
    ";
        }
        // line 413
        echo "    
    ";
        // line 414
        if ((($__internal_compile_66 = ($context["chart"] ?? null)) && is_array($__internal_compile_66) || $__internal_compile_66 instanceof ArrayAccess ? ($__internal_compile_66["training_days"] ?? null) : null)) {
            // line 415
            echo "      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        ";
            // line 416
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_67 = ($context["chart"] ?? null)) && is_array($__internal_compile_67) || $__internal_compile_67 instanceof ArrayAccess ? ($__internal_compile_67["training_days"] ?? null) : null), 416, $this->source), "html", null, true);
            echo "
      </div>
    ";
        }
        // line 419
        echo "    
    ";
        // line 420
        if ((($__internal_compile_68 = ($context["chart"] ?? null)) && is_array($__internal_compile_68) || $__internal_compile_68 instanceof ArrayAccess ? ($__internal_compile_68["training_providers"] ?? null) : null)) {
            // line 421
            echo "      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        ";
            // line 422
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_69 = ($context["chart"] ?? null)) && is_array($__internal_compile_69) || $__internal_compile_69 instanceof ArrayAccess ? ($__internal_compile_69["training_providers"] ?? null) : null), 422, $this->source), "html", null, true);
            echo "
      </div>
    ";
        }
        // line 425
        echo "    
    ";
        // line 426
        if ((($__internal_compile_70 = ($context["chart"] ?? null)) && is_array($__internal_compile_70) || $__internal_compile_70 instanceof ArrayAccess ? ($__internal_compile_70["courses"] ?? null) : null)) {
            // line 427
            echo "      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        ";
            // line 428
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_71 = ($context["chart"] ?? null)) && is_array($__internal_compile_71) || $__internal_compile_71 instanceof ArrayAccess ? ($__internal_compile_71["courses"] ?? null) : null), 428, $this->source), "html", null, true);
            echo "
      </div>
    ";
        }
        // line 431
        echo "    
    ";
        // line 432
        if ((($__internal_compile_72 = ($context["chart"] ?? null)) && is_array($__internal_compile_72) || $__internal_compile_72 instanceof ArrayAccess ? ($__internal_compile_72["employees"] ?? null) : null)) {
            // line 433
            echo "      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        ";
            // line 434
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_73 = ($context["chart"] ?? null)) && is_array($__internal_compile_73) || $__internal_compile_73 instanceof ArrayAccess ? ($__internal_compile_73["employees"] ?? null) : null), 434, $this->source), "html", null, true);
            echo "
      </div>
    ";
        }
        // line 437
        echo "    
    ";
        // line 438
        if ((($__internal_compile_74 = ($context["chart"] ?? null)) && is_array($__internal_compile_74) || $__internal_compile_74 instanceof ArrayAccess ? ($__internal_compile_74["certificate"] ?? null) : null)) {
            // line 439
            echo "      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        ";
            // line 440
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_75 = ($context["chart"] ?? null)) && is_array($__internal_compile_75) || $__internal_compile_75 instanceof ArrayAccess ? ($__internal_compile_75["certificate"] ?? null) : null), 440, $this->source), "html", null, true);
            echo "
      </div>
    ";
        }
        // line 443
        echo "    
    ";
        // line 444
        if ((($__internal_compile_76 = ($context["chart"] ?? null)) && is_array($__internal_compile_76) || $__internal_compile_76 instanceof ArrayAccess ? ($__internal_compile_76["training_gap_score"] ?? null) : null)) {
            // line 445
            echo "      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        ";
            // line 446
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_77 = ($context["chart"] ?? null)) && is_array($__internal_compile_77) || $__internal_compile_77 instanceof ArrayAccess ? ($__internal_compile_77["training_gap_score"] ?? null) : null), 446, $this->source), "html", null, true);
            echo "
      </div>
    ";
        }
        // line 449
        echo "    
    ";
        // line 450
        if ((($__internal_compile_78 = ($context["chart"] ?? null)) && is_array($__internal_compile_78) || $__internal_compile_78 instanceof ArrayAccess ? ($__internal_compile_78["training_gap_count"] ?? null) : null)) {
            // line 451
            echo "      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        ";
            // line 452
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_79 = ($context["chart"] ?? null)) && is_array($__internal_compile_79) || $__internal_compile_79 instanceof ArrayAccess ? ($__internal_compile_79["training_gap_count"] ?? null) : null), 452, $this->source), "html", null, true);
            echo "
      </div>
    ";
        }
        // line 455
        echo "    ";
        if ((($__internal_compile_80 = ($context["chart"] ?? null)) && is_array($__internal_compile_80) || $__internal_compile_80 instanceof ArrayAccess ? ($__internal_compile_80["to_be_actioned"] ?? null) : null)) {
            // line 456
            echo "      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        ";
            // line 457
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_81 = ($context["chart"] ?? null)) && is_array($__internal_compile_81) || $__internal_compile_81 instanceof ArrayAccess ? ($__internal_compile_81["to_be_actioned"] ?? null) : null), 457, $this->source), "html", null, true);
            echo "
      </div>
    ";
        }
        // line 460
        echo "    
    ";
        // line 461
        if ((($__internal_compile_82 = ($context["chart"] ?? null)) && is_array($__internal_compile_82) || $__internal_compile_82 instanceof ArrayAccess ? ($__internal_compile_82["course_expired"] ?? null) : null)) {
            // line 462
            echo "      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        ";
            // line 463
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_83 = ($context["chart"] ?? null)) && is_array($__internal_compile_83) || $__internal_compile_83 instanceof ArrayAccess ? ($__internal_compile_83["course_expired"] ?? null) : null), 463, $this->source), "html", null, true);
            echo "
      </div>
    ";
        }
        // line 466
        echo "  </div>                
</div>
<!-- Dashboard Content End -->";
    }

    public function getTemplateName()
    {
        return "modules/custom/matrics_dashboard/templates/dashboard-manager.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  836 => 466,  830 => 463,  827 => 462,  825 => 461,  822 => 460,  816 => 457,  813 => 456,  810 => 455,  804 => 452,  801 => 451,  799 => 450,  796 => 449,  790 => 446,  787 => 445,  785 => 444,  782 => 443,  776 => 440,  773 => 439,  771 => 438,  768 => 437,  762 => 434,  759 => 433,  757 => 432,  754 => 431,  748 => 428,  745 => 427,  743 => 426,  740 => 425,  734 => 422,  731 => 421,  729 => 420,  726 => 419,  720 => 416,  717 => 415,  715 => 414,  712 => 413,  706 => 410,  703 => 409,  701 => 408,  698 => 407,  692 => 404,  689 => 403,  687 => 402,  684 => 401,  680 => 399,  676 => 397,  673 => 396,  671 => 395,  666 => 392,  654 => 383,  645 => 378,  643 => 377,  638 => 374,  626 => 365,  617 => 360,  615 => 359,  610 => 356,  598 => 347,  589 => 342,  587 => 341,  582 => 338,  570 => 329,  561 => 324,  559 => 323,  554 => 320,  542 => 311,  533 => 306,  531 => 305,  526 => 302,  514 => 293,  505 => 288,  503 => 287,  497 => 283,  485 => 274,  476 => 269,  474 => 268,  467 => 263,  464 => 262,  462 => 261,  460 => 260,  458 => 259,  456 => 258,  454 => 257,  452 => 256,  450 => 255,  448 => 254,  446 => 253,  444 => 252,  442 => 251,  440 => 250,  438 => 249,  436 => 248,  431 => 245,  419 => 236,  410 => 231,  408 => 230,  403 => 227,  391 => 218,  382 => 213,  380 => 212,  374 => 208,  362 => 199,  353 => 194,  351 => 193,  346 => 190,  334 => 181,  325 => 176,  323 => 175,  318 => 172,  306 => 163,  297 => 158,  295 => 157,  290 => 154,  277 => 144,  268 => 139,  266 => 138,  261 => 135,  249 => 126,  240 => 121,  238 => 120,  234 => 118,  222 => 109,  213 => 104,  211 => 103,  206 => 100,  194 => 91,  185 => 86,  183 => 85,  179 => 83,  172 => 78,  165 => 73,  156 => 68,  154 => 67,  149 => 64,  137 => 55,  128 => 50,  126 => 49,  122 => 47,  110 => 38,  101 => 33,  99 => 32,  97 => 31,  95 => 30,  93 => 29,  91 => 28,  89 => 27,  86 => 25,  84 => 24,  82 => 23,  80 => 22,  78 => 21,  76 => 20,  74 => 19,  72 => 18,  66 => 14,  60 => 11,  54 => 10,  45 => 4,  41 => 2,  39 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("{% set roles = user.getroles(TRUE) %}
<div class=\"filter-mobile-head\"><h2>Filter</h2></div>
<div class=\"page-topForm page-topForm-mobile\" style=\"width:100%\">
    {{ blocks }}
</div>
<!-- Dashboard Content Start -->
<div class=\"dashboard-content\" id=\"box-content\" style=\"clear:both;\">
  <div class=\"page-header \">
    <div class=\"page-header__inner\">
      <h1>{{ dashboard_title }} {{ dashboard_title_url }}</h1>
      {% if roles[0] == 'administrator' or  roles[0] == 'mnager' or  roles[0] == 'tms_admins_' %} 
        <a href=\"/manage/tiles\" class=\"use-ajax manage_dashboard\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">   Manage Dashboard   </a>
      {% endif %}
    </div>
  </div>
  <div class=\"row card-parent customCard-width\" style=\"display: flex; display: -webkit-flex;\">
    <!-- Card  Start -->
    {% if tile_status[0].status == 1 %}
        {#<div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[0].tile_order}}\">#}
        {#    <a href=\"/dashboard-tile/booking_in_progress\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">#}
        {#        <div class=\"card--inner\">#}
        {#            <div class=\"card-content\">#}
        {#                <h3>Bookings in progress</h3>#}
        {#                <p>{{ items['inprogress_count'] }}</p>#}
                        
        {#            </div>#}
        {#            <div class=\"card-icon\">#}
        {#                <img src=\"/themes/custom/matrics/img/booking.png\" alt=\"\">#}
        {#            </div>#}
        {#        </div>#}
        {#    </a>#}
        {#</div>#}
        <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[0].tile_order}}\">
          <a href=\"/dashboard-tile/course_booked\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
            <div class=\"card--inner\">
              <div class=\"card-content\">
                <h3>Booked Course</h3>
                <p>{{ items['course_booked'] }}</p>
              </div>
              <div class=\"card-icon\">
                <img src=\"/themes/custom/matrics/img/booking.png\" alt=\"\">
              </div>
            </div>
          </a>
        </div>
    {% endif %}
    <!-- Card  End -->
    <!-- Card  Start -->
    {% if tile_status[1].status == 1 %}
      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[1].tile_order}}\">
        <a href=\"/dashboard-tile/booking_completed\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Booking completed</h3>
              <p>{{ items['complete_count'] }}</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/booking_confirm.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    {% endif %}
    <!-- Card  End -->
    
    <!-- Card  Start -->
    {% if tile_status[2].status == 1 %}
      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[2].tile_order}}\">
        <a href=\"/dashboard-tile/booking_cancelled_by_customer\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Bookings cancelled by customer</h3>
              <p>{{ items['cacelled_count_customer'] }}</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/cancel_icon.png\" alt=\"\">
              {#<img src=\"/themes/custom/matrics/img/complete_icon.png\" alt=\"\">#}
            </div>
          </div>
        </a>
      </div>
    {% endif %}
    <!-- Card  End -->
    <!-- Card  Start -->
    {% if tile_status[3].status == 1 %}
      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[3].tile_order}}\">
        <a href=\"/dashboard-tile/booking_cancelled_by_provider\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Booking cancelled by training provider</h3>
              <p>{{ items['cacelled_count_provider'] }}</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/cancel_icon.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    {% endif %}
    <!-- Card  End -->
    
    <!-- Card  Start -->
    {% if tile_status[4].status == 1 %}
      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[4].tile_order}}\">
        <a href=\"/dashboard-tile/total_spend_TMS\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Total spend – TMS charges </h3>
              <p>£{{ items['cmt_fee_courses'] }}</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/pound-currency.jpg\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    {% endif %}
    <!-- Card  End -->
    <!-- Card  Start -->
    {% if tile_status[5].status == 1 %}
      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[5].tile_order}}\">
        <a href=\"/dashboard-tile/total_spend_Customer\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Total spend – training courses</h3>
              <p>£{{ items['customer_fee_courses'] }}</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/pound-currency.jpg\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    {% endif %}
    <!-- Card  End -->
    
    <!-- Card  Start -->
    {% if tile_status[6].status == 1 %}
      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[6].tile_order}}\">
        <a href=\"javascript:void(0);\" class=\"card\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Total spend – cancellation fees</h3>
              <p>£{{ items['cancellation_fees_courses'] }}
              </p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/pound-currency.jpg\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    {% endif %}
    <!-- Card  End -->
    
    <!-- Card  Start -->
    {% if tile_status[7].status == 1 %}
      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[7].tile_order}}\">
        <a href=\"/dashboard-tile/course_duration\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Number of training days</h3>
              <p>{{ items['course_duration'] }}</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/icon1.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    {% endif %}
    <!-- Card  End -->
    
    <!-- Card  Start -->
    {% if tile_status[8].status == 1 %}
      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[8].tile_order}}\">
        <a href=\"/training-management\" class=\"card\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Training providers used</h3>
              {{ drupal_view('courses_list', 'block_3') }}
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/profile-setting.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    {% endif %}
    <!-- Card  End -->
    
    <!-- Card  Start -->
    {% if tile_status[9].status == 1 %}
      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[9].tile_order}}\">
        <a href=\"/supplier-portal\" class=\"card\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Approved training providers</h3>
              <p>{{ items['approved_training_provider'] }}</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/profile-setting.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    {% endif %}
    <!-- Card  End -->
    
    <!-- Card  Start -->
     
    {% if tile_status[10].status == 1 %}
      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[10].tile_order}}\">
        <a href=\"/dashboard-tile/course_passed\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Courses passed</h3>
              <p>{{ items['course_passed'] }}</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/icon1.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    {% endif %}
    <!-- Card  End -->
    
    <!-- Card  Start -->
    {% if tile_status[11].status == 1 %}
      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[11].tile_order}}\">
        <a href=\"/dashboard-tile/course_failed\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Courses failed</h3>
              <p>{{ items['course_failed'] }}</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/icon1.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    {% endif %}
    <!-- Card  End -->
    
    <!-- Card  Start -->
    {% if tile_status[12].status == 1 %}
        {#<div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[12].tile_order}}\">#}
        {#  <a href=\"javascript:void(0);\" class=\"card\">#}
        {#    <div class=\"card--inner\">#}
        {#      <div class=\"card-content\">#}
        {#        <h3>Courses resit</h3>#}
        {#        <p>{{ items['course_resit'] }}</p>#}
        {#      </div>#}
        {#      <div class=\"card-icon\">#}
        {#        <img src=\"/themes/custom/matrics/img/icon1.png\" alt=\"\">#}
        {#      </div>#}
        {#    </div>#}
        {#  </a>#}
        {#</div>#}
    {% endif %}
    <!-- Card  End -->
    
    
    
    <!-- Card  Start -->
    {% if tile_status[13].status == 1 %}
      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[13].tile_order}}\">
        <a href=\"/dashboard-tile/employee\" class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Employees registered</h3>
              <p>{{ items['employees_registered'] }}</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/profile-setting.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    {% endif %}
    <!-- Card  End -->
    
    
    <!-- Card  Start -->
    {% if tile_status[14].status == 1 %}
      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[14].tile_order}}\">
        <a href=\"/certification\" class=\"card\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Certificates issued</h3>
              <p>{{ items['certificate_issued'] }}</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/icon1.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    {% endif %}
    <!-- Card  End -->
    
    <!-- Card  Start -->
    {% if tile_status[15].status == 1 %}
      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[15].tile_order}}\">
        <a href=\"/training-management\" class=\"card\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Certificates pending</h3>
              <p>{{ items['certificate_pending'] }}</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/icon1.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    {% endif %}
    <!-- Card  End -->
    
    <!-- Card  Start -->
    {% if tile_status[16].status == 1 %}
      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[16].tile_order}}\">
        <a href=\"/training-management\" class=\"card\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Training gap score</h3>
              <p>{{ items['training_gap_score'] }}%</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/icon1.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    {% endif %}
    <!-- Card  End -->
    
    <!-- Card  Start -->
    {% if tile_status[17].status == 1 %}
      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[17].tile_order}}\">
        <a href=\"/training-management\" class=\"card\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Training gap count</h3>
              <p>{{ items['training_gap_count'] }}</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/icon1.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    {% endif %}
    <!-- Card  End -->
    
    <!-- Card  Start -->
    {% if tile_status[18].status == 1 %}
      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[18].tile_order}}\">
        <a href=\"/training-management\" class=\"card\">
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>To Be Actioned</h3>
              <p>{{ items['to_be_actioned'] }}</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/icon1.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    {% endif %}
    <!-- Card  End -->
    
    <!-- Card  Start -->
    {% if tile_status[19].status == 1 %}
      <div class=\"col-xl-2 col-sm-12 card-child\" style=\"order:{{tile_status[19].tile_order}}\">
        <a href=\"/dashboard-tile/course_expired\"  class=\"card use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\" >
          <div class=\"card--inner\">
            <div class=\"card-content\">
              <h3>Course Expired</h3>
              <p>{{ items['course_expired'] }}</p>
            </div>
            <div class=\"card-icon\">
              <img src=\"/themes/custom/matrics/img/icon1.png\" alt=\"\">
            </div>
          </div>
        </a>
      </div>
    {% endif %}
    <!-- Card  End -->
  </div>
  <div class=\"row card-parent customCard-width\">
    {% if chart['booking'] %}
      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        {{ chart['booking'] }}
        {#{{ drupal_view('data', 'block_1') }}#}
      </div>
    {% endif %}
    
    {% if chart['booking_cancel'] %}
      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        {{ chart['booking_cancel'] }}
      </div>
    {% endif %}
    
    {% if chart['total_spend'] %}
      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        {{ chart['total_spend'] }}
      </div>
    {% endif %}
    
    {% if chart['training_days'] %}
      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        {{ chart['training_days'] }}
      </div>
    {% endif %}
    
    {% if chart['training_providers'] %}
      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        {{ chart['training_providers'] }}
      </div>
    {% endif %}
    
    {% if chart['courses'] %}
      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        {{ chart['courses'] }}
      </div>
    {% endif %}
    
    {% if chart['employees'] %}
      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        {{ chart['employees'] }}
      </div>
    {% endif %}
    
    {% if chart['certificate'] %}
      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        {{ chart['certificate'] }}
      </div>
    {% endif %}
    
    {% if chart['training_gap_score'] %}
      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        {{ chart['training_gap_score'] }}
      </div>
    {% endif %}
    
    {% if chart['training_gap_count'] %}
      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        {{ chart['training_gap_count'] }}
      </div>
    {% endif %}
    {% if chart['to_be_actioned'] %}
      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        {{ chart['to_be_actioned'] }}
      </div>
    {% endif %}
    
    {% if chart['course_expired'] %}
      <div class=\"col-xl-6 col-sm-12 card-child-margin\">
        {{ chart['course_expired'] }}
      </div>
    {% endif %}
  </div>                
</div>
<!-- Dashboard Content End -->", "modules/custom/matrics_dashboard/templates/dashboard-manager.html.twig", "/home/matrics/public_html/yogita/web/modules/custom/matrics_dashboard/templates/dashboard-manager.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("set" => 1, "if" => 11);
        static $filters = array("escape" => 4);
        static $functions = array("drupal_view" => 181);

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if'],
                ['escape'],
                ['drupal_view']
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
