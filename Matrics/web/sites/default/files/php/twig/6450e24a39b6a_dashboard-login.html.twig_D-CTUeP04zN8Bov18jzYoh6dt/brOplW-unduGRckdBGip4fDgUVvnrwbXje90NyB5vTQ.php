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

/* modules/custom/matrics_dashboard/templates/dashboard-login.html.twig */
class __TwigTemplate_0a37e9321ff543cc196b7512855da83d5ec6b0428f38168ea868b1dbca46dc67 extends \Twig\Template
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
        echo "<div class=\"login-body\">
  <div id=\"wrapper\">
    <!--Main wrapper start-->
    <div class=\"login-section\">
      <div class=\"container\">
        <div class=\"login-wrapper\">
          <div class=\"row g-0 login_row\">
            <!--Login Left Start -->
            <div class=\"col-lg-4 login-left\">
              <div class=\"login_form\">
                <div class=\"login_head\">
                  <div class=\"login_head_left\">
                    <a href=\"#\">
                      <h3>
                        <img src=\"/themes/custom/matrics/img/logo-blue.svg\" alt=\"\">
                      </h3>
                      <p class=\"active\">MATRICS</p>
                    </a>
                  </div>
                  <div class=\"login_head_right\">
                    <a href=\"#\">
                      <div class=\"flag_img\">
                        <img src=\"/themes/custom/matrics/img/logotype_flag.png\" alt=\"\">
                      </div>
                      <p>STENA</p>
                    </a>
                  </div>
                </div>
                <div id=\"loginForm\">
                  ";
        // line 30
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["blocks"] ?? null), 30, $this->source), "html", null, true);
        echo "
                  <div class=\"forgot-password\">
                    <a href=\"";
        // line 32
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["items"] ?? null), "link", [], "any", false, false, true, 32), 32, $this->source), "html", null, true);
        echo "\">Contact Us</a>
                    <a href=\"";
        // line 33
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["items"] ?? null), "forgot_password", [], "any", false, false, true, 33), 33, $this->source), "html", null, true);
        echo "\">Forgot Password?</a>
                  </div>
                </div>
              </div>
            </div>
            <!--Login Left  End-->
            <!--Login Right Start -->
            <div class=\"col-lg-8 login-right\" style=\"background: url(";
        // line 40
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["items"] ?? null), "img", [], "any", false, false, true, 40), 40, $this->source), "html", null, true);
        echo ") no-repeat center \">
              <div class=\"login-content\">
                <h1>";
        // line 42
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["items"] ?? null), "title", [], "any", false, false, true, 42), 42, $this->source), "html", null, true);
        echo "</h1>
                <p>";
        // line 43
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["items"] ?? null), "description", [], "any", false, false, true, 43), 43, $this->source), "html", null, true);
        echo "</p>
              </div>
            </div>
            <!--Login Right End -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>";
    }

    public function getTemplateName()
    {
        return "modules/custom/matrics_dashboard/templates/dashboard-login.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  98 => 43,  94 => 42,  89 => 40,  79 => 33,  75 => 32,  70 => 30,  39 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("<div class=\"login-body\">
  <div id=\"wrapper\">
    <!--Main wrapper start-->
    <div class=\"login-section\">
      <div class=\"container\">
        <div class=\"login-wrapper\">
          <div class=\"row g-0 login_row\">
            <!--Login Left Start -->
            <div class=\"col-lg-4 login-left\">
              <div class=\"login_form\">
                <div class=\"login_head\">
                  <div class=\"login_head_left\">
                    <a href=\"#\">
                      <h3>
                        <img src=\"/themes/custom/matrics/img/logo-blue.svg\" alt=\"\">
                      </h3>
                      <p class=\"active\">MATRICS</p>
                    </a>
                  </div>
                  <div class=\"login_head_right\">
                    <a href=\"#\">
                      <div class=\"flag_img\">
                        <img src=\"/themes/custom/matrics/img/logotype_flag.png\" alt=\"\">
                      </div>
                      <p>STENA</p>
                    </a>
                  </div>
                </div>
                <div id=\"loginForm\">
                  {{ blocks }}
                  <div class=\"forgot-password\">
                    <a href=\"{{ items.link }}\">Contact Us</a>
                    <a href=\"{{ items.forgot_password }}\">Forgot Password?</a>
                  </div>
                </div>
              </div>
            </div>
            <!--Login Left  End-->
            <!--Login Right Start -->
            <div class=\"col-lg-8 login-right\" style=\"background: url({{ items.img }}) no-repeat center \">
              <div class=\"login-content\">
                <h1>{{ items.title }}</h1>
                <p>{{ items.description }}</p>
              </div>
            </div>
            <!--Login Right End -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>", "modules/custom/matrics_dashboard/templates/dashboard-login.html.twig", "/home/matrics/public_html/yogita/web/modules/custom/matrics_dashboard/templates/dashboard-login.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array();
        static $filters = array("escape" => 30);
        static $functions = array();

        try {
            $this->sandbox->checkSecurity(
                [],
                ['escape'],
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
