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

/* themes/custom/matrics/templates/page.html.twig */
class __TwigTemplate_d42ce6a147b8755606959ce5bb6a3886496066c4f9a04b31e6f6c7a7fb35ad34 extends \Twig\Template
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
        // line 53
        if (($context["logged_in"] ?? null)) {
            echo " 
  <div class=\"dashboard-page\">
    <!-- Dashboard page start CSS -->
    <div id=\"dash_wrapper\">
      <!-- Sidebar start -->
      <div id=\"sidebar-wrapper\" class=\"\">
        <!-- Sidebar Top Start -->
        <div class=\"sidebar_top\">
          <div class=\"top-bar-brand\">
            <a class=\"navbar-brand\" href=\"/\">
              <img  class=\"lighttheme-logo\" src=\"/themes/custom/matrics/img/whitelogo1.svg\" alt=\"logo\" title=\"\"><img  class=\"darktheme-logo\" style=\"display:none\" src=\"/themes/custom/matrics/img/whitelogo1-darktheme.svg\" alt=\"logo\" title=\"\">
            </a>
            <a href=\"javascript:void();\" class=\"hamburger-icon\"><img src=\"/themes/custom/matrics/img/hamburger_icon.svg\" alt=\"\"></a>
          </div>
          <div class=\"dashboard_user\">
            <h3>";
            // line 68
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["name"] ?? null), 68, $this->source), "html", null, true);
            echo "</h3>
            ";
            // line 69
            if ((($context["cus_session"] ?? null) != "")) {
                // line 70
                echo "              <p class=\"session\">Viewing ";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["cus_session"] ?? null), 70, $this->source), "html", null, true);
                echo "</p>
            ";
            } else {
                // line 71
                echo " 
              <p>Viewing All Customers</p>
            ";
            }
            // line 74
            echo "            ";
            // line 75
            echo "            ";
            // line 76
            echo "            ";
            // line 77
            echo "            ";
            // line 78
            echo "            <div class=\"user_img\">
              <a href=\"/user/";
            // line 79
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["uid"] ?? null), 79, $this->source), "html", null, true);
            echo "/edit?q=change_profile&destination=/\" class=\"use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\"><img src=\"";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["user_image"] ?? null), 79, $this->source), "html", null, true);
            echo "\" alt=\"\"></a>
            </div>
          </div>
        </div>
        <!-- Sidebar Top End -->
        <!-- Sidebar Menu Start -->
        ";
            // line 85
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_first", [], "any", false, false, true, 85), 85, $this->source), "html", null, true);
            echo "
        <p class=\"site_version\">Version: ";
            // line 86
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["version"] ?? null), 86, $this->source), "html", null, true);
            echo "</p>
        <!-- Sidebar Menu End -->
        <!-- Bootstrap CSS -->
      </div>
      <!-- Sidebar End -->
      <!-- Dashboard Right Start -->
      <div class=\"dashboard-rightbar\">
        <!-- Dashboard Header Start -->
        <header class=\"dashboard-header\">
          <div class=\"dashboard-inner\">
            <div class=\"desh-headerright\">
              <div class=\"header_icons\">
                ";
            // line 99
            echo "                ";
            // line 100
            echo "                ";
            // line 101
            echo "                ";
            // line 102
            echo "                ";
            // line 103
            echo "                <div class=\"search__form\">
                  <form>
                    <div class=\"form-group\">
                      <input class=\"form-control\" type=\"text\">
                      <a href=\"javascript:void(0);\" class=\"search-icon\"><img src=\"/themes/custom/matrics/img/search_icon.svg\" alt=\"\"></a>
                    </div>
                  </form>
                </div>
                ";
            // line 112
            echo "                <div class=\"logged-in\">
                  <a href=\"#\" class=\"profile-notification\"><img src=\"/themes/custom/matrics/img/bell_icon.svg\" alt=\"\"></a>
                  ";
            // line 114
            if ((($context["notification_status"] ?? null) == 1)) {
                // line 115
                echo "                    ";
                if ((($context["suggested_count"] ?? null) != 0)) {
                    // line 116
                    echo "                      <span class=\"suggested_count\">";
                    echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["suggested_count"] ?? null), 116, $this->source), "html", null, true);
                    echo "</span>
                    ";
                }
                // line 118
                echo "                    <ul class=\"notify\">
                      ";
                // line 119
                if ((($context["suggested"] ?? null) != 0)) {
                    // line 120
                    echo "                        <li>
                          ";
                    // line 121
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable(($context["suggested_msg"] ?? null));
                    foreach ($context['_seq'] as $context["key"] => $context["value"]) {
                        // line 122
                        echo "                            ";
                        echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->sandbox->ensureToStringAllowed($context["value"], 122, $this->source));
                        echo "
                          ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['key'], $context['value'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 124
                    echo "                        </li>
                      ";
                }
                // line 126
                echo "                    </ul>
                  ";
            }
            // line 128
            echo "                </div>
              </div>
              <div class=\"logged-in\">
                <div class=\"user_identity\">
                  <p>";
            // line 132
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["name"] ?? null), 132, $this->source), "html", null, true);
            echo "</p>
                  <span>";
            // line 133
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["role"] ?? null), 133, $this->source), "html", null, true);
            echo "</span>
                </div>
                <a href=\"#\" class=\"profile-detail\">
                  <span class=\"logged-img\">
                    <img src=\"";
            // line 137
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["user_image"] ?? null), 137, $this->source), "html", null, true);
            echo "\" alt=\"lprofile\">
                  </span>
                </a>
                <ul>
                  <li>
                    <a href=\"/user\">Profile </a>
                  </li>
                  <li>
                    <a href=\"/user/";
            // line 145
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["uid"] ?? null), 145, $this->source), "html", null, true);
            echo "/edit?q=change_profile&destination=/\" class=\"use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">Change Picture </a>
                  </li>
                  <li>
                    <a href=\"/administration\">Administration</a>
                  </li>
                  <li>
                    <a href=\"/switch-customer\">Switch Customer</a>
                  </li>
                  <li>
                    <a href=\"/user/logout\">Logout</a>
                  </li>
                </ul>
              </div>
              <label class=\"changesite-theme\" for=\"dark_mode\">
              <input type=\"checkbox\" id=\"dark_mode\"><span><img class=\"sun-img\" src=\"/themes/custom/matrics/img/sun.svg\" alt=\"\"> <img class=\"moon-img\" src=\"/themes/custom/matrics/img/moon.svg\" alt=\"\"> </span> </label>
            </div>
            <div class=\"desh-headerleft\">
              <div class=\"sidemenu-btn\">
                <a href=\"javascript:void();\" class=\"hamburger-icon\"><img src=\"/themes/custom/matrics/img/hamburger_icon.svg\" alt=\"\"></a>
              </div>
              <a class=\"logo-2\" href=\"javascript:void();\">
                <img class=\"lighttheme-logo\" src=\"/themes/custom/matrics/img/whitelogo2.svg\" alt=\"logo\" title=\"\"><img  class=\"darktheme-logo\" src=\"/themes/custom/matrics/img/whitelogo-darktheme.svg\" alt=\"logo\" title=\"\">
              </a>
            </div>
          </div>
        </header>
        <!-- Dashboard Header End -->
        ";
            // line 172
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "content", [], "any", false, false, true, 172), 172, $this->source), "html", null, true);
            echo "
      </div>
      <!-- Dashboard Right End -->
    </div>
    <!-- Dashboard Page End -->
  </div>
";
        } else {
            // line 179
            echo "  <div id=\"page-wrapper\">
    <div id=\"page\">
      <main id=\"content\" class=\"column main-content\" role=\"main\">
        <section class=\"section\">
          <a id=\"main-content\" tabindex=\"-1\"></a>
          ";
            // line 184
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "content", [], "any", false, false, true, 184), 184, $this->source), "html", null, true);
            echo "
        </section>
      </main>
      ";
            // line 187
            if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_first", [], "any", false, false, true, 187)) {
                // line 188
                echo "        <div id=\"sidebar-first\" class=\"column sidebar\">
          <aside class=\"section\" role=\"complementary\">
            ";
                // line 190
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_first", [], "any", false, false, true, 190), 190, $this->source), "html", null, true);
                echo "
          </aside>
        </div>
      ";
            }
            // line 194
            echo "      ";
            if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 194)) {
                // line 195
                echo "        <div id=\"sidebar-second\" class=\"column sidebar\">
          <aside class=\"section\" role=\"complementary\">
            ";
                // line 197
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 197), 197, $this->source), "html", null, true);
                echo "
          </aside>
        </div>
      ";
            }
            // line 201
            echo "    </div>
  </div>
";
        }
    }

    public function getTemplateName()
    {
        return "themes/custom/matrics/templates/page.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  285 => 201,  278 => 197,  274 => 195,  271 => 194,  264 => 190,  260 => 188,  258 => 187,  252 => 184,  245 => 179,  235 => 172,  205 => 145,  194 => 137,  187 => 133,  183 => 132,  177 => 128,  173 => 126,  169 => 124,  160 => 122,  156 => 121,  153 => 120,  151 => 119,  148 => 118,  142 => 116,  139 => 115,  137 => 114,  133 => 112,  123 => 103,  121 => 102,  119 => 101,  117 => 100,  115 => 99,  100 => 86,  96 => 85,  85 => 79,  82 => 78,  80 => 77,  78 => 76,  76 => 75,  74 => 74,  69 => 71,  63 => 70,  61 => 69,  57 => 68,  39 => 53,);
    }

    public function getSourceContext()
    {
        return new Source("{#
/**
 * @file
 * Bartik's theme implementation to display a single page.
 *
 * The doctype, html, head and body tags are not in this template. Instead they
 * can be found in the html.html.twig template normally located in the
 * core/modules/system directory.
 *
 * Available variables:
 *
 * General utility variables:
 * - base_path: The base URL path of the Drupal installation. Will usually be
 *   \"/\" unless you have installed Drupal in a sub-directory.
 * - is_front: A flag indicating if the current page is the front page.
 * - logged_in: A flag indicating if the user is registered and signed in.
 * - is_admin: A flag indicating if the user has permission to access
 *   administration pages.
 *
 * Site identity:
 * - front_page: The URL of the front page. Use this instead of base_path when
 *   linking to the front page. This includes the language domain or prefix.
 *
 * Page content (in order of occurrence in the default page.html.twig):
 * - node: Fully loaded node, if there is an automatically-loaded node
 *   associated with the page and the node ID is the second argument in the
 *   page's path (e.g. node/12345 and node/12345/revisions, but not
 *   comment/reply/12345).
 *
 * Regions:
 * - page.header: Items for the header region.
 * - page.highlighted: Items for the highlighted region.
 * - page.primary_menu: Items for the primary menu region.
 * - page.secondary_menu: Items for the secondary menu region.
 * - page.featured_top: Items for the featured top region.
 * - page.content: The main content of the current page.
 * - page.sidebar_first: Items for the first sidebar.
 * - page.sidebar_second: Items for the second sidebar.
 * - page.featured_bottom_first: Items for the first featured bottom region.
 * - page.featured_bottom_second: Items for the second featured bottom region.
 * - page.featured_bottom_third: Items for the third featured bottom region.
 * - page.footer_first: Items for the first footer column.
 * - page.footer_second: Items for the second footer column.
 * - page.footer_third: Items for the third footer column.
 * - page.footer_fourth: Items for the fourth footer column.
 * - page.footer_fifth: Items for the fifth footer column.
 * - page.breadcrumb: Items for the breadcrumb region.
 *
 * @see template_preprocess_page()
 * @see html.html.twig
 */
#}
{% if logged_in %} 
  <div class=\"dashboard-page\">
    <!-- Dashboard page start CSS -->
    <div id=\"dash_wrapper\">
      <!-- Sidebar start -->
      <div id=\"sidebar-wrapper\" class=\"\">
        <!-- Sidebar Top Start -->
        <div class=\"sidebar_top\">
          <div class=\"top-bar-brand\">
            <a class=\"navbar-brand\" href=\"/\">
              <img  class=\"lighttheme-logo\" src=\"/themes/custom/matrics/img/whitelogo1.svg\" alt=\"logo\" title=\"\"><img  class=\"darktheme-logo\" style=\"display:none\" src=\"/themes/custom/matrics/img/whitelogo1-darktheme.svg\" alt=\"logo\" title=\"\">
            </a>
            <a href=\"javascript:void();\" class=\"hamburger-icon\"><img src=\"/themes/custom/matrics/img/hamburger_icon.svg\" alt=\"\"></a>
          </div>
          <div class=\"dashboard_user\">
            <h3>{{ name }}</h3>
            {% if cus_session != '' %}
              <p class=\"session\">Viewing {{ cus_session }}</p>
            {% else %} 
              <p>Viewing All Customers</p>
            {% endif %}
            {#<p>{{ customer_position }}</p>#}
            {#<div class=\"customer-switch\">#}
            {#    {{ drupal_form('Drupal\\\\matrics_dashboard\\\\Form\\\\CustomerSelectForm') }}#}
            {#</div>#}
            <div class=\"user_img\">
              <a href=\"/user/{{ uid }}/edit?q=change_profile&destination=/\" class=\"use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\"><img src=\"{{ user_image }}\" alt=\"\"></a>
            </div>
          </div>
        </div>
        <!-- Sidebar Top End -->
        <!-- Sidebar Menu Start -->
        {{ page.sidebar_first }}
        <p class=\"site_version\">Version: {{ version }}</p>
        <!-- Sidebar Menu End -->
        <!-- Bootstrap CSS -->
      </div>
      <!-- Sidebar End -->
      <!-- Dashboard Right Start -->
      <div class=\"dashboard-rightbar\">
        <!-- Dashboard Header Start -->
        <header class=\"dashboard-header\">
          <div class=\"dashboard-inner\">
            <div class=\"desh-headerright\">
              <div class=\"header_icons\">
                {#{% if cus_session != '' %}#}
                {#  <div class=\"logged-in\">#}
                {#    <p class=\"session\">View as {{ cus_session }}</p>#}
                {#  </div>#}
                {#{% endif %}#}
                <div class=\"search__form\">
                  <form>
                    <div class=\"form-group\">
                      <input class=\"form-control\" type=\"text\">
                      <a href=\"javascript:void(0);\" class=\"search-icon\"><img src=\"/themes/custom/matrics/img/search_icon.svg\" alt=\"\"></a>
                    </div>
                  </form>
                </div>
                {#<a href=\"/task\"><img src=\"/themes/custom/matrics/img/bell_icon.svg\" alt=\"\"></a>#}
                <div class=\"logged-in\">
                  <a href=\"#\" class=\"profile-notification\"><img src=\"/themes/custom/matrics/img/bell_icon.svg\" alt=\"\"></a>
                  {% if notification_status == 1 %}
                    {% if suggested_count != 0 %}
                      <span class=\"suggested_count\">{{ suggested_count }}</span>
                    {% endif %}
                    <ul class=\"notify\">
                      {% if suggested != 0 %}
                        <li>
                          {% for key,value in suggested_msg %}
                            {{ value|raw }}
                          {% endfor %}
                        </li>
                      {% endif %}
                    </ul>
                  {% endif %}
                </div>
              </div>
              <div class=\"logged-in\">
                <div class=\"user_identity\">
                  <p>{{ name }}</p>
                  <span>{{ role }}</span>
                </div>
                <a href=\"#\" class=\"profile-detail\">
                  <span class=\"logged-img\">
                    <img src=\"{{ user_image }}\" alt=\"lprofile\">
                  </span>
                </a>
                <ul>
                  <li>
                    <a href=\"/user\">Profile </a>
                  </li>
                  <li>
                    <a href=\"/user/{{ uid }}/edit?q=change_profile&destination=/\" class=\"use-ajax\" data-dialog-options=\"{&quot;width&quot;:800}\" data-dialog-type=\"modal\">Change Picture </a>
                  </li>
                  <li>
                    <a href=\"/administration\">Administration</a>
                  </li>
                  <li>
                    <a href=\"/switch-customer\">Switch Customer</a>
                  </li>
                  <li>
                    <a href=\"/user/logout\">Logout</a>
                  </li>
                </ul>
              </div>
              <label class=\"changesite-theme\" for=\"dark_mode\">
              <input type=\"checkbox\" id=\"dark_mode\"><span><img class=\"sun-img\" src=\"/themes/custom/matrics/img/sun.svg\" alt=\"\"> <img class=\"moon-img\" src=\"/themes/custom/matrics/img/moon.svg\" alt=\"\"> </span> </label>
            </div>
            <div class=\"desh-headerleft\">
              <div class=\"sidemenu-btn\">
                <a href=\"javascript:void();\" class=\"hamburger-icon\"><img src=\"/themes/custom/matrics/img/hamburger_icon.svg\" alt=\"\"></a>
              </div>
              <a class=\"logo-2\" href=\"javascript:void();\">
                <img class=\"lighttheme-logo\" src=\"/themes/custom/matrics/img/whitelogo2.svg\" alt=\"logo\" title=\"\"><img  class=\"darktheme-logo\" src=\"/themes/custom/matrics/img/whitelogo-darktheme.svg\" alt=\"logo\" title=\"\">
              </a>
            </div>
          </div>
        </header>
        <!-- Dashboard Header End -->
        {{ page.content }}
      </div>
      <!-- Dashboard Right End -->
    </div>
    <!-- Dashboard Page End -->
  </div>
{% else %}
  <div id=\"page-wrapper\">
    <div id=\"page\">
      <main id=\"content\" class=\"column main-content\" role=\"main\">
        <section class=\"section\">
          <a id=\"main-content\" tabindex=\"-1\"></a>
          {{ page.content }}
        </section>
      </main>
      {% if page.sidebar_first %}
        <div id=\"sidebar-first\" class=\"column sidebar\">
          <aside class=\"section\" role=\"complementary\">
            {{ page.sidebar_first }}
          </aside>
        </div>
      {% endif %}
      {% if page.sidebar_second %}
        <div id=\"sidebar-second\" class=\"column sidebar\">
          <aside class=\"section\" role=\"complementary\">
            {{ page.sidebar_second }}
          </aside>
        </div>
      {% endif %}
    </div>
  </div>
{% endif %}", "themes/custom/matrics/templates/page.html.twig", "/home/matrics/public_html/yogita/web/themes/custom/matrics/templates/page.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("if" => 53, "for" => 121);
        static $filters = array("escape" => 68, "raw" => 122);
        static $functions = array();

        try {
            $this->sandbox->checkSecurity(
                ['if', 'for'],
                ['escape', 'raw'],
                []
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
